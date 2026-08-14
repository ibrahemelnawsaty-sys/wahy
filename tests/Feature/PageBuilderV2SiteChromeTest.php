<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\TemplatePart;
use App\PageBuilder\PageResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * تغذية راجعة: خيار «استخدم هيدر/فوتر الموقع الرئيسيّ» في الصفحات الثانوية —
 * يُصيَّر هيدر/فوتر موحّد بدل جزء v2، ويُعدَّل من «محتوى الصفحة الرئيسية» (lc/الإعدادات).
 */
class PageBuilderV2SiteChromeTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_use_site_header_renders_unified_site_header(): void
    {
        // جزء هيدر افتراضيّ بمحتوى مميّز — يجب ألّا يظهر حين تختار الصفحة هيدر الموقع.
        TemplatePart::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'name' => 'ه', 'kind' => 'header', 'is_active' => true,
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'هيدر جزء v2']]],
        ]);
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'من نحن', 'slug' => 'about-site', 'status' => 'published', 'published_at' => now(),
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'الجسم']]],
            'use_site_header' => true,
        ]);
        PageResolver::enable('about-site');

        $res = $this->get('/pages/about-site');
        $res->assertOk();
        $res->assertSee('pb-site-header', false);   // هيدر الموقع الموحّد
        $res->assertSee('أثيل مكة');                 // اسم الموقع (setting)
        $res->assertDontSee('هيدر جزء v2');          // لم يُستعمل جزء v2
    }

    public function test_use_site_footer_renders_unified_site_footer(): void
    {
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ص', 'slug' => 'about-foot', 'status' => 'published', 'published_at' => now(),
            'blocks' => [], 'use_site_footer' => true,
        ]);
        PageResolver::enable('about-foot');

        $this->get('/pages/about-foot')->assertOk()
            ->assertSee('pb-site-footer', false)
            ->assertSee('جميع الحقوق محفوظة');
    }

    public function test_update_persists_use_site_flags(): void
    {
        $admin = $this->admin();
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ص', 'slug' => 'p-site', 'status' => 'draft', 'blocks' => [],
        ]);

        $this->actingAs($admin)->putJson(route('admin.pb.pages.update', $page), [
            'title' => 'ص', 'slug' => 'p-site', 'use_site_header' => true, 'use_site_footer' => true,
        ])->assertOk();

        $fresh = $page->fresh();
        $this->assertTrue((bool) $fresh->use_site_header);
        $this->assertTrue((bool) $fresh->use_site_footer);
    }

    public function test_preview_honors_use_site_header(): void
    {
        $html = $this->actingAs($this->admin())->post(route('admin.pb.preview'), [
            'body' => [['type' => 'hero', 'props' => ['title' => 'جسم']]],
            'use_site_header' => true,
        ])->assertOk()->getContent();

        $this->assertStringContainsString('pb-site-header', $html);
    }
}
