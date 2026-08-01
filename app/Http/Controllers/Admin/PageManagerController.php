<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PageBuilder\BlockTree;
use App\PageBuilder\BlockValidator;
use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\PageRevision;
use App\PageBuilder\Models\TemplatePart;
use App\PageBuilder\PageResolver;
use App\PageBuilder\PageService;
use App\PageBuilder\SlugGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * واجهة إدارة صفحات المحرّر الاحترافيّ (المرحلة 1، الدفعة 2) — حفظ/نشر/تراجع.
 * نقاط JSON يستدعيها محرّر المرحلة 2. محميّة can:access-admin (سوبر أدمن).
 * الأمن: BlockValidator (مخطّط + XSS) + SlugGuard (منع تظليل المسارات) على كلّ حفظ.
 */
class PageManagerController extends Controller
{
    public function __construct(private PageService $pages) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'pages' => Page::query()->latest('updated_at')
                ->get(['id', 'title', 'slug', 'locale', 'status', 'translation_group', 'updated_at']),
            'live_slugs' => PageResolver::enabledSlugs(), // المسارات المخدومة عبر v2 (ت-١٢)
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validated($request);

        if (SlugGuard::isReserved($data['slug'])) {
            return $this->reject("المسار «{$data['slug']}» محجوز للنظام — اختر غيره.");
        }
        if (Page::where('slug', $data['slug'])->where('locale', $data['locale'])->exists()) {
            return $this->reject("توجد صفحة بالمسار «{$data['slug']}» في هذه اللغة.");
        }
        if (array_key_exists('blocks', $data) && ($errors = BlockValidator::validate($data['blocks']))) {
            return $this->reject('محتوى غير صالح/غير آمن.', $errors);
        }

        $page = $this->pages->savePage($data, null, $request->user()?->id);

        return response()->json(['success' => true, 'page' => $page], 201);
    }

    public function update(Request $request, Page $page): JsonResponse
    {
        $data = $this->validated($request);

        // قفل تفاؤليّ (ت-٧): لو أرسل المحرّر updated_at متوقّعاً وتغيّر على الخادم → رفض بدل الطمس.
        if ($request->filled('expected_updated_at')
            && (string) $request->input('expected_updated_at') !== (string) $page->updated_at) {
            return $this->reject('عُدِّلت الصفحة من مكانٍ آخر منذ فتحك لها. أعِد التحميل قبل الحفظ.', [], 409);
        }

        if ($data['slug'] !== $page->slug && SlugGuard::isReserved($data['slug'])) {
            return $this->reject("المسار «{$data['slug']}» محجوز للنظام — اختر غيره.");
        }
        if (Page::where('slug', $data['slug'])->where('locale', $data['locale'])
            ->where('id', '!=', $page->id)->exists()) {
            return $this->reject("توجد صفحة أخرى بالمسار «{$data['slug']}» في هذه اللغة.");
        }
        if (array_key_exists('blocks', $data) && ($errors = BlockValidator::validate($data['blocks']))) {
            return $this->reject('محتوى غير صالح/غير آمن.', $errors);
        }

        $page = $this->pages->savePage($data, $page, $request->user()?->id);

        return response()->json(['success' => true, 'page' => $page]);
    }

    public function publish(Request $request, Page $page): JsonResponse
    {
        // نتحقّق من سلامة المخزَّن قبل النشر (دفاع بالعمق — لا نُنشر محتوى ملوَّثاً).
        if ($errors = BlockValidator::validate($page->blocks ?? [])) {
            return $this->reject('لا يمكن نشر محتوى غير آمن — صحّحه أوّلاً.', $errors);
        }

        $page = $this->pages->publishPage($page, $request->user()?->id);

        return response()->json(['success' => true, 'page' => $page]);
    }

    public function restore(Request $request, Page $page, PageRevision $revision): JsonResponse
    {
        $page = $this->pages->restorePageRevision($page, $revision, $request->user()?->id);

        return response()->json(['success' => true, 'page' => $page]);
    }

    public function destroy(Page $page): JsonResponse
    {
        PageResolver::disable($page->slug); // لا نُبقي علماً معلَّقاً لمسار محذوف
        $page->delete();

        return response()->json(['success' => true]);
    }

    /**
     * تفعيل خدمة v2 لهذا المسار العامّ (ت-١٢) — يشترط أن تكون الصفحة منشورة.
     * لا نفحص الحجز هنا: إنشاء الصفحات محجوبٌ أصلاً بـSlugGuard، والصفحات المحجوزة
     * الوحيدة القابلة للوجود (home) مقصودةٌ للترحيل؛ والعلم يؤثّر في مسارات المُصيِّر الموصولة فقط.
     */
    public function goLive(Page $page): JsonResponse
    {
        if ($page->status !== 'published') {
            return $this->reject('انشر الصفحة أوّلاً قبل تفعيلها على المسار العامّ.');
        }

        PageResolver::enable($page->slug);

        return response()->json(['success' => true, 'live_slugs' => PageResolver::enabledSlugs()]);
    }

    /** إيقاف خدمة v2 لهذا المسار (ارتداد للنظام القديم). */
    public function takeDown(Page $page): JsonResponse
    {
        PageResolver::disable($page->slug);

        return response()->json(['success' => true, 'live_slugs' => PageResolver::enabledSlugs()]);
    }

    /** الجزء الفعّال (هيدر/فوتر) لِلُغةٍ ما — يُنشئ فارغاً إن لم يوجد (تحرير المناطق المستقلّة). */
    public function activePart(Request $request, string $kind): JsonResponse
    {
        abort_unless(in_array($kind, ['header', 'footer'], true), 404);
        $locale = (string) $request->input('locale', 'ar');

        $part = TemplatePart::activeFor($kind, $locale) ?: TemplatePart::create([
            'translation_group' => (string) Str::uuid(),
            'locale' => $locale,
            'name' => $kind === 'header' ? 'الهيدر' : 'الفوتر',
            'kind' => $kind,
            'blocks' => [],
            'is_active' => true,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json(['part' => [
            'id' => $part->id, 'name' => $part->name, 'kind' => $part->kind, 'blocks' => $part->blocks ?? [],
        ]]);
    }

    /** معاينة آمنة: تُصيَّر الكتل عبر المُصيِّر الموثوق نفسه (قائمة سماح + تهريب) — لا HTML خامّ. */
    public function preview(Request $request): View
    {
        $blocks = BlockTree::prepare($this->decodeBlocks($request->input('blocks', [])));

        return view('pb.preview', ['blocks' => $blocks]);
    }

    public function updatePart(Request $request, TemplatePart $part): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'sometimes|string|max:255',
            'blocks' => 'sometimes',
        ]);
        $validated['blocks'] = $this->decodeBlocks($request->input('blocks', $part->blocks));

        if ($errors = BlockValidator::validate($validated['blocks'])) {
            return $this->reject('محتوى الجزء غير صالح/غير آمن.', $errors);
        }

        $part = $this->pages->saveTemplatePart($validated, $part, $request->user()?->id);

        return response()->json(['success' => true, 'part' => $part]);
    }

    /** تحقّق وتطبيع مدخلات الصفحة. */
    private function validated(Request $request): array
    {
        $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'required|string|max:200',
            'locale' => 'sometimes|string|max:8',
            'blocks' => 'sometimes',
            'header_part_id' => 'nullable|integer|exists:pb_template_parts,id',
            'footer_part_id' => 'nullable|integer|exists:pb_template_parts,id',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = [
            'title' => $request->input('title'),
            'slug' => SlugGuard::normalize($request->input('slug')),
            'locale' => $request->input('locale', 'ar'),
            'header_part_id' => $request->input('header_part_id'),
            'footer_part_id' => $request->input('footer_part_id'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'translation_group' => $request->input('translation_group'),
        ];

        // نُدرِج blocks فقط حين تُرسَل فعلاً — كي لا يمسح حفظُ بيانات وصفيّة (بلا كتل) جسمَ الصفحة.
        if ($request->has('blocks')) {
            $data['blocks'] = $this->decodeBlocks($request->input('blocks'));
        }

        return $data;
    }

    /** يقبل blocks كمصفوفة (JSON body) أو نصّ JSON (form). */
    private function decodeBlocks(mixed $blocks): array
    {
        if (is_string($blocks)) {
            $decoded = json_decode($blocks, true);

            return is_array($decoded) ? $decoded : [];
        }

        return is_array($blocks) ? $blocks : [];
    }

    private function reject(string $message, array $errors = [], int $status = 422): JsonResponse
    {
        return response()->json(['success' => false, 'message' => $message, 'errors' => $errors], $status);
    }
}
