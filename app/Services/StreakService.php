<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\Streak;
use App\Models\User;
use Carbon\Carbon;

/**
 * سلسلة الأيام المتتالية (Daily Streak) — مصدر وحيد للحقيقة.
 *
 * يُستدعى من مسارين:
 *  - عند تسجيل الدخول (UpdateLoginStreak) — «أيام متتالية» = أيام الحضور.
 *  - عند إكمال نشاط (UpdateStreak) — احتياط لو لم يُلتقَط الدخول.
 *
 * يكتب على الأعمدة الصحيحة (current_streak / longest_streak / last_activity_date)
 * — الكود القديم كان يكتب current_days/longest_days غير الموجودة فيفشل صامتاً ويبقى العدّاد 0.
 */
class StreakService
{
    /** معالم المكافأة (أيام) */
    private const MILESTONES = [7, 14, 30, 50, 100];

    /**
     * سجّل «يوم حضور» للمستخدم وحدّث السلسلة. Idempotent لكل يوم تقويمي.
     *
     * @return array{changed:bool,current:int,milestone:bool}
     */
    public static function touch(User $user): array
    {
        $today = Carbon::today();

        $streak = Streak::firstOrCreate(
            ['user_id' => $user->id],
            ['current_streak' => 0, 'longest_streak' => 0, 'last_activity_date' => null],
        );

        $last = $streak->last_activity_date
            ? Carbon::parse($streak->last_activity_date)->startOfDay()
            : null;

        // نفس اليوم مسجّل بالفعل → لا شيء
        if ($last && $last->isSameDay($today)) {
            return ['changed' => false, 'current' => (int) $streak->current_streak, 'milestone' => false];
        }

        if ($last && $last->isSameDay($today->copy()->subDay())) {
            // آخر حضور كان أمس → استمرار السلسلة
            $streak->current_streak = (int) $streak->current_streak + 1;
        } else {
            // أوّل يوم على الإطلاق أو انقطاع (فجوة يوم فأكثر) → إعادة البدء من 1
            $streak->current_streak = 1;
        }

        $streak->longest_streak = max((int) $streak->longest_streak, (int) $streak->current_streak);
        $streak->last_activity_date = $today->toDateString();
        $streak->save();

        $current = (int) $streak->current_streak;
        $milestone = in_array($current, self::MILESTONES, true);

        if ($milestone) {
            // مكافأة معلم — أعمدة coins الصحيحة (reason/transaction_type)
            Coin::create([
                'user_id' => $user->id,
                'coins' => $current,
                'reason' => "إنجاز رائع! {$current} يوم متواصل 🔥",
                'transaction_type' => 'bonus',
            ]);

            // إشعار/شارات السلسلة (اختياري — لا يُفشل التحديث لو غاب المستمع)
            try {
                event(new \App\Events\StreakUpdated($user, $current, true));
            } catch (\Throwable $e) {
                // حدث تجميلي غير حرج — لا نُفشل تحديث السلسلة بسببه
                \Illuminate\Support\Facades\Log::warning('StreakUpdated dispatch failed: ' . $e->getMessage());
            }
        }

        return ['changed' => true, 'current' => $current, 'milestone' => $milestone];
    }
}
