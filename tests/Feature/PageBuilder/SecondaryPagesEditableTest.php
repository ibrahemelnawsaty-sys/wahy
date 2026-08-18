<?php

namespace Tests\Feature\PageBuilder;

use App\Models\User;
use App\PageBuilder\Models\Page;
use App\PageBuilder\PageResolver;
use App\PageBuilder\SlugGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * الصفحات الجانبيّة (الشروط/الخصوصيّة) يحرّرها الأدمن من محرّر الصفحات /admin/pb.
 *
 * العائق الذي كان يمنع ذلك: `SlugGuard` يرفض أيّ slug يطابق مسار GET مسجَّلاً — و`terms`
 * و`privacy` مساران مسجَّلان، فكان إنشاؤهما مرفوضاً أصلاً. وحتى لو أُنشئا، كان
 * `PagesController::terms()` يُرجِع القالب الثابت بلا استشارة المحرّر إطلاقاً.
 *
 * الحلّ يحفظ الأمان: استثناءٌ **مُصرَّح به لكلّ slug** لا فتحُ الحارس، مع ارتدادٍ للقالب الثابت
 * ما لم تُنشَر صفحةٌ ذات محتوى — فلا شاشة بيضاء ولا فقدان للمحتوى القانونيّ القائم.
 */
class SecondaryPagesEditableTest extends TestCase
{
    use RefreshDatabase;

    private function publish(string $slug, string $text): Page
    {
        $page = Page::create([
            'translation_group' => (string) Str::uuid(),
            'locale' => 'ar',
            'title' => $slug,
            'slug' => $slug,
            'status' => 'published',
            'published_at' => now(),
            'blocks' => [['type' => 'richtext', 'v' => 1, 'props' => ['html' => '<p>' . $text . '</p>']]],
        ]);
        PageResolver::enable($slug);

        return $page;
    }

    public function test_slug_guard_allows_the_delegating_slugs_only(): void
    {
        $this->assertFalse(SlugGuard::isReserved('terms'));
        $this->assertFalse(SlugGuard::isReserved('privacy'));

        // ولا يُفتح الحارس لغيرها: المسارات الحسّاسة تبقى محجوبة.
        $this->assertTrue(SlugGuard::isReserved('admin'));
        $this->assertTrue(SlugGuard::isReserved('login'));
        $this->assertTrue(SlugGuard::isReserved('student'));
        $this->assertTrue(SlugGuard::isReserved('dashboard'));
    }

    public function test_terms_falls_back_to_the_static_page_when_nothing_is_published(): void
    {
        $this->get('/terms')->assertOk()->assertSee('الشروط والأحكام', false);
    }

    public function test_privacy_falls_back_to_the_static_page_when_nothing_is_published(): void
    {
        $this->get('/privacy')->assertOk()->assertSee('سياسة الخصوصية', false);
    }

    public function test_published_builder_page_takes_over_terms(): void
    {
        $this->publish('terms', 'شروطٌ حرّرها الأدمن');

        $this->get('/terms')->assertOk()
            ->assertSee('شروطٌ حرّرها الأدمن', false)
            ->assertSee('pb-page', false);
    }

    public function test_published_builder_page_takes_over_privacy(): void
    {
        $this->publish('privacy', 'خصوصيّةٌ حرّرها الأدمن');

        $this->get('/privacy')->assertOk()->assertSee('خصوصيّةٌ حرّرها الأدمن', false);
    }

    public function test_draft_page_never_replaces_the_legal_text(): void
    {
        Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'terms', 'slug' => 'terms', 'status' => 'draft',
            'blocks' => [['type' => 'richtext', 'v' => 1, 'props' => ['html' => '<p>مسودّة سرّيّة</p>']]],
        ]);
        PageResolver::enable('terms');

        $this->get('/terms')->assertOk()
            ->assertDontSee('مسودّة سرّيّة', false)
            ->assertSee('الشروط والأحكام', false);
    }

    public function test_empty_published_page_does_not_blank_the_legal_text(): void
    {
        // درس حادثة «الرئيسية بيضاء»: صفحةٌ منشورة بلا كتل يجب ألّا تحجب محتوىً قائماً.
        Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'terms', 'slug' => 'terms', 'status' => 'published',
            'published_at' => now(), 'blocks' => [],
        ]);
        PageResolver::enable('terms');

        $this->get('/terms')->assertOk()->assertSee('الشروط والأحكام', false);
    }

    public function test_admin_can_create_a_terms_page_through_the_manager(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);

        $this->actingAs($admin)->postJson(route('admin.pb.pages.store'), [
            'title' => 'الشروط والأحكام',
            'slug' => 'terms',
            'locale' => 'ar',
            'blocks' => [['type' => 'richtext', 'v' => 1, 'props' => ['html' => '<p>نصّ</p>']]],
        ])->assertCreated();

        $this->assertDatabaseHas('pb_pages', ['slug' => 'terms', 'status' => 'draft']);
    }

    // ---------------- أمر التوليد ----------------

    public function test_scaffold_creates_drafts_carrying_the_real_current_text(): void
    {
        $this->artisan('pb:scaffold-pages')->assertExitCode(0);

        foreach (['terms', 'privacy'] as $slug) {
            $page = Page::where('slug', $slug)->where('locale', 'ar')->first();
            $this->assertNotNull($page, "يجب توليد مسودّة «{$slug}»");
            $this->assertSame('draft', $page->status, 'تُولَّد مسودّةً لا منشوراً');
            $this->assertNotEmpty($page->blocks);

            // المحتوى مأخوذ من الصفحة الحاليّة لا صفحة بيضاء.
            $html = collect($page->blocks)->firstWhere('type', 'richtext')['props']['html'] ?? '';
            $this->assertNotSame('<p></p>', $html, 'يجب استخراج نصّ الصفحة القائمة');
            $this->assertStringNotContainsString('<script', $html, 'تُزال السكربتات');
        }
    }

    public function test_scaffold_does_not_go_live_by_itself(): void
    {
        // غير مدمِّر: لا يرفع العلم ولا ينشر — الزائر يبقى يرى الصفحة الثابتة.
        $this->artisan('pb:scaffold-pages')->assertExitCode(0);

        $this->assertFalse(PageResolver::isEnabled('terms'));
        $this->get('/terms')->assertOk()->assertSee('الشروط والأحكام', false);
    }

    public function test_scaffold_is_idempotent(): void
    {
        $this->artisan('pb:scaffold-pages')->assertExitCode(0);
        $this->artisan('pb:scaffold-pages')->assertExitCode(0);

        $this->assertSame(1, Page::where('slug', 'terms')->count(), 'لا يُكرّر ولا يدهس');
    }
}
