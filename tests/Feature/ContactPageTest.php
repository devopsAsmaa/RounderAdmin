<?php

namespace Tests\Feature;

use App\Modules\Identity\Models\Role;
use App\Modules\Identity\Models\User;
use App\Modules\Support\Enums\SupportCategory;
use App\Modules\Support\Enums\SupportStatus;
use App\Modules\Support\Models\SupportTicket;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ContactPageTest extends TestCase
{
    use DatabaseTransactions;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
        $this->withoutMiddleware([
            \Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class,
            \Illuminate\Foundation\Http\Middleware\PreventRequestForgery::class,
        ]);
    }

    public function test_guest_can_view_contact_page(): void
    {
        $response = $this->get('/contact-us');

        $response->assertOk()
            ->assertSee('تواصل معنا')
            ->assertSee('مركز خدمة العملاء والدعم الفني')
            ->assertSee('920000488')
            ->assertSee('support@rounder.sa')
            ->assertSee('أرسل رسالتك مباشرة');
    }

    public function test_contact_alias_redirects_to_contact_us(): void
    {
        $response = $this->get('/contact');

        $response->assertRedirect('/contact-us');
    }

    public function test_validation_fails_for_empty_fields(): void
    {
        $response = $this->from('/contact-us')->post('/contact-us', []);

        $response->assertRedirect('/contact-us')
            ->assertSessionHasErrors(['name', 'phone', 'email', 'category', 'subject', 'message']);
    }

    public function test_validation_fails_for_invalid_saudi_phone(): void
    {
        $response = $this->from('/contact-us')->post('/contact-us', [
            'name' => 'محمد عبدالله',
            'phone' => '0123456789',
            'email' => 'valid@example.com',
            'category' => SupportCategory::Other->value,
            'subject' => 'استفسار عن الخدمة',
            'message' => 'هذه رسالة تجريبية لاختبار التحقق من صحة البيانات.',
        ]);

        $response->assertRedirect('/contact-us')
            ->assertSessionHasErrors(['phone']);
    }

    public function test_guest_can_submit_contact_message_and_create_ticket(): void
    {
        Storage::fake('public');

        $payload = [
            'name' => 'خالد العتيبي',
            'phone' => '0551234567',
            'email' => 'khaled.tester@example.com',
            'category' => SupportCategory::Other->value,
            'subject' => 'استفسار بخصوص خدمات الأعمال والشركات',
            'message' => 'نود الاستفسار عن إمكانية تقديم خدمات صيانة دورية للمكاتب الإدارية.',
            'attachment' => UploadedFile::fake()->create('contract_sample.pdf', 500, 'application/pdf'),
        ];

        $response = $this->from('/contact-us')->post('/contact-us', $payload);

        $response->assertRedirect('/contact-us')
            ->assertSessionHas('status')
            ->assertSessionHas('ticket_reference');

        $ticket = SupportTicket::where('subject', $payload['subject'])->first();
        $this->assertNotNull($ticket);
        $this->assertEquals(SupportStatus::Open->value, $ticket->status);
        $this->assertEquals(SupportCategory::Other->value, $ticket->category);
        $this->assertNotNull($ticket->attachment_path);
        Storage::disk('public')->assertExists($ticket->attachment_path);

        $user = User::find($ticket->user_id);
        $this->assertNotNull($user);
        $this->assertEquals('0551234567', $user->phone);
        $this->assertEquals('khaled.tester@example.com', $user->email);
    }

    public function test_authenticated_user_submits_contact_ticket_linked_to_account(): void
    {
        $residentRole = Role::where('code', 'resident')->firstOrFail();
        $user = User::create([
            'role_id' => $residentRole->id,
            'name' => 'سارة الشمري',
            'phone' => '0569876543',
            'email' => 'sara.sh@example.com',
            'locale' => 'ar',
            'password' => 'Secret123!',
        ]);

        $payload = [
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'category' => SupportCategory::Other->value,
            'subject' => 'اقتراح لإضافة خدمة جديدة',
            'message' => 'أقترح إضافة خدمة تنسيق وصيانة الحدائق المنزلية ضمن التطبيق.',
        ];

        $response = $this->actingAs($user)
            ->from('/contact-us')
            ->post('/contact-us', $payload);

        $response->assertRedirect('/contact-us')
            ->assertSessionHas('status');

        $this->assertDatabaseHas('support_tickets', [
            'user_id' => $user->id,
            'subject' => $payload['subject'],
            'category' => SupportCategory::Other->value,
        ]);
    }
}
