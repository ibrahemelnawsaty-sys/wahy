<?php

namespace App\Http\Controllers;

use App\Models\LandingContent;
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
        // محرّر v2 له الأسبقيّة إن رُفِع علمه لـ home وُوجِدت صفحة منشورة (ت-١٢).
        if ($v2 = PageResolver::resolve('home', app()->getLocale())) {
            return view('pb.document', ['page' => $v2]);
        }

        // مصدر حقيقة واحد للصفحة الرئيسية: إن وُجدت صفحة PageBuilder مفعّلة **وذات محتوى** بـ slug=home
        // نعرضها، وإلا نرجع للصفحة الثابتة (fallback). يحلّ عدم انعكاس تخصيص الأدمن على / — Issue 12.
        // صفٌّ فارغ لا يُعتبر تخصيصاً: لولا ذلك لحجب landing.blade وأعاد صفحة بيضاء.
        if ($page = PageBuilder::servableBySlug('home')) {
            return view('pages.show', compact('page'));
        }

        return view('landing');
    }

    /**
     * الشروط والأحكام (مهمّة 20) — صفحة ثابتة آمنة.
     */
    public function terms()
    {
        return view('legal.terms');
    }

    /**
     * سياسة الخصوصية (مهمّة 21) — صفحة ثابتة آمنة.
     */
    public function privacy()
    {
        return view('legal.privacy');
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
     * الصفحة الرئيسية المخصصة /home
     */
    public function home()
    {
        if ($v2 = PageResolver::resolve('home', app()->getLocale())) {
            return view('pb.document', ['page' => $v2]);
        }

        if ($page = PageBuilder::servableBySlug('home')) {
            return view('pages.show', compact('page'));
        }

        abort(404);
    }

    /**
     * تجديد CSRF Token (استخدام AJAX)
     */
    public function refreshCsrf()
    {
        return response()->json(['token' => csrf_token()]);
    }

    /**
     * حفظ snapshot للـ Landing Content
     */
    public function landingSnapshot()
    {
        try {
            LandingContent::createSnapshot();

            return response()->json(['success' => true, 'message' => 'تم حفظ النسخة']);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Landing snapshot failed', ['error' => $e->getMessage()]);

            return response()->json(['success' => false, 'message' => 'حدث خطأ غير متوقع'], 500);
        }
    }
}
