<?php

namespace App\View\Composers;

use App\Models\ActivitySubmission;
use App\Models\RegistrationRequest;
use App\Models\SupportTicket;
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

        // تذاكر الدعم المُصعّدة العالقة (مفتوحة/تم الرد) — تظهر كتنبيه للسوبر أدمن.
        // مغلّفة بـtry/catch لأنّ الجدول قد لا يكون مُرحّلاً بعد على الإنتاج (وإلا ينكسر كل لوحات الأدمن).
        try {
            $escalatedTicketsCount = SupportTicket::where('escalated', true)
                ->whereIn('status', [SupportTicket::STATUS_OPEN, SupportTicket::STATUS_ANSWERED])
                ->count();
        } catch (\Throwable $e) {
            $escalatedTicketsCount = 0;
        }

        $view->with(compact('newUsersCount', 'newSubmissionsCount', 'escalatedTicketsCount'));
    }
}
