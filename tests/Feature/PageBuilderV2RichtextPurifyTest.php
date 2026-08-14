<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\HtmlPurify;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * §10.12: كتلة النصّ الغنيّ تُعقَّم بقائمة سماح HTMLPurifier — تُبقي التنسيق الآمن وتُسقط الخطر.
 */
class PageBuilderV2RichtextPurifyTest extends TestCase
{
    use RefreshDatabase;

    public function test_purify_keeps_safe_tags_and_strips_dangerous(): void
    {
        $out = HtmlPurify::clean(
            '<p>مرحبا <strong>عالم</strong> <a href="https://x.com">رابط</a></p>'
            . '<script>alert(1)</script><img src="javascript:alert(1)"><iframe src="https://evil"></iframe>'
        );

        $this->assertStringContainsString('<strong>عالم</strong>', $out);
        $this->assertStringContainsString('https://x.com', $out);
        $this->assertStringNotContainsString('<script', $out);
        $this->assertStringNotContainsString('javascript:', $out);
        $this->assertStringNotContainsString('<iframe', $out);
    }

    public function test_richtext_block_purifies_on_render(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $html = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => [
            ['type' => 'richtext', 'props' => ['html' => '<p>نصّ <b>غامق</b></p><script>alert(1)</script>']],
        ]])->assertOk()->getContent();

        $this->assertStringContainsString('<b>غامق</b>', $html);
        $this->assertStringNotContainsString('<script>alert(1)</script>', $html);
    }
}
