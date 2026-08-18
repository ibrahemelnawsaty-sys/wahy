<?php

namespace App\Http\Controllers;

use App\Models\PageBuilder;
use App\Models\Survey;
use App\PageBuilder\PageResolver;

/**
 * PagesController
 * يتعامل مع الصفحات العامة: الصفحة الرئيسية، الصفحات الديناميكية،
 * الاستبيانات العامة، وتحديث CSRF token.
 * تم استخراجه من Closure routes في web.php لتمكين route:cache.
 */
class PagesController extends Controller
{
    /**
     * الصفحة الرئيسية
     */
    public function landing()
    {
        // مصدر الحقيقة الوحيد للصفحة الرئيسية هو landing.blade — يُحرَّر عبر لوحة الأدمن
        // («محتوى الصفحة الرئيسية» + المحرّر المدمج). أُلغي تجاوزُ Page Builder لـslug=home
        // (بطلب المالك): كان فتحُ محرّر الصفحات يُنشئ صفحةَ home مبنيّة/تجريبيّة تحجب الصفحة
        // الحقيقيّة. لا يزال Page Builder يعمل لبقيّة الصفحات (about-us…) عبر showPage.
        return view('landing');
    }

    /**
     * الشروط والأحكام (مهمّة 20) — صفحة ثابتة آمنة.
     */
    public function terms()
    {
        return $this->builderOr('terms', 'legal.terms');
    }

    /**
     * صفحةٌ جانبيّة يحرّرها الأدمن من محرّر الصفحات، وإلّا يُخدَم قالبها الثابت.
     *
     * آمنٌ بالتصميم كالرئيسية: ما لم يُنشئ الأدمن صفحةً **منشورة** بهذا الـslug ويرفع علمها،
     * تبقى الصفحة الثابتة هي المخدومة — فلا شاشة بيضاء ولا فقدان محتوى. ويُشترط أن تحمل
     * الصفحة كتلةً واحدة على الأقلّ (صفحة فارغة منشورة لا تحجب المحتوى القائم).
     */
    private function builderOr(string $slug, string $fallbackView)
    {
        $page = PageResolver::resolve($slug, app()->getLocale());

        if ($page && ! empty($page->blocks)) {
            return view('pb.document', ['page' => $page]);
        }

        return view($fallbackView);
    }

    /**
     * سياسة الخصوصية (مهمّة 21) — صفحة ثابتة آمنة.
     */
    public function privacy()
    {
        return $this->builderOr('privacy', 'legal.privacy');
    }

    /**
     * صفحة التسجيل
     */
    public function register()
    {
        // احترام مفتاح enable_registration (الافتراضي مفعّل)
        if (! setting('enable_registration', true)) {
            abort(403, 'التسجيل مغلق حالياً');
        }

        return view('register');
    }

    /**
     * عرض استبيان عام
     */
    public function showSurvey($id)
    {
        $survey = Survey::with('questions')->findOrFail($id);

        // لا يُكشَف استبيانٌ غير نشط/منتهٍ عبر الرابط العامّ (البوّابة الكاملة للاستهداف في submit).
        if (! $survey->isActive()) {
            abort(404);
        }

        return view('survey.show', compact('survey'));
    }

    /**
     * عرض صفحة ديناميكية بـ slug — /pages/{slug}
     */
    public function showPage($slug)
    {
        if ($v2 = PageResolver::resolve($slug, app()->getLocale())) {
            return view('pb.document', ['page' => $v2]);
        }

        // صفحة بلا محتوى قابل للعرض = غير مضبوطة ⟶ 404 بدل غلافٍ أبيض.
        if ($page = PageBuilder::servableBySlug($slug)) {
            return view('pages.show', compact('page'));
        }

        abort(404);
    }

    /**
     * عرض صفحة ديناميكية بـ slug — /page/{slug}
     */
    public function showPageAlt($slug)
    {
        if ($v2 = PageResolver::resolve($slug, app()->getLocale())) {
            return view('pb.document', ['page' => $v2]);
        }

        if ($page = PageBuilder::servableBySlug($slug)) {
            return view('pages.show', compact('page'));
        }

        abort(404);
    }

    /**
     * /home — يُحوَّل للجذر «/».
     * الصفحة الرئيسية مصدرها الوحيد landing.blade (تُحرَّر عبر «محتوى الصفحة الرئيسية»).
     * لم يعد /home يُخدَّم من page_builder (خطّة الدمج) لمنع رئيسيّة موازية مُتباعِدة.
     */
    public function home()
    {
        return redirect('/');
    }

    /**
     * تجديد CSRF Token (استخدام AJAX)
     */
    public function refreshCsrf()
    {
        return response()->json(['token' => csrf_token()]);
    }
}
