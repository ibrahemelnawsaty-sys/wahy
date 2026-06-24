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
        
        // التحقق من أن الدور المطلوب متاح للمستخدم
        if (!in_array($role, $user->getAllRoles())) {
            return back()->with('error', 'هذا الدور غير متاح لك');
        }
        
        // تبديل الدور
        $user->switchRole($role);
        
        // إعادة التوجيه للداشبورد الخاص بالدور الجديد
        $dashboardRoute = $user->getRoleDashboardRoute($role);
        
        return redirect($dashboardRoute)->with('success', 'تم التبديل إلى دور ' . $user->getRoleNameAr($role));
    }
}

