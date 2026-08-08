<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 4 (مهامّ 14،15،16،18): تنظيف روابط الفوتر + زرّ واتساب عائم.
 * (13 و17 مُنجزتان سابقاً: مستقبِل بريد التواصل + تعديل الإيميل/الجوال في الإعدادات.)
 */
class LandingBatch4Test extends TestCase
{
    use RefreshDatabase;

    public function test_footer_links_cleaned(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        $res->assertDontSee('لوحة التحكم', false);      // مهمّة 15
        $res->assertDontSee('سياسة الاستخدام', false);   // مهمّة 16
        // تبقى الشروط والخصوصية (الدفعة 5 تُفعّلهما)
        $res->assertSee('سياسة الخصوصية', false);
        $res->assertSee('الشروط والأحكام', false);
    }

    public function test_whatsapp_button_hidden_without_number(): void
    {
        $this->get('/')->assertOk()->assertDontSee('wa.me', false);
    }

    public function test_whatsapp_button_shows_and_sanitizes_number(): void
    {
        set_setting('whatsapp_number', '+966 50 123 4567', 'string');
        Setting::clearCache();

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('https://wa.me/966501234567', false); // أرقام فقط — بلا حقن
        $res->assertSee('wa-float', false);
    }
}
