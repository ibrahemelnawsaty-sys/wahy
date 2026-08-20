<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * التحقق من دور المستخدم
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        // التحقق من حالة الحساب أولاً
        if ($user->status !== 'active') {
            auth()->logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('login')->with('error', 'تم تعطيل حسابك. يرجى التواصل مع مدير المدرسة لتفعيل الحساب.');
        }

        // الحصول على الدور النشط (يدعم تبديل الأدوار)
        $activeRole = session('active_role_' . $user->id, $user->active_role ?? $user->role);

        // التحقق من أن المستخدم لديه أحد الأدوار المطلوبة
        // نتحقق من الدور النشط أو جميع الأدوار المتاحة للمستخدم
        $userRoles = $user->getAllRoles();

        $hasPermission = in_array($activeRole, $roles) || ! empty(array_intersect($userRoles, $roles));

        // ملاحظة: لا تجاوز شامل للسوبر أدمن هنا — كان يُدخِله تجارب الأدوار (طالب/معلّم/وليّ) بحسابه
        // فتُعرَض لوحة الطالب لمدير النظام عبر تغيير الرابط، ويستطيع الفعل كطالب (تسريب عزل الأدوار).
        // مراجعة محتوى المعلّم/الاعتماد تعيش أصلاً تحت /admin (بوّابة can:access-admin). إن لزم لاحقاً
        // وصولٌ صريح لمجموعةٍ بعينها، يُضاف super_admin لقائمة role: لتلك المجموعة في routes/web.php.

        if (! $hasPermission) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الصفحة');
        }

        return $next($request);
    }
}
