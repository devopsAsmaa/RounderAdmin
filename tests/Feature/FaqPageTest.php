<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\DatabaseTransactions;
use Tests\TestCase;

class FaqPageTest extends TestCase
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

    public function test_public_can_view_faq_page(): void
    {
        $response = $this->get('/faq');

        $response->assertOk()
            ->assertSee('الأسئلة الشائعة')
            ->assertSee('خدمات الأفراد والحجز')
            ->assertSee('الأسعار والدفع والضمان')
            ->assertSee('حلول الأعمال والشركات')
            ->assertSee('بوابة مزودي الخدمة والفنيين')
            ->assertSee('كيف أطلب خدمة صيانة منزلية من راوندر؟')
            ->assertSee('faq-search-input');
    }

    public function test_faqs_alias_redirects_to_faq(): void
    {
        $response = $this->get('/faqs');

        $response->assertRedirect('/faq');
    }

    public function test_landing_page_faq_section_contains_real_operational_qa(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('في بالك سؤال؟')
            ->assertSee('كيف أطلب خدمة صيانة منزلية من راوندر؟')
            ->assertSee('هل تتوفر ضمانات على أعمال الصيانة المنفذة؟')
            ->assertSee('حلول صيانة مخصصة للشركات والعقارات')
            ->assertSee('عرض جميع الأسئلة الشائعة')
            ->assertDontSee('الطلبات لا تُرسل إلى فنيين');
    }
}

