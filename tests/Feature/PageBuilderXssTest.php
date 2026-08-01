<?php

namespace Tests\Feature;

use App\Models\PageBuilder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * تحصين رندرة صفحات PageBuilder العامّة (pages/show) ضدّ XSS المخزَّن:
 * حقن اسم الوسم (heading level / list type)، ومخطّطات الروابط javascript:، وiframe غير موثوق.
 * تُصيَّر لزوّار غير مصادَقين على /pages/{slug} — فالتحصين حرِج.
 */
class PageBuilderXssTest extends TestCase
{
    use RefreshDatabase;

    public function test_safe_url_neutralizes_dangerous_schemes(): void
    {
        $this->assertSame('#', safe_url('javascript:alert(1)'));
        $this->assertSame('#', safe_url("jav\tascript:alert(1)"));
        $this->assertSame('#', safe_url('data:text/html,x'));
        $this->assertSame('#', safe_url('JavaScript:alert(1)'));
        $this->assertSame('https://youtube.com/e/x', safe_url('https://youtube.com/e/x'));
        $this->assertSame('/relative', safe_url('/relative'));
        $this->assertSame('mailto:a@b.com', safe_url('mailto:a@b.com'));
    }

    public function test_public_page_render_neutralizes_injected_blocks(): void
    {
        $page = PageBuilder::create([
            'page_name' => 'xss-probe',
            'slug' => 'xss-probe',
            'json_data' => [
                'sections' => [[
                    'columns' => 1,
                    'grid' => [[
                        ['type' => 'heading', 'content' => ['level' => 'img src=x onerror=alert(1)', 'text' => 'مرحبا']],
                        ['type' => 'list', 'content' => ['type' => 'script', 'items' => ['أ', 'ب']]],
                        ['type' => 'button', 'content' => ['link' => 'javascript:alert(document.cookie)', 'text' => 'زر']],
                        ['type' => 'link', 'content' => ['url' => 'javascript:alert(1)', 'text' => 'رابط']],
                        ['type' => 'video', 'content' => ['url' => 'javascript:alert(1)//youtube.com']],
                    ]],
                ]],
            ],
            'is_active' => true,
        ]);

        $res = $this->get('/pages/' . $page->slug);
        $res->assertOk();

        // اسم الوسم المحقون لم يُصيَّر (whitelist): لا <img onerror ولا <script class=component-list
        $res->assertDontSee('src=x onerror', false);
        $res->assertDontSee('<script class="component-list"', false);
        // مخطّط javascript: حُيِّد إلى #
        $res->assertDontSee('javascript:alert', false);
        // العنوان صار h2 آمناً، والقائمة ul، والروابط #
        $res->assertSee('component-heading', false);
        $res->assertSee('href="#"', false);
        // iframe يوتيوب المزيّف لم يُصيَّر (ليس https حقيقيّاً)
        $res->assertDontSee('<iframe', false);
    }
}
