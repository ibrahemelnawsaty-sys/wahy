<?php

namespace App\View\Composers;

use App\Models\ActivitySubmission;
use App\Models\RegistrationRequest;
use Illuminate\View\View;

class HeaderDataComposer
{
    /**
     * ربط البيانات بالـ view
     */
    public function compose(View $view): void
    {
        if (! auth()->check()) {
            return;
        }

        // عدد طلبات التسجيل المعلّقة (Issue #46) — كان يَعدّ users المنشأين في آخر 24 ساعة،
        // وهو معيار جامد لا يتأثر بقرار المسؤول. الآن يَعدّ الطلبات pending.
        try {
            $newUsersCount = RegistrationRequest::where('status', 'pending')->count();
        } catch (\Throwable $e) {
            $newUsersCount = 0;
        }

        // عدد التقديمات المعلقة
        $newSubmissionsCount = ActivitySubmission::where('status', 'pending')->count();

        $view->with(compact('newUsersCount', 'newSubmissionsCount'));
    }
}
