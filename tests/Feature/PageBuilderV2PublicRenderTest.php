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

    public function test_home_slug_v2_never_overrides_static_landing(): void
    {
        // القرار (خطّة دمج محرّرات الرئيسية): «/» يعرض landing.blade **دائماً** — المصدر الوحيد
        // «محتوى الصفحة الرئيسية». v2 لم يعد يخدم الجذر حتى مع صفحة home منشورة + العلم مرفوع.
        $this->publishedHome();
        PageResolver::enable('home');

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('فوائد التعلم التعاوني', false); // landing.blade الثابتة هي المُصيَّرة
        $res->assertDontSee('مرحبا بالعالم v2');          // v2 لا يتجاوز الجذر
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

    public function test_scaffolded_home_v2_does_not_serve_at_root(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        // أمر التهيئة ما زال يُنشئ صفّ home v2، لكنّه لم يعد يُخدَم على «/» (landing غير مشروط).
        $this->artisan('pb:scaffold-home')->assertExitCode(0);
        $home = Page::where('slug', 'home')->firstOrFail();

        $this->actingAs($admin)->postJson(route('admin.pb.pages.go-live', $home))->assertOk();
        $this->assertTrue(PageResolver::isEnabled('home'));

        // رغم النشر والتفعيل، «/» يبقى landing.blade (لا يُخدَم v2 home).
        $this->get('/')->assertOk()->assertSee('فوائد التعلم التعاوني', false);
    }

    public function test_inline_edit_attributes_only_on_top_level_preview(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $preview = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => [
            ['type' => 'heading', 'props' => ['text' => 'عنوان علويّ', 'level' => 'h2']],
            ['type' => 'columns', 'props' => ['count' => 2], 'children' => [
                ['type' => 'heading', 'props' => ['text' => 'داخل عمود', 'level' => 'h3']],
            ]],
        ]])->assertOk()->getContent();

        $this->assertStringContainsString('data-pb-edit="text"', $preview);
        // النصّ المتداخل (داخل الأعمدة) غير قابل للتحرير في المكان (مسار متداخل) — فقط العلويّ
        $this->assertSame(1, substr_count($preview, 'data-pb-edit='));

        // الصفحة العامّة بلا أيّ سمات تحرير
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ص', 'slug' => 'ie', 'status' => 'published', 'published_at' => now(),
            'blocks' => [['type' => 'heading', 'props' => ['text' => 'عنوان', 'level' => 'h2']]],
        ]);
        PageResolver::enable('ie');
        $this->assertStringNotContainsString('data-pb-edit', $this->get('/pages/ie')->assertOk()->getContent());
    }

    public function test_preview_tags_blocks_for_click_to_edit_but_public_does_not(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $body = [['type' => 'heading', 'props' => ['text' => 'عنوان', 'level' => 'h2']]];

        // المعاينة تلفّ الكتل بـdata-pb-path لتفعيل «انقر للتحرير»
        $preview = $this->actingAs($admin)->post(route('admin.pb.preview'), ['body' => $body])->assertOk()->getContent();
        $this->assertStringContainsString('data-pb-path="0"', $preview);
        $this->assertStringContainsString('<div class="pb-pv-block"', $preview);

        // الصفحة العامّة **لا** تلفّها (بلا سمات تحرير)
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ص', 'slug' => 'clk', 'status' => 'published', 'published_at' => now(), 'blocks' => $body,
        ]);
        PageResolver::enable('clk');
        $public = $this->get('/pages/clk')->assertOk()->getContent();
        $this->assertStringNotContainsString('data-pb-path', $public);
        $this->assertStringNotContainsString('<div class="pb-pv-block"', $public);
    }

    public function test_draft_edits_do_not_affect_live_until_republish(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ص', 'slug' => 'sep', 'status' => 'draft',
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'الإصدار المنشور']]],
        ]);

        $this->actingAs($admin)->postJson(route('admin.pb.pages.publish', $page))->assertOk();
        PageResolver::enable('sep');
        $this->get('/pages/sep')->assertOk()->assertSee('الإصدار المنشور');

        // تعديل المسودّة (blocks) دون إعادة نشر
        $this->actingAs($admin)->putJson(route('admin.pb.pages.update', $page), [
            'title' => 'ص', 'slug' => 'sep',
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'مسودّة غير منشورة']]],
        ])->assertOk();

        // الحيّ يبقى على المنشور — لا تتسرّب المسودّة
        $this->get('/pages/sep')->assertOk()->assertSee('الإصدار المنشور')->assertDontSee('مسودّة غير منشورة');

        // إعادة النشر ⟶ الحيّ يعرض الجديد
        $this->actingAs($admin)->postJson(route('admin.pb.pages.publish', $page->fresh()))->assertOk();
        $this->get('/pages/sep')->assertOk()->assertSee('مسودّة غير منشورة');
    }

    public function test_scheduled_future_page_not_served_until_due(): void
    {
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'مجدولة', 'slug' => 'sched', 'status' => 'published', 'published_at' => now()->addDay(),
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'محتوى مجدول']]],
        ]);
        PageResolver::enable('sched');

        // تاريخ نشرٍ مستقبليّ ⟶ لا يُخدَم بعد (يرتدّ 404 إذ لا نظير قديم)
        $this->get('/pages/sched')->assertNotFound();

        // حان الوقت ⟶ يُخدَم
        $page->update(['published_at' => now()->subMinute()]);
        $this->get('/pages/sched')->assertOk()->assertSee('محتوى مجدول');
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
