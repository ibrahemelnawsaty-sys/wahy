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

        // السوبر آدمن لديه صلاحية الوصول لكل المسارات (يستطيع إنشاء/مراجعة محتوى المعلم)
        if (! $hasPermission && in_array('super_admin', $userRoles, true)) {
            $hasPermission = true;
        }

        if (! $hasPermission) {
            abort(403, 'ليس لديك صلاحية للوصول لهذه الصفحة');
        }

        return $next($request);
    }
}
