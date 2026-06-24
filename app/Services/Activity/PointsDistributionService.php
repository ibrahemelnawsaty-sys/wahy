<?php

namespace App\Services\Activity;

use App\Models\ParentPoint;
use App\Models\SchoolPoint;
use App\Models\TeacherPoint;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * توزيع النقاط على المعلم/ولي الأمر/المدرسة عند إكمال طالب لنشاط.
 *
 * مستخرج من StudentController::distributePoints لتقليل حجم الـ controller
 * وتسهيل الاختبار + إعادة الاستخدام في PvP، التمارين، أنشطة الفرق…
 */
class PointsDistributionService
{
    /** نسبة نقاط المعلم من نقاط الطالب */
    private const TEACHER_PERCENTAGE = 0.10;

    /** نسبة نقاط ولي الأمر */
    private const PARENT_PERCENTAGE = 0.05;

    /** نسبة نقاط المدرسة */
    private const SCHOOL_PERCENTAGE = 0.02;

    /**
     * توزيع النقاط بشكل ذرّي على الأطراف الثلاثة.
     *
     * كل عملية لها try/catch منفصل + Log — حتى لا يُفشل خطأ في طرف واحد توزيع البقية.
     */
    public function distribute(User $student, int $points, string $source, string $description): void
    {
        if ($points <= 0) {
            return;
        }

        $this->awardTeacher($student, $points);
        $this->awardParent($student, $points, $source, $description);
        $this->awardSchool($student, $points, $source, $description);
    }

    private function awardTeacher(User $student, int $points): void
    {
        try {
            $classroom = $student->classrooms()->with('teacher:id')->first();
            $teacher   = $classroom?->teacher;

            if (!$teacher) {
                return;
            }

            $teacherPoints = max(1, (int) floor($points * self::TEACHER_PERCENTAGE));

            // teacher_points فيه قيد unique(teacher_id) — صف واحد مجمّع لكل معلم.
            // create() كان يفشل صامتاً بعد أول نشاط؛ نستخدم firstOrCreate + increment ذرّي.
            $tp = TeacherPoint::firstOrCreate(
                ['teacher_id' => $teacher->id],
                [
                    'points'                => 0,
                    'students_total_points' => 0,
                    'students_count'        => 0,
                    'activities_created'    => 0,
                    'questions_approved'    => 0,
                ]
            );
            $tp->increment('points', $teacherPoints);
            $tp->increment('students_total_points', $points);
            $tp->increment('students_count', 1);
        } catch (\Throwable $e) {
            Log::warning('Teacher points distribution failed', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function awardParent(User $student, int $points, string $source, string $description): void
    {
        try {
            $parentRelation = DB::table('parent_student')
                ->where('student_id', $student->id)
                ->first();

            if (!$parentRelation) {
                return;
            }

            $parentPoints = max(1, (int) floor($points * self::PARENT_PERCENTAGE));

            ParentPoint::create([
                'parent_id'      => $parentRelation->parent_id,
                'points'         => $parentPoints,
                'reason'         => "من نشاط الطفل: {$description}",
                'reference_type' => $source,
                'reference_id'   => $student->id,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Parent points distribution failed', [
                'student_id' => $student->id,
                'error'      => $e->getMessage(),
            ]);
        }
    }

    private function awardSchool(User $student, int $points, string $source, string $description): void
    {
        if (!$student->school_id) {
            return;
        }

        try {
            $schoolPoints = max(1, (int) floor($points * self::SCHOOL_PERCENTAGE));

            SchoolPoint::create([
                'school_id'   => $student->school_id,
                'points'      => $schoolPoints,
                'source'      => $source,
                'description' => "من نشاط الطالب: {$description}",
                'user_id'     => $student->id,
            ]);

            // increment denormalized total — مع log عند الفشل (ليس catch صامت)
            try {
                \App\Models\School::where('id', $student->school_id)
                    ->increment('total_points', $schoolPoints);
            } catch (\Throwable $e) {
                Log::warning('School total_points increment failed', [
                    'school_id' => $student->school_id,
                    'amount'    => $schoolPoints,
                    'error'     => $e->getMessage(),
                ]);
            }
        } catch (\Throwable $e) {
            Log::warning('School points distribution failed', [
                'school_id' => $student->school_id,
                'error'     => $e->getMessage(),
            ]);
        }
    }
}
