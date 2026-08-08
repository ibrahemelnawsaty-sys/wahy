<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 3 (مهامّ 3،11،12): رؤية الأقسام بأعلام الإعدادات — إخفاء/إعادة بلا نشر كود.
 * الافتراضات: إحصائيات مخفيّة · فوائد ظاهرة · شركاء مخفيّون.
 */
class LandingSectionVisibilityTest extends TestCase
{
    use RefreshDatabase;

    public function test_default_visibility(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        $res->assertDontSee('50k+', false);                 // إحصائيات مخفيّة افتراضاً
        $res->assertDontSee('2k+', false);
        $res->assertDontSee('شركاؤنا في النجاح', false);     // الشركاء مخفيّون افتراضاً
        $res->assertSee('فوائد التعلم التعاوني', false);      // الفوائد ظاهرة افتراضاً
    }

    public function test_flags_toggle_sections(): void
    {
        set_setting('show_hero_stats', true, 'boolean');
        set_setting('show_partners', true, 'boolean');
        set_setting('show_coop_benefits', false, 'boolean');
        Setting::clearCache();

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('50k+', false);                      // ظهرت الإحصائيات
        $res->assertSee('شركاؤنا في النجاح', false);          // ظهر الشركاء
        $res->assertDontSee('فوائد التعلم التعاوني', false);  // أُخفيت الفوائد
    }

    public function test_partners_header_link_follows_flag(): void
    {
        // العلم مطفأ → لا رابط ترويسة «#partners» (ولا القسم) — لا مِرساة ميتة
        $this->get('/')->assertOk()->assertDontSee('href="#partners"', false);

        set_setting('show_partners', true, 'boolean');
        Setting::clearCache();
        $this->get('/')->assertOk()->assertSee('href="#partners"', false);
    }
}
