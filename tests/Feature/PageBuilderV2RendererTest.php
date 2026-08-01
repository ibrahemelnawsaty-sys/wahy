<?php

namespace Tests\Feature;

use App\PageBuilder\BlockRegistry;
use App\PageBuilder\BlockTree;
use Tests\TestCase;

/**
 * المرحلة 1: المُصيِّر الآمن لشجرة الكتل (مكوّنات Blade موثوقة، لا HTML خام).
 * قائمة سماح الأنواع + تحييد XSS في كلّ حقل + ترقية الكتلة عند القراءة (ت-٢).
 */
class PageBuilderV2RendererTest extends TestCase
{
    private function render(array $blocks): string
    {
        return view('pb.renderer', ['blocks' => BlockTree::prepare($blocks)])->render();
    }

    public function test_prepare_drops_unknown_block_types(): void
    {
        $blocks = [
            ['type' => 'hero', 'props' => ['title' => 'مرحبا']],
            ['type' => 'evil_unknown', 'props' => ['x' => 1]],
            ['type' => 'spacer', 'props' => ['height' => 40]],
        ];
        $prepared = BlockTree::prepare($blocks);

        $this->assertCount(2, $prepared);
        $this->assertSame(['hero', 'spacer'], array_column($prepared, 'type'));
    }

    public function test_renderer_escapes_and_neutralizes_injected_payloads(): void
    {
        $html = $this->render([
            ['type' => 'hero', 'props' => [
                'title' => '<img src=x onerror=alert(1)>',
                'button_text' => 'اضغط',
                'button_link' => 'javascript:alert(document.cookie)',
            ]],
            ['type' => 'richtext', 'props' => ['html' => '<p>مرحبا</p><script>alert(1)</script>']],
            ['type' => 'button', 'props' => ['text' => 'زر', 'link' => 'vbscript:msgbox(1)', 'style' => 'evil-style']],
        ]);

        // العنوان الخبيث مُهرَّب (لا يُنفَّذ)
        $this->assertStringNotContainsString('<img src=x onerror', $html);
        $this->assertStringContainsString('&lt;img', $html);
        // مخطّطات الروابط حُيِّدت إلى #
        $this->assertStringNotContainsString('javascript:alert', $html);
        $this->assertStringNotContainsString('vbscript:', $html);
        $this->assertStringContainsString('href="#"', $html);
        // النصّ الغنيّ: <script> أُزيل، <p> بقي
        $this->assertStringNotContainsString('<script>alert', $html);
        $this->assertStringContainsString('<p>مرحبا</p>', $html);
        // نمط الزرّ غير المسموح استُبدِل بـprimary (لا يُصبّ خاماً في الصنف)
        $this->assertStringNotContainsString('pb-btn-evil-style', $html);
        $this->assertStringContainsString('pb-btn-primary', $html);
    }

    public function test_columns_render_nested_children_safely(): void
    {
        $html = $this->render([
            ['type' => 'columns', 'props' => ['count' => 2], 'children' => [
                ['type' => 'hero', 'props' => ['title' => 'عمود أوّل']],
                ['type' => 'unknown', 'props' => []],
                ['type' => 'spacer', 'props' => ['height' => 10]],
            ]],
        ]);

        $this->assertStringContainsString('pb-columns', $html);
        $this->assertStringContainsString('عمود أوّل', $html);
        $this->assertStringContainsString('--pb-cols:2', $html);
    }

    public function test_block_upgrade_mechanism_runs_on_read(): void
    {
        // آليّة الترقية (ت-٢): كتلة تُقرأ ويُثبَّت لها v = الإصدار الحاليّ دون كسر.
        $prepared = BlockTree::prepare([['type' => 'hero', 'v' => 1, 'props' => ['title' => 'x']]]);
        $this->assertSame(BlockRegistry::currentVersion('hero'), $prepared[0]['v']);
    }
}
