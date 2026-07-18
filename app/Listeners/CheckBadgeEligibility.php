<?php

namespace App\Listeners;

use App\Models\Badge;
use App\Models\User;
use App\Support\BadgeMetrics;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

class CheckBadgeEligibility
{
    /**
     * محرّك منح الشارات المبنيّ على الشرط.
     *
     * عند أيّ من الأحداث الثلاثة (نشاط مكتمل / ترقّي مستوى / تحديث سلسلة) نُعيد تقييم
     * الشارات النشطة **غير المكتسبة بعد** مقابل مقاييس الطالب، ونمنح ما تحقّق
     * (المقياس الحاليّ >= condition_value).
     */
    public function handle($event)
    {
        $student = $event->student ?? null;
        if (! $student) {
            return;
        }

        $this->evaluateBadges($student);
    }

    /**
     * تقييم الشارات النشطة غير المكتسبة مقابل مقاييس الطالب.
     */
    private function evaluateBadges($student): void
    {
        $badges = Badge::where('status', 'active')
            ->whereNotNull('condition_type')
            ->get();

        if ($badges->isEmpty()) {
            return;
        }

        // نستبعد المكتسبة أولاً — إن كانت كلها مكتسبة نخرج قبل حساب أيّ مقياس (لا نمسّ شجرة المحتوى إطلاقاً).
        $earnedIds = DB::table('user_badges')
            ->where('user_id', $student->id)
            ->pluck('badge_id')
            ->all();

        $unearned = $badges->whereNotIn('id', $earnedIds);
        if ($unearned->isEmpty()) {
            return;
        }

        // نحسب فقط المقاييس التي تطلبها الشارات غير المكتسبة (كسول + points يُحسب مرّة واحدة).
        $neededTypes = $unearned->pluck('condition_type')->unique()->all();
        $metrics = BadgeMetrics::compute($student, $neededTypes);

        foreach ($unearned as $badge) {
            $current = $metrics[$badge->condition_type] ?? null;
            if ($current === null) {
                continue;
            }

            if ($current >= (int) $badge->condition_value) {
                $this->awardBadge($student->id, $badge->id, (int) $badge->coins_reward);
            }
        }
    }

    /**
     * إسناد الوسام للطالب — idempotent تحت التزامن (فحص + قفل + التقاط انتهاك المفتاح الفريد).
     * يمنح coins = coins_reward للشارة.
     */
    private function awardBadge($studentId, $badgeId, int $coinsReward = 50)
    {
        $justInserted = DB::transaction(function () use ($studentId, $badgeId, $coinsReward) {
            $exists = DB::table('user_badges')
                ->where('user_id', $studentId)
                ->where('badge_id', $badgeId)
                ->lockForUpdate()
                ->exists();

            if ($exists) {
                return false;
            }

            try {
                DB::table('user_badges')->insert([
                    'user_id' => $studentId,
                    'badge_id' => $badgeId,
                    'earned_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            } catch (QueryException $e) {
                // سباق عبر الطلبات: صفّ user_badges لم يكن موجوداً وقت القفل فمرّ حدثان معاً.
                // الفهرس الفريد (user_id, badge_id) يرفض المكرّر — نعامله كـ«موجود مسبقاً» بلا منح مضاعف.
                if ($this->isDuplicateKey($e)) {
                    return false;
                }
                throw $e;
            }

            DB::table('coins')->insert([
                'user_id' => $studentId,
                'coins' => max(0, $coinsReward),
                'source' => 'badge_earned',
                'description' => 'حصلت على وسام جديد!',
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            return true;
        }, 3);

        // إطلاق Event مرة واحدة فقط — خارج المعاملة لتجنب تكرار الإشعار
        if ($justInserted) {
            $user = User::find($studentId);
            $badge = Badge::find($badgeId);
            if ($user && $badge) {
                event(new \App\Events\BadgeEarned($user, $badge));
            }
        }
    }

    /**
     * هل الاستثناء انتهاك مفتاح فريد/مكرّر؟ (SQLSTATE 23000 / رمز MySQL 1062).
     */
    private function isDuplicateKey(QueryException $e): bool
    {
        return ($e->getCode() === '23000')
            || (isset($e->errorInfo[1]) && (int) $e->errorInfo[1] === 1062);
    }
}
