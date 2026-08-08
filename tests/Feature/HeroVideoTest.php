<?php

namespace Tests\Feature;

use App\Models\Setting;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 6 (مهمّة 4): فيديو الهيرو يستبدل الصورة عند التفعيل، بمصدر آمن وبلا تشغيل تلقائيّ.
 */
class HeroVideoTest extends TestCase
{
    use RefreshDatabase;

    public function test_image_shown_when_video_disabled(): void
    {
        $res = $this->get('/');
        $res->assertOk();
        $res->assertDontSee('<video', false);          // معطَّل افتراضاً
        $res->assertSee('hero-illustration', false);    // الصورة ظاهرة
    }

    public function test_video_replaces_image_when_enabled(): void
    {
        set_setting('hero_video_enabled', true, 'boolean');
        set_setting('hero_video_url', 'videos/hero-main.mp4', 'string');
        Setting::clearCache();

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('<video', false);
        $res->assertSee('videos/hero-main.mp4', false);
        $res->assertSee('preload="metadata"', false);   // لا تشغيل تلقائيّ
        $res->assertDontSee('data-editable-image="hero_image"', false); // استُبدلت الصورة
    }

    public function test_dangerous_video_url_is_neutralized(): void
    {
        set_setting('hero_video_enabled', true, 'boolean');
        set_setting('hero_video_url', 'javascript:alert(1)', 'string');
        Setting::clearCache();

        $res = $this->get('/');
        $res->assertOk();
        // مخطّط خطير → مصدر فارغ → يرتدّ للصورة، لا وسم فيديو
        $res->assertDontSee('<video', false);
        $res->assertDontSee('javascript:alert(1)', false);
    }
}
