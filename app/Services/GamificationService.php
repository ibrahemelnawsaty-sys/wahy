<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\LevelUp;
use App\Services\NotificationService;

class GamificationService
{
    /**
     * إضافة XP للطالب — Transactional + lockForUpdate لمنع race condition عند Level Up.
     */
    public function addXP($studentId, $points, $source, $description)
    {
        $result = DB::transaction(function () use ($studentId, $points, $source, $description) {
            // قفل صفوف نقاط الطالب لمنع Level Up مزدوج عند تقديمين متزامنين
            $currentXP = (int) DB::table('points')
                ->where('user_id', $studentId)
                ->lockForUpdate()
                ->sum('points');

            $oldLevel = (int) floor($currentXP / 100) + 1;

            DB::table('points')->insert([
                'user_id'     => $studentId,
                'points'      => $points,
                'source'      => $source,
                'description' => $description,
                'created_at'  => now(),
                'updated_at'  => now(),
            ]);

            $newXP = $currentXP + (int) $points;
            $newLevel = (int) floor($newXP / 100) + 1;
            $leveledUp = $newLevel > $oldLevel;

            if ($leveledUp) {
                // مكافأة Level Up داخل نفس الـ transaction (ذرّية مع نقاط XP)
                $this->addCoinsRaw($studentId, $newLevel * 10, 'level_up', "مبروك المستوى {$newLevel}! 🎉");
            }

            return [
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'level_up'  => $leveledUp,
            ];
        }, 3); // 3 محاولات على الـ Deadlock

        // الأحداث والإشعارات بعد commit (تجنّب الإطلاق ثم Rollback)
        if ($result['level_up']) {
            try {
                $student = \App\Models\User::find($studentId);
                if ($student) {
                    event(new LevelUp($student, $result['new_level'], $result['old_level']));
                }
                NotificationService::levelUp($studentId, $result['new_level']);
            } catch (\Throwable $e) {
                Log::warning('Level-up post-commit notification failed: ' . $e->getMessage());
            }
        }

        return $result;
    }

    /**
     * إضافة عملات للطالب (نقطة دخول عامة — Transactional).
     */
    public function addCoins($studentId, $coins, $source, $description)
    {
        return DB::transaction(function () use ($studentId, $coins, $source, $description) {
            return $this->addCoinsRaw($studentId, $coins, $source, $description);
        });
    }

    /**
     * INSERT الفعلي — للاستخدام داخل transactions أخرى.
     */
    protected function addCoinsRaw($studentId, $coins, $source, $description): bool
    {
        return DB::table('coins')->insert([
            'user_id'     => $studentId,
            'coins'       => $coins,
            'source'      => $source,
            'description' => $description,
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }

    /**
     * خصم عملات من الطالب — يمر الآن عبر SpendService (قفل صف users + idempotent + لا overspend).
     *
     * SpendService يملك معاملته الخاصة؛ فإن استُدعيت داخل معاملة مستدعٍ تتداخل كـ savepoint
     * على صف users نفسه (لا قفل مزدوج خاطئ — نفس المعاملة تحمل القفل). لمنع الخصم المزدوج عند
     * الإعادة، على المستدعي تمرير $idempotencyKey مستقر؛ بدونه يُولَّد مفتاح فريد لكل نداء.
     */
    public function deductCoins($studentId, $coins, $description, ?string $idempotencyKey = null)
    {
        $result = \App\Services\SpendService::spend(
            (int) $studentId,
            'gamification_deduct',
            $idempotencyKey ?? (string) \Illuminate\Support\Str::uuid(),
            (int) $coins,
            $description,
        );

        return [
            'success' => $result['success'],
            'remaining' => $result['balance'],
            'message' => $result['success'] ? null : 'رصيد غير كافٍ',
        ];
    }

    /**
     * الحصول على إحصائيات الطالب
     */
    public function getStudentStats($studentId)
    {
        $totalXP = (int) DB::table('points')->where('user_id', $studentId)->sum('points');
        $totalCoins = (int) DB::table('coins')->where('user_id', $studentId)->sum('coins');
        $currentLevel = (int) floor($totalXP / 100) + 1;
        $xpForNextLevel = ($currentLevel * 100) - $totalXP;
        $progressPercentage = (($totalXP % 100) / 100) * 100;

        $streak = DB::table('streaks')->where('user_id', $studentId)->first();
        // الجدول الصحيح في schema هذا المشروع هو user_badges (وليس badge_user الافتراضي)
        $badges = DB::table('user_badges')->where('user_id', $studentId)->count();

        return [
            'total_xp' => $totalXP,
            'total_coins' => $totalCoins,
            'current_level' => $currentLevel,
            'xp_for_next_level' => $xpForNextLevel,
            'progress_percentage' => round($progressPercentage, 1),
            'streak_days' => $streak->current_days ?? 0,
            'longest_streak' => $streak->longest_days ?? 0,
            'badges_count' => $badges,
        ];
    }
}
