<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\Models\Page;
use App\PageBuilder\PageResolver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * الترحيل التدريجيّ (ت-١٢): علم لكلّ مسار، ارتداد آمن للقديم، رندرة مستند v2 عبر المسار العامّ.
 */
class PageBuilderV2PublicRenderTest extends TestCase
{
    use RefreshDatabase;

    private function publishedHome(): Page
    {
        return Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'الرئيسيّة', 'slug' => 'home', 'status' => 'published', 'published_at' => now(),
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'مرحبا بالعالم v2']]],
        ]);
    }

    public function test_v2_not_served_when_flag_off(): void
    {
        // صفحة v2 منشورة على مسار بلا نظير قديم، والعلم مرفوع → يجب ألّا تُخدَم (404).
        Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'من نحن', 'slug' => 'about-us', 'status' => 'published', 'published_at' => now(),
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'محتوى v2 مخفيّ']]],
        ]);
        $this->assertFalse(PageResolver::isEnabled('about-us'));

        $this->get('/pages/about-us')->assertNotFound(); // لم يُخدَم v2، ولا نظير قديم
    }

    public function test_home_serves_v2_when_flag_on(): void
    {
        $this->publishedHome();
        PageResolver::enable('home');

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('مرحبا بالعالم v2');
        $res->assertSee('pb-page', false);
    }

    public function test_draft_page_is_not_served_even_when_enabled(): void
    {
        Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 't', 'slug' => 'home', 'status' => 'draft',
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'مسودّة سرّيّة']]],
        ]);
        PageResolver::enable('home');

        $res = $this->get('/');
        $res->assertOk();
        $res->assertDontSee('مسودّة سرّيّة'); // مسودّة لا تُخدَم
    }

    public function test_go_live_requires_published_then_serves(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'من نحن', 'slug' => 'about-us', 'status' => 'draft',
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'قصّتنا']]],
        ]);

        // مسودّة → رفض go-live
        $this->actingAs($admin)->postJson(route('admin.pb.pages.go-live', $page))->assertStatus(422);

        $page->update(['status' => 'published', 'published_at' => now()]);
        $this->actingAs($admin)->postJson(route('admin.pb.pages.go-live', $page))->assertOk();

        $this->assertTrue(PageResolver::isEnabled('about-us'));
        $this->get('/pages/about-us')->assertOk()->assertSee('قصّتنا');

        // take-down يعيد المسار للقديم (404 هنا إذ لا صفحة قديمة)
        $this->actingAs($admin)->postJson(route('admin.pb.pages.take-down', $page))->assertOk();
        $this->assertFalse(PageResolver::isEnabled('about-us'));
    }

    public function test_full_home_migration_flow_scaffold_go_live_served(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        // ترحيل home: أمر التهيئة يُنشئها منشورة (home محجوز لكنّه مقصود للترحيل)
        $this->artisan('pb:scaffold-home')->assertExitCode(0);
        $home = Page::where('slug', 'home')->firstOrFail();

        // go-live يقبل home رغم كونه محجوزاً (لا يرفضه حارس الحجز)
        $this->actingAs($admin)->postJson(route('admin.pb.pages.go-live', $home))->assertOk();
        $this->assertTrue(PageResolver::isEnabled('home'));

        $this->get('/')->assertOk()->assertSee('منصّة قِيَم التعليميّة');
    }

    public function test_metadata_only_update_preserves_blocks(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'قديم', 'slug' => 'keep-blocks', 'status' => 'draft',
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'يجب أن يبقى']]],
        ]);

        // حفظ بلا إرسال blocks (تعديل عنوان فقط) — يجب ألّا يُمسح الجسم
        $this->actingAs($admin)->putJson(route('admin.pb.pages.update', $page), [
            'title' => 'عنوان محدَّث', 'slug' => 'keep-blocks',
        ])->assertOk();

        $fresh = $page->fresh();
        $this->assertSame('عنوان محدَّث', $fresh->title);
        $this->assertSame('يجب أن يبقى', $fresh->blocks[0]['props']['title']);
    }

    public function test_scaffold_home_command_is_idempotent(): void
    {
        $this->artisan('pb:scaffold-home')->assertExitCode(0);
        $this->assertDatabaseHas('pb_pages', ['slug' => 'home', 'status' => 'published']);

        // مرّة ثانية لا تُنشئ نسخة ثانية
        $this->artisan('pb:scaffold-home')->assertExitCode(0);
        $this->assertSame(1, Page::where('slug', 'home')->count());
    }
}
