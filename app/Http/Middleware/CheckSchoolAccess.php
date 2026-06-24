<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckSchoolAccess
{
    /**
     * التحقق من أن المستخدم ينتمي للمدرسة الصحيحة
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        
        // Super Admin يصل لكل شيء
        if ($user && $user->isSuperAdmin()) {
            return $next($request);
        }
        
        // التحقق من وجود مدرسة للمستخدم
        if ($user && !$user->school_id) {
            abort(403, 'لا يوجد مدرسة مرتبطة بحسابك. يرجى التواصل مع الإدارة.');
        }
        
        // إذا كان الطلب يحتوي على school_id في المعاملات
        $schoolId = $request->route('school') ?? $request->input('school_id');
        
        if ($schoolId && $user->school_id != $schoolId) {
            abort(403, 'ليس لديك صلاحية للوصول لبيانات هذه المدرسة');
        }
        
        return $next($request);
    }
}
