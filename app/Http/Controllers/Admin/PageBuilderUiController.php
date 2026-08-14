<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PageBuilder\BlockRegistry;
use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\TemplatePart;
use App\PageBuilder\PageResolver;
use Illuminate\Contracts\View\View;

/**
 * واجهة المحرّر المخصّص الخفيف (المرحلة 2 — بلا React، داخل لوحة الأدمن).
 * صفحات Blade فقط؛ الحفظ/النشر/المعاينة عبر نقاط JSON في PageManagerController.
 * محميّة can:access-admin (تُطبَّق في المجموعة).
 */
class PageBuilderUiController extends Controller
{
    public function index(): View
    {
        return view('admin.pb.index', [
            'pages' => Page::query()->latest('updated_at')->get(),
            'liveSlugs' => PageResolver::enabledSlugs(),
        ]);
    }

    public function create(): View
    {
        return view('admin.pb.editor', ['page' => null, 'boot' => $this->boot(null, false)]);
    }

    public function edit(Page $page): View
    {
        return view('admin.pb.editor', ['page' => $page, 'boot' => $this->boot($page, PageResolver::isEnabled($page->slug))]);
    }

    /** حمولة إقلاع المحرّر (تُبنى في الخادم لتفادي مصفوفات inline داخل توجيهات Blade). */
    private function boot(?Page $page, bool $isLive): array
    {
        $locale = $page?->locale ?? 'ar';
        $partsFor = fn (string $kind) => TemplatePart::kind($kind)->where('locale', $locale)
            ->orderByDesc('is_active')->orderBy('name')->get(['id', 'name', 'is_active'])->toArray();

        return [
            'csrf' => csrf_token(),
            'schema' => BlockRegistry::schema(),
            'page' => $page ? [
                'id' => $page->id,
                'title' => $page->title,
                'slug' => $page->slug,
                'locale' => $page->locale,
                'status' => $page->status,
                'meta_title' => $page->meta_title,
                'meta_description' => $page->meta_description,
                'blocks' => $page->blocks ?? [],
                'header_part_id' => $page->header_part_id,
                'footer_part_id' => $page->footer_part_id,
                'hide_header' => (bool) $page->hide_header,
                'hide_footer' => (bool) $page->hide_footer,
                'use_site_header' => (bool) $page->use_site_header,
                'use_site_footer' => (bool) $page->use_site_footer,
                'has_unpublished' => $page->status === 'published'
                    && json_encode($page->blocks ?? []) !== json_encode($page->published_blocks ?? []),
            ] : null,
            'isLive' => $isLive,
            'parts' => ['header' => $partsFor('header'), 'footer' => $partsFor('footer')],
            'patterns' => \App\PageBuilder\Patterns::all(),
            'revisions' => $page ? $page->revisions()->limit(25)->get()->map(fn ($r) => [
                'id' => $r->id,
                'label' => $r->label,
                'at' => $r->created_at?->format('Y/m/d H:i'),
            ])->all() : [],
            'translations' => $page ? $page->translations()->get(['id', 'locale', 'title'])->toArray() : [],
            'urls' => [
                'store' => route('admin.pb.pages.store'),
                'update' => url('admin/pb/pages'),
                'publish' => url('admin/pb/pages'),
                'goLive' => url('admin/pb/pages'),
                'preview' => route('admin.pb.preview'),
                'activePart' => url('admin/pb/parts/active'),
                'updatePart' => url('admin/pb/parts'),
                'partsList' => url('admin/pb/parts'),
                'partCreate' => route('admin.pb.parts.create'),
                'partBase' => url('admin/pb/parts'),
                'design' => url('admin/pb/design'),
                'mediaIndex' => route('admin.pb.media.index'),
                'mediaStore' => route('admin.pb.media.store'),
                'indexUi' => route('admin.pb.ui.index'),
            ],
        ];
    }
}
