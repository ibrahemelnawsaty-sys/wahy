<?php

namespace Tests\Feature;

use App\Models\User;
use App\PageBuilder\BlockValidator;
use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\TemplatePart;
use App\PageBuilder\SlugGuard;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Tests\TestCase;

/**
 * المرحلة 1، الدفعة 2: واجهة حفظ/نشر/تراجع الصفحات + حماية الـslug + تحقّق المخطّط.
 */
class PageBuilderV2ManagerTest extends TestCase
{
    use RefreshDatabase;

    private function admin(): User
    {
        return User::factory()->create(['role' => 'super_admin']);
    }

    private function heroBlocks(): array
    {
        return [['type' => 'hero', 'v' => 1, 'props' => ['title' => 'مرحبا']]];
    }

    public function test_slug_guard_blocks_reserved_and_route_collisions(): void
    {
        $this->assertTrue(SlugGuard::isReserved('admin'));
        $this->assertTrue(SlugGuard::isReserved('login'));
        $this->assertTrue(SlugGuard::isReserved('api/x'));
        $this->assertTrue(SlugGuard::isReserved('student')); // مسار مسجَّل
        $this->assertFalse(SlugGuard::isReserved('about-us'));
        $this->assertFalse(SlugGuard::isReserved('our-values'));
    }

    public function test_block_validator_rejects_unknown_types_and_payloads(): void
    {
        $this->assertNotEmpty(BlockValidator::validate([['type' => 'evil', 'props' => []]]));
        $this->assertNotEmpty(BlockValidator::validate([['type' => 'button', 'props' => ['link' => 'javascript:alert(1)']]]));
        $this->assertSame([], BlockValidator::validate($this->heroBlocks()));
    }

    public function test_store_creates_page_and_rejects_reserved_slug_and_payloads(): void
    {
        $admin = $this->admin();

        // صالح
        $this->actingAs($admin)->postJson(route('admin.pb.pages.store'), [
            'title' => 'من نحن', 'slug' => 'about-us', 'locale' => 'ar', 'blocks' => $this->heroBlocks(),
        ])->assertCreated();
        $this->assertDatabaseHas('pb_pages', ['slug' => 'about-us', 'status' => 'draft']);

        // slug محجوز
        $this->actingAs($admin)->postJson(route('admin.pb.pages.store'), [
            'title' => 'x', 'slug' => 'admin', 'blocks' => $this->heroBlocks(),
        ])->assertStatus(422);

        // حمولة XSS
        $this->actingAs($admin)->postJson(route('admin.pb.pages.store'), [
            'title' => 'x', 'slug' => 'evil-1', 'blocks' => [['type' => 'button', 'props' => ['link' => 'javascript:alert(1)']]],
        ])->assertStatus(422);
        $this->assertDatabaseMissing('pb_pages', ['slug' => 'evil-1']);
    }

    public function test_update_snapshots_revision_and_optimistic_lock(): void
    {
        $admin = $this->admin();
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar', 'title' => 't', 'slug' => 'p1',
            'status' => 'draft', 'blocks' => $this->heroBlocks(),
        ]);

        // قفل تفاؤليّ: updated_at متوقّع خاطئ → 409
        $this->actingAs($admin)->putJson(route('admin.pb.pages.update', $page), [
            'title' => 't2', 'slug' => 'p1', 'blocks' => $this->heroBlocks(),
            'expected_updated_at' => 'قديم-خاطئ',
        ])->assertStatus(409);

        // تعديل صحيح → يُنشئ لقطة إصدار
        $this->actingAs($admin)->putJson(route('admin.pb.pages.update', $page), [
            'title' => 'عنوان جديد', 'slug' => 'p1', 'blocks' => [['type' => 'cta', 'props' => ['title' => 'ابدأ']]],
        ])->assertOk();

        $this->assertDatabaseHas('pb_pages', ['id' => $page->id, 'title' => 'عنوان جديد']);
        $this->assertDatabaseHas('pb_page_revisions', ['page_id' => $page->id]); // لقطة قبل التعديل
    }

    public function test_publish_and_restore(): void
    {
        $admin = $this->admin();
        $page = Page::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar', 'title' => 't', 'slug' => 'p2',
            'status' => 'draft', 'blocks' => [['type' => 'hero', 'props' => ['title' => 'أصليّ']]],
        ]);

        $this->actingAs($admin)->postJson(route('admin.pb.pages.publish', $page))->assertOk();
        $this->assertDatabaseHas('pb_pages', ['id' => $page->id, 'status' => 'published']);
        $this->assertNotNull($page->fresh()->published_at);

        // عدّل ثمّ استرجع أوّل لقطة
        $this->actingAs($admin)->putJson(route('admin.pb.pages.update', $page), [
            'title' => 't', 'slug' => 'p2', 'blocks' => [['type' => 'hero', 'props' => ['title' => 'معدّل']]],
        ])->assertOk();
        $rev = $page->revisions()->orderBy('id')->first();

        $this->actingAs($admin)->postJson(route('admin.pb.pages.restore', [$page, $rev]))->assertOk();
        $this->assertSame('أصليّ', $page->fresh()->blocks[0]['props']['title']);
    }

    public function test_non_admin_cannot_manage(): void
    {
        $student = User::factory()->student()->create();
        $this->actingAs($student)->postJson(route('admin.pb.pages.store'), [
            'title' => 'x', 'slug' => 'y', 'blocks' => [],
        ])->assertForbidden();
    }

    public function test_template_part_save_snapshots_revision(): void
    {
        $admin = $this->admin();
        $part = TemplatePart::create([
            'translation_group' => (string) Str::uuid(), 'locale' => 'ar', 'name' => 'الهيدر',
            'kind' => 'header', 'blocks' => [['type' => 'hero', 'props' => ['title' => 'قديم']]], 'is_active' => true,
        ]);

        $this->actingAs($admin)->putJson(route('admin.pb.parts.update', $part), [
            'name' => 'الهيدر', 'blocks' => [['type' => 'cta', 'props' => ['title' => 'جديد']]],
        ])->assertOk();

        $this->assertDatabaseHas('pb_template_part_revisions', ['template_part_id' => $part->id]);
        $this->assertSame('cta', $part->fresh()->blocks[0]['type']);
    }
}
