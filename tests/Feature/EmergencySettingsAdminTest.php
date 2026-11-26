<?php

namespace Tests\Feature;

use App\Modules\Emergencies\Models\EmergencySetting;
use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Role;
use App\Modules\Properties\Models\Region;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class EmergencySettingsAdminTest extends TestCase
{
    use DatabaseTransactions;

    private Admin $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        ]);

        $adminRole = Role::firstOrCreate(['code' => 'admin']);
        $this->admin = Admin::firstOrCreate(
            ['email' => 'admin_emergency_settings@rounder.sa'],
            [
                'role_id' => $adminRole->id,
                'name' => 'مدير تجريبي لإعدادات الطوارئ',
                'password' => 'Secret123!',
                'is_active' => true,
                'is_super_admin' => true,
                'locale' => 'ar',
            ]
        );
    }

    public function test_guest_cannot_view_emergency_settings(): void
    {
        $response = $this->get(route('admin.emergency-settings.index'));
        $response->assertRedirect();
    }

    public function test_admin_can_view_emergency_settings_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.emergency-settings.index'));

        $response->assertOk()
            ->assertSee('إعدادات طلبات الطوارئ')
            ->assertSee('جدول تسعير الطوارئ بحسب المنطقة الجغرافية')
            ->assertSee('رسوم الطوارئ (السعر)')
            ->assertSee('وقت الاستجابة المتوقع');
    }

    public function test_admin_can_update_region_emergency_price(): void
    {
        $setting = EmergencySetting::first();
        if (!$setting) {
            $region = Region::firstOrCreate(['code' => 'test_region']);
            $setting = EmergencySetting::create([
                'region_id' => $region->id,
                'price' => 150.00,
                'cancellation_fee' => 0.00,
                'estimated_time_minutes' => 30,
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->admin)->put(route('admin.emergency-settings.update', $setting->id), [
            'price' => 220.50,
            'cancellation_fee' => 25.00,
            'estimated_time_minutes' => 20,
            'is_active' => '1',
            'notes' => 'تحديث تسعيرة الطوارئ للاختبار',
        ]);

        $response->assertRedirect(route('admin.emergency-settings.index'))
            ->assertSessionHas('status');

        $setting->refresh();
        $this->assertEquals(220.50, (float) $setting->price);
        $this->assertEquals(20, $setting->estimated_time_minutes);
        $this->assertEquals('تحديث تسعيرة الطوارئ للاختبار', $setting->notes);
    }

    public function test_admin_can_toggle_region_emergency_status(): void
    {
        $setting = EmergencySetting::first();
        if (!$setting) {
            $region = Region::firstOrCreate(['code' => 'test_region']);
            $setting = EmergencySetting::create([
                'region_id' => $region->id,
                'price' => 150.00,
                'is_active' => true,
            ]);
        }

        $initialStatus = $setting->is_active;

        $response = $this->actingAs($this->admin)->post(route('admin.emergency-settings.toggle', $setting->id));

        $response->assertRedirect(route('admin.emergency-settings.index'))
            ->assertSessionHas('status');

        $setting->refresh();
        $this->assertNotEquals($initialStatus, $setting->is_active);
    }

    public function test_admin_can_add_emergency_setting_for_new_region(): void
    {
        $region = Region::create([
            'code' => 'new_zone_' . uniqid(),
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->post(route('admin.emergency-settings.store'), [
            'region_id' => $region->id,
            'price' => 180.00,
            'cancellation_fee' => 15.00,
            'estimated_time_minutes' => 25,
            'is_active' => '1',
            'notes' => 'منطقة جديدة تمت إضافتها',
        ]);

        $response->assertRedirect(route('admin.emergency-settings.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('emergency_settings', [
            'region_id' => $region->id,
            'price' => 180.00,
            'estimated_time_minutes' => 25,
        ]);
    }

    public function test_price_is_required_when_updating(): void
    {
        $setting = EmergencySetting::first();
        if (!$setting) {
            $region = Region::firstOrCreate(['code' => 'test_region']);
            $setting = EmergencySetting::create([
                'region_id' => $region->id,
                'price' => 150.00,
                'is_active' => true,
            ]);
        }

        $response = $this->actingAs($this->admin)->put(route('admin.emergency-settings.update', $setting->id), [
            'price' => '',
        ]);

        $response->assertSessionHasErrors(['price']);
    }

    public function test_admin_can_bulk_update_emergency_settings(): void
    {
        $setting1 = EmergencySetting::first();
        if (!$setting1) {
            $region = Region::firstOrCreate(['code' => 'test_region']);
            $setting1 = EmergencySetting::create([
                'region_id' => $region->id,
                'price' => 150.00,
                'is_active' => true,
            ]);
        }

        $payload = [
            'settings' => [
                [
                    'id' => $setting1->id,
                    'price' => 195.00,
                    'estimated_time_minutes' => 35,
                    'is_active' => '1',
                ],
            ],
        ];

        $response = $this->actingAs($this->admin)->put(route('admin.emergency-settings.bulk-update'), $payload);

        $response->assertRedirect(route('admin.emergency-settings.index'))
            ->assertSessionHas('status');

        $setting1->refresh();
        $this->assertEquals(195.00, (float) $setting1->price);
        $this->assertEquals(35, $setting1->estimated_time_minutes);
    }
}
