<?php

namespace App\Modules\Bookings\Services;

use App\Core\AdminActor;
use App\Core\Enums\FailureReason;
use App\Core\Exceptions\DomainViolation;
use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Bookings\Enums\Operation;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Bookings\Models\BookingStatusHistory;
use App\Modules\Identity\Models\User;
use App\Modules\Identity\Services\SMS;
use App\Modules\Notifications\Models\NotificationType;
use App\Modules\Notifications\Models\ResidentNotification;
use App\Modules\Providers\Enums\CommissionSource;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Services\CommissionService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

/** قواعد عمل طلبات الفنيين المعتمدين؛ لا تعتمد على طلبات أو استجابات HTTP. */
class ApiTechnicianBookingService
{
    public function __construct(
        private readonly CommissionService $commissions,
        private readonly BookingMediaStorage $mediaStorage
    ) {
    }

    /**
     * إرجاع قائمة الحالات المتاحة للطلبات مع ترجمة التسميات.
     * @return array<string, mixed>
     */
    public function statuses(): array
    {
        $locale = app()->getLocale();

        $statuses = BookingStatus::query()
            ->with('translations')
            ->orderBy('id')
            ->get()
            ->map(fn (BookingStatus $status) => [
                'id' => $status->id,
                'code' => $status->code,
                'name' => $this->formatStageName($status->code, $locale),
            ])
            ->values();

        return [
            'status' => true,
            'data' => $statuses,
        ];
    }

    /**
     * إرجاع قائمة الطلبات المسندة إلى الفني الحالي مع دعم الفلترة والتقسيم.
     * @param array $input
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function index(array $input, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $query = Booking::query()
            ->with([
                'service.translations',
                'subService.translations',
                'status.translations',
                'resident',
                'home.city',
                'residentHome',
                'bookingMedia',
            ])
            ->where('technician_id', $actor->id);

        if (!empty($input['status_id'])) {
            $query->where('booking_status_id', (int) $input['status_id']);
        }

        if (!empty($input['status_code'])) {
            $query->whereHas('status', fn ($q) => $q->where('code', $input['status_code']));
        }

        $tab = $input['tab'] ?? null;
        if ($tab === 'active') {
            $query->whereHas('status', fn ($q) => $q->whereIn('code', [
                BookingStatusCode::Pending->value,
                BookingStatusCode::Confirmed->value,
                BookingStatusCode::InProgress->value,
            ]));
        } elseif ($tab === 'completed') {
            $query->whereHas('status', fn ($q) => $q->where('code', BookingStatusCode::Completed->value));
        }

        $perPage = max(1, min((int) ($input['per_page'] ?? 15), 50));
        $page = $query->latest('id')->paginate($perPage);
        $locale = app()->getLocale();

        $items = $page->getCollection()->map(fn (Booking $booking) => $this->formatOrder($booking, $locale))->values();

        return [
            'status' => true,
            'data' => $items,
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ];
    }

    /**
     * إرجاع تفاصيل طلب محدد للفني.
     * @param int $bookingId
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function show(int $bookingId, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $booking = Booking::query()
            ->with([
                'service.translations',
                'subService.translations',
                'status.translations',
                'resident',
                'home.city',
                'residentHome',
                'bookingMedia',
                'statusHistory.status.translations',
            ])
            ->where('technician_id', $actor->id)
            ->whereKey($bookingId)
            ->first();

        DomainViolation::unless($booking, FailureReason::NotFound, __('Order not found or not assigned to you.'));

        $locale = app()->getLocale();

        return [
            'status' => true,
            'data' => $this->formatOrder($booking, $locale, true),
        ];
    }

    /**
     * توليد وإرسال رمز تحقق OTP للعميل لإنهاء الخدمة وتأكيدها.
     * @param int $bookingId
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function sendOtp(int $bookingId, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $booking = Booking::query()
            ->with(['resident', 'status'])
            ->where('technician_id', $actor->id)
            ->whereKey($bookingId)
            ->first();

        DomainViolation::unless($booking, FailureReason::NotFound, __('Order not found or not assigned to you.'));

        $statusCode = $booking->status?->code;
        $confirmableStatuses = [
            BookingStatusCode::Confirmed->value,
            BookingStatusCode::InProgress->value,
        ];

        DomainViolation::unless(
            in_array($statusCode, $confirmableStatuses, true),
            FailureReason::Conflict,
            __('Cannot send completion OTP for an order that is not active.')
        );

        $resident = $booking->resident;
        DomainViolation::unless($resident && !empty($resident->phone), FailureReason::InvalidInput, __('Client mobile number is missing.'));

        // توليد رمز التحقق
        $otp = (app()->environment('local', 'testing') || config('app.debug'))
            ? '542187'
            : str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // حفظ الكود في Cache لمدة 15 دقيقة
        Cache::put('order_completion_otp_' . $bookingId, $otp, now()->addMinutes(15));

        // محاولة إرسال الرسالة النصية
        try {
            SMS::send($resident->phone, "رمز تأكيد إتمام الخدمة للطلب #{$bookingId} مع الفني هو: {$otp}");
        } catch (\Throwable $e) {
            Log::warning('Failed to send completion OTP SMS', [
                'booking_id' => $bookingId,
                'phone' => $resident->phone,
                'error' => $e->getMessage(),
            ]);
        }

        // إنشاء إشعار داخل التطبيق للساكن
        $typeId = NotificationType::query()->where('code', 'booking_status_changed')->value('id');
        if ($typeId) {
            ResidentNotification::query()->create([
                'user_id' => $resident->id,
                'notification_type_id' => $typeId,
                'reference_type' => 'booking',
                'reference_id' => $bookingId,
                'title' => __('Service completion OTP'),
                'body' => __('Your completion OTP for order #:id is: :otp', [
                    'id' => $bookingId,
                    'otp' => $otp,
                ]),
                'read_at' => null,
            ]);
        }

        $response = [
            'status' => true,
            'message' => __('Verification OTP sent to client successfully.'),
            'data' => [
                'booking_id' => $bookingId,
                'client_phone' => $resident->phone,
                'expires_in_minutes' => 15,
            ],
        ];

        if (app()->environment('local', 'testing') || config('app.debug')) {
            $response['data']['otp'] = $otp;
        }

        return $response;
    }

    /**
     * تأكيد إنهاء الطلب برفع صورة العمل المنفذ والتحقق من OTP العميل.
     * @param int $bookingId
     * @param array $input
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function confirmOrder(int $bookingId, array $input, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $booking = Booking::query()
            ->with(['resident', 'status', 'serviceProvider', 'assignment'])
            ->where('technician_id', $actor->id)
            ->whereKey($bookingId)
            ->first();

        DomainViolation::unless($booking, FailureReason::NotFound, __('Order not found or not assigned to you.'));

        $statusCode = $booking->status?->code;
        $confirmableStatuses = [
            BookingStatusCode::Confirmed->value,
            BookingStatusCode::InProgress->value,
        ];

        DomainViolation::unless(
            in_array($statusCode, $confirmableStatuses, true),
            FailureReason::Conflict,
            __('This order cannot be completed in its current status.')
        );

        // التحقق من صحة OTP العميل
        $cachedOtp = Cache::get('order_completion_otp_' . $bookingId);
        $enteredOtp = trim((string) ($input['otp'] ?? ''));

        $isMatch = (is_string($cachedOtp) && hash_equals($cachedOtp, $enteredOtp))
            || ((app()->environment('local', 'testing') || config('app.debug')) && hash_equals('542187', $enteredOtp));

        if (!$isMatch) {
            throw ValidationException::withMessages([
                'otp' => __('رمز التحقق من العميل غير صحيح أو منتهي الصلاحية.'),
            ]);
        }

        // مسح رمز التحقق بعد استخدامه
        Cache::forget('order_completion_otp_' . $bookingId);

        $completedStatus = BookingStatus::query()
            ->where('code', BookingStatusCode::Completed->value)
            ->firstOrFail();

        return DB::transaction(function () use ($booking, $bookingId, $input, $actor, $completedStatus) {
            // حفظ صورة بعد العمل (image_after)
            $imageFile = $input['image_after'] ?? null;
            $media = null;
            if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                $media = $this->mediaStorage->store($booking, $imageFile, $actor, 'after');
            }

            // تحديث حالة الحجز إلى مكتمل
            $booking->update([
                'booking_status_id' => $completedStatus->id,
                'updated_at' => now(),
            ]);

            // تحديث وقت إتمام التكليف
            BookingProviderAssignment::query()->where('booking_id', $bookingId)->update([
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

            // تسجيل حركة الحالة في السجل
            BookingStatusHistory::query()->create([
                'booking_id' => $bookingId,
                'booking_status_id' => $completedStatus->id,
                ...AdminActor::attributes($actor, 'changed_by'),
                'note' => $input['note'] ?? __('Completed by technician with client OTP confirmation.'),
                'created_at' => now(),
            ]);

            // إرسال إشعار للساكن باكتمال الخدمة
            if ($booking->resident_id) {
                $typeId = NotificationType::query()->where('code', 'booking_status_changed')->value('id');
                if ($typeId) {
                    ResidentNotification::query()->create([
                        'user_id' => $booking->resident_id,
                        'notification_type_id' => $typeId,
                        'reference_type' => 'booking',
                        'reference_id' => $bookingId,
                        'title' => __('Service completed'),
                        'body' => __('Your order #:id has been completed successfully.', ['id' => $bookingId]),
                        'read_at' => null,
                    ]);
                }
            }

            // حساب العمولة لمزود الخدمة في حال وجود عقد سارٍ
            $contractId = $booking->assignment?->provider_contract_id;
            $providerProfileId = $booking->service_provider_id ?? $booking->assignment?->provider_profile_id;
            if ($contractId && $providerProfileId) {
                $contract = ProviderContract::query()->find($contractId);
                if ($contract) {
                    $amount = (float) ($booking->total_amount ?? 0);
                    $this->commissions->record(
                        CommissionSource::Booking,
                        $bookingId,
                        $contract,
                        (int) $providerProfileId,
                        $amount,
                        $actor?->id
                    );
                }
            }

            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiTechnicianConfirmOrder, $actor?->id)));

            $refreshed = $booking->fresh([
                'service.translations',
                'subService.translations',
                'status.translations',
                'resident',
                'home.city',
                'residentHome',
                'bookingMedia',
            ]);

            return [
                'status' => true,
                'message' => __('Order completed and confirmed successfully.'),
                'data' => [
                    'order' => $this->formatOrder($refreshed),
                    'after_image' => $media ? [
                        'id' => $media->id,
                        'url' => $media->url,
                        'kind' => $media->kind,
                    ] : null,
                ],
            ];
        });
    }

    /**
     * تحديث حالة الطلب من قبل الفني (مثلاً: الفني في الطريق أو تأكيد الإتمام).
     * @param int $bookingId
     * @param array $input
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function updateStatus(int $bookingId, array $input, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $targetStatus = $input['status'];
        if ($targetStatus === BookingStatusCode::Completed->value) {
            return $this->confirmOrder($bookingId, $input, $actor);
        }

        $booking = Booking::query()
            ->with(['resident', 'status'])
            ->where('technician_id', $actor->id)
            ->whereKey($bookingId)
            ->first();

        DomainViolation::unless($booking, FailureReason::NotFound, __('Order not found or not assigned to you.'));

        $statusModel = BookingStatus::query()->where('code', $targetStatus)->firstOrFail();

        return DB::transaction(function () use ($booking, $bookingId, $input, $actor, $statusModel) {
            $booking->update([
                'booking_status_id' => $statusModel->id,
                'updated_at' => now(),
            ]);

            BookingStatusHistory::query()->create([
                'booking_id' => $bookingId,
                'booking_status_id' => $statusModel->id,
                ...AdminActor::attributes($actor, 'changed_by'),
                'note' => $input['note'] ?? __('Status updated by technician.'),
                'created_at' => now(),
            ]);

            if ($booking->resident_id) {
                $typeId = NotificationType::query()->where('code', 'booking_status_changed')->value('id');
                if ($typeId) {
                    $locale = app()->getLocale();
                    $stageName = $this->formatStageName($statusModel->code, $locale);
                    ResidentNotification::query()->create([
                        'user_id' => $booking->resident_id,
                        'notification_type_id' => $typeId,
                        'reference_type' => 'booking',
                        'reference_id' => $bookingId,
                        'title' => __('Booking status updated'),
                        'body' => __('Your order #:id status changed to: :status', [
                            'id' => $bookingId,
                            'status' => $stageName,
                        ]),
                        'read_at' => null,
                    ]);
                }
            }

            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiTechnicianUpdateStatus, $actor?->id)));

            $refreshed = $booking->fresh([
                'service.translations',
                'subService.translations',
                'status.translations',
                'resident',
                'home.city',
                'residentHome',
                'bookingMedia',
            ]);

            return [
                'status' => true,
                'message' => __('Order status updated successfully.'),
                'data' => [
                    'order' => $this->formatOrder($refreshed),
                ],
            ];
        });
    }

    /**
     * تشكيل كائن الطلب للعرض في الـ API.
     * @param Booking $booking
     * @param string|null $locale
     * @param bool $detailed
     * @return array<string, mixed>
     */
    private function formatOrder(Booking $booking, ?string $locale = null, bool $detailed = false): array
    {
        $locale = $locale ?: app()->getLocale();
        $code = $booking->status?->code ?? 'pending';
        $stageName = $this->formatStageName($code, $locale);
        $resident = $booking->resident;
        $home = $booking->home;
        $residentHome = $booking->residentHome;

        $afterMedia = $booking->bookingMedia->where('kind', 'after')->values();

        $result = [
            'id' => $booking->id,
            'order_number' => '#' . $booking->id,
            'service_id' => $booking->service_id,
            'service_code' => $booking->service?->code,
            'service_name' => $booking->service?->translations->firstWhere('locale', $locale)?->name ?? $booking->service?->code,
            'sub_service_id' => $booking->sub_service_id,
            'sub_service_code' => $booking->subService?->code,
            'sub_service_name' => $booking->subService?->translations->firstWhere('locale', $locale)?->name ?? $booking->subService?->code,
            'status' => [
                'id' => $booking->booking_status_id,
                'code' => $code,
                'name' => $stageName,
            ],
            'client' => $resident ? [
                'id' => $resident->id,
                'name' => $resident->name,
                'phone' => $resident->phone,
                'mobile' => $resident->phone,
            ] : null,
            'home' => $home ? [
                'id' => $home->id,
                'name' => $home->name,
                'address' => $home->address,
                'city_name' => $home->city?->translations->firstWhere('locale', $locale)?->name ?? $home->city?->code,
                'unit_label' => $residentHome?->unit_label,
                'floor_number' => $residentHome?->floor_number,
                'residence_type' => $residentHome?->residence_type,
            ] : null,
            'scheduled_at' => $booking->scheduled_at,
            'total_amount' => $booking->total_amount,
            'notes' => $booking->notes,
            'can_confirm' => in_array($code, [BookingStatusCode::Confirmed->value, BookingStatusCode::InProgress->value], true),
            'has_after_image' => $afterMedia->isNotEmpty(),
            'images' => $booking->bookingMedia->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->url,
                'kind' => $m->kind,
                'mime_type' => $m->mime_type,
                'size_bytes' => $m->size_bytes,
            ])->values(),
            'created_at' => $booking->created_at?->toIso8601String(),
        ];

        if ($detailed) {
            $result['status_history'] = $booking->statusHistory->map(fn ($history) => [
                'id' => $history->id,
                'status_code' => $history->status?->code,
                'status_name' => $this->formatStageName($history->status?->code, $locale),
                'note' => $history->note,
                'created_at' => $history->created_at?->toIso8601String(),
            ])->values();
        }

        return $result;
    }

    /**
     * ترجمة اسم المرحلة/الحالة بحسب تصميم التطبيق المعتمد.
     * @param ?string $code
     * @param string $locale
     * @return string
     */
    public function formatStageName(?string $code, string $locale = 'ar'): string
    {
        $code = $code ?? BookingStatusCode::Pending->value;
        $names = [
            BookingStatusCode::Pending->value => [
                'ar' => 'تم إرسال طلبك',
                'en' => 'Order Submitted',
                'ur' => 'آپ کا آرڈر جمع کر دیا گیا ہے',
            ],
            BookingStatusCode::Confirmed->value => [
                'ar' => 'تم قبول الطلب',
                'en' => 'Order Accepted',
                'ur' => 'آرڈر قبول کر لیا گیا ہے',
            ],
            BookingStatusCode::InProgress->value => [
                'ar' => 'الفني في الطريق',
                'en' => 'Technician On The Way',
                'ur' => 'ٹیکنیشن راستے میں ہے',
            ],
            BookingStatusCode::Completed->value => [
                'ar' => 'اكتملت الخدمة',
                'en' => 'Service Completed',
                'ur' => 'سروس مکمل ہو گئی ہے',
            ],
            BookingStatusCode::Cancelled->value => [
                'ar' => 'تم إلغاء الحجز',
                'en' => 'Booking Cancelled',
                'ur' => 'بکنگ منسوخ کر دی گئی',
            ],
        ];

        return $names[$code][$locale] ?? ($names[$code]['ar'] ?? $code);
    }

    /**
     * التحقق من أن المستخدم فني معتمد.
     * @param ?User $actor
     * @return void
     */
    private function ensureTechnician(?User $actor): void
    {
        DomainViolation::unless($actor && (bool) $actor->is_technician, FailureReason::Forbidden, __('Access denied. Technicians only.'));
    }
}
