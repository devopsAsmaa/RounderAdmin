<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Admin;
use App\Modules\Identity\Models\Role;
use App\Modules\Settings\Models\Faq;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class AdminFaqTest extends TestCase
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
            ['email' => 'admin_faq_tester@rounder.sa'],
            [
                'role_id' => $adminRole->id,
                'name' => 'مدير تجريبي للأسئلة',
                'password' => 'Secret123!',
                'is_active' => true,
                'is_super_admin' => true,
                'locale' => 'ar',
            ]
        );
    }

    public function test_admin_can_view_faqs_list(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.faqs.index'));

        $response->assertOk()
            ->assertSee('إدارة الأسئلة الشائعة')
            ->assertSee('خدمات الأفراد والحجز');
    }

    public function test_admin_can_filter_faqs_by_category(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.faqs.index', ['category' => 'business']));

        $response->assertOk()
            ->assertSee('حلول الأعمال والشركات');
    }

    public function test_admin_can_view_create_faq_page(): void
    {
        $response = $this->actingAs($this->admin)->get(route('admin.faqs.create'));

        $response->assertOk()
            ->assertSee('إضافة سؤال شائع جديد')
            ->assertSee('نص السؤال');
    }

    public function test_admin_can_create_new_faq(): void
    {
        $payload = [
            'category' => 'individuals',
            'sort_order' => 99,
            'is_active' => 1,
            'question_ar' => 'سؤال تجريبي مضاف من الأدمن؟',
            'answer_ar' => 'هذه إجابة تجريبية للتأكد من نجاح الإضافة من لوحة التحكم.',
            'question_en' => 'Test question added from admin?',
            'answer_en' => 'This is a test answer for admin verification.',
        ];

        $response = $this->actingAs($this->admin)->post(route('admin.faqs.store'), $payload);

        $response->assertRedirect(route('admin.faqs.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('faq_translations', [
            'locale' => 'ar',
            'question' => $payload['question_ar'],
            'answer' => $payload['answer_ar'],
        ]);

        $this->assertDatabaseHas('faq_translations', [
            'locale' => 'en',
            'question' => $payload['question_en'],
        ]);
    }

    public function test_admin_can_edit_and_update_faq(): void
    {
        $faq = Faq::first();
        $this->assertNotNull($faq);

        $response = $this->actingAs($this->admin)->get(route('admin.faqs.edit', $faq->id));
        $response->assertOk()->assertSee('تعديل السؤال الشائع');

        $updatePayload = [
            'category' => 'pricing',
            'sort_order' => 50,
            'is_active' => 1,
            'question_ar' => 'سؤال محدث بالكامل؟',
            'answer_ar' => 'إجابة محدثة بالكامل من فحص الاختبار الآلي.',
        ];

        $putResponse = $this->actingAs($this->admin)->put(route('admin.faqs.update', $faq->id), $updatePayload);

        $putResponse->assertRedirect(route('admin.faqs.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseHas('faq_translations', [
            'faq_id' => $faq->id,
            'locale' => 'ar',
            'question' => 'سؤال محدث بالكامل؟',
        ]);
    }

    public function test_admin_can_toggle_faq_active_status(): void
    {
        $faq = Faq::first();
        $this->assertNotNull($faq);
        $originalState = $faq->is_active;

        $response = $this->actingAs($this->admin)->post(route('admin.faqs.toggle', $faq->id));

        $response->assertRedirect(route('admin.faqs.index'))
            ->assertSessionHas('status');

        $this->assertEquals(!$originalState, $faq->fresh()->is_active);
    }

    public function test_admin_can_delete_faq(): void
    {
        $faq = Faq::create([
            'category' => 'individuals',
            'sort_order' => 100,
            'is_active' => true,
        ]);

        $response = $this->actingAs($this->admin)->delete(route('admin.faqs.destroy', $faq->id));

        $response->assertRedirect(route('admin.faqs.index'))
            ->assertSessionHas('status');

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_guest_cannot_access_faq_admin(): void
    {
        $response = $this->get(route('admin.faqs.index'));

        $response->assertRedirect(route('admin.login'));
    }
}

