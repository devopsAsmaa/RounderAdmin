<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\{User, Role};
use App\Modules\Catalog\Models\Service;
use App\Modules\Catalog\Models\ServiceCategory;
use App\Modules\Providers\Models\ProviderProfile;
use App\Services\RichTextSanitizer;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

/** تغطية رحلة الإضافة والتعديل وعزل بيانات العقود على MySQL. */
class ProviderFormsTest extends TestCase
{
    use DatabaseTransactions;

    /** @return User */
    private function admin(): \App\Modules\Identity\Models\Admin
    {
        $this->seed();
        return \App\Modules\Identity\Models\Admin::create(['is_super_admin'=>true,'is_active'=>true,'name'=>'Admin', 'email'=>'provider-admin@example.test',
            'password'=>'StrongPassword123!', 'locale'=>'ar',
            'role_id'=>Role::where('code','admin')->value('id')]);
    }

    /** @return array<string, mixed> */
    private function payload(): array
    {
        return ['name'=>'Contact person','email'=>'company@example.test','phone'=>'+966511112222',
            'locale'=>'en','password'=>'StrongPassword123!','password_confirmation'=>'StrongPassword123!',
            'company_name'=>'Rounder partner','is_approved'=>1,'service_commission_percent'=>'10.00',
            'emergency_commission_percent'=>'15.00','starts_on'=>'2026-10-01','ends_on'=>null,
            'terms'=>'<h2>Terms</h2><p><strong>Approved</strong></p><script>alert(1)</script><img src=x onerror=alert(1)>'];
    }

    /** @return void */
    public function test_listing_add_and_edit_are_separate_and_save_atomically(): void
    {
        $this->actingAs($this->admin());
        $this->get('/admin/providers')->assertOk()->assertSee(route('admin.providers.create'))
            ->assertDontSee('name="company_name"', false);
        foreach (['ar','en'] as $locale) {
            $this->withSession(['locale'=>$locale])->get('/admin/providers/create')->assertOk()
                ->assertSee('contenteditable="true"', false)->assertSee('name="terms"', false);
        }
        $category = ServiceCategory::create(['code' => 'provider-service']);
        $service = Service::create([
            'service_category_id' => $category->id,
            'code' => 'provider-service-test',
            'base_price' => 100,
            'is_active' => true,
        ]);
        $data = $this->payload();
        $data['service_ids'] = [$service->id];
        $this->post('/admin/providers', $data)->assertRedirect(route('admin.providers.index'))->assertSessionHasNoErrors();
        $provider = ProviderProfile::where('company_name',$data['company_name'])->firstOrFail();
        $this->assertDatabaseHas('provider_services', [
            'provider_profile_id' => $provider->id,
            'service_id' => $service->id,
        ]);
        $contract = $provider->contracts()->firstOrFail();
        $this->assertSame('<h2>Terms</h2><p><strong>Approved</strong></p>', $contract->terms);
        $this->assertTrue(Hash::check($data['password'], $provider->user->password));
        $this->get('/admin/providers')->assertSee(route('admin.providers.edit',$provider->id));
        $this->get('/admin/providers/'.$provider->id.'/edit')->assertOk()
            ->assertSee($data['company_name'])
            ->assertSee('name="service_ids[]"', false)
            ->assertSee('checked', false);
        $data['company_name'] = 'Updated company';
        $data['name'] = 'Updated contact';
        $data['is_approved'] = 0;
        $data['service_ids'] = [];
        unset($data['password'], $data['password_confirmation']);
        $data['service_commission_percent'] = 99;
        $this->put('/admin/providers/'.$provider->id,$data)->assertRedirect(route('admin.providers.index'))->assertSessionHasNoErrors();
        $this->assertDatabaseHas('provider_profiles',['id'=>$provider->id,'company_name'=>'Updated company','is_approved'=>0]);
        $this->assertDatabaseMissing('provider_services', [
            'provider_profile_id' => $provider->id,
            'service_id' => $service->id,
        ]);
        $this->assertDatabaseHas('users',['id'=>$provider->user_id,'name'=>'Updated contact']);
        $this->assertTrue(Hash::check('StrongPassword123!',$provider->user->fresh()->password));
        $this->assertEquals(10, $contract->fresh()->service_commission_percent);
        $this->assertSame(1,$provider->contracts()->count());

        // التحقق من تغيير كلمة المرور عند تقديمها مع التأكيد
        $data['password'] = 'NewValidPass123!';
        $data['password_confirmation'] = 'NewValidPass123!';
        $this->put('/admin/providers/'.$provider->id, $data)->assertRedirect(route('admin.providers.index'))->assertSessionHasNoErrors();
        $this->assertTrue(Hash::check('NewValidPass123!', $provider->user->fresh()->password));

        // التحقق من رفض كلمة المرور عند عدم تطابق التأكيد
        $data['password'] = 'MismatchPass123!';
        $data['password_confirmation'] = 'Different123!';
        $this->put('/admin/providers/'.$provider->id, $data)->assertSessionHasErrors('password');

        $this->get('/admin/providers/999999/edit')->assertNotFound();
    }

    /** @return void */
    public function test_validation_and_permissions_prevent_partial_or_unauthorized_changes(): void
    {
        $admin = $this->admin();
        $data = $this->payload();
        $this->actingAs($admin)->post('/admin/providers',$data)->assertSessionHasNoErrors();
        $provider = ProviderProfile::where('company_name',$data['company_name'])->firstOrFail();
        $other = User::create(['name'=>'Other provider','email'=>'other-provider@example.test','password'=>'StrongPassword123!',
            'role_id'=>Role::where('code','provider')->value('id'),'locale'=>'ar']);
        $data['email'] = $other->email;
        $data['company_name'] = 'Must not save';
        $this->put('/admin/providers/'.$provider->id,$data)->assertSessionHasErrors('email');
        $this->assertDatabaseMissing('provider_profiles',['company_name'=>'Must not save']);
        $data['phone'] = 'invalid';
        $this->from('/admin/providers/create')->post('/admin/providers',$data)
            ->assertRedirect('/admin/providers/create')->assertSessionHasErrors(['email','phone']);
        $resident = User::create(['name'=>'Resident','email'=>'forms-resident@example.test',
            'password'=>'StrongPassword123!','locale'=>'ar','role_id'=>Role::where('code','resident')->value('id')]);
        $this->actingAs($resident)->get('/admin/providers/create')->assertForbidden();
        $this->get('/admin/providers/'.$provider->id.'/edit')->assertForbidden();
        $this->put('/admin/providers/'.$provider->id,$this->payload())->assertForbidden();
    }

    /** @return void */
    public function test_contract_html_retains_formatting_without_active_content(): void
    {
        $cleaner = app(RichTextSanitizer::class);
        $this->assertNull($cleaner->clean(null));
        $this->assertSame('<p>شروط <b>العقد</b></p><ul><li>One</li></ul>',
            $cleaner->clean('<p onclick="bad()">شروط <b style="color:red">العقد</b></p><ul><li>One</li></ul>'));
        $html = $cleaner->clean('<svg><script>bad()</script></svg><iframe srcdoc="x"></iframe><a href="javascript:bad()">Text</a><p onmouseover="bad()">Safe</p>');
        $this->assertSame('Text<p>Safe</p>', $html);
        $this->assertSame('Plain terms', $cleaner->clean('Plain terms'));
    }

    public function test_admin_can_approve_provider_from_modal_route(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $data = $this->payload();
        $data['is_approved'] = 0; // Create unapproved provider
        $this->post('/admin/providers', $data)->assertRedirect(route('admin.providers.index'));

        $provider = ProviderProfile::where('company_name', $data['company_name'])->firstOrFail();
        $this->assertFalse((bool) $provider->is_approved);

        // Verify index page displays approval modal for unapproved provider
        $indexResponse = $this->get('/admin/providers');
        $indexResponse->assertOk()
            ->assertSee('approveProviderModal' . $provider->id)
            ->assertSee(route('admin.providers.approve', $provider->id));

        // Submit approval
        $response = $this->post(route('admin.providers.approve', $provider->id));
        $response->assertRedirect();

        $this->assertTrue((bool) $provider->fresh()->is_approved);
        $contract = $provider->fresh()->contracts()->latest('id')->first();
        $this->assertSame('active', $contract?->status?->code);
    }

    public function test_admin_approving_provider_from_edit_updates_status(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin);

        $data = $this->payload();
        $data['is_approved'] = 0;
        $this->post('/admin/providers', $data);

        $provider = ProviderProfile::where('company_name', $data['company_name'])->firstOrFail();
        $this->assertFalse((bool) $provider->is_approved);

        // Edit and set is_approved to 1
        $data['is_approved'] = 1;
        $contract = $provider->contracts()->latest('id')->first();
        $data['contract_id'] = $contract->id;
        $this->put('/admin/providers/' . $provider->id, $data)->assertRedirect(route('admin.providers.index'));

        $this->assertTrue((bool) $provider->fresh()->is_approved);
    }

    public function test_provider_approval_sms_is_translated_in_all_three_languages(): void
    {
        $translations = [
            'ar' => __('providers.approval_sms', ['company_name' => 'شركة راوندر', 'login_url' => 'https://rounder-sa.com/provider/login'], 'ar'),
            'en' => __('providers.approval_sms', ['company_name' => 'Rounder Company', 'login_url' => 'https://rounder-sa.com/provider/login'], 'en'),
            'ur' => __('providers.approval_sms', ['company_name' => 'راؤنڈر کمپنی', 'login_url' => 'https://rounder-sa.com/provider/login'], 'ur'),
        ];

        $this->assertStringContainsString('تم اعتماد وتفعيل حسابكم في منصة راوندر بنجاح', $translations['ar']);
        $this->assertStringContainsString('شركة راوندر', $translations['ar']);

        $this->assertStringContainsString('your account on Rounder has been successfully approved and activated', $translations['en']);
        $this->assertStringContainsString('Rounder Company', $translations['en']);

        $this->assertStringContainsString('راؤنڈر پر آپ کا اکاؤنٹ کامیابی سے منظور اور فعال کر دیا گیا ہے', $translations['ur']);
        $this->assertStringContainsString('راؤنڈر کمپنی', $translations['ur']);
    }
}
