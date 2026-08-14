<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\BlockRegistry;
use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\TemplatePart;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * المرحلة 2: واجهة المحرّر المخصّص الخفيف — صفحات الإدارة + المعاينة الآمنة + الجزء الفعّال.
 */
class PageBuilderV2EditorUiTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    public function test_index_and_editor_pages_load_for_admin(): void
    {
        $admin = $this->admin();
        $this->actingAs($admin)->get(route('admin.pb.ui.index'))->assertOk()->assertSee('محرّر الصفحات');
        // بنية المحرّر الجديدة (خطوتان + معاينة مُضمَّنة + لوحة كتل) تُخدَم فعلاً
        $this->actingAs($admin)->get(route('admin.pb.ui.create'))->assertOk()
            ->assertSee('pbEditor', false)
            ->assertSee('data-pb-step="2"', false)   // تبويب خطوة المحتوى
            ->assertSee('pbPreviewFrame', false)     // إطار المعاينة المُضمَّن
            ->assertSee('pbToStep2', false)          // زرّ «التالي»
            ->assertSee('pbPalette', false);         // لوحة الكتل

        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'صفحتي', 'slug' => 'mine', 'status' => 'draft', 'blocks' => [],
        ]);
        $this->actingAs($admin)->get(route('admin.pb.ui.edit', $page))->assertOk()->assertSee('صفحتي');
    }

    public function test_editor_pages_forbidden_for_non_admin(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student)->get(route('admin.pb.ui.index'))->assertForbidden();
        $this->actingAs($student)->get(route('admin.pb.ui.create'))->assertForbidden();
    }

    public function test_preview_renders_blocks_and_neutralizes_xss(): void
    {
        $res = $this->actingAs($this->admin())->post(route('admin.pb.preview'), [
            'blocks' => [
                ['type' => 'hero', 'props' => ['title' => 'عنوان المعاينة']],
                ['type' => 'richtext', 'props' => ['html' => '<script>alert(1)</script>نصّ آمن']],
                ['type' => 'evil', 'props' => []], // نوع مجهول يُسقَط
            ],
        ]);

        $res->assertOk();
        $res->assertSee('عنوان المعاينة');
        $res->assertSee('نصّ آمن');
        $res->assertDontSee('<script>alert(1)</script>', false); // عُقِّم
    }

    public function test_preview_forbidden_for_non_admin(): void
    {
        $this->actingAs(User::factory()->student()->create())
            ->post(route('admin.pb.preview'), ['blocks' => []])
            ->assertForbidden();
    }

    public function test_active_part_creates_then_reuses(): void
    {
        $admin = $this->admin();

        $first = $this->actingAs($admin)->getJson(route('admin.pb.parts.active', 'header') . '?locale=ar');
        $first->assertOk()->assertJsonPath('part.kind', 'header');
        $this->assertDatabaseCount('pb_template_parts', 1);
        $id = $first->json('part.id');

        // نداء ثانٍ يعيد الجزء الفعّال نفسه (لا يُنشئ ثانياً)
        $second = $this->actingAs($admin)->getJson(route('admin.pb.parts.active', 'header') . '?locale=ar');
        $second->assertOk()->assertJsonPath('part.id', $id);
        $this->assertDatabaseCount('pb_template_parts', 1);
    }

    public function test_active_part_rejects_unknown_kind(): void
    {
        $this->actingAs($this->admin())->getJson(route('admin.pb.parts.active', 'sidebar'))->assertNotFound();
    }

    public function test_every_registered_block_has_editor_schema(): void
    {
        $schema = BlockRegistry::schema();
        foreach (array_keys(BlockRegistry::all()) as $type) {
            $this->assertArrayHasKey($type, $schema, "الكتلة {$type} تنقصها مخطّط المحرّر");
            $this->assertArrayHasKey('fields', $schema[$type]);
        }
    }

    public function test_saved_header_part_renders_on_live_page(): void
    {
        $admin = $this->admin();

        // أنشئ جزء هيدر فعّال عبر activePart ثمّ احفظ فيه كتلة
        $partId = $this->actingAs($admin)->getJson(route('admin.pb.parts.active', 'header') . '?locale=ar')->json('part.id');
        $part = TemplatePart::find($partId);
        $this->actingAs($admin)->putJson(route('admin.pb.parts.update', $part), [
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'ترويسة الموقع']]],
        ])->assertOk();

        // صفحة ثانويّة منشورة تستعمل الهيدر الفعّال الافتراضيّ + بثّ مباشر (v2 لا يخدم «/»).
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ر', 'slug' => 'about-us', 'status' => 'published', 'published_at' => now(),
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'جسم الصفحة']]],
        ]);
        \App\PageBuilder\PageResolver::enable('about-us');

        $this->get('/pages/about-us')->assertOk()->assertSee('ترويسة الموقع')->assertSee('جسم الصفحة');
    }
}
