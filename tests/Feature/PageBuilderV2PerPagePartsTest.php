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
 * دفعة 1 (محرّر الصفحات الاحترافيّ): هيدر/فوتر لكلّ صفحة (إخفاء + جزء مُسمّى)
 * + معاينة المستند الكامل (هيدر+جسم+فوتر) + نقاط إدارة الأجزاء (سرد/إنشاء/تعيين افتراضيّ).
 */
class PageBuilderV2PerPagePartsTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    private function activeHeader(string $title): TemplatePart
    {
        return TemplatePart::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'name' => 'الهيدر', 'kind' => 'header', 'is_active' => true,
            'blocks' => [['type' => 'hero', 'props' => ['title' => $title]]],
        ]);
    }

    private function servedPage(array $attrs): Page
    {
        $page = Page::create(array_merge([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'status' => 'published', 'published_at' => now(),
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'جسم']]],
        ], $attrs));
        PageResolver::enable($page->slug);

        return $page;
    }

    public function test_default_page_shows_the_global_active_header(): void
    {
        $this->activeHeader('هيدر عامّ');
        $this->servedPage(['title' => 'ص', 'slug' => 'p-default']);

        $this->get('/pages/p-default')->assertOk()->assertSee('هيدر عامّ')->assertSee('جسم');
    }

    public function test_hide_header_removes_header_for_that_page_only(): void
    {
        $this->activeHeader('هيدر عامّ');
        $this->servedPage(['title' => 'ص', 'slug' => 'p-nohdr', 'hide_header' => true]);

        $this->get('/pages/p-nohdr')->assertOk()->assertSee('جسم')->assertDontSee('هيدر عامّ');
    }

    public function test_page_can_use_a_named_header_part(): void
    {
        $this->activeHeader('هيدر عامّ');
        $named = TemplatePart::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'name' => 'هيدر خاصّ', 'kind' => 'header', 'is_active' => false,
            'blocks' => [['type' => 'hero', 'props' => ['title' => 'هيدر مخصَّص']]],
        ]);
        $this->servedPage(['title' => 'ص', 'slug' => 'p-named', 'header_part_id' => $named->id]);

        $this->get('/pages/p-named')->assertOk()->assertSee('هيدر مخصَّص')->assertDontSee('هيدر عامّ');
    }

    public function test_update_endpoint_persists_hide_header_and_footer_part(): void
    {
        $admin = $this->admin();
        $namedFooter = TemplatePart::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'name' => 'فوتر', 'kind' => 'footer', 'is_active' => false, 'blocks' => [],
        ]);
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ص', 'slug' => 'p-save', 'status' => 'draft', 'blocks' => [],
        ]);

        $this->actingAs($admin)->putJson(route('admin.pb.pages.update', $page), [
            'title' => 'ص', 'slug' => 'p-save',
            'hide_header' => true, 'footer_part_id' => $namedFooter->id,
        ])->assertOk();

        $fresh = $page->fresh();
        $this->assertTrue((bool) $fresh->hide_header);
        $this->assertSame($namedFooter->id, $fresh->footer_part_id);
    }

    public function test_metadata_only_update_preserves_header_assignment(): void
    {
        $admin = $this->admin();
        $named = TemplatePart::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'name' => 'ه', 'kind' => 'header', 'is_active' => false, 'blocks' => [],
        ]);
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar',
            'title' => 'ص', 'slug' => 'p-meta', 'status' => 'draft', 'blocks' => [],
            'header_part_id' => $named->id,
        ]);

        // حفظ لا يتضمّن header_part_id (تعديل عنوان فقط) — يجب ألّا يمسح الإسناد القائم.
        $this->actingAs($admin)->putJson(route('admin.pb.pages.update', $page), [
            'title' => 'محدَّث', 'slug' => 'p-meta',
        ])->assertOk();

        $this->assertSame($named->id, $page->fresh()->header_part_id);
    }

    public function test_parts_endpoints_list_create_and_set_default(): void
    {
        $admin = $this->admin();

        $id = $this->actingAs($admin)->postJson(route('admin.pb.parts.create'), [
            'kind' => 'header', 'name' => 'هيدر جديد', 'locale' => 'ar',
        ])->assertCreated()->json('part.id');

        $this->actingAs($admin)->getJson(route('admin.pb.parts.index') . '?kind=header&locale=ar')
            ->assertOk()->assertJsonFragment(['name' => 'هيدر جديد']);

        $this->actingAs($admin)->postJson(route('admin.pb.parts.set-default', $id))->assertOk();
        $this->assertTrue((bool) TemplatePart::find($id)->is_active);
    }

    public function test_preview_renders_full_document_with_live_header(): void
    {
        $admin = $this->admin();

        $res = $this->actingAs($admin)->post(route('admin.pb.preview'), [
            'locale' => 'ar',
            'body' => [['type' => 'hero', 'props' => ['title' => 'جسم المعاينة']]],
            'header' => [['type' => 'hero', 'props' => ['title' => 'هيدر المعاينة']]],
        ]);

        $res->assertOk();
        $html = $res->getContent();
        $this->assertStringContainsString('هيدر المعاينة', $html);
        $this->assertStringContainsString('جسم المعاينة', $html);
        $this->assertStringContainsString('pb-page-header', $html);
    }

    public function test_preview_honors_hide_header(): void
    {
        $admin = $this->admin();
        $this->activeHeader('هيدر افتراضيّ');

        $res = $this->actingAs($admin)->post(route('admin.pb.preview'), [
            'locale' => 'ar',
            'body' => [['type' => 'hero', 'props' => ['title' => 'جسم فقط']]],
            'hide_header' => true,
        ]);

        $res->assertOk();
        $this->assertStringContainsString('جسم فقط', $res->getContent());
        $this->assertStringNotContainsString('هيدر افتراضيّ', $res->getContent());
    }
}
