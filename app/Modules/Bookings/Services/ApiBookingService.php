<?php

namespace App\Modules\Bookings\Services;
use App\Core\Exceptions\DomainViolation;
use App\Core\Enums\FailureReason;
use App\Modules\Bookings\Enums\BookingStatusCode;

use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderProfile;

use App\Modules\Identity\Models\User;
use Illuminate\Support\Facades\DB;
use App\Modules\Bookings\Events\BookingsChanged;
use App\Modules\Bookings\Enums\Operation;

/** قواعد عمل Bookings؛ تستقبل بيانات متحققاً منها دون الاعتماد على HTTP. */
class ApiBookingService
{
    /**
     * @param BookingMediaStorage $mediaStorage
     */
    public function __construct(
        private readonly BookingMediaStorage $mediaStorage = new BookingMediaStorage()
    ) {}

    /**
     * قراءة بيانات المجال مع الحفاظ على قيود الوصول.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function index(array $input, ?User $actor): mixed
    {
        $query = Booking::with(['home', 'service.translations', 'subService.translations', 'status.translations', 'serviceProvider', 'bookingMedia'])
            ->where('resident_id', $actor->id);

        if (!empty($input['status_id'])) {
            $query->where('booking_status_id', $input['status_id']);
        }

        if (!empty($input['status_code'])) {
            $query->whereHas('status', fn ($q) => $q->where('code', $input['status_code']));
        }

        if (!empty($input['resident_home_id'])) {
            $query->where('resident_home_id', $input['resident_home_id']);
        }

        $page = $query->latest()->paginate(max(1, min((int) ($input['per_page'] ?? 15), 50)));
        $locale = app()->getLocale();
        $items = $page->getCollection()->map(fn (Booking $booking) => [
            'id' => $booking->id,
            'service_name' => $booking->service->translations->firstWhere('locale', $locale)?->name ?? $booking->service->code,
            'sub_service_id' => $booking->sub_service_id,
            'sub_service_name' => $booking->subService?->translations->firstWhere('locale', $locale)?->name ?? $booking->subService?->code,
            'status_id' => $booking->booking_status_id,
            'status_name' => $booking->status?->translations->firstWhere('locale', $locale)?->name ?? $booking->status?->translations->first()?->name ?? $booking->status?->code,
            'status_code' => $booking->status?->code,
            'scheduled_at' => $booking->scheduled_at,
            'total_amount' => $booking->total_amount,
            'service_provider_id' => $booking->service_provider_id,
            'service_provider_name' => $booking->serviceProvider?->company_name,
            'images' => $booking->bookingMedia->map(fn ($m) => [
                'id' => $m->id,
                'url' => $m->url,
                'kind' => $m->kind,
                'mime_type' => $m->mime_type,
                'size_bytes' => $m->size_bytes,
            ])->values(),
        ])->values();

        return ['data' => $items, 'meta' => [
            'current_page' => $page->currentPage(), 'last_page' => $page->lastPage(),
            'per_page' => $page->perPage(), 'total' => $page->total(),
        ]];
    }

    /**
     * جلب قائمة حالات الحجوزات لتطبيق الجوال مع ترجمة الاسم بحسب لغة المستخدم.
     * @return array<string, mixed>
     */
    public function statuses(): array
    {
        $locale = app()->getLocale();

        $statuses = BookingStatus::with('translations')
            ->orderBy('id')
            ->get()
            ->map(fn (BookingStatus $status) => [
                'id' => $status->id,
                'code' => $status->code,
                'name' => $status->translations->firstWhere('locale', $locale)?->name
                    ?? $status->translations->first()?->name
                    ?? $status->code,
            ])
            ->values();

        return ['data' => $statuses];
    }

    /**
     * جلب قائمة مزودي الخدمة المعتمدين لتطبيق الجوال.
     * @return array<string, mixed>
     */
    public function serviceProviders(?int $serviceId = null): array
    {
        $providers = ProviderProfile::query()
            ->where('is_approved', true)
            ->when($serviceId, fn ($query) => $query->whereHas('services', fn ($services) => $services->whereKey($serviceId)))
            ->with('user:id,name,phone')
            ->orderBy('company_name')
            ->get()
            ->map(fn (ProviderProfile $p) => [
                'id' => $p->id,
                'company_name' => $p->company_name,
                'contact_name' => $p->user?->name,
                'phone' => $p->user?->phone,
            ]);

        return ['data' => $providers];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function store(array $input, ?User $actor): mixed
    {
        return DB::transaction(function () use ($input, $actor) {
            $result = $this->performStore($input, $actor);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiBookingStore, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performStore(array $input, ?User $actor): mixed
    {
        $images = $input['images'] ?? [];
        $data = $input;
        unset($data['images']);

        $booking = DB::transaction(function () use ($input, $actor, $data, $images) {
            $pending = BookingStatus::where('code', BookingStatusCode::Pending->value)->firstOrFail();
            $service = Service::where('is_active', true)->lockForUpdate()->findOrFail($data['service_id']);

            $providerUserId = null;
            $serviceProviderId = null;
            $contract = null;

            if (!empty($data['service_provider_id'])) {
                $providerProfile = ProviderProfile::where('id', $data['service_provider_id'])
                    ->where('is_approved', true)
                    ->first();
                if ($providerProfile) {
                    $serviceProviderId = $providerProfile->id;
                    $providerUserId = $providerProfile->user_id;

                    $contract = ProviderContract::query()
                        ->where('provider_profile_id', $providerProfile->id)
                        ->where(function ($q) {
                            $q->whereHas('status', fn ($s) => $s->where('code', ContractStatusCode::Active->value))
                              ->orWhereNull('ends_on');
                        })
                        ->latest('id')
                        ->first();
                }
            }

            $residentHomeId = $data['resident_home_id'] ?? null;
            if (!$residentHomeId && !empty($data['home_id'])) {
                $residentHomeId = ResidentHome::where('home_id', $data['home_id'])
                    ->where('user_id', $actor->id)
                    ->value('id');
            }

            $booking = $actor->bookings()->create($data + [
                'booking_status_id' => $pending->id,
                'total_amount' => $service->base_price,
                'provider_id' => $providerUserId,
                'service_provider_id' => $serviceProviderId,
                'resident_home_id' => $residentHomeId,
            ]);

            if (!empty($images) && is_array($images)) {
                foreach ($images as $image) {
                    if ($image instanceof \Illuminate\Http\UploadedFile) {
                        $this->mediaStorage->store($booking, $image, $actor, 'photo');
                    }
                }
            }

            $booking->statusHistory()->create([
                'booking_status_id' => $pending->id,
                'changed_by' => $actor->id,
                'note' => 'Created by resident',
            ]);

            if ($serviceProviderId && $contract) {
                BookingProviderAssignment::query()->create([
                    'booking_id' => $booking->id,
                    'provider_profile_id' => $serviceProviderId,
                    'provider_contract_id' => $contract->id,
                    'assigned_at' => now(),
                    'provider_note' => $data['notes'] ?? null,
                ]);
            }

            return $booking;
        });

        return ['data' => $booking->load(['service.translations', 'subService.translations', 'status.translations', 'serviceProvider', 'bookingMedia'])];
    }

    /**
     * تنفيذ التغيير داخل معاملة؛ الحدث لا يخرج إلا بعد نجاح الحفظ.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    public function cancel(array $input, ?User $actor, int $bookingId): mixed
    {
        return DB::transaction(function () use ($input, $actor, $bookingId) {
            $result = $this->performCancel($input, $actor, $bookingId);
            DB::afterCommit(fn () => event(new BookingsChanged(Operation::ApiBookingCancel, $actor?->id)));
            return $result;
        });
    }

    /**
     * قواعد العملية؛ المعاملة والتبليغ مسؤولية المدخل العام للخدمة.
     * @param array $input
     * @param ?User $actor
     * @param int $bookingId
     * @return mixed
     * @throws \Throwable عند فشل التحقق من قواعد العمل أو حفظ البيانات.
     */
    private function performCancel(array $input, ?User $actor, int $bookingId): mixed
    {
        $data = $input;

        
        $booking = DB::transaction(function () use ($input, $actor, $bookingId, $data) {
            $booking = $actor->bookings()->lockForUpdate()->findOrFail($bookingId);
            DomainViolation::unless(in_array($booking->status()->value('code'), [BookingStatusCode::Pending->value, BookingStatusCode::Confirmed->value], true), FailureReason::Conflict, __('This booking can no longer be cancelled.'));
            $cancelled = BookingStatus::where('code', BookingStatusCode::Cancelled->value)->firstOrFail();
            $booking->update([
                'booking_status_id' => $cancelled->id,
                'notes' => trim(($booking->notes ? $booking->notes."\n" : '').'Cancellation: '.$data['reason']),
            ]);
            $booking->statusHistory()->create([
                'booking_status_id' => $cancelled->id,
                'changed_by' => $actor->id, 'note' => $data['reason'],
            ]);

            return $booking;
        });

        return ['data' => $booking->fresh()->load('status.translations')];
    }
}
