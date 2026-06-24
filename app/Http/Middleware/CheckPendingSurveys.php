<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Survey;
use Symfony\Component\HttpFoundation\Response;

class CheckPendingSurveys
{
    /**
     * المسارات المستثناة من فحص الاستبيانات
     */
    protected $except = [
        'survey/submit',
        'survey/respond/*',
        'logout',
        'api/*',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // تجاهل طلبات AJAX
        if ($request->ajax() || $request->wantsJson()) {
            return $next($request);
        }

        // تجاهل المسارات المستثناة
        foreach ($this->except as $pattern) {
            if ($request->is($pattern)) {
                return $next($request);
            }
        }

        // التحقق من تسجيل الدخول
        if (!Auth::check()) {
            return $next($request);
        }

        $user = Auth::user();

        // تجاهل السوبر أدمن
        if ($user->role === 'super_admin' || $user->role === 'admin') {
            return $next($request);
        }

        // جلب الاستبيانات المعلقة
        $pendingSurveys = Survey::getPendingSurveysForUser($user);

        if ($pendingSurveys->isNotEmpty()) {
            // تخزين الاستبيانات في الجلسة
            session(['pending_surveys' => $pendingSurveys]);
            session(['show_survey_popup' => true]);
        } else {
            session()->forget(['pending_surveys', 'show_survey_popup']);
        }

        return $next($request);
    }
}
