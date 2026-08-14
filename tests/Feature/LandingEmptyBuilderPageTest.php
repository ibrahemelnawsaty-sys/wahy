<?php

namespace Tests\Feature;

use App\Models\LandingContent;
use App\Models\PageBuilder;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * الجذر «/» مصدره الوحيد landing.blade (يُصيَّر خادميّاً عبر lc('key', الافتراضيّ))،
 * ويُحرَّر من محرّرٍ **واحد** هو «محتوى الصفحة الرئيسية» (Admin\HomeContentController →
 * جدول landing_content). بعد خطّة دمج المحرّرات (انظر docs/HOME_EDITORS_MERGE_PLAN.md):
 *   - landing() تُرجِع view('landing') **غير مشروط** — لا يتجاوزها page_builder(slug=home) إطلاقاً.
 *   - أُزيلت المحرّرات القديمة (LandingPageController، كتل SuperAdminController، المدمج WYSIWYG).
 *
 * حرّاس مُلزِمون (الدستور §2 «الإصلاح الموجَّه بالاختبار» + §10.1 «مصدر حقيقة واحد»):
 *   1) لا يحجب أيّ صفّ page_builder(slug=home) — بأيّ حالة — الصفحةَ الثابتة (حادثة «الصفحة البيضاء»).
 *   2) المحرّر الموحّد يعدّل landing_content ويظهر خادميّاً عبر lc()، مع لقطة/استرجاع ذرّيّ.
 */
class LandingEmptyBuilderPageTest extends TestCase
{
    use RefreshDatabase;

    /** علامة مستقرّة على أنّ landing.blade الثابتة هي المُصيَّرة (افتراضيّ benefits_title). */
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
        PageBuilder::create([
            'page_name' => 'الصفحة الرئيسية',
            'slug' => 'home',
            'json_data' => [['id' => 'x'], 'nonsense'],
            'is_active' => true,
        ]);

        $this->get('/')->assertOk()->assertSee(self::STATIC_LANDING_MARKER, false);
    }

    /**
     * حارس تراجع للنموذج الجديد: حتى صفّ page_builder(slug=home) **بمحتوى حقيقيّ ونشِط**
     * لم يعد يتجاوز الصفحة الثابتة (landing() غير مشروط). الرئيسية تُحرَّر عبر «محتوى الصفحة الرئيسية».
     */
    public function test_active_builder_home_no_longer_overrides_static_landing(): void
    {
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
        $res->assertSee(self::STATIC_LANDING_MARKER, false);
        $res->assertDontSee('عنوان مخصَّص من اللوحة', false);
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

    public function test_home_slug_is_reserved_in_page_builder_v1(): void
    {
        // حارس دفعة 0: لا يمكن إنشاء رئيسيّة موازية عبر بناء الصفحات (v1) على slug=home.
        $this->actingAs($this->admin())
            ->post(route('admin.pages.store'), [
                'page_name' => 'محاولة رئيسيّة موازية',
                'slug' => 'home',
                'json_data' => json_encode([['id' => 'b', 'type' => 'heading', 'content' => ['text' => 'x']]]),
            ])
            ->assertSessionHasErrors('slug');

        $this->assertDatabaseMissing('page_builder', ['slug' => 'home']);
    }

    public function test_home_redirects_to_root(): void
    {
        // /home لم يعد يُخدَّم من page_builder — يُحوَّل للجذر (يمنع رئيسيّة مُتباعِدة).
        $this->get('/home')->assertRedirect('/');
    }

    public function test_opening_home_content_editor_creates_no_rows(): void
    {
        $this->actingAs($this->admin())
            ->get(route('admin.home-content.edit'))
            ->assertOk();

        // فتح المحرّر (GET) لا يكتب شيئاً (لا نشر ضمنيّ).
        $this->assertDatabaseCount('landing_content', 0);
        $this->get('/')->assertOk()->assertSee(self::STATIC_LANDING_MARKER, false);
    }

    public function test_saving_home_content_overrides_the_page_via_lc(): void
    {
        $custom = 'عنوان هيرو مختبَر فريد ١٢٣';

        $this->actingAs($this->admin())
            ->post(route('admin.home-content.update'), ['hero_title' => $custom])
            ->assertRedirect(route('admin.home-content.edit'));

        $this->assertDatabaseHas('landing_content', ['key' => 'hero_title', 'value' => $custom]);
        $this->get('/')->assertOk()->assertSee($custom, false);
    }

    public function test_saving_default_value_stores_no_override(): void
    {
        // منطق «الحذف عند الافتراضيّ»: قيمة مطابقة للافتراضيّ لا تُنشئ صفّاً (نظافة).
        $default = config('landing_editable.hero.fields.hero_title.default');

        $this->actingAs($this->admin())
            ->post(route('admin.home-content.update'), ['hero_title' => $default])
            ->assertRedirect();

        $this->assertDatabaseMissing('landing_content', ['key' => 'hero_title']);
    }

    public function test_snapshot_is_taken_and_can_be_restored(): void
    {
        $admin = $this->admin();

        // حفظ أوّل (الجدول فارغ ⟶ لا لقطة)، ثم حفظ ثانٍ (يلتقط لقطة للحالة الأولى).
        $this->actingAs($admin)->post(route('admin.home-content.update'), ['hero_title' => 'الحالة أ']);
        $this->actingAs($admin)->post(route('admin.home-content.update'), ['hero_title' => 'الحالة ب']);

        $this->assertDatabaseHas('landing_content', ['key' => 'hero_title', 'value' => 'الحالة ب']);
        $version = DB::table('landing_content_versions')->latest('id')->first();
        $this->assertNotNull($version, 'يجب أن تُحفظ لقطة قبل الحفظ الثاني');

        // استرجاع اللقطة (حالة «أ») ذرّيّاً.
        $this->actingAs($admin)
            ->post(route('admin.home-content.restore', $version->id))
            ->assertRedirect(route('admin.home-content.edit'));

        $this->assertDatabaseHas('landing_content', ['key' => 'hero_title', 'value' => 'الحالة أ']);
        $this->get('/')->assertOk()->assertSee('الحالة أ', false);
    }
}
