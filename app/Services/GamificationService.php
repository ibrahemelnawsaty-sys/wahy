<?php

namespace App\Services;

use App\Events\LevelUp;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GamificationService
{
    /**
     * ★ منحنى المستويات الموحّد (مصدر واحد لكل المنصّة). تصاعديّ: عتبة المستوى L = 100×(L−1)².
     * فالمستوى = 1 + ⌊√(النقاط ÷ 100)⌋. يُبقي المستويين 1 و2 عند 0 و100 نقطة (كالصيغة الخطّيّة
     * القديمة) ثمّ يضغط الطرف العالي تدريجيّاً كي لا تظهر أرقام ضخمة (89,200 نقطة ⇒ مستوى 30 لا 892).
     */
    public static function levelForXp(int $xp): int
    {
        return 1 + (int) floor(sqrt(max(0, $xp) / 100));
    }

    /**
     * أدنى إجمالي XP لبلوغ مستوىً ما (معكوس المنحنى) — لأشرطة التقدّم والعتبات.
     */
    public static function xpForLevel(int $level): int
    {
        $level = max(1, $level);

        return 100 * ($level - 1) * ($level - 1);
    }

    /**
     * تقدّم الطالب داخل مستواه الحاليّ نحو التالي — للأشرطة والنِّسَب.
     * يُرجِع: level، into (XP داخل المستوى)، span (سعة المستوى)، percent (0-100)، to_next (المتبقّي).
     *
     * @return array{level:int,into:int,span:int,percent:int,to_next:int,floor:int,ceil:int}
     */
    public static function levelProgress(int $xp): array
    {
        $xp = max(0, $xp);
        $level = self::levelForXp($xp);
        $floor = self::xpForLevel($level);
        $ceil = self::xpForLevel($level + 1);
        $span = max(1, $ceil - $floor);
        $into = max(0, $xp - $floor);

        return [
            'level' => $level,
            'into' => $into,
            'span' => $span,
            'percent' => (int) min(100, round($into / $span * 100)),
            'to_next' => max(0, $ceil - $xp),
            'floor' => $floor,
            'ceil' => $ceil,
        ];
    }

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

            $oldLevel = self::levelForXp($currentXP);

            DB::table('points')->insert([
                'user_id' => $studentId,
                'points' => $points,
                'source' => $source,
                'description' => $description,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            $newXP = $currentXP + (int) $points;
            $newLevel = self::levelForXp($newXP);
            $leveledUp = $newLevel > $oldLevel;

            if ($leveledUp) {
                // مكافأة Level Up داخل نفس الـ transaction (ذرّية مع نقاط XP)
                $this->addCoinsRaw($studentId, $newLevel * 10, 'level_up', "مبروك المستوى {$newLevel}! 🎉");
            }

            return [
                'old_level' => $oldLevel,
                'new_level' => $newLevel,
                'level_up' => $leveledUp,
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
            'user_id' => $studentId,
            'coins' => $coins,
            'source' => $source,
            'description' => $description,
            'created_at' => now(),
            'updated_at' => now(),
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
        $progress = self::levelProgress($totalXP);
        $currentLevel = $progress['level'];
        $xpForNextLevel = $progress['to_next'];
        $progressPercentage = $progress['percent'];

        $streak = DB::table('streaks')->where('user_id', $studentId)->first();
        // الجدول الصحيح في schema هذا المشروع هو user_badges (وليس badge_user الافتراضي)
        $badges = DB::table('user_badges')->where('user_id', $studentId)->count();

        return [
            'total_xp' => $totalXP,
            'total_coins' => $totalCoins,
            'current_level' => $currentLevel,
            'xp_for_next_level' => $xpForNextLevel,
            'progress_percentage' => round($progressPercentage, 1),
            'streak_days' => $streak->current_streak ?? 0,
            'longest_streak' => $streak->longest_streak ?? 0,
            'badges_count' => $badges,
        ];
    }
}
