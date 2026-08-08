<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 1: إعادة تسمية العلامة إلى «أثيل مكة» + هيرو جديد (مهامّ 1،2،5).
 * يتحقّق من ظهور المحتوى الجديد للزائر واختفاء الصياغة القديمة.
 */
class LandingRebrandTest extends TestCase
{
    use RefreshDatabase;

    public function test_landing_shows_rebranded_hero_and_why_section(): void
    {
        $res = $this->get('/');
        $res->assertOk();

        // العلامة الجديدة (site_name ثبّتته هجرة إعادة التسمية = «أثيل مكة»)
        $res->assertSee('أثيل مكة', false);
        // نصّ الهيرو الجديد
        $res->assertSee('قيم نبوية يحيى بها الطالب', false);
        $res->assertSee('من مكة المكرمة', false);
        // قسم المزايا «لماذا أثيل مكة؟»
        $res->assertSee('لماذا أثيل مكة؟', false);
    }

    public function test_old_branding_is_gone(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        $res->assertDontSee('منصة القيم المدرسية – تعليم يعيش مع الطلاب', false);
        $res->assertDontSee('لماذا قيمّ؟', false);
    }

    public function test_site_name_setting_is_atheel_after_migration(): void
    {
        $this->assertSame('أثيل مكة', setting('site_name'));
    }
}
