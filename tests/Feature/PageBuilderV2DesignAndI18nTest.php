<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\Models\Page;
use App\PageBuilder\PageDesign;
use App\PageBuilder\PageResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * تحسينات المرحلة 2: رموز التصميم (ت-١٠) + نسخ اللغات المرتبطة (ت-٣).
 */
class PageBuilderV2DesignAndI18nTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_design_sanitize_rejects_css_injection(): void
    {
        $clean = PageDesign::sanitize([
            'primary' => 'red;}body{display:none}',   // حقن CSS
            'secondary' => '#abc',
            'text' => 'javascript:alert(1)',
            'bg' => '#ffffff',
            'font' => 'Comic Sans</style><script>',   // خطّ خارج القائمة
            'radius' => 9999,
        ]);

        $this->assertSame(PageDesign::DEFAULTS['primary'], $clean['primary']); // رُفِض → افتراضيّ
        $this->assertSame('#abc', $clean['secondary']);                        // hex صالح
        $this->assertSame(PageDesign::DEFAULTS['text'], $clean['text']);       // رُفِض
        $this->assertSame('Tajawal', $clean['font']);                          // رُفِض → افتراضيّ
        $this->assertSame(40, $clean['radius']);                               // قُيِّد للحدّ

        // لا محرف خطير في متغيّرات CSS الناتجة
        $css = PageDesign::cssVars();
        $this->assertStringNotContainsString('}', $css);
        $this->assertStringNotContainsString('<', $css);
    }

    public function test_admin_saves_and_reads_design_tokens(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->putJson(route('admin.pb.design.save'), [
            'primary' => '#123456', 'font' => 'Cairo', 'radius' => 20,
        ])->assertOk()->assertJsonPath('tokens.primary', '#123456')
            ->assertJsonPath('tokens.font', 'Cairo');

        $this->actingAs($admin)->getJson(route('admin.pb.design.show'))
            ->assertOk()->assertJsonPath('tokens.radius', 20);
    }

    public function test_design_tokens_apply_on_live_page(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->putJson(route('admin.pb.design.save'), ['primary' => '#0abcde']);

        // صفحة ثانويّة (v2 لم يعد يخدم «/» — landing غير مشروط) تُخدَم عبر /pages/{slug}.
        Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ر', 'slug' => 'design-page', 'status' => 'published', 'published_at' => now(),
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'ت']]],
        ]);
        PageResolver::enable('design-page');

        $this->get('/pages/design-page')->assertOk()->assertSee('--pb-primary:#0abcde', false);
    }

    public function test_design_endpoints_forbidden_for_non_admin(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->getJson(route('admin.pb.design.show'))->assertForbidden();
    }

    public function test_translate_creates_linked_locale_version(): void
    {
        $admin = $this->admin();
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'من نحن', 'slug' => 'about', 'status' => 'published', 'published_at' => now(),
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'أصل']]],
        ]);

        $res = $this->actingAs($admin)->postJson(route('admin.pb.pages.translate', $page), ['locale' => 'en']);
        $res->assertCreated()->assertJsonPath('existed', false);

        $en = Page::where('slug', 'about')->where('locale', 'en')->firstOrFail();
        $this->assertSame($page->translation_group, $en->translation_group); // مرتبطة
        $this->assertSame('draft', $en->status);                              // نسخة جديدة مسودّة
        $this->assertSame('أصل', $en->blocks[0]['props']['title']);           // نُسِخت الكتل

        // نداء ثانٍ يعيد النسخة نفسها (لا تكرار)
        $again = $this->actingAs($admin)->postJson(route('admin.pb.pages.translate', $page), ['locale' => 'en']);
        $again->assertOk()->assertJsonPath('existed', true);
        $this->assertSame(1, Page::where('translation_group', $page->translation_group)->where('locale', 'en')->count());
    }

    public function test_translate_rejects_unknown_locale(): void
    {
        $admin = $this->admin();
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 't', 'slug' => 'x', 'status' => 'draft', 'blocks' => [],
        ]);
        $this->actingAs($admin)->postJson(route('admin.pb.pages.translate', $page), ['locale' => 'fr'])
            ->assertStatus(422);
    }

    public function test_locale_versions_resolve_independently(): void
    {
        $group = (string) Str::uuid();
        Page::create([
            'translation_group' => $group, 'locale' => 'ar', 'title' => 'ع', 'slug' => 'p',
            'status' => 'published', 'published_at' => now(), 'blocks' => [['type' => 'hero', 'props' => ['title' => 'عربيّ']]],
        ]);
        Page::create([
            'translation_group' => $group, 'locale' => 'en', 'title' => 'e', 'slug' => 'p',
            'status' => 'published', 'published_at' => now(), 'blocks' => [['type' => 'hero', 'props' => ['title' => 'English']]],
        ]);
        PageResolver::enable('p');

        // المُصيِّر يخدم النسخة الصحيحة حسب اللغة الممرَّرة (يحترم لغة الطلب حين تُضاف آليّة التبديل).
        $this->assertSame('عربيّ', PageResolver::resolve('p', 'ar')->blocks[0]['props']['title']);
        $this->assertSame('English', PageResolver::resolve('p', 'en')->blocks[0]['props']['title']);

        // المسار العامّ يعرض النسخة الافتراضيّة (لغة التطبيق) بنجاح
        $this->get('/pages/p')->assertOk()->assertSee('عربيّ');
    }
}
