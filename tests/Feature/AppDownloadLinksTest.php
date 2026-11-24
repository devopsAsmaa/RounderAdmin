<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Role;
use App\Modules\Settings\Models\CompanySetting;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AppDownloadLinksTest extends TestCase
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
            ['email' => 'admin_app_tester@rounder.sa'],
            [
                'role_id' => $adminRole->id,
                'name' => 'مدير تجريبي لإعدادات التطبيق',
                'password' => 'Secret123!',
                'is_active' => true,
                'is_super_admin' => true,
                'locale' => 'ar',
            ]
        );

        CompanySetting::query()->firstOrCreate([], [
            'trade_name' => 'شركة راوندر لخدمات التشغيل والصيانة',
            'cr_number' => '10102497799',
            'legal_representative' => 'سعيد احمد',
            'representative_title' => 'المدير العام',
            'support_phone' => '920000488',
            'support_email' => 'support@rounder.sa',
            'app_store_url' => 'https://apps.apple.com/sa/app/6829814780',
            'play_store_url' => 'https://play.google.com/store/apps/details?id=com.futuretec.rounderapp.sa',
            'app_download_enabled' => true,
        ]);
    }

    public function test_landing_page_displays_app_showcase_and_download_links(): void
    {
        $setting = CompanySetting::first();
        $setting->update([
            'app_store_url' => 'https://apps.apple.com/sa/app/6829814780',
            'play_store_url' => 'https://play.google.com/store/apps/details?id=com.futuretec.rounderapp.sa',
            'app_download_enabled' => true,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('تطبيق راوندر متوفر الآن على الجوال')
            ->assertSee('كل خدمات بيتك وصيانتك')
            ->assertSee('في جيبك.. بلمسة واحدة!')
            ->assertSee('https://apps.apple.com/sa/app/6829814780')
            ->assertSee('https://play.google.com/store/apps/details?id=com.futuretec.rounderapp.sa')
            ->assertSee('app-mockup.png');
    }

    public function test_app_showcase_is_hidden_when_disabled(): void
    {
        $setting = CompanySetting::first();
        $setting->update([
            'app_download_enabled' => false,
        ]);

        $response = $this->get('/');

        $response->assertOk()
            ->assertDontSee('id="app-download"', false)
            ->assertDontSee('تطبيق راوندر متوفر الآن على الجوال');
    }

    public function test_admin_can_update_app_download_settings(): void
    {
        $response = $this->actingAs($this->admin)->put(route('admin.company-settings.update'), [
            'trade_name' => 'شركة راوندر لخدمات التشغيل والصيانة',
            'cr_number' => '10102497799',
            'legal_representative' => 'سعيد احمد',
            'representative_title' => 'المدير العام',
            'support_phone' => '920000488',
            'support_email' => 'support@rounder.sa',
            'support_working_hours' => 'الأحد إلى الخميس، 10 ص - 8 م',
            'support_chat_enabled' => '1',
            'app_store_url' => 'https://apps.apple.com/sa/app/rounder-test/id99999999',
            'play_store_url' => 'https://play.google.com/store/apps/details?id=com.futuretec.testapp',
            'app_download_enabled' => '1',
        ]);

        $response->assertSessionHas('status');

        $updated = CompanySetting::current();
        $this->assertEquals('https://apps.apple.com/sa/app/rounder-test/id99999999', $updated->app_store_url);
        $this->assertEquals('https://play.google.com/store/apps/details?id=com.futuretec.testapp', $updated->play_store_url);
        $this->assertTrue($updated->app_download_enabled);
    }

    public function test_faq_and_contact_footers_display_store_links(): void
    {
        $setting = CompanySetting::first();
        $setting->update([
            'app_store_url' => 'https://apps.apple.com/sa/app/6829814780',
            'play_store_url' => 'https://play.google.com/store/apps/details?id=com.futuretec.rounderapp.sa',
            'app_download_enabled' => true,
        ]);

        $faqResponse = $this->get('/faq');
        $faqResponse->assertOk()
            ->assertSee('حمّل تطبيق راوندر:')
            ->assertSee('https://apps.apple.com/sa/app/6829814780')
            ->assertSee('https://play.google.com/store/apps/details?id=com.futuretec.rounderapp.sa');

        $contactResponse = $this->get('/contact-us');
        $contactResponse->assertOk()
            ->assertSee('حمّل تطبيق راوندر:')
            ->assertSee('https://apps.apple.com/sa/app/6829814780')
            ->assertSee('https://play.google.com/store/apps/details?id=com.futuretec.rounderapp.sa');
    }
}

