<?php

namespace Tests\Feature;

use App\Models\PageBuilder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * حادثة «الصفحة الرئيسية بيضاء»: مجرّد فتح محرّر الصفحة الرئيسية (GET) كان يُنشئ صفّ
 * page_builder(slug=home, json_data=[], is_active=true)، فيَحجب landing.blade الغنيّة
 * ويُصيَّر عبر pages.show كغلافٍ فارغ (div بارتفاع 100vh بلا أيّ محتوى).
 *
 * حارسان مُلزِمان (الدستور §2 «الإصلاح الموجَّه بالاختبار» + §10.1.4 «انضباط النشر»):
 *   1) صفحة محرّر بلا محتوى قابل للعرض **يُمنع** أن تحجب الصفحة الثابتة.
 *   2) طلب GET على المحرّر **يُمنع** أن يكتب في قاعدة البيانات (لا نشر ضمنيّ).
 */
class LandingEmptyBuilderPageTest extends TestCase
{
    use RefreshDatabase;

    /** علامة مستقرّة على أنّ landing.blade الثابتة هي المُصيَّرة. */
    private const STATIC_LANDING_MARKER = 'فوائد التعلم التعاوني';

    /** علامة غلاف pages.show الفارغ (الغلاف الذي ظهر في الحادثة). */
    private const EMPTY_SHELL_MARKER = 'min-height: 100vh; padding: 80px 0;';

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_empty_active_builder_page_does_not_blank_the_landing(): void
    {
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => [],
            'is_active' => true,
        ]);

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee(self::STATIC_LANDING_MARKER, false);
        $res->assertDontSee(self::EMPTY_SHELL_MARKER, false);
    }

    public function test_page_whose_sections_are_empty_does_not_blank_the_landing(): void
    {
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => ['sections' => []],
            'is_active' => true,
        ]);

        $this->get('/')->assertOk()->assertSee(self::STATIC_LANDING_MARKER, false);
    }

    public function test_page_whose_blocks_are_all_malformed_does_not_blank_the_landing(): void
    {
        // كتل بلا مفتاح type يتخطّاها القالب بـ@continue ⟶ ناتج فارغ = نفس الشاشة البيضاء.
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => [['id' => 'x'], 'nonsense'],
            'is_active' => true,
        ]);

        $this->get('/')->assertOk()->assertSee(self::STATIC_LANDING_MARKER, false);
    }

    public function test_page_with_real_blocks_still_overrides_the_static_landing(): void
    {
        // حارس تراجع: الميزة نفسها (تخصيص الرئيسية من اللوحة) يجب أن تبقى عاملة.
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => [[
                'id' => 'block_hero_1',
                'type' => 'hero',
                'content' => ['title' => 'عنوان مخصَّص من اللوحة'],
            ]],
            'is_active' => true,
        ]);

        $res = $this->get('/');
        $res->assertOk();
        $res->assertSee('عنوان مخصَّص من اللوحة', false);
        $res->assertDontSee(self::STATIC_LANDING_MARKER, false);
    }

    public function test_inactive_page_with_content_is_not_served(): void
    {
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => [['id' => 'b1', 'type' => 'hero', 'content' => ['title' => 'مسودّة سرّيّة']]],
            'is_active' => false,
        ]);

        $this->get('/')->assertOk()
            ->assertDontSee('مسودّة سرّيّة', false)
            ->assertSee(self::STATIC_LANDING_MARKER, false);
    }

    public function test_opening_the_editor_does_not_create_or_publish_a_home_page(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.landing-page'))
            ->assertOk();

        // الأثر الجانبيّ الممنوع: GET يكتب صفّاً منشوراً فارغاً.
        $this->assertDatabaseMissing('page_builder', ['slug' => 'home']);

        // والصفحة العامّة تبقى سليمة بعد فتح المحرّر.
        $this->get('/')->assertOk()->assertSee(self::STATIC_LANDING_MARKER, false);
    }

    public function test_import_current_landing_works_without_a_pre_created_row(): void
    {
        // بعد إلغاء الإنشاء في GET، يجب أن يظلّ الاستيراد قادراً على إنشاء الصفحة.
        $this->actingAs($this->admin())
            ->postJson(route('admin.landing-page.import-current'))
            ->assertOk()
            ->assertJson(['success' => true]);

        $page = PageBuilder::where('slug', 'home')->firstOrFail();
        $this->assertNotEmpty($page->json_data);
        $this->assertTrue($page->hasRenderableContent());
    }

    public function test_saving_content_from_the_editor_creates_and_publishes_the_page(): void
    {
        $this->actingAs($this->admin())
            ->postJson(route('admin.landing-page.content'), [
                'json_data' => [[
                    'id' => 'b1',
                    'type' => 'heading',
                    'content' => ['level' => 'h2', 'text' => 'عنوان محفوظ'],
                ]],
            ])
            ->assertOk()
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('page_builder', ['slug' => 'home', 'is_active' => true]);
        $this->get('/')->assertOk()->assertSee('عنوان محفوظ', false);
    }
}
