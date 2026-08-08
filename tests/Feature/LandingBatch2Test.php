<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 2 (مهامّ 6،8،9،10): المزايا 9، المنهجية 5 خطوات، حذف «مثال عملي»، أسماء الفرق.
 */
class LandingBatch2Test extends TestCase
{
    use RefreshDatabase;

    public function test_nine_features_present(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        // الخمس الجديدة
        foreach (['تحديات ومنافسات', 'قياس الأثر', 'أنشطة سلوكية', 'تحفيز وتنشيط', 'الانسيابية والسهولة'] as $t) {
            $res->assertSee($t, false);
        }
        // الأربع القائمة
        foreach (['QR فريد لكل مستخدم', 'لوحة صدارة ذكية', 'اقتراح أنشطة بالذكاء الاصطناعي', 'متابعة وتقييم المعلمين'] as $t) {
            $res->assertSee($t, false);
        }
    }

    public function test_five_step_methodology(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        foreach (['القيمة الكلية', 'القيمة الضمنية', 'المفاهيم الرئيسية', 'المعاني المرتبطة'] as $t) {
            $res->assertSee($t, false);
        }
        $res->assertSee('حفل الإحسان', false);   // مثال الخطوة الخامسة (الأنشطة)
        $res->assertSee('الرحمة', false);          // مثال الخطوة الأولى
    }

    public function test_practical_example_section_removed(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        $res->assertDontSee('مثال عملي: تعليم قيمة الصدق', false);
        $res->assertDontSee('قصة أحمد الصادق', false);
    }

    public function test_team_names_renamed(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('فريق الريادة', false);
        $res->assertSee('فريق السمو', false);
        $res->assertSee('فريق المعالي', false);
        $res->assertDontSee('فريق النجوم', false);
        $res->assertDontSee('فريق الصواريخ', false);
        $res->assertDontSee('فريق الماس', false);
    }
}
