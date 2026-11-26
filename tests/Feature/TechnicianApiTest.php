<?php

namespace Tests\Feature;

use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingMedia;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\SubService;
use App\Modules\Emergencies\Enums\EmergencyStatusCode;
use App\Modules\Emergencies\Models\EmergencyProviderAssignment;
use App\Modules\Emergencies\Models\EmergencyRequest;
use App\Modules\Emergencies\Models\EmergencyRequestStatus;
use App\Modules\Emergencies\Models\EmergencyType;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Notifications\Models\NotificationType;
use App\Modules\Properties\Models\City;
use App\Modules\Properties\Models\Home;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Providers\Services\ProviderPortalService;
use App\Modules\Providers\Services\ProviderRegistrationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class TechnicianApiTest extends TestCase
{
    use DatabaseTransactions;

    private User $technician;
    private User $resident;
    private $profile;
    private Service $service;
    private SubService $subService;
    private Booking $booking;
    private string $techPhone;
    private string $techPassword;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        Storage::fake('public');

        $this->withoutMiddleware(\Illuminate\Routing\Middleware\ThrottleRequests::class);

        $this->service = Service::query()->where('is_active', true)->firstOrFail();
        $this->subService = SubService::query()->create([
            'service_id' => $this->service->id,
            'code' => 'TECH-API-SUB-' . uniqid(),
            'sort_order' => 1,
            'is_active' => true,
        ]);
        $this->subService->translations()->create(['locale' => 'ar', 'name' => 'خدمة فرعية للتجربة']);

        $this->profile = app(ProviderRegistrationService::class)->register([
            'company_name' => 'شركة فنيين التقنية',
            'trade_name' => 'فنيين التقنية',
            'provider_type' => 'شركة صيانة',
            'years_of_experience' => 5,
            'service_ids' => [$this->service->id],
            'phone' => '055' . random_int(1000000, 9999999),
            'password' => 'StrongPass123!',
        ]);
        $this->profile->update(['is_approved' => true, 'contract_accepted_at' => now()]);

        $this->techPhone = '055' . random_int(1000000, 9999999);
        $this->techPassword = 'TechnicianSecret123!';

        $portal = app(ProviderPortalService::class);
        $technicianId = $portal->saveTechnician($this->profile, $this->profile->user, [
            'name' => 'أحمد الفني التجريبي',
            'phone' => $this->techPhone,
            'password' => $this->techPassword,
            'service_ids' => [$this->service->id],
            'sub_service_ids' => [$this->subService->id],
        ]);
        $this->technician = User::query()->findOrFail($technicianId);
        $this->technician->update(['locale' => 'ar']);

        NotificationType::query()->firstOrCreate(['code' => 'booking_status_changed'], ['name' => 'تغيير حالة الطلب']);
        NotificationType::query()->firstOrCreate(['code' => 'emergency_status_changed'], ['name' => 'تغيير حالة الطوارئ']);

        $residentRole = Role::query()->where('code', RoleCode::Resident->value)->firstOrFail();
        $this->resident = User::query()->create([
            'role_id' => $residentRole->id,
            'name' => 'محمد العميل التجريبي',
            'phone' => '050' . random_int(1000000, 9999999),
            'locale' => 'ar',
            'password' => 'ResidentSecret123!',
        ]);

        $cityId = City::query()->value('id') ?? DB::table('cities')->insertGetId(['code' => 'RUH', 'created_at' => now(), 'updated_at' => now()]);
        $homeId = DB::table('homes')->insertGetId([
            'city_id' => $cityId,
            'name' => 'برج الروابي التجريبي',
            'address' => 'الرياض - حي الروابي',
            'property_code' => 'PROP-TECH-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $residentHomeId = DB::table('resident_homes')->insertGetId([
            'user_id' => $this->resident->id,
            'home_id' => $homeId,
            'unit_label' => 'A-204',
            'is_primary' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $confirmedStatusId = BookingStatus::query()->where('code', BookingStatusCode::Confirmed->value)->value('id');
        $this->booking = Booking::query()->create([
            'resident_id' => $this->resident->id,
            'resident_home_id' => $residentHomeId,
            'home_id' => $homeId,
            'service_id' => $this->service->id,
            'sub_service_id' => $this->subService->id,
            'provider_id' => $this->profile->user_id,
            'service_provider_id' => $this->profile->id,
            'technician_id' => $this->technician->id,
            'booking_status_id' => $confirmedStatusId,
            'scheduled_at' => now()->addDay(),
            'total_amount' => 350.00,
            'notes' => 'يرجى إصلاح العطل بالتكييف في الصالة',
        ]);

        BookingProviderAssignment::query()->create([
            'booking_id' => $this->booking->id,
            'provider_profile_id' => $this->profile->id,
            'provider_contract_id' => $this->profile->contracts()->value('id'),
            'assigned_by' => $this->profile->user_id,
            'assigned_at' => now(),
        ]);
    }

    public function test_technician_can_login_via_technician_endpoint(): void
    {
        $response = $this->postJson('/api/v1/technician/auth/login', [
            'mobile' => $this->techPhone,
            'password' => $this->techPassword,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'message',
                'data' => [
                    'user' => [
                        'id',
                        'name',
                        'phone',
                        'mobile',
                        'is_technician',
                        'provider_profile_id',
                        'provider_name',
                    ],
                    'token',
                ],
            ]);

        $this->assertTrue($response->json('data.user.is_technician'));
        $this->assertSame($this->technician->id, $response->json('data.user.id'));
        $this->assertNotEmpty($response->json('data.token'));
    }

    public function test_technician_can_login_via_standard_user_auth_login(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'mobile' => $this->techPhone,
            'password' => $this->techPassword,
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('data.user.id', $this->technician->id)
            ->assertJsonPath('data.user.is_technician', true);
    }

    public function test_non_technician_cannot_login_via_technician_endpoint(): void
    {
        $response = $this->postJson('/api/v1/technician/auth/login', [
            'mobile' => $this->resident->phone,
            'password' => 'ResidentSecret123!',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['mobile']);
    }

    public function test_technician_can_get_user_info_and_profile(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/v1/technician/profile');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.id', $this->technician->id)
            ->assertJsonPath('data.is_technician', true)
            ->assertJsonPath('data.provider.id', $this->profile->id)
            ->assertJsonPath('data.provider.company_name', 'شركة فنيين التقنية')
            ->assertJsonStructure([
                'status',
                'data' => [
                    'id',
                    'name',
                    'phone',
                    'mobile',
                    'is_technician',
                    'provider' => ['id', 'company_name', 'trade_name'],
                    'services',
                    'sub_services',
                    'stats' => ['assigned_orders_count', 'active_orders_count', 'completed_orders_count'],
                ],
            ]);

        $this->assertNotEmpty($response->json('data.services'));
        $this->assertNotEmpty($response->json('data.sub_services'));
        $this->assertSame(1, $response->json('data.stats.assigned_orders_count'));

        // Also test /api/v1/technician/me alias
        $meResponse = $this->getJson('/api/v1/technician/me');
        $meResponse->assertStatus(200)->assertJsonPath('data.id', $this->technician->id);

        // Also test /api/v1/me returns is_technician flag
        $commonMe = $this->getJson('/api/v1/me');
        $commonMe->assertStatus(200)->assertJsonPath('data.is_technician', true);
    }

    public function test_resident_cannot_access_technician_profile(): void
    {
        Sanctum::actingAs($this->resident);

        $this->getJson('/api/v1/technician/profile')->assertStatus(403);
    }

    public function test_technician_can_list_statuses(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/v1/technician/statuses');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'code', 'name'],
                ],
            ]);

        $codes = collect($response->json('data'))->pluck('code')->all();
        $this->assertContains(BookingStatusCode::Pending->value, $codes);
        $this->assertContains(BookingStatusCode::Confirmed->value, $codes);
        $this->assertContains(BookingStatusCode::InProgress->value, $codes);
        $this->assertContains(BookingStatusCode::Completed->value, $codes);
    }

    public function test_technician_can_list_assigned_orders_and_filter(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/v1/technician/orders');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'order_number',
                        'service_id',
                        'service_name',
                        'sub_service_id',
                        'sub_service_name',
                        'status' => ['id', 'code', 'name'],
                        'client' => ['id', 'name', 'phone'],
                        'home' => ['id', 'name', 'address', 'unit_label'],
                        'scheduled_at',
                        'total_amount',
                        'can_confirm',
                    ],
                ],
                'meta' => ['current_page', 'last_page', 'per_page', 'total'],
            ]);

        $order = collect($response->json('data'))->firstWhere('id', $this->booking->id);
        $this->assertNotNull($order);
        $this->assertSame($this->resident->phone, $order['client']['phone']);
        $this->assertTrue($order['can_confirm']);

        // Filter by tab
        $activeResponse = $this->getJson('/api/v1/technician/orders?tab=active');
        $activeResponse->assertStatus(200);
        $this->assertCount(1, $activeResponse->json('data'));

        $completedResponse = $this->getJson('/api/v1/technician/orders?tab=completed');
        $completedResponse->assertStatus(200);
        $this->assertCount(0, $completedResponse->json('data'));

        // Show single order
        $showResponse = $this->getJson('/api/v1/technician/orders/' . $this->booking->id);
        $showResponse->assertStatus(200)
            ->assertJsonPath('data.id', $this->booking->id)
            ->assertJsonPath('data.client.name', $this->resident->name);
    }

    public function test_technician_can_send_otp_to_client(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->postJson('/api/v1/technician/orders/' . $this->booking->id . '/send-otp');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.booking_id', $this->booking->id)
            ->assertJsonPath('data.client_phone', $this->resident->phone);

        $cachedOtp = Cache::get('order_completion_otp_' . $this->booking->id);
        $this->assertNotNull($cachedOtp);
        $this->assertSame($response->json('data.otp'), $cachedOtp);

        $this->assertDatabaseHas('resident_notifications', [
            'user_id' => $this->resident->id,
            'reference_type' => 'booking',
            'reference_id' => $this->booking->id,
        ]);
    }

    public function test_technician_confirm_order_fails_with_invalid_otp(): void
    {
        Sanctum::actingAs($this->technician);

        Cache::put('order_completion_otp_' . $this->booking->id, '123456', now()->addMinutes(15));
        $image = UploadedFile::fake()->image('after_work.jpg', 600, 600);

        $response = $this->postJson('/api/v1/technician/orders/' . $this->booking->id . '/confirm', [
            'otp' => '999999',
            'image_after' => $image,
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['otp']);
    }

    public function test_technician_confirm_order_fails_without_after_image(): void
    {
        Sanctum::actingAs($this->technician);

        Cache::put('order_completion_otp_' . $this->booking->id, '123456', now()->addMinutes(15));

        $response = $this->postJson('/api/v1/technician/orders/' . $this->booking->id . '/confirm', [
            'otp' => '123456',
        ]);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['image_after']);
    }

    public function test_technician_can_confirm_order_with_valid_otp_and_after_image(): void
    {
        Sanctum::actingAs($this->technician);

        $otp = '654321';
        Cache::put('order_completion_otp_' . $this->booking->id, $otp, now()->addMinutes(15));
        $image = UploadedFile::fake()->image('repair_completed.jpg', 800, 600);

        $response = $this->postJson('/api/v1/technician/orders/' . $this->booking->id . '/confirm', [
            'otp' => $otp,
            'image_after' => $image,
            'note' => 'تم استبدال المفتاح واختبار التكييف بنجاح',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.order.status.code', BookingStatusCode::Completed->value)
            ->assertJsonPath('data.order.has_after_image', true)
            ->assertJsonPath('data.after_image.kind', 'after');

        $completedStatusId = BookingStatus::query()->where('code', BookingStatusCode::Completed->value)->value('id');
        $this->assertSame($completedStatusId, $this->booking->fresh()->booking_status_id);

        // Verify image recorded in booking_media
        $media = BookingMedia::query()->where('booking_id', $this->booking->id)->where('kind', 'after')->first();
        $this->assertNotNull($media);
        $this->assertSame($this->technician->id, $media->uploaded_by);
        Storage::disk('public')->assertExists($media->path);

        // Verify assignment completed_at
        $assignment = BookingProviderAssignment::query()->where('booking_id', $this->booking->id)->first();
        $this->assertNotNull($assignment->completed_at);

        // Verify status history
        $this->assertDatabaseHas('booking_status_history', [
            'booking_id' => $this->booking->id,
            'booking_status_id' => $completedStatusId,
            'changed_by' => $this->technician->id,
        ]);

        // Verify OTP cleared from cache
        $this->assertNull(Cache::get('order_completion_otp_' . $this->booking->id));
    }

    public function test_technician_can_update_status_to_in_progress(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->putJson('/api/v1/technician/orders/' . $this->booking->id . '/status', [
            'status' => BookingStatusCode::InProgress->value,
            'note' => 'الفني تحرك باتجاه موقع العميل',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.order.status.code', BookingStatusCode::InProgress->value);

        $inProgressId = BookingStatus::query()->where('code', BookingStatusCode::InProgress->value)->value('id');
        $this->assertSame($inProgressId, $this->booking->fresh()->booking_status_id);
    }

    public function test_technician_can_list_emergency_statuses(): void
    {
        Sanctum::actingAs($this->technician);

        $response = $this->getJson('/api/v1/technician/emergencies/statuses');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => ['id', 'code', 'name', 'badge_label'],
                ],
            ]);

        $codes = collect($response->json('data'))->pluck('code')->all();
        $this->assertContains('assigned', $codes);
        $this->assertContains('on_the_way', $codes);
        $this->assertContains('resolved', $codes);
    }

    public function test_technician_can_list_assigned_emergency_requests_matching_mobile_design(): void
    {
        Sanctum::actingAs($this->technician);

        $type = EmergencyType::query()->firstOrCreate(['code' => 'fire', 'is_active' => true]);
        $type->translations()->updateOrCreate(['locale' => 'ar'], ['name' => 'حريق أو دخان']);

        $assignedStatus = EmergencyRequestStatus::query()->where('code', EmergencyStatusCode::Assigned->value)->first();
        $assignedStatusId = $assignedStatus?->id ?? DB::table('emergency_request_statuses')->insertGetId(['code' => EmergencyStatusCode::Assigned->value, 'created_at' => now(), 'updated_at' => now()]);

        $emergency = EmergencyRequest::query()->create([
            'resident_id' => $this->resident->id,
            'resident_home_id' => $this->booking->resident_home_id,
            'home_id' => $this->booking->home_id,
            'emergency_type_id' => $type->id,
            'status_id' => $assignedStatusId,
            'reference' => 'RD-EM-1042',
            'contact_phone' => '0551234567',
            'location_details' => 'برج النخيل • وحدة 1204 • الدور 12',
            'description' => 'رائحة دخان قوية قرب باب الشقة ولا يوجد لهب ظاهر.',
            'requested_at' => now(),
            'handled_by' => $this->technician->id,
        ]);

        $response = $this->getJson('/api/v1/technician/emergencies');

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure([
                'status',
                'data' => [
                    '*' => [
                        'id',
                        'reference',
                        'title',
                        'status' => ['code', 'name', 'badge_label', 'badge_variant'],
                        'location',
                        'contact_phone',
                        'description',
                        'time_formatted',
                    ],
                ],
            ]);

        $item = collect($response->json('data'))->firstWhere('id', $emergency->id);
        $this->assertNotNull($item);
        $this->assertSame('RD-EM-1042', $item['reference']);
        $this->assertSame('حريق أو دخان', $item['title']);
        $this->assertSame('قيد المتابعة', $item['status']['badge_label']);
        $this->assertSame('0551234567', $item['contact_phone']);

        // Test tabs: active tab must have the emergency, completed tab must be empty
        $activeTab = $this->getJson('/api/v1/technician/emergencies?tab=active');
        $activeTab->assertStatus(200);
        $this->assertCount(1, $activeTab->json('data'));

        $completedTab = $this->getJson('/api/v1/technician/emergencies?tab=completed');
        $completedTab->assertStatus(200);
        $this->assertCount(0, $completedTab->json('data'));

        // Test single show
        $show = $this->getJson('/api/v1/technician/emergencies/' . $emergency->id);
        $show->assertStatus(200)
            ->assertJsonPath('data.reference', 'RD-EM-1042');
    }

    public function test_technician_can_update_emergency_status_to_on_the_way(): void
    {
        Sanctum::actingAs($this->technician);

        $type = EmergencyType::query()->firstOrCreate(['code' => 'water_leak', 'is_active' => true]);
        $type->translations()->updateOrCreate(['locale' => 'ar'], ['name' => 'تسريب مياه']);

        $assignedStatus = EmergencyRequestStatus::query()->where('code', EmergencyStatusCode::Assigned->value)->first();
        $assignedStatusId = $assignedStatus?->id ?? DB::table('emergency_request_statuses')->insertGetId(['code' => EmergencyStatusCode::Assigned->value, 'created_at' => now(), 'updated_at' => now()]);

        $emergency = EmergencyRequest::query()->create([
            'resident_id' => $this->resident->id,
            'resident_home_id' => $this->booking->resident_home_id,
            'home_id' => $this->booking->home_id,
            'emergency_type_id' => $type->id,
            'status_id' => $assignedStatusId,
            'reference' => 'RD-EM-0981',
            'contact_phone' => '0559876543',
            'location_details' => 'الرياض - حي الروابي - شقة 204',
            'description' => 'تسريب مياه في الحمام الرئيسي',
            'requested_at' => now(),
            'handled_by' => $this->technician->id,
        ]);

        $response = $this->putJson('/api/v1/technician/emergencies/' . $emergency->id . '/status', [
            'status' => 'on_the_way',
            'note' => 'الفريق في الطريق إلى الشقة',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.emergency.status.code', 'on_the_way')
            ->assertJsonPath('data.emergency.status.badge_label', 'قيد المتابعة');

        $onTheWayStatusId = EmergencyRequestStatus::query()->where('code', 'on_the_way')->value('id');
        $this->assertSame($onTheWayStatusId, $emergency->fresh()->status_id);
        $this->assertNotNull($emergency->fresh()->dispatched_at);
    }

    public function test_technician_can_resolve_emergency_request(): void
    {
        Sanctum::actingAs($this->technician);

        $type = EmergencyType::query()->firstOrCreate(['code' => 'water_leak', 'is_active' => true]);
        $type->translations()->updateOrCreate(['locale' => 'ar'], ['name' => 'تسريب مياه']);

        $onTheWayStatus = EmergencyRequestStatus::query()->where('code', EmergencyStatusCode::OnTheWay->value)->first();
        $onTheWayStatusId = $onTheWayStatus?->id ?? DB::table('emergency_request_statuses')->insertGetId(['code' => EmergencyStatusCode::OnTheWay->value, 'created_at' => now(), 'updated_at' => now()]);

        $emergency = EmergencyRequest::query()->create([
            'resident_id' => $this->resident->id,
            'resident_home_id' => $this->booking->resident_home_id,
            'home_id' => $this->booking->home_id,
            'emergency_type_id' => $type->id,
            'status_id' => $onTheWayStatusId,
            'reference' => 'RD-EM-0981',
            'contact_phone' => '0559876543',
            'location_details' => 'الرياض - حي الروابي - شقة 204',
            'description' => 'تسريب مياه تم التعامل معه',
            'requested_at' => now()->subHour(),
            'handled_by' => $this->technician->id,
        ]);

        $response = $this->postJson('/api/v1/technician/emergencies/' . $emergency->id . '/resolve', [
            'status' => 'resolved',
            'note' => 'تم استبدال وصلة التصريف واختبار المياه.',
        ]);

        $response->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.emergency.status.code', 'resolved')
            ->assertJsonPath('data.emergency.status.badge_label', 'تمت المعالجة');

        $resolvedStatusId = EmergencyRequestStatus::query()->where('code', 'resolved')->value('id');
        $this->assertSame($resolvedStatusId, $emergency->fresh()->status_id);
        $this->assertNotNull($emergency->fresh()->resolved_at);
    }

    public function test_technician_can_send_otp_and_confirm_emergency_request_with_client_otp(): void
    {
        Sanctum::actingAs($this->technician);

        $type = EmergencyType::query()->firstOrCreate(['code' => 'water_leak', 'is_active' => true]);
        $type->translations()->updateOrCreate(['locale' => 'ar'], ['name' => 'تسريب مياه']);

        $onTheWayStatus = EmergencyRequestStatus::query()->where('code', EmergencyStatusCode::OnTheWay->value)->first();
        $onTheWayStatusId = $onTheWayStatus?->id ?? DB::table('emergency_request_statuses')->insertGetId(['code' => EmergencyStatusCode::OnTheWay->value, 'created_at' => now(), 'updated_at' => now()]);

        $emergency = EmergencyRequest::query()->create([
            'resident_id' => $this->resident->id,
            'resident_home_id' => $this->booking->resident_home_id,
            'home_id' => $this->booking->home_id,
            'emergency_type_id' => $type->id,
            'status_id' => $onTheWayStatusId,
            'reference' => 'RD-EM-OTP-01',
            'contact_phone' => '0559876543',
            'location_details' => 'الرياض - حي الروابي - شقة 204',
            'description' => 'تسريب مياه يتطلب التحقق بـ OTP',
            'requested_at' => now()->subHour(),
            'handled_by' => $this->technician->id,
        ]);

        $contractStatus = \App\Modules\Providers\Models\ProviderContractStatus::query()
            ->where('code', \App\Modules\Providers\Enums\ContractStatusCode::Active->value)->value('id');
        $contract = $this->profile->contracts()->create([
            'status_id' => $contractStatus,
            'reference' => 'PC-TEST-EMG-01',
            'service_commission_percent' => 15.0,
            'emergency_commission_percent' => 20.0,
            'starts_on' => today()->subMonth(),
            'terms' => 'Test terms',
        ]);

        $region = \App\Modules\Properties\Models\Region::query()->firstOrCreate(['code' => 'test-reg-emg'], ['country_code' => 'SA', 'is_active' => true]);
        $city = City::query()->find(Home::query()->find($this->booking->home_id)->city_id);
        $city->update(['region_id' => $region->id]);
        \App\Modules\Emergencies\Models\EmergencySetting::query()->updateOrCreate(
            ['region_id' => $region->id],
            ['price' => 300.0, 'is_active' => true, 'estimated_time_minutes' => 30]
        );

        EmergencyProviderAssignment::query()->create([
            'emergency_request_id' => $emergency->id,
            'provider_profile_id' => $this->profile->id,
            'provider_contract_id' => $contract->id,
            'assigned_at' => now()->subHour(),
        ]);

        // 1. Send OTP to client
        $otpResponse = $this->postJson('/api/v1/technician/emergencies/' . $emergency->id . '/send-otp');
        $otpResponse->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonStructure(['data' => ['emergency_id', 'client_phone', 'expires_in_minutes']]);

        $otp = $otpResponse->json('data.otp') ?? '542187';

        // 2. Reject incorrect OTP
        $failResponse = $this->postJson('/api/v1/technician/emergencies/' . $emergency->id . '/confirm', [
            'otp' => '000000',
            'note' => 'كود خاطئ',
        ]);
        $failResponse->assertStatus(422)
            ->assertJsonValidationErrors(['otp']);

        // 3. Confirm with valid OTP and optional after image
        $imageFile = UploadedFile::fake()->image('emergency_after.jpg', 640, 480);
        $confirmResponse = $this->postJson('/api/v1/technician/emergencies/' . $emergency->id . '/confirm', [
            'otp' => $otp,
            'image_after' => $imageFile,
            'note' => 'تم إنهاء بلاغ الطوارئ بنجاح بـ OTP وتأكيد الساكن.',
        ], ['Accept-Language' => 'ar']);

        $confirmResponse->assertStatus(200)
            ->assertJsonPath('status', true)
            ->assertJsonPath('data.emergency.status.code', 'resolved')
            ->assertJsonPath('data.emergency.status.badge_label', 'تمت المعالجة');

        $resolvedStatusId = EmergencyRequestStatus::query()->where('code', 'resolved')->value('id');
        $this->assertSame($resolvedStatusId, $emergency->fresh()->status_id);
        $this->assertNotNull($emergency->fresh()->resolved_at);

        $assignment = EmergencyProviderAssignment::query()->where('emergency_request_id', $emergency->id)->first();
        $this->assertNotNull($assignment?->completed_at);

        $commissionEntry = \App\Modules\Providers\Models\ProviderCommissionEntry::query()
            ->where('emergency_request_id', $emergency->id)
            ->first();
        $this->assertNotNull($commissionEntry);
        $this->assertSame($this->profile->id, $commissionEntry->provider_profile_id);
        $this->assertEquals(20.0, (float) $commissionEntry->commission_percent_snapshot);
        $this->assertEquals(300.0, (float) $commissionEntry->gross_amount);
        $this->assertEquals(60.0, (float) $commissionEntry->commission_amount);
    }
}
