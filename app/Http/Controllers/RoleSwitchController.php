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
        if (!in_array($role, $user->getAllRoles(), true)) {
            abort(403, 'هذا الدور غير متاح لك');
        }

        // تبديل الدور — يُعيد التحقق داخلياً من getAllRoles؛ نرفض إن فشل
        if (!$user->switchRole($role)) {
            abort(403, 'هذا الدور غير متاح لك');
        }
        
        // إعادة التوجيه للداشبورد الخاص بالدور الجديد
        $dashboardRoute = $user->getRoleDashboardRoute($role);
        
        return redirect($dashboardRoute)->with('success', 'تم التبديل إلى دور ' . $user->getRoleNameAr($role));
    }
}

