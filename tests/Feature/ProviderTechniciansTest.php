<?php

namespace Tests\Feature;

use App\Modules\Bookings\Enums\BookingStatusCode;
use App\Modules\Bookings\Models\Booking;
use App\Modules\Bookings\Models\BookingProviderAssignment;
use App\Modules\Bookings\Models\BookingStatus;
use App\Modules\Catalog\Models\Service;
use App\Modules\Identity\Enums\RoleCode;
use App\Modules\Identity\Models\User;
use App\Modules\Properties\Models\ResidentHome;
use App\Modules\Providers\Services\ProviderPortalService;
use App\Modules\Providers\Services\ProviderRegistrationService;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProviderTechniciansTest extends TestCase
{
    use DatabaseTransactions;

    public function test_provider_can_manage_technicians_and_assign_only_eligible_ones(): void
    {
        $this->seed();
        $service = Service::query()->where('is_active', true)->firstOrFail();
        $profile = app(ProviderRegistrationService::class)->register([
            'company_name' => 'شركة الفنيين',
            'trade_name' => 'فريق الفنيين',
            'provider_type' => 'شركة صيانة',
            'years_of_experience' => 5,
            'service_ids' => [$service->id],
            'phone' => '0557654381',
            'password' => 'StrongPassword123!',
        ]);
        $profile->update(['is_approved' => true, 'contract_accepted_at' => now()]);

        $subServiceId = DB::table('sub_services')->insertGetId([
            'service_id' => $service->id,
            'code' => 'TECHNICIAN-SUB-' . uniqid(),
            'sort_order' => 1,
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $portal = app(ProviderPortalService::class);
        $technicianId = $portal->saveTechnician($profile, $profile->user, [
            'name' => 'فني الخدمة',
            'phone' => '055' . random_int(10000000, 99999999),
            'password' => 'TechnicianPassword123!',
            'service_ids' => [$service->id],
            'sub_service_ids' => [$subServiceId],
        ]);
        $technician = User::query()->findOrFail($technicianId);

        $this->assertTrue($technician->is_technician);
        $this->assertSame($profile->id, $technician->provider_profile_id);
        $this->assertTrue(Hash::check('TechnicianPassword123!', $technician->password));
        $this->assertDatabaseHas('provider_technician_services', [
            'technician_id' => $technicianId,
            'service_id' => $service->id,
        ]);
        $this->assertDatabaseHas('provider_technician_sub_services', [
            'technician_id' => $technicianId,
            'sub_service_id' => $subServiceId,
        ]);
        $this->actingAs($profile->user, 'provider');
        $this->get('/provider/technicians')->assertOk()->assertSee('فني الخدمة')
            ->assertSee('data-bs-toggle="modal"', false)
            ->assertSee(__('Technician services'));
        $this->get('/provider/technicians/create')->assertOk()->assertSee('name="service_ids[]"', false);
        $this->get(route('provider.technicians.edit', $technicianId))->assertOk()->assertSee('فني الخدمة');

        $portal->saveTechnician($profile, $profile->user, [
            'name' => 'فني الخدمة المحدث',
            'phone' => $technician->phone,
            'service_ids' => [$service->id],
            'sub_service_ids' => [$subServiceId],
        ], $technicianId);
        $this->assertSame('فني الخدمة المحدث', $technician->fresh()->name);
        $this->assertTrue(Hash::check('TechnicianPassword123!', $technician->fresh()->password));

        $unqualifiedTechnicianId = $portal->saveTechnician($profile, $profile->user, [
            'name' => 'فني غير مؤهل',
            'phone' => '055' . random_int(10000000, 99999999),
            'password' => 'TechnicianPassword123!',
            'service_ids' => [$service->id],
            'sub_service_ids' => [],
        ]);

        $resident = User::query()->create([
            'name' => 'Resident technician test',
            'email' => 'resident-technician-' . uniqid() . '@example.test',
            'password' => 'ResidentPassword123!',
            'locale' => 'ar',
            'role_id' => DB::table('roles')->where('code', RoleCode::Resident->value)->value('id'),
        ]);
        $homeId = DB::table('homes')->insertGetId([
            'city_id' => DB::table('cities')->value('id'),
            'name' => 'Technician test home',
            'address' => 'Test address',
            'property_code' => 'TECH-' . uniqid(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $membership = ResidentHome::query()->create([
            'user_id' => $resident->id,
            'home_id' => $homeId,
            'unit_label' => '7',
        ]);
        $booking = Booking::query()->create([
            'resident_id' => $resident->id,
            'resident_home_id' => $membership->id,
            'home_id' => $homeId,
            'service_id' => $service->id,
            'sub_service_id' => $subServiceId,
            'provider_id' => $profile->user_id,
            'service_provider_id' => $profile->id,
            'booking_status_id' => BookingStatus::query()
                ->where('code', BookingStatusCode::Pending->value)->value('id'),
            'scheduled_at' => now()->addDay(),
            'total_amount' => 100,
        ]);
        BookingProviderAssignment::query()->create([
            'booking_id' => $booking->id,
            'provider_profile_id' => $profile->id,
            'provider_contract_id' => $profile->contracts()->value('id'),
            'assigned_by' => $profile->user_id,
            'assigned_at' => now(),
        ]);
        $this->get(route('provider.orders.show', $booking->id))->assertOk()->assertSee(__('Assign technician'));

        try {
            $portal->assignTechnician($profile, $booking->id, $unqualifiedTechnicianId);
            $this->fail('A technician without the requested sub-service must not be assigned.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('technician_id', $exception->errors());
        }

        $this->put(route('provider.orders.technician', $booking->id), [
            'technician_id' => $technicianId,
        ])->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame($technicianId, $booking->fresh()->technician_id);
        try {
            $portal->removeTechnician($profile, $technicianId);
            $this->fail('A technician assigned to an order must not be deleted.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('technician', $exception->errors());
        }

        $portal->assignTechnician($profile, $booking->id, null);
        $portal->removeTechnician($profile, $technicianId);
        $this->assertDatabaseMissing('users', ['id' => $technicianId]);
        $this->assertNull($booking->fresh()->technician_id);
    }
}
