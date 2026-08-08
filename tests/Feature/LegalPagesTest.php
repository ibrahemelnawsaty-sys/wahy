<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 5 (مهامّ 20، 21): صفحتا الشروط والأحكام + سياسة الخصوصية + ربطهما بالفوتر.
 */
class LegalPagesTest extends TestCase
{
    use RefreshDatabase;

    public function test_terms_page_renders(): void
    {
        $res = $this->get('/terms');
        $res->assertOk();
        $res->assertSee('الشروط والأحكام', false);
        $res->assertSee('أثيل مكة', false);         // العلامة من الإعداد (بعد الهجرة)
        $res->assertSee('atheel-makkah.com', false); // النطاق الجديد
    }

    public function test_privacy_page_renders(): void
    {
        $res = $this->get('/privacy');
        $res->assertOk();
        $res->assertSee('سياسة الخصوصية', false);
        $res->assertSee('خصوصية الطلاب', false);      // قسم القاصرين
        $res->assertSee('atheel-makkah.com', false);
    }

    public function test_footer_links_reach_legal_pages(): void
    {
        // روابط الفوتر على الصفحة الرئيسية تشير للصفحتين (لا 404)
        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('href="/privacy"', false);
        $res->assertSee('href="/terms"', false);
    }

    public function test_no_stale_qiyamm_domain_on_legal_pages(): void
    {
        $this->get('/terms')->assertOk()->assertDontSee('qiyamm', false);
        $this->get('/privacy')->assertOk()->assertDontSee('qiyamm', false);
    }
}
