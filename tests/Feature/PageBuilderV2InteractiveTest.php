<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\Embed;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * دفعة 4: كتل تفاعليّة (أكورديون=details أصليّ، تبويبات=خطّ أصول مُدقَّق) + تضمين فيديو
 * (iframe مبنيّ خادميّاً من مضيف مسموح) + حقن pb-runtime.js شرطيّاً.
 */
class PageBuilderV2InteractiveTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    private function previewHtml(array $blocks): string
    {
        return $this->actingAs($this->admin())
            ->post(route('admin.pb.preview'), ['body' => $blocks])
            ->assertOk()->getContent();
    }

    public function test_embed_builds_safe_iframes_and_rejects_others(): void
    {
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', Embed::iframe('https://youtu.be/dQw4w9WgXcQ'));
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', Embed::iframe('https://www.youtube.com/watch?v=dQw4w9WgXcQ'));
        $this->assertStringContainsString('player.vimeo.com/video/123456789', Embed::iframe('https://vimeo.com/123456789'));
        $this->assertNull(Embed::iframe('https://evil.example.com/x'));
        $this->assertNull(Embed::iframe('javascript:alert(1)'));
        $this->assertNull(Embed::iframe(''));
    }

    public function test_video_block_renders_allowlisted_embed_only(): void
    {
        $ok = $this->previewHtml([['type' => 'video', 'props' => ['url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'caption' => 'شرح']]]);
        $this->assertStringContainsString('youtube-nocookie.com/embed/dQw4w9WgXcQ', $ok);
        $this->assertStringContainsString('شرح', $ok);

        $bad = $this->previewHtml([['type' => 'video', 'props' => ['url' => 'https://evil.example.com/hack']]]);
        $this->assertStringNotContainsString('<iframe', $bad); // رابط غير مسموح ⟶ لا شيء
    }

    public function test_accordion_renders_native_details(): void
    {
        $html = $this->previewHtml([['type' => 'accordion', 'props' => ['items' => [
            ['title' => 'سؤال أوّل', 'content' => 'جواب أوّل'],
        ]]]]);
        $this->assertStringContainsString('<details class="pb-acc-item"', $html);
        $this->assertStringContainsString('سؤال أوّل', $html);
        $this->assertStringContainsString('جواب أوّل', $html);
    }

    public function test_tabs_render_and_inject_runtime_conditionally(): void
    {
        $withTabs = $this->previewHtml([['type' => 'tabs', 'props' => ['items' => [
            ['title' => 'تبويب أ', 'content' => 'محتوى أ'],
            ['title' => 'تبويب ب', 'content' => 'محتوى ب'],
        ]]]]);
        $this->assertStringContainsString('data-pb-tabs', $withTabs);
        $this->assertStringContainsString('تبويب أ', $withTabs);
        $this->assertStringContainsString('pb-runtime.js', $withTabs); // خطّ الأصول حُقِن

        $noTabs = $this->previewHtml([['type' => 'heading', 'props' => ['text' => 'بلا تفاعل', 'level' => 'h2']]]);
        $this->assertStringNotContainsString('pb-runtime.js', $noTabs); // لم يُحقَن بلا كتلة تفاعليّة
    }
}
