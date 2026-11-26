<?php

namespace Tests\Feature;

use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Providers\Enums\ContractStatusCode;
use App\Modules\Providers\Models\ProviderContract;
use App\Modules\Providers\Models\ProviderContractStatus;
use App\Modules\Providers\Services\ProviderPortalService;
use App\Modules\Providers\Services\ProviderRegistrationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class OrderBulkStatusTechnicianAssignmentTest extends TestCase
{
    use DatabaseTransactions;

    public function test_provider_can_bulk_update_status_and_technician_in_same_form(): void
    {
        $this->seed();
        $service = Service::query()->where('is_active', true)->firstOrFail();
        $profile = app(ProviderRegistrationService::class)->register([
            'company_name' => 'شركة الصيانة المتحدة',
            'trade_name' => 'الصيانة المتحدة',
            'provider_type' => 'شركة صيانة',
            'years_of_experience' => 4,
            'service_ids' => [$service->id],
            'phone' => '055' . random_int(10000000, 99999999),
            'password' => 'StrongPassword123!',
        ]);
        $profile->update(['is_approved' => true, 'contract_accepted_at' => now()]);

        $subServiceId = DB::table('sub_services')->insertGetId([
            'service_id' => $service->id,
            'code' => 'SUB-BULK-' . uniqid(),
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $portal = app(ProviderPortalService::class);
        $technicianId = $portal->saveTechnician($profile, $profile->user, [
            'name' => 'فهد الفني المعتمد',
            'phone' => '055' . random_int(10000000, 99999999),
            'password' => 'TechPassword123!',
            'service_ids' => [$service->id],
            'sub_service_ids' => [$subServiceId],
        ]);

        $resident = User::query()->create([
            'name' => 'ساكن تجريبي',
            'email' => 'bulk-resident-' . uniqid() . '@example.test',
            'password' => 'ResidentPassword123!',
            'locale' => 'ar',
            'role_id' => DB::table('roles')->where('code', RoleCode::Resident->value)->value('id'),
        ]);

        $homeId = DB::table('homes')->insertGetId([
            'city_id' => DB::table('cities')->value('id'),
            'name' => 'برج الاختبار',
            'address' => 'طريق الملك فهد',
            'property_code' => 'TEST-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $membership = ResidentHome::query()->create([
            'user_id' => $resident->id,
            'home_id' => $homeId,
            'unit_label' => '101',
        ]);

        $pendingStatusId = BookingStatus::query()->where('code', BookingStatusCode::Pending->value)->value('id');
        $confirmedStatusId = BookingStatus::query()->where('code', BookingStatusCode::Confirmed->value)->value('id');

        $contractId = $profile->contracts()->value('id');

        $booking1 = Booking::query()->create([
            'resident_id' => $resident->id,
            'resident_home_id' => $membership->id,
            'home_id' => $homeId,
            'service_id' => $service->id,
            'sub_service_id' => $subServiceId,
            'provider_id' => $profile->user_id,
            'service_provider_id' => $profile->id,
            'booking_status_id' => $pendingStatusId,
            'scheduled_at' => now()->addDay(),
            'total_amount' => 150,
        ]);
        BookingProviderAssignment::query()->create([
            'booking_id' => $booking1->id,
            'provider_profile_id' => $profile->id,
            'provider_contract_id' => $contractId,
            'assigned_by' => $profile->user_id,
            'assigned_at' => now(),
        ]);

        $booking2 = Booking::query()->create([
            'resident_id' => $resident->id,
            'resident_home_id' => $membership->id,
            'home_id' => $homeId,
            'service_id' => $service->id,
            'sub_service_id' => $subServiceId,
            'provider_id' => $profile->user_id,
            'service_provider_id' => $profile->id,
            'booking_status_id' => $pendingStatusId,
            'scheduled_at' => now()->addDays(2),
            'total_amount' => 200,
        ]);
        BookingProviderAssignment::query()->create([
            'booking_id' => $booking2->id,
            'provider_profile_id' => $profile->id,
            'provider_contract_id' => $contractId,
            'assigned_by' => $profile->user_id,
            'assigned_at' => now(),
        ]);

        $this->actingAs($profile->user, 'provider');

        // Check index view shows bulk form and checkboxes
        $this->get(route('provider.orders.index'))
            ->assertOk()
            ->assertSee('providerBulkActionForm')
            ->assertSee('selectAllProviderOrders')
            ->assertSee('provider-order-cb')
            ->assertSee('فهد الفني المعتمد');

        // Execute bulk update: change status to confirmed AND assign technician in same form
        $response = $this->put(route('provider.orders.bulk-update'), [
            'order_ids' => [$booking1->id, $booking2->id],
            'status' => 'confirmed',
            'technician_id' => $technicianId,
            'note' => 'تم قبول الطلبين وتعيين الفني فهد',
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();

        $booking1->refresh();
        $booking2->refresh();

        $this->assertSame($confirmedStatusId, (int) $booking1->booking_status_id);
        $this->assertSame($technicianId, (int) $booking1->technician_id);

        $this->assertSame($confirmedStatusId, (int) $booking2->booking_status_id);
        $this->assertSame($technicianId, (int) $booking2->technician_id);

        // Test single order update with both status and technician in same form
        $inProgressStatusId = BookingStatus::query()->where('code', BookingStatusCode::InProgress->value)->value('id');
        $this->put(route('provider.orders.update', $booking1->id), [
            'status' => 'in_progress',
            'technician_id' => $technicianId,
            'note' => 'الفني بدأ العمل',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $booking1->refresh();
        $this->assertSame($inProgressStatusId, (int) $booking1->booking_status_id);
        $this->assertSame($technicianId, (int) $booking1->technician_id);

        // Test unassigning technician via bulk
        $this->put(route('provider.orders.bulk-update'), [
            'order_ids' => [$booking1->id],
            'technician_id' => 'unassign',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $booking1->refresh();
        $this->assertNull($booking1->technician_id);
    }

    public function test_admin_can_bulk_update_status_and_technician_in_same_form(): void
    {
        $this->seed();
        $admin = Admin::query()->create([
            'name' => 'مدير النظام التجريبي',
            'email' => 'admin-bulk-' . uniqid() . '@example.test',
            'password' => 'AdminPass123!',
            'locale' => 'ar',
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        $service = Service::query()->where('is_active', true)->firstOrFail();
        $profile = app(ProviderRegistrationService::class)->register([
            'company_name' => 'شركة المناديب الفنية',
            'trade_name' => 'المناديب الفنية',
            'provider_type' => 'شركة صيانة',
            'years_of_experience' => 3,
            'service_ids' => [$service->id],
            'phone' => '055' . random_int(10000000, 99999999),
            'password' => 'StrongPassword123!',
        ]);
        $profile->update(['is_approved' => true, 'contract_accepted_at' => now()]);

        // Ensure active contract
        $activeContractStatusId = DB::table('provider_contract_statuses')
            ->where('code', ContractStatusCode::Active->value)->value('id');
        $contract = ProviderContract::query()->where('provider_profile_id', $profile->id)->first();
        if ($contract) {
            $contract->update(['status_id' => $activeContractStatusId, 'starts_on' => now()->subDay(), 'ends_on' => now()->addYear()]);
        } else {
            $contract = ProviderContract::query()->create([
                'provider_profile_id' => $profile->id,
                'status_id' => $activeContractStatusId,
                'starts_on' => now()->subDay(),
                'ends_on' => now()->addYear(),
                'commission_rate' => 10,
            ]);
        }

        $subServiceId = DB::table('sub_services')->insertGetId([
            'service_id' => $service->id,
            'code' => 'SUB-ADMIN-BULK-' . uniqid(),
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $portal = app(ProviderPortalService::class);
        $technicianId = $portal->saveTechnician($profile, $profile->user, [
            'name' => 'المندوب سالم',
            'phone' => '055' . random_int(10000000, 99999999),
            'password' => 'TechPassword123!',
            'service_ids' => [$service->id],
            'sub_service_ids' => [$subServiceId],
        ]);

        $resident = User::query()->create([
            'name' => 'ساكن أدمن تجريبي',
            'email' => 'admin-resident-' . uniqid() . '@example.test',
            'password' => 'ResidentPassword123!',
            'locale' => 'ar',
            'role_id' => DB::table('roles')->where('code', RoleCode::Resident->value)->value('id'),
        ]);

        $homeId = DB::table('homes')->insertGetId([
            'city_id' => DB::table('cities')->value('id'),
            'name' => 'برج النخيل أدمن',
            'address' => 'طريق العليا',
            'property_code' => 'ADM-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $membership = ResidentHome::query()->create([
            'user_id' => $resident->id,
            'home_id' => $homeId,
            'unit_label' => '502',
        ]);

        $pendingStatusId = BookingStatus::query()->where('code', BookingStatusCode::Pending->value)->value('id');
        $confirmedStatusId = BookingStatus::query()->where('code', BookingStatusCode::Confirmed->value)->value('id');

        $booking = Booking::query()->create([
            'resident_id' => $resident->id,
            'resident_home_id' => $membership->id,
            'home_id' => $homeId,
            'service_id' => $service->id,
            'sub_service_id' => $subServiceId,
            'booking_status_id' => $pendingStatusId,
            'scheduled_at' => now()->addDay(),
            'total_amount' => 300,
        ]);

        $this->actingAs($admin);

        // Check admin service-orders index view contains bulk form, checkboxes, and technician dropdown
        $this->get(route('admin.service-orders.index'))
            ->assertOk()
            ->assertSee('adminBulkActionForm')
            ->assertSee('selectAllAdminTable')
            ->assertSee('admin-order-cb')
            ->assertSee('المندوب سالم');

        // Admin bulk updates booking: sets status, technician, and provider in same form
        $response = $this->put(route('admin.service-orders.bulk-update'), [
            'order_ids' => [$booking->id],
            'booking_status_id' => $confirmedStatusId,
            'provider_profile_id' => $profile->id,
            'technician_id' => $technicianId,
            'note' => 'تم تعيين المندوب والمزود وتحديث الحالة بواسطة الأدمن',
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();

        $booking->refresh();
        $this->assertSame($confirmedStatusId, (int) $booking->booking_status_id);
        $this->assertSame($technicianId, (int) $booking->technician_id);
        $this->assertSame($profile->user_id, (int) $booking->provider_id);

        // Admin single update modal / form with technician
        $inProgressStatusId = BookingStatus::query()->where('code', BookingStatusCode::InProgress->value)->value('id');
        $this->put(route('admin.service-orders.update', $booking->id), [
            'booking_status_id' => $inProgressStatusId,
            'provider_profile_id' => $profile->id,
            'technician_id' => $technicianId,
            'note' => 'تحديث فردي للحالة والمندوب معاً',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $booking->refresh();
        $this->assertSame($inProgressStatusId, (int) $booking->booking_status_id);
        $this->assertSame($technicianId, (int) $booking->technician_id);

        // Verify show page has technician select in quick update form
        $this->get(route('admin.service-orders.show', $booking->id))
            ->assertOk()
            ->assertSee('technician-select')
            ->assertSee('المندوب سالم');

        // Admin unassigns technician via bulk
        $this->put(route('admin.service-orders.bulk-update'), [
            'order_ids' => [$booking->id],
            'technician_id' => 'unassign',
        ])->assertRedirect()->assertSessionHasNoErrors();

        $booking->refresh();
        $this->assertNull($booking->technician_id);
    }

    public function test_admin_can_assign_technician_to_emergency_request_at_admin_emergencies_page(): void
    {
        $this->seed();
        $admin = Admin::query()->create([
            'name' => 'مدير بلاغات الطوارئ',
            'email' => 'admin-em-' . uniqid() . '@example.test',
            'password' => 'AdminPass123!',
            'locale' => 'ar',
            'is_active' => true,
            'is_super_admin' => true,
        ]);

        $service = Service::query()->where('is_active', true)->firstOrFail();
        $profile = app(ProviderRegistrationService::class)->register([
            'company_name' => 'شركة طوارئ السريعة',
            'trade_name' => 'طوارئ السريعة',
            'provider_type' => 'شركة صيانة',
            'years_of_experience' => 4,
            'service_ids' => [$service->id],
            'phone' => '055' . random_int(10000000, 99999999),
            'password' => 'StrongPassword123!',
        ]);
        $profile->update(['is_approved' => true, 'contract_accepted_at' => now()]);

        // Ensure active contract for emergency assignments
        $activeContractStatusId = DB::table('provider_contract_statuses')
            ->where('code', ContractStatusCode::Active->value)->value('id');
        $contract = ProviderContract::query()->where('provider_profile_id', $profile->id)->first();
        if ($contract) {
            $contract->update(['status_id' => $activeContractStatusId, 'starts_on' => now()->subDay(), 'ends_on' => now()->addYear()]);
        } else {
            $contract = ProviderContract::query()->create([
                'provider_profile_id' => $profile->id,
                'status_id' => $activeContractStatusId,
                'starts_on' => now()->subDay(),
                'ends_on' => now()->addYear(),
                'commission_rate' => 10,
            ]);
        }

        $portal = app(ProviderPortalService::class);
        $technicianId = $portal->saveTechnician($profile, $profile->user, [
            'name' => 'مندوب الطوارئ خالد',
            'phone' => '055' . random_int(10000000, 99999999),
            'password' => 'TechPassword123!',
        ]);

        $resident = User::query()->create([
            'name' => 'ساكن البلاغ التجريبي',
            'email' => 'resident-em-' . uniqid() . '@example.test',
            'password' => 'ResidentPassword123!',
            'locale' => 'ar',
            'role_id' => DB::table('roles')->where('code', RoleCode::Resident->value)->value('id'),
        ]);

        $homeId = DB::table('homes')->insertGetId([
            'city_id' => DB::table('cities')->value('id'),
            'name' => 'أبراج الصحافة',
            'address' => 'طريق أنس بن مالك',
            'property_code' => 'EM-PROP-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $membership = ResidentHome::query()->create([
            'user_id' => $resident->id,
            'home_id' => $homeId,
            'unit_label' => '104',
        ]);

        $emergencyType = \App\Modules\Emergencies\Models\EmergencyType::query()->firstOrCreate(['code' => 'water_leak_test', 'is_active' => true]);
        $emergencyType->translations()->updateOrCreate(['locale' => 'ar'], ['name' => 'تسريب مياه حاد']);

        $receivedStatus = \App\Modules\Emergencies\Models\EmergencyRequestStatus::query()->where('code', \App\Modules\Emergencies\Enums\EmergencyStatusCode::Received->value)->first();
        $assignedStatus = \App\Modules\Emergencies\Models\EmergencyRequestStatus::query()->where('code', \App\Modules\Emergencies\Enums\EmergencyStatusCode::Assigned->value)->first();

        $emergency = \App\Modules\Emergencies\Models\EmergencyRequest::query()->create([
            'resident_id' => $resident->id,
            'resident_home_id' => $membership->id,
            'home_id' => $homeId,
            'emergency_type_id' => $emergencyType->id,
            'status_id' => $receivedStatus->id,
            'reference' => 'RD-EM-TEST-1',
            'contact_phone' => '0551122334',
            'description' => 'تسريب مياه مفاجئ في المطبخ',
            'location_details' => 'شقة 104 - الدور 1',
            'requested_at' => now(),
        ]);

        $this->actingAs($admin);

        // 1. Visit /admin/emergencies and ensure technician-select is present
        $this->get(route('admin.emergencies.index'))
            ->assertOk()
            ->assertSee('technician-select')
            ->assertSee('مندوب الطوارئ خالد');

        // 2. Admin assigns provider and technician to the emergency request
        $response = $this->put(route('admin.emergencies.update', $emergency->id), [
            'status_id' => $assignedStatus->id,
            'provider_profile_id' => $profile->id,
            'technician_id' => $technicianId,
            'note' => 'تم تعيين المندوب خالد وإرسال البلاغ لشركة الصيانة',
        ]);

        $response->assertRedirect()->assertSessionHasNoErrors();

        $emergency->refresh();
        $this->assertSame($assignedStatus->id, (int) $emergency->status_id);
        $this->assertSame($technicianId, (int) $emergency->handled_by);

        // 3. Visit index again and verify technician name appears in the row
        $this->get(route('admin.emergencies.index'))
            ->assertOk()
            ->assertSee('مندوب الطوارئ خالد');

        // 4. Admin unassigns the technician
        $unassignResponse = $this->put(route('admin.emergencies.update', $emergency->id), [
            'status_id' => $assignedStatus->id,
            'provider_profile_id' => $profile->id,
            'technician_id' => '',
            'note' => 'إلغاء تعيين المندوب',
        ]);

        $unassignResponse->assertRedirect()->assertSessionHasNoErrors();
        $emergency->refresh();
        $this->assertNull($emergency->handled_by);
    }
}
