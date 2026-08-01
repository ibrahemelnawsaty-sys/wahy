<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PageBuilder\BlockRegistry;
use App\PageBuilder\Models\Page;
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
            ] : null,
            'isLive' => $isLive,
            'urls' => [
                'store' => route('admin.pb.pages.store'),
                'update' => url('admin/pb/pages'),
                'publish' => url('admin/pb/pages'),
                'goLive' => url('admin/pb/pages'),
                'preview' => route('admin.pb.preview'),
                'activePart' => url('admin/pb/parts/active'),
                'updatePart' => url('admin/pb/parts'),
                'mediaIndex' => route('admin.pb.media.index'),
                'mediaStore' => route('admin.pb.media.store'),
                'indexUi' => route('admin.pb.ui.index'),
            ],
        ];
    }
}
