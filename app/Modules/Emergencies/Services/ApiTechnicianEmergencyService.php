<?php

namespace App\Modules\Emergencies\Services;

use App\Core\AdminActor;
use App\Core\Enums\FailureReason;
use App\Core\Exceptions\DomainViolation;
use App\Modules\Emergencies\Enums\EmergencyStatusCode;
use App\Modules\Emergencies\Enums\Operation;
use App\Modules\Emergencies\Events\EmergenciesChanged;
use App\Modules\Emergencies\Models\EmergencyProviderAssignment;
use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Emergencies\Models\EmergencyRequestStatus;
use App\Modules\Emergencies\Models\EmergencyRequestUpdate;
use App\Modules\Identity\Models\User;
use App\Modules\Notifications\Models\NotificationType;
use App\Modules\Notifications\Models\ResidentNotification;
use App\Core\Support\SMS;
use App\Modules\Emergencies\Models\EmergencySetting;
use App\Modules\Providers\Enums\CommissionSource;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Services\CommissionService;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

/** قواعد عمل طلبات وبلاغات الطوارئ للفنيين المعتمدين. */
class ApiTechnicianEmergencyService
{
    public function __construct(private readonly CommissionService $commissions)
    {
    }

    /**
     * إرجاع قائمة حالات طلبات الطوارئ المعتمدة للفني.
     * @return array<string, mixed>
     */
    public function statuses(): array
    {
        $locale = app()->getLocale();

        $statuses = EmergencyRequestStatus::query()
            ->with('translations')
            ->orderBy('id')
            ->get()
            ->map(fn (EmergencyRequestStatus $status) => [
                'id' => $status->id,
                'code' => $status->code,
                'name' => $status->translations->firstWhere('locale', $locale)?->name ?? $status->code,
                'badge_label' => in_array($status->code, [EmergencyStatusCode::Assigned->value, EmergencyStatusCode::OnTheWay->value, EmergencyStatusCode::Received->value], true)
                    ? ($locale === 'ar' ? 'قيد المتابعة' : 'In Progress')
                    : ($locale === 'ar' ? 'تمت المعالجة' : 'Resolved'),
            ])
            ->values();

        return [
            'status' => true,
            'data' => $statuses,
        ];
    }

    /**
     * إرجاع قائمة طلبات الطوارئ المسندة للفني مع دعم التبويبات والفلترة.
     * @param array $input
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function index(array $input, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $query = EmergencyRequest::query()
            ->with([
                'emergencyType.translations',
                'status.translations',
                'resident',
                'home.city',
                'residentHome',
                'assignment.providerProfile',
                'emergencyRequestUpdates',
            ])
            ->where(function ($q) use ($actor) {
                $q->where('handled_by', $actor->id);
                if ($actor->provider_profile_id) {
                    $q->orWhere(function ($sub) use ($actor) {
                        $sub->whereNull('handled_by')
                            ->whereHas('assignment', fn ($a) => $a->where('provider_profile_id', $actor->provider_profile_id));
                    });
                }
            });

        if (!empty($input['status_id'])) {
            $query->where('status_id', (int) $input['status_id']);
        }

        if (!empty($input['status_code'])) {
            $query->whereHas('status', fn ($q) => $q->where('code', $input['status_code']));
        }

        $tab = $input['tab'] ?? null;
        if (in_array($tab, ['active', 'in_progress'], true)) {
            $query->whereHas('status', fn ($q) => $q->whereIn('code', [
                EmergencyStatusCode::Received->value,
                EmergencyStatusCode::Assigned->value,
                EmergencyStatusCode::OnTheWay->value,
            ]));
        } elseif (in_array($tab, ['completed', 'resolved'], true)) {
            $query->whereHas('status', fn ($q) => $q->where('code', EmergencyStatusCode::Resolved->value));
        }

        $perPage = max(1, min((int) ($input['per_page'] ?? 15), 50));
        $page = $query->latest('id')->paginate($perPage);
        $locale = app()->getLocale();

        $items = $page->getCollection()->map(fn (EmergencyRequest $request) => $this->formatEmergency($request, $locale))->values();

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
     * إرجاع تفاصيل طلب طوارئ محدد.
     * @param int $requestId
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function show(int $requestId, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $request = EmergencyRequest::query()
            ->with([
                'emergencyType.translations',
                'status.translations',
                'resident',
                'home.city',
                'residentHome',
                'assignment.providerProfile',
                'emergencyRequestUpdates.status.translations',
            ])
            ->where(function ($q) use ($actor) {
                $q->where('handled_by', $actor->id);
                if ($actor->provider_profile_id) {
                    $q->orWhere(function ($sub) use ($actor) {
                        $sub->whereNull('handled_by')
                            ->whereHas('assignment', fn ($a) => $a->where('provider_profile_id', $actor->provider_profile_id));
                    });
                }
            })
            ->whereKey($requestId)
            ->first();

        DomainViolation::unless($request, FailureReason::NotFound, __('Emergency request not found or not assigned to you.'));

        $locale = app()->getLocale();

        return [
            'status' => true,
            'data' => $this->formatEmergency($request, $locale, true),
        ];
    }

    /**
     * تحديث حالة طلب الطوارئ (مثلاً: الفريق في الطريق / قيد المتابعة أو تمت المعالجة).
     * @param int $requestId
     * @param array $input
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function updateStatus(int $requestId, array $input, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $targetStatus = $input['status'];
        $request = EmergencyRequest::query()
            ->with(['resident', 'status', 'assignment'])
            ->where(function ($q) use ($actor) {
                $q->where('handled_by', $actor->id);
                if ($actor->provider_profile_id) {
                    $q->orWhere(function ($sub) use ($actor) {
                        $sub->whereNull('handled_by')
                            ->whereHas('assignment', fn ($a) => $a->where('provider_profile_id', $actor->provider_profile_id));
                    });
                }
            })
            ->whereKey($requestId)
            ->first();

        DomainViolation::unless($request, FailureReason::NotFound, __('Emergency request not found or not assigned to you.'));

        $statusModel = EmergencyRequestStatus::query()->where('code', $targetStatus)->firstOrFail();

        return DB::transaction(function () use ($request, $requestId, $input, $actor, $statusModel, $targetStatus) {
            $isResolved = $targetStatus === EmergencyStatusCode::Resolved->value;

            $request->update([
                'status_id' => $statusModel->id,
                'handled_by' => $actor->id,
                'dispatched_at' => $request->dispatched_at ?? now(),
                'resolved_at' => $isResolved ? now() : $request->resolved_at,
                'updated_at' => now(),
            ]);

            if ($isResolved) {
                EmergencyProviderAssignment::query()->where('emergency_request_id', $requestId)->update([
                    'completed_at' => now(),
                    'updated_at' => now(),
                ]);
                $this->recordCommission($request, $actor, $input);
            }

            EmergencyRequestUpdate::query()->create([
                'emergency_request_id' => $requestId,
                'status_id' => $statusModel->id,
                ...AdminActor::attributes($actor, 'updated_by'),
                'note' => $input['note'] ?? ($isResolved ? __('Resolved by technician.') : __('Technician is on the way.')),
                'created_at' => now(),
            ]);

            if ($request->resident_id) {
                $typeId = NotificationType::query()->where('code', 'emergency_status_changed')->value('id')
                    ?? NotificationType::query()->where('code', 'booking_status_changed')->value('id');

                if ($typeId) {
                    $locale = app()->getLocale();
                    $statusName = $statusModel->translations->firstWhere('locale', $locale)?->name ?? $statusModel->code;
                    ResidentNotification::query()->create([
                        'user_id' => $request->resident_id,
                        'notification_type_id' => $typeId,
                        'reference_type' => 'emergency',
                        'reference_id' => $requestId,
                        'title' => __('Emergency request update'),
                        'body' => __('Your emergency request :ref status is now: :status', [
                            'ref' => $request->reference,
                            'status' => $statusName,
                        ]),
                        'read_at' => null,
                    ]);
                }
            }

            DB::afterCommit(fn () => event(new EmergenciesChanged(Operation::ApiTechnicianEmergencyUpdate, $actor?->id)));

            $refreshed = $request->fresh([
                'emergencyType.translations',
                'status.translations',
                'resident',
                'home.city',
                'residentHome',
                'assignment.providerProfile',
                'emergencyRequestUpdates.status.translations',
            ]);

            return [
                'status' => true,
                'message' => $isResolved ? __('Emergency request marked as resolved.') : __('Emergency request status updated to on the way.'),
                'data' => [
                    'emergency' => $this->formatEmergency($refreshed),
                ],
            ];
        });
    }

    /**
     * توليد وإرسال رمز تحقق OTP للعميل لإنهاء ومعالجة طلب الطوارئ.
     * @param int $requestId
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function sendOtp(int $requestId, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $request = EmergencyRequest::query()
            ->with(['resident', 'status'])
            ->where(function ($q) use ($actor) {
                $q->where('handled_by', $actor->id);
                if ($actor->provider_profile_id) {
                    $q->orWhere(function ($sub) use ($actor) {
                        $sub->whereNull('handled_by')
                            ->whereHas('assignment', fn ($a) => $a->where('provider_profile_id', $actor->provider_profile_id));
                    });
                }
            })
            ->whereKey($requestId)
            ->first();

        DomainViolation::unless($request, FailureReason::NotFound, __('Emergency request not found or not assigned to you.'));

        $statusCode = $request->status?->code;
        $confirmableStatuses = [
            EmergencyStatusCode::Received->value,
            EmergencyStatusCode::Assigned->value,
            EmergencyStatusCode::OnTheWay->value,
        ];

        DomainViolation::unless(
            in_array($statusCode, $confirmableStatuses, true),
            FailureReason::Conflict,
            __('Cannot send completion OTP for an emergency request that is not active.')
        );

        $phone = $request->contact_phone ?: $request->resident?->phone;
        DomainViolation::unless(!empty($phone), FailureReason::InvalidInput, __('Client mobile number is missing.'));

        // توليد رمز التحقق
        $otp = (app()->environment('local', 'testing') || config('app.debug'))
            ? '542187'
            : str_pad((string) random_int(0, 999999), 6, '0', STR_PAD_LEFT);

        // حفظ الكود في Cache لمدة 15 دقيقة
        Cache::put('emergency_completion_otp_' . $requestId, $otp, now()->addMinutes(15));

        // محاولة إرسال الرسالة النصية
        try {
            SMS::send($phone, "رمز تأكيد معالجة بلاغ الطوارئ #{$request->reference} مع الفني هو: {$otp}");
        } catch (\Throwable $e) {
            Log::warning('Failed to send emergency completion OTP SMS', [
                'emergency_id' => $requestId,
                'phone' => $phone,
                'error' => $e->getMessage(),
            ]);
        }

        // إنشاء إشعار داخل التطبيق للساكن
        if ($request->resident_id) {
            $typeId = NotificationType::query()->where('code', 'emergency_status_changed')->value('id')
                ?? NotificationType::query()->where('code', 'booking_status_changed')->value('id');

            if ($typeId) {
                ResidentNotification::query()->create([
                    'user_id' => $request->resident_id,
                    'notification_type_id' => $typeId,
                    'reference_type' => 'emergency',
                    'reference_id' => $requestId,
                    'title' => __('Emergency completion OTP'),
                    'body' => __('Your completion OTP for emergency request :ref is: :otp', [
                        'ref' => $request->reference,
                        'otp' => $otp,
                    ]),
                    'read_at' => null,
                ]);
            }
        }

        DB::afterCommit(fn () => event(new EmergenciesChanged(Operation::ApiTechnicianEmergencySendOtp, $actor?->id)));

        $response = [
            'status' => true,
            'message' => __('Verification OTP sent to client successfully.'),
            'data' => [
                'emergency_id' => $requestId,
                'client_phone' => $phone,
                'expires_in_minutes' => 15,
            ],
        ];

        if (app()->environment('local', 'testing') || config('app.debug')) {
            $response['data']['otp'] = $otp;
        }

        return $response;
    }

    /**
     * تأكيد معالجة طلب الطوارئ بالرمز OTP وصورة الإنجاز وملاحظة اختيارية.
     * @param int $requestId
     * @param array $input
     * @param ?User $actor
     * @return array<string, mixed>
     */
    public function confirm(int $requestId, array $input, ?User $actor): array
    {
        $this->ensureTechnician($actor);

        $request = EmergencyRequest::query()
            ->with(['resident', 'status', 'assignment'])
            ->where(function ($q) use ($actor) {
                $q->where('handled_by', $actor->id);
                if ($actor->provider_profile_id) {
                    $q->orWhere(function ($sub) use ($actor) {
                        $sub->whereNull('handled_by')
                            ->whereHas('assignment', fn ($a) => $a->where('provider_profile_id', $actor->provider_profile_id));
                    });
                }
            })
            ->whereKey($requestId)
            ->first();

        DomainViolation::unless($request, FailureReason::NotFound, __('Emergency request not found or not assigned to you.'));

        $statusCode = $request->status?->code;
        $confirmableStatuses = [
            EmergencyStatusCode::Received->value,
            EmergencyStatusCode::Assigned->value,
            EmergencyStatusCode::OnTheWay->value,
        ];

        DomainViolation::unless(
            in_array($statusCode, $confirmableStatuses, true),
            FailureReason::Conflict,
            __('This emergency request cannot be completed in its current status.')
        );

        // التحقق من صحة OTP العميل
        $cachedOtp = Cache::get('emergency_completion_otp_' . $requestId);
        $enteredOtp = trim((string) ($input['otp'] ?? ''));

        $isMatch = (is_string($cachedOtp) && hash_equals($cachedOtp, $enteredOtp))
            || ((app()->environment('local', 'testing') || config('app.debug')) && hash_equals('542187', $enteredOtp));

        if (!$isMatch) {
            throw ValidationException::withMessages([
                'otp' => __('رمز التحقق من العميل غير صحيح أو منتهي الصلاحية.'),
            ]);
        }

        // مسح رمز التحقق بعد استخدامه
        Cache::forget('emergency_completion_otp_' . $requestId);

        $resolvedStatus = EmergencyRequestStatus::query()->where('code', EmergencyStatusCode::Resolved->value)->firstOrFail();

        return DB::transaction(function () use ($request, $requestId, $input, $actor, $resolvedStatus) {
            $imageFile = $input['image_after'] ?? null;
            $imageUrl = null;
            if ($imageFile instanceof \Illuminate\Http\UploadedFile) {
                $path = $imageFile->store('emergencies', 'public');
                $imageUrl = Storage::disk('public')->url($path);
            }

            $request->update([
                'status_id' => $resolvedStatus->id,
                'handled_by' => $actor->id,
                'dispatched_at' => $request->dispatched_at ?? now(),
                'resolved_at' => now(),
                'updated_at' => now(),
            ]);

            EmergencyProviderAssignment::query()->where('emergency_request_id', $requestId)->update([
                'completed_at' => now(),
                'updated_at' => now(),
            ]);

            $this->recordCommission($request, $actor, $input);

            $note = $input['note'] ?? __('Resolved by technician with client OTP confirmation.');
            if ($imageUrl) {
                $note .= " | [Photo: {$imageUrl}]";
            }

            EmergencyRequestUpdate::query()->create([
                'emergency_request_id' => $requestId,
                'status_id' => $resolvedStatus->id,
                ...AdminActor::attributes($actor, 'updated_by'),
                'note' => $note,
                'created_at' => now(),
            ]);

            if ($request->resident_id) {
                $typeId = NotificationType::query()->where('code', 'emergency_status_changed')->value('id')
                    ?? NotificationType::query()->where('code', 'booking_status_changed')->value('id');

                if ($typeId) {
                    ResidentNotification::query()->create([
                        'user_id' => $request->resident_id,
                        'notification_type_id' => $typeId,
                        'reference_type' => 'emergency',
                        'reference_id' => $requestId,
                        'title' => __('Emergency resolved'),
                        'body' => __('Your emergency request :ref has been resolved successfully.', [
                            'ref' => $request->reference,
                        ]),
                        'read_at' => null,
                    ]);
                }
            }

            DB::afterCommit(fn () => event(new EmergenciesChanged(Operation::ApiTechnicianEmergencyConfirm, $actor?->id)));

            $refreshed = $request->fresh([
                'emergencyType.translations',
                'status.translations',
                'resident',
                'home.city',
                'residentHome',
                'assignment.providerProfile',
                'emergencyRequestUpdates.status.translations',
            ]);

            $locale = app()->getLocale();

            return [
                'status' => true,
                'message' => __('Emergency request confirmed and resolved successfully with client OTP.'),
                'data' => [
                    'emergency' => $this->formatEmergency($refreshed, $locale, true),
                    'image_after_url' => $imageUrl,
                ],
            ];
        });
    }

    /**
     * تشكيل كائن طلب الطوارئ للعرض في الـ API مطابقاً لتصميم كروت الواجهة.
     * @param EmergencyRequest $request
     * @param string|null $locale
     * @param bool $detailed
     * @return array<string, mixed>
     */
    private function formatEmergency(EmergencyRequest $request, ?string $locale = null, bool $detailed = false): array
    {
        $locale = $locale ?: app()->getLocale();
        $code = $request->status?->code ?? 'received';
        $statusName = $request->status?->translations->firstWhere('locale', $locale)?->name ?? $code;
        $typeName = $request->emergencyType?->translations->firstWhere('locale', $locale)?->name ?? $request->emergencyType?->code;
        $resident = $request->resident;
        $home = $request->home;
        $residentHome = $request->residentHome;

        $isInProgress = in_array($code, [
            EmergencyStatusCode::Assigned->value,
            EmergencyStatusCode::OnTheWay->value,
            EmergencyStatusCode::Received->value,
        ], true);

        $badgeLabel = $isInProgress
            ? ($locale === 'ar' ? 'قيد المتابعة' : 'In Progress')
            : ($locale === 'ar' ? 'تمت المعالجة' : 'Resolved');

        $badgeVariant = $isInProgress ? 'warning' : 'success';

        $locationParts = array_filter([
            $home?->name,
            $residentHome?->unit_label ? ('وحدة ' . $residentHome->unit_label) : null,
            $residentHome?->floor_number ? ('الدور ' . $residentHome->floor_number) : null,
        ]);
        $locationString = !empty($locationParts) ? implode(' • ', $locationParts) : ($request->location_details ?: '');

        $lastUpdate = $request->emergencyRequestUpdates->sortByDesc('created_at')->first();
        $lastUpdateString = $lastUpdate?->created_at ? $this->formatRelativeTime($lastUpdate->created_at, $locale) : null;

        $result = [
            'id' => $request->id,
            'reference' => $request->reference,
            'title' => $typeName,
            'type_code' => $request->emergencyType?->code,
            'status' => [
                'id' => $request->status_id,
                'code' => $code,
                'name' => $statusName,
                'badge_label' => $badgeLabel,
                'badge_variant' => $badgeVariant,
            ],
            'requested_at' => $request->requested_at?->toIso8601String(),
            'time_formatted' => $request->requested_at ? $this->formatDateTime($request->requested_at, $locale) : null,
            'location' => $locationString,
            'home' => $home ? [
                'id' => $home->id,
                'name' => $home->name,
                'unit_label' => $residentHome?->unit_label,
                'floor_number' => $residentHome?->floor_number,
                'city_name' => $home->city?->translations->firstWhere('locale', $locale)?->name ?? $home->city?->code,
            ] : null,
            'contact_phone' => $request->contact_phone ?: $resident?->phone,
            'client_name' => $resident?->name,
            'description' => $request->description,
            'last_update' => $lastUpdateString,
            'can_update' => $code !== EmergencyStatusCode::Resolved->value,
            'can_resolve' => $code !== EmergencyStatusCode::Resolved->value,
        ];

        if ($detailed) {
            $result['updates_history'] = $request->emergencyRequestUpdates->sortByDesc('id')->map(fn (EmergencyRequestUpdate $u) => [
                'id' => $u->id,
                'status_code' => $u->emergencyRequest?->status?->code,
                'note' => $u->note,
                'created_at' => $u->created_at?->toIso8601String(),
                'time_formatted' => $u->created_at ? $this->formatDateTime($u->created_at, $locale) : null,
            ])->values();
        }

        return $result;
    }

    private function formatDateTime(Carbon|string $datetime, string $locale = 'ar'): string
    {
        $dt = is_string($datetime) ? Carbon::parse($datetime) : $datetime;
        if ($dt->isToday()) {
            return ($locale === 'ar' ? 'اليوم، ' : 'Today, ') . $dt->format('g:i A');
        }
        if ($dt->isYesterday()) {
            return ($locale === 'ar' ? 'أمس، ' : 'Yesterday, ') . $dt->format('g:i A');
        }

        return $dt->format('Y-m-d g:i A');
    }

    private function formatRelativeTime(Carbon|string $datetime, string $locale = 'ar'): string
    {
        $dt = is_string($datetime) ? Carbon::parse($datetime) : $datetime;
        if ($locale === 'ar') {
            $diffMinutes = (int) $dt->diffInMinutes();
            if ($diffMinutes < 1) {
                return 'الآن';
            }
            if ($diffMinutes < 60) {
                return "قبل {$diffMinutes} دقائق";
            }
            if ($dt->isToday()) {
                return 'اليوم';
            }
            if ($dt->isYesterday()) {
                return 'أمس';
            }
        }

        return $dt->diffForHumans();
    }

    private function ensureTechnician(?User $actor): void
    {
        DomainViolation::unless($actor && (bool) $actor->is_technician, FailureReason::Forbidden, __('Access denied. Technicians only.'));
    }

    /**
     * احتساب وتسجيل عمولة الطوارئ لمزود الخدمة في حال وجود عقد سارٍ.
     *
     * @param EmergencyRequest $request
     * @param User|null $actor
     * @param array<string, mixed> $input
     * @return void
     */
    private function recordCommission(EmergencyRequest $request, ?User $actor, array $input = []): void
    {
        $assignment = $request->assignment
            ?? EmergencyProviderAssignment::query()->where('emergency_request_id', $request->id)->first();
        $providerProfileId = $assignment?->provider_profile_id ?? $actor?->provider_profile_id;
        $contractId = $assignment?->provider_contract_id;

        if (!$providerProfileId) {
            return;
        }

        $contract = $contractId
            ? ProviderContract::query()->find($contractId)
            : ProviderContract::query()->where('provider_profile_id', $providerProfileId)
                ->whereHas('status', fn ($query) => $query->where('code', ContractStatusCode::Active->value))
                ->whereDate('starts_on', '<=', today())
                ->where(fn ($query) => $query->whereNull('ends_on')->orWhereDate('ends_on', '>=', today()))
                ->latest('starts_on')
                ->first();

        if (!$contract) {
            return;
        }

        // جلب سعر الطوارئ المعتمد للمنطقة من جدول الإعدادات ديناميكياً
        $request->loadMissing('home.city');
        $regionId = $request->home?->city?->region_id;
        $setting = $regionId ? EmergencySetting::forRegion($regionId) : null;

        // استخدام المبلغ المدخل إن وجد، أو سعر المنطقة المعتمد من الإعدادات
        $grossAmount = isset($input['gross_amount']) && is_numeric($input['gross_amount'])
            ? (float) $input['gross_amount']
            : ($setting && $setting->is_active ? (float) $setting->price : 0.0);

        if ($grossAmount > 0) {
            $this->commissions->record(
                CommissionSource::Emergency,
                $request->id,
                $contract,
                (int) $providerProfileId,
                $grossAmount,
                $actor?->id
            );
        }
    }
}
