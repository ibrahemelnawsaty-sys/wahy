<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\BlockStyle;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * دفعة 3: تصميم الكتلة (خلفيّة/نصّ/محاذاة/حشو/عرض). تتحقّق من التعقيم الصارم (لا حقن CSS)
 * ومن تطبيق التصميم المُعقَّم على المستند المُصيَّر.
 */
class PageBuilderV2BlockStyleTest extends TestCase
{
    use RefreshDatabase;

    public function test_sanitizer_allows_only_whitelisted_safe_values(): void
    {
        $out = BlockStyle::inline([
            'bg' => '#ffcc00', 'color' => '#111', 'align' => 'center', 'pt' => 40, 'pb' => 20, 'maxw' => 800,
        ]);
        $this->assertStringContainsString('background:#ffcc00', $out);
        $this->assertStringContainsString('color:#111', $out);
        $this->assertStringContainsString('text-align:center', $out);
        $this->assertStringContainsString('padding-top:40px', $out);
        $this->assertStringContainsString('max-width:800px', $out);
    }

    public function test_sanitizer_rejects_css_injection_and_bad_values(): void
    {
        $out = BlockStyle::inline([
            'bg' => 'red;}body{display:none}',   // ليس hex → يُرفض
            'color' => 'javascript:alert(1)',    // يُرفض
            'align' => 'center;}x{',              // ليس من القائمة → يُرفض
            'pt' => '40px;evil',                  // ليس رقماً → يُرفض
            'maxw' => 999999,                     // فوق الحدّ → يُرفض
        ]);
        $this->assertSame('', $out);
        $this->assertStringNotContainsString('body', $out);
        $this->assertStringNotContainsString('}', $out);
    }

    public function test_block_style_applies_in_rendered_document(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $html = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => [
            ['type' => 'heading', 'props' => ['text' => 'مُلوَّن', 'level' => 'h2', '_style' => ['bg' => '#0000ff', 'align' => 'center']]],
        ]])->assertOk()->getContent();

        $this->assertStringContainsString('<div class="pb-blockwrap"', $html);
        $this->assertStringContainsString('background:#0000ff', $html);
        $this->assertStringContainsString('مُلوَّن', $html);
    }

    public function test_device_visibility_classes_render_on_wrapper(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $html = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => [
            ['type' => 'heading', 'props' => ['text' => 'مخفيّ على الجوّال', 'level' => 'h2', '_style' => ['hide_mobile' => true, 'hide_desktop' => true]]],
        ]])->assertOk()->getContent();

        $this->assertStringContainsString('<div class="pb-blockwrap pb-hide-mobile pb-hide-desktop"', $html);
    }

    public function test_unstyled_block_is_not_wrapped(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $html = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => [
            ['type' => 'heading', 'props' => ['text' => 'بلا تنسيق', 'level' => 'h2']],
        ]])->assertOk()->getContent();

        $this->assertStringNotContainsString('<div class="pb-blockwrap"', $html);
    }
}
