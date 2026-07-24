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

        // التحقق من وجود مدرسة نشطة **قابلة للحلّ** — لا مجرّد school_id غير فارغ. لو كان school_id
        // مضبوطاً لكنّ سجلّ المدرسة محذوفاً (أو الجلسة تشير لمدرسة غير مُدارة)، فإن activeSchool = null
        // فينهار كلّ مُتحكّم يقرأ $user->activeSchool->id بخطأ 500. حارسٌ موحّد هنا يمنع ذلك.
        if ($user && ! $user->activeSchool) {
            abort(403, 'لا يوجد مدرسة مرتبطة بحسابك. يرجى التواصل مع الإدارة.');
        }

        // إذا كان الطلب يحتوي على school_id في المعاملات
        $schoolId = $request->route('school') ?? $request->input('school_id');

        if ($schoolId && ! in_array((int) $schoolId, $user->managedSchoolIds(), true)) {
            abort(403, 'ليس لديك صلاحية للوصول لبيانات هذه المدرسة');
        }

        return $next($request);
    }
}
