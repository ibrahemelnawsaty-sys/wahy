<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\PageBuilder\BlockTree;
use App\PageBuilder\BlockValidator;
use App\PageBuilder\Models\Page;
use App\PageBuilder\Models\PageRevision;
use App\PageBuilder\Models\TemplatePart;
use App\PageBuilder\Models\TemplatePartRevision;
use App\PageBuilder\PageDesign;
use App\PageBuilder\PageResolver;
use App\PageBuilder\PageService;
use App\PageBuilder\SlugGuard;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
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

    /** تكرار صفحة — نسخة مسودّة بمسارٍ فريد، تنسخ الجسم والبيانات وإسناد الهيدر/الفوتر. */
    public function duplicate(Request $request, Page $page): JsonResponse
    {
        $base = $page->slug . '-copy';
        $slug = $base;
        $n = 2;
        while (Page::where('slug', $slug)->where('locale', $page->locale)->exists()) {
            $slug = $base . '-' . $n;
            $n++;
        }

        $copy = Page::create([
            'translation_group' => (string) Str::uuid(),
            'locale' => $page->locale,
            'title' => $page->title . ' (نسخة)',
            'slug' => $slug,
            'status' => 'draft',
            'blocks' => $page->blocks ?? [],
            'header_part_id' => $page->header_part_id,
            'footer_part_id' => $page->footer_part_id,
            'hide_header' => $page->hide_header,
            'hide_footer' => $page->hide_footer,
            'use_site_header' => $page->use_site_header,
            'use_site_footer' => $page->use_site_footer,
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json(['success' => true, 'page' => $copy], 201);
    }

    public function destroy(Page $page): JsonResponse
    {
        PageResolver::disable($page->slug); // لا نُبقي علماً معلَّقاً لمسار محذوف
        $page->delete();

        return response()->json(['success' => true]);
    }

    /**
     * إنشاء نسخة لغة مرتبطة (ت-٣) — تتشارك translation_group وتحتفظ بالـslug (unique على slug+locale).
     * تنسخ الكتل ليترجمها المحرّر. إن وُجِدت نسخة اللغة سابقاً تُعاد بدل التكرار.
     */
    public function translate(Request $request, Page $page): JsonResponse
    {
        $request->validate(['locale' => 'required|string|in:ar,en']);
        $locale = $request->input('locale');

        $existing = Page::where('translation_group', $page->translation_group)
            ->where('locale', $locale)->first();
        if ($existing) {
            return response()->json(['success' => true, 'page' => $existing, 'existed' => true]);
        }

        if (Page::where('slug', $page->slug)->where('locale', $locale)->exists()) {
            return $this->reject("توجد صفحة أخرى بالمسار «{$page->slug}» في اللغة «{$locale}».");
        }

        $copy = Page::create([
            'translation_group' => $page->translation_group,
            'locale' => $locale,
            'title' => $page->title,
            'slug' => $page->slug,
            'status' => 'draft',
            'blocks' => $page->blocks ?? [],
            'meta_title' => $page->meta_title,
            'meta_description' => $page->meta_description,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json(['success' => true, 'page' => $copy, 'existed' => false], 201);
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

    /** رموز التصميم الحاليّة + الخطوط المتاحة (ت-١٠). */
    public function design(): JsonResponse
    {
        return response()->json([
            'tokens' => PageDesign::tokens(),
            'fonts' => array_keys(PageDesign::FONTS),
        ]);
    }

    /** حفظ رموز التصميم — تُعقَّم بصرامة قبل التخزين (منع حقن CSS). */
    public function saveDesign(Request $request): JsonResponse
    {
        $tokens = PageDesign::save($request->only(['primary', 'secondary', 'text', 'bg', 'font', 'radius']));

        return response()->json(['success' => true, 'tokens' => $tokens]);
    }

    /**
     * معاينة حيّة آمنة للمستند الكامل (هيدر+جسم+فوتر+تصميم) عبر المُصيِّر الموثوق نفسه
     * (قائمة سماح + تهريب) — لا HTML خامّ. تقبل كتلاً حيّة (غير محفوظة) لكلّ منطقة، أو تشتقّ
     * الهيدر/الفوتر من المُعرّف المختار أو الافتراضيّ العالميّ. تحترم إخفاء المناطق.
     */
    public function preview(Request $request): View
    {
        $locale = (string) $request->input('locale', 'ar');
        $bodyBlocks = BlockTree::prepare($this->decodeBlocks($request->input('body', $request->input('blocks', []))));

        $resolvePart = function (string $kind) use ($request, $locale): array {
            if ($request->has($kind)) { // كتل حيّة مُرسَلة للمنطقة (تحرير غير محفوظ)
                return BlockTree::prepare($this->decodeBlocks($request->input($kind)));
            }
            $partId = $request->input($kind . '_part_id');
            $part = $partId ? TemplatePart::find($partId) : TemplatePart::activeFor($kind, $locale);

            return BlockTree::prepare($part?->blocks ?? []);
        };

        $useSiteHeader = $request->boolean('use_site_header');
        $useSiteFooter = $request->boolean('use_site_footer');

        return view('pb.preview-doc', [
            'locale' => $locale,
            'useSiteHeader' => $useSiteHeader,
            'useSiteFooter' => $useSiteFooter,
            'headerBlocks' => ($request->boolean('hide_header') || $useSiteHeader) ? [] : $resolvePart('header'),
            'bodyBlocks' => $bodyBlocks,
            'footerBlocks' => ($request->boolean('hide_footer') || $useSiteFooter) ? [] : $resolvePart('footer'),
        ]);
    }

    /** سرد أجزاء القالب (هيدر/فوتر) للُغةٍ ما — لاختيار الجزء لكلّ صفحة. */
    public function parts(Request $request): JsonResponse
    {
        $kind = (string) $request->input('kind');
        abort_unless(in_array($kind, ['header', 'footer'], true), 404);
        $locale = (string) $request->input('locale', 'ar');

        return response()->json([
            'parts' => TemplatePart::kind($kind)->where('locale', $locale)
                ->orderByDesc('is_active')->orderBy('name')
                ->get(['id', 'name', 'kind', 'is_active']),
        ]);
    }

    /** إنشاء جزء قالب مُسمّى جديد (ليس الافتراضيّ العالميّ إلّا بتعيينه صراحةً). */
    public function createPart(Request $request): JsonResponse
    {
        $data = $request->validate([
            'kind' => 'required|in:header,footer',
            'name' => 'required|string|max:255',
            'locale' => 'sometimes|string|max:8',
        ]);

        $part = TemplatePart::create([
            'translation_group' => (string) Str::uuid(),
            'locale' => $data['locale'] ?? 'ar',
            'name' => $data['name'],
            'kind' => $data['kind'],
            'blocks' => [],
            'is_active' => false,
            'created_by' => $request->user()?->id,
            'updated_by' => $request->user()?->id,
        ]);

        return response()->json(['success' => true, 'part' => [
            'id' => $part->id, 'name' => $part->name, 'kind' => $part->kind, 'is_active' => false, 'blocks' => [],
        ]], 201);
    }

    /** جلب جزء قالب بعينه (كتله) — لتحرير الجزء الذي اختارته الصفحة تحديداً. */
    public function showPart(TemplatePart $part): JsonResponse
    {
        return response()->json(['part' => [
            'id' => $part->id, 'name' => $part->name, 'kind' => $part->kind, 'blocks' => $part->blocks ?? [],
        ]]);
    }

    /** جعل هذا الجزء الافتراضيّ العالميّ لنوعه ولغته (يخفض is_active عن أشقائه). */
    public function setDefaultPart(TemplatePart $part): JsonResponse
    {
        DB::transaction(function () use ($part) {
            TemplatePart::kind($part->kind)->where('locale', $part->locale)
                ->where('id', '!=', $part->id)->update(['is_active' => false]);
            $part->update(['is_active' => true]);
        });

        return response()->json(['success' => true]);
    }

    /** استرجاع لقطة سابقة لجزء قالب (مرآة restore للصفحات). */
    public function restorePart(Request $request, TemplatePart $part, TemplatePartRevision $revision): JsonResponse
    {
        $part = $this->pages->restoreTemplatePartRevision($part, $revision, $request->user()?->id);

        return response()->json(['success' => true, 'part' => [
            'id' => $part->id, 'name' => $part->name, 'kind' => $part->kind, 'blocks' => $part->blocks ?? [],
        ]]);
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
            'hide_header' => 'sometimes|boolean',
            'hide_footer' => 'sometimes|boolean',
            'use_site_header' => 'sometimes|boolean',
            'use_site_footer' => 'sometimes|boolean',
            'meta_title' => 'nullable|string|max:255',
            'meta_description' => 'nullable|string|max:500',
        ]);

        $data = [
            'title' => $request->input('title'),
            'slug' => SlugGuard::normalize($request->input('slug')),
            'locale' => $request->input('locale', 'ar'),
            'meta_title' => $request->input('meta_title'),
            'meta_description' => $request->input('meta_description'),
            'translation_group' => $request->input('translation_group'),
        ];

        // حقول المناطق تُطبَّق فقط حين تُرسَل صراحةً (has) — كي لا يمسح حفظٌ لا يتضمّنها الإسنادَ القائم.
        // (null صريح يعني «الافتراضيّ العالميّ»؛ الغياب يعني «اتركها كما هي».)
        foreach (['header_part_id', 'footer_part_id'] as $k) {
            if ($request->has($k)) {
                $data[$k] = $request->input($k);
            }
        }
        foreach (['hide_header', 'hide_footer', 'use_site_header', 'use_site_footer'] as $k) {
            if ($request->has($k)) {
                $data[$k] = $request->boolean($k);
            }
        }

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
