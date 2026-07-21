<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RoleSwitchController extends Controller
{
    /**
     * تبديل دور المستخدم
     */
    public function switch(Request $request, string $role)
    {
        $user = Auth::user();

        // التحقق من أن الدور المطلوب متاح للمستخدم (منع تصعيد الصلاحيات)
        if (! in_array($role, $user->getAllRoles(), true)) {
            abort(403, 'هذا الدور غير متاح لك');
        }

        // إن كان الدور معطوباً (مثلاً مرتبط بمدرسة والمستخدم بلا مدرسة) — لا نبدّل إليه،
        // بل نوضّح سبب عدم الفتح ونعرض خيار العودة للحساب الأساسيّ (بدل لوحة تنكسر).
        $reason = $user->roleBlockReason($role);
        if ($reason !== null) {
            return response()->view('auth.role-unavailable', [
                'roleName' => $user->getRoleNameAr($role),
                'reason' => $reason,
                'primaryRoleName' => $user->getRoleNameAr($user->role),
            ]);
        }

        // تبديل الدور — يُعيد التحقق داخلياً من getAllRoles؛ نرفض إن فشل
        if (! $user->switchRole($role)) {
            abort(403, 'هذا الدور غير متاح لك');
        }

        // إعادة التوجيه للداشبورد الخاص بالدور الجديد
        $dashboardRoute = $user->getRoleDashboardRoute($role);

        return redirect($dashboardRoute)->with('success', 'تم التبديل إلى دور ' . $user->getRoleNameAr($role));
    }

    /**
     * إرجاع المستخدم إلى دوره الأساسيّ (من صفحة «تعذّر فتح الدور» أو أيّ مكان).
     */
    public function resetToPrimary(Request $request)
    {
        $user = Auth::user();
        $user->clearActiveRole();

        return redirect()->route('dashboard')->with('success', 'تم الرجوع إلى حسابك الأساسيّ');
    }
}
