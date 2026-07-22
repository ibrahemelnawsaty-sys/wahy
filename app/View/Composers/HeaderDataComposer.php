<?php

namespace App\View\Composers;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\RegistrationRequest;
use App\Models\SupportTicket;
use App\Models\User;
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

        // «مستخدم جديد» = مستخدمون جدد جديرون بالانتباه. كان يَعدّ طلبات التسجيل المعلّقة فقط،
        // فلا يظهر المستخدم المُنشأ مباشرةً (أدمن) ولا المُسجَّل ذاتياً (User غير نشط عبر /register)
        // رغم ظهوره في الإحصاءات. الآن = طلبات معلّقة + (مستخدم غير نشط بانتظار التفعيل، أياً كان
        // عمره) ∪ (مستخدم أُنشئ خلال آخر 7 أيام). مغلّف بـtry/catch (لا يكسر لايوت الأدمن).
        try {
            $newUsersCount = RegistrationRequest::where('status', 'pending')->count()
                + User::where('id', '!=', auth()->id()) // لا يَعدّ المسؤول الحاليّ نفسه
                    ->where(function ($q) {
                        $q->where('status', 'inactive')
                            ->orWhere('created_at', '>=', now()->subDays(7));
                    })->count();
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

        // أنشطة المعلّمين المعتمدة مدرسياً وبانتظار اعتماد الأدمن النهائيّ (طابور السوبر أدمن) —
        // ليعرف المهامّ التي عليه بمجرّد الدخول. مغلّفة بـtry/catch (لا تكسر لايوت الأدمن).
        try {
            $pendingActivitiesCount = Activity::whereNotNull('created_by')
                ->where('school_approval_status', 'approved')
                ->where('approval_status', 'pending')
                ->whereHas('creator', fn ($q) => $q->where('role', 'teacher'))
                ->count();
        } catch (\Throwable $e) {
            $pendingActivitiesCount = 0;
        }

        $view->with(compact('newUsersCount', 'newSubmissionsCount', 'escalatedTicketsCount', 'pendingActivitiesCount'));
    }
}
