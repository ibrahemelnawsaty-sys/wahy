<?php

namespace App\PageBuilder;

use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\PageRevision;
use App\PageBuilder\Models\TemplatePart;
use App\PageBuilder\Models\TemplatePartRevision;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * منطق حفظ/نشر/تراجع الصفحات وأجزاء القالب — طبقة خدمة قابلة لإعادة الاستخدام والاختبار.
 * كلّ تعديل يُنشئ لقطة إصدار (تراجع). النشر يثبّت status=published + published_at.
 */
class PageService
{
    /** حفظ صفحة (إنشاء/تعديل) كمسودّة. التعديل يُنشئ لقطة قبله. */
    public function savePage(array $data, ?Page $page, ?int $userId): Page
    {
        return DB::transaction(function () use ($data, $page, $userId) {
            if ($page) {
                $this->snapshotPage($page, $userId, 'auto');
                $page->fill([
                    'title' => $data['title'] ?? $page->title,
                    'slug' => $data['slug'] ?? $page->slug,
                    'locale' => $data['locale'] ?? $page->locale,
                    'blocks' => $data['blocks'] ?? $page->blocks,
                    'header_part_id' => array_key_exists('header_part_id', $data) ? $data['header_part_id'] : $page->header_part_id,
                    'footer_part_id' => array_key_exists('footer_part_id', $data) ? $data['footer_part_id'] : $page->footer_part_id,
                    'hide_header' => array_key_exists('hide_header', $data) ? $data['hide_header'] : $page->hide_header,
                    'hide_footer' => array_key_exists('hide_footer', $data) ? $data['hide_footer'] : $page->hide_footer,
                    'meta_title' => $data['meta_title'] ?? $page->meta_title,
                    'meta_description' => $data['meta_description'] ?? $page->meta_description,
                    'og_image' => $data['og_image'] ?? $page->og_image,
                    'updated_by' => $userId,
                ]);
                $page->save();

                return $page;
            }

            return Page::create([
                'translation_group' => $data['translation_group'] ?? (string) Str::uuid(),
                'locale' => $data['locale'] ?? 'ar',
                'title' => $data['title'] ?? '',
                'slug' => $data['slug'] ?? '',
                'status' => 'draft',
                'blocks' => $data['blocks'] ?? [],
                'header_part_id' => $data['header_part_id'] ?? null,
                'footer_part_id' => $data['footer_part_id'] ?? null,
                'hide_header' => $data['hide_header'] ?? false,
                'hide_footer' => $data['hide_footer'] ?? false,
                'meta_title' => $data['meta_title'] ?? null,
                'meta_description' => $data['meta_description'] ?? null,
                'og_image' => $data['og_image'] ?? null,
                'created_by' => $userId,
                'updated_by' => $userId,
            ]);
        }, 3);
    }

    public function publishPage(Page $page, ?int $userId): Page
    {
        return DB::transaction(function () use ($page, $userId) {
            $this->snapshotPage($page, $userId, 'publish');
            $page->update([
                'status' => 'published',
                'published_at' => now(),
                'updated_by' => $userId,
            ]);

            return $page;
        }, 3);
    }

    public function restorePageRevision(Page $page, PageRevision $revision, ?int $userId): Page
    {
        abort_unless((int) $revision->page_id === (int) $page->id, 404);

        return DB::transaction(function () use ($page, $revision, $userId) {
            $this->snapshotPage($page, $userId, 'pre-restore');
            $page->update(['blocks' => $revision->blocks, 'updated_by' => $userId]);

            return $page;
        }, 3);
    }

    /** استرجاع لقطة جزء قالب (هيدر/فوتر) — يُنشئ لقطة للحالة الحاليّة قبله (تراجع قابل للتراجع). */
    public function restoreTemplatePartRevision(TemplatePart $part, TemplatePartRevision $revision, ?int $userId): TemplatePart
    {
        abort_unless((int) $revision->template_part_id === (int) $part->id, 404);

        return DB::transaction(function () use ($part, $revision, $userId) {
            $this->snapshotPart($part, $userId, 'pre-restore');
            $part->update(['blocks' => $revision->blocks, 'updated_by' => $userId]);

            return $part;
        }, 3);
    }

    /** حفظ جزء قالب (هيدر/فوتر) — يُنشئ لقطة قبله. */
    public function saveTemplatePart(array $data, TemplatePart $part, ?int $userId): TemplatePart
    {
        return DB::transaction(function () use ($data, $part, $userId) {
            $this->snapshotPart($part, $userId, 'auto');
            $part->fill([
                'name' => $data['name'] ?? $part->name,
                'blocks' => $data['blocks'] ?? $part->blocks,
                'updated_by' => $userId,
            ]);
            $part->save();

            return $part;
        }, 3);
    }

    private function snapshotPage(Page $page, ?int $userId, string $label): void
    {
        PageRevision::create([
            'page_id' => $page->id,
            'blocks' => $page->blocks,
            'label' => $label,
            'created_by' => $userId,
            'created_at' => now(),
        ]);
    }

    private function snapshotPart(TemplatePart $part, ?int $userId, string $label): void
    {
        TemplatePartRevision::create([
            'template_part_id' => $part->id,
            'blocks' => $part->blocks,
            'label' => $label,
            'created_by' => $userId,
            'created_at' => now(),
        ]);
    }
}
