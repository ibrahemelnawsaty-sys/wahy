<?php

namespace Tests\Feature;

use App\Models\LandingLayout;
use App\Models\User;
use App\Support\LandingHtmlSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * يغطّي المحرّر المرئي المدمج: التعقيم الخادِمي + الحفظ + العرض + الصلاحيات + الاستعادة.
 */
class LandingLayoutTest extends TestCase
{
    use RefreshDatabase;

    private const DIRTY = '<section class="hero" id="home"><h1>عنوان آمن</h1>'
        . '<script>alert(1)</script>'
        . '<a href="javascript:alert(2)">رابط خبيث</a>'
        . '<a href="/register">رابط سليم</a>'
        . '<button onclick="steal()">زر</button>'
        . '<div class="section-actions">أدوات محرّر</div>'
        . '<p contenteditable="true">فقرة</p>'
        . '<iframe src="https://evil.example"></iframe></section>';

    public function test_sanitizer_strips_dangerous_content_and_keeps_safe_markup(): void
    {
        $clean = LandingHtmlSanitizer::clean(self::DIRTY);

        // يبقى المحتوى الآمن
        $this->assertStringContainsString('عنوان آمن', $clean);
        $this->assertStringContainsString('/register', $clean);

        // يُزال كل ما هو خطِر
        $this->assertStringNotContainsStringIgnoringCase('<script', $clean);
        $this->assertStringNotContainsStringIgnoringCase('javascript:', $clean);
        $this->assertStringNotContainsStringIgnoringCase('onclick', $clean);
        $this->assertStringNotContainsStringIgnoringCase('<iframe', $clean);
        $this->assertStringNotContainsString('contenteditable', $clean);
        // تُزال أدوات المحرّر بالكامل (مع نصّها)
        $this->assertStringNotContainsString('أدوات محرّر', $clean);
    }

    public function test_super_admin_can_save_layout_and_it_renders_sanitized_on_home(): void
    {
        $admin = User::factory()->superAdmin()->create();

        $this->actingAs($admin)
            ->postJson('/api/landing/layout', ['html' => self::DIRTY])
            ->assertOk()
            ->assertJson(['success' => true]);

        $stored = LandingLayout::currentHtml();
        $this->assertNotNull($stored);
        $this->assertStringNotContainsStringIgnoringCase('<script', $stored);

        // العرض العام يعكس التخطيط المُعقّم بلا سكربت
        $response = $this->get('/');
        $response->assertOk();
        $response->assertSee('عنوان آمن', false);
        $response->assertDontSee('<script>alert(1)', false);
    }

    public function test_non_super_admin_cannot_save_layout(): void
    {
        $student = User::factory()->student()->create();

        $this->actingAs($student)
            ->postJson('/api/landing/layout', ['html' => '<section>x</section>'])
            ->assertForbidden();

        $this->assertNull(LandingLayout::currentHtml());
    }

    public function test_super_admin_can_reset_layout_to_default(): void
    {
        $admin = User::factory()->superAdmin()->create();
        LandingLayout::store('<section class="hero" id="home"><h1>مخصّص</h1></section>');
        $this->assertNotNull(LandingLayout::currentHtml());

        $this->actingAs($admin)
            ->deleteJson('/api/landing/layout')
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertNull(LandingLayout::currentHtml());
    }
}
