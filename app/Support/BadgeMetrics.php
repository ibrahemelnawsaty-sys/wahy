<?php

namespace App\Support;

use App\Models\ActivitySubmission;
use App\Models\Value;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * مصدر موحّد لمقاييس الشارات الستّة القياسية.
 *
 * يُستعمل من محرّك المنح (CheckBadgeEligibility) ومن صفحة الطالب (StudentController@badges)
 * معاً — نقطة واحدة للحقيقة تمنع انحراف شريط تقدّم الطالب عن عتبة المنح الفعليّة.
 */
class BadgeMetrics
{
    /**
     * احسب المقاييس المطلوبة فقط (كسول) لطالب واحد.
     * مجموع نقاط XP يُحسب مرّة واحدة ويُشارك بين مقياسَي points و level.
     *
     * @param  array<int,string>  $types  أنواع الشروط المطلوب حسابها فقط
     * @return array<string,int>          خريطة النوع → القيمة الحاليّة
     */
    public static function compute($student, array $types): array
    {
        $types = array_values(array_unique($types));
        $out = [];
        $totalPoints = null;

        $points = function () use ($student, &$totalPoints): int {
            if ($totalPoints === null) {
                $totalPoints = (int) DB::table('points')->where('user_id', $student->id)->sum('points');
            }

            return $totalPoints;
        };

        foreach ($types as $type) {
            switch ($type) {
                case 'activities_completed':
                    $out[$type] = (int) ActivitySubmission::where('student_id', $student->id)
                        ->whereIn('status', ActivitySubmission::DONE_STATUSES)
                        ->count();
                    break;

                case 'points':
                    // إجمالي XP = مجموع جدول points (نفس مصدر getStudentStats).
                    $out[$type] = $points();
                    break;

                case 'level':
                    // المستوى مشتقّ من إجمالي XP: floor(totalXP / 100) + 1 (مطابق GamificationService).
                    $out[$type] = (int) floor($points() / 100) + 1;
                    break;

                case 'streak':
                    $out[$type] = (int) (optional($student->streak)->current_streak ?? 0);
                    break;

                case 'values_mastered':
                    $out[$type] = (int) $student->crowns()->count();
                    break;

                case 'lessons_completed':
                    $out[$type] = self::completedLessonsCount($student);
                    break;
            }
        }

        return $out;
    }

    /**
     * عدد الدروس المكتملة: درس يُعدّ مكتملاً متى أُنجزت كل أنشطته النشطة المعتمَدة.
     * نسخة على مستوى الدرس من منطق masteredValueIds. مُغلَّفة بـtry/catch كي لا تُسقِط مسار الطلب.
     */
    public static function completedLessonsCount($student): int
    {
        try {
            $completedActivityIds = ActivitySubmission::where('student_id', $student->id)
                ->whereIn('status', ActivitySubmission::DONE_STATUSES)
                ->pluck('activity_id')->unique()->all();

            if (empty($completedActivityIds)) {
                return 0;
            }

            $values = Value::visibleForSchool($student->school_id)
                ->with(['concepts.lessons.activities'])
                ->get();

            $done = 0;
            foreach ($values as $value) {
                foreach ($value->concepts as $concept) {
                    foreach ($concept->lessons->where('status', 'active') as $lesson) {
                        $actIds = $lesson->activities
                            ->where('status', 'active')
                            ->where('approval_status', 'approved')
                            ->pluck('id')->all();
                        if (empty($actIds)) {
                            continue;
                        }
                        if (count(array_diff($actIds, $completedActivityIds)) === 0) {
                            $done++;
                        }
                    }
                }
            }

            return $done;
        } catch (\Throwable $e) {
            Log::warning('BadgeMetrics::completedLessonsCount failed for user ' . ($student->id ?? '?') . ': ' . $e->getMessage());

            return 0;
        }
    }
}
