<?php

namespace App\Services;

use App\Enums\UserRole;
use App\Models\ParentPoint;
use App\Models\Point;
use App\Models\School;
use App\Models\SchoolPoint;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PointsService
{
    /**
     * نسبة نقاط المعلم من نقاط الطالب
     */
    const TEACHER_PERCENTAGE = 0.1; // 10%

    /**
     * نسبة نقاط ولي الأمر من نقاط الطالب
     */
    const PARENT_PERCENTAGE = 0.05; // 5%

    /**
     * نسبة نقاط المدرسة من نقاط الطالب
     */
    const SCHOOL_PERCENTAGE = 0.02; // 2%

    /**
     * إضافة نقاط للطالب مع توزيع النقاط
     */
    public static function awardStudentPoints(
        int $studentId,
        int $points,
        string $source,
        ?string $description = null,
    ): array {
        $student = User::find($studentId);
        if (! $student || $student->role !== 'student') {
            return ['success' => false, 'message' => 'الطالب غير موجود'];
        }

        // إضافة نقاط الطالب
        Point::create([
            'user_id' => $studentId,
            'points' => $points,
            'source' => $source,
            'description' => $description,
        ]);

        // مجموع نقاط الطالب يُحسب من جدول points (علاقة hasMany) — لا حاجة لعمود مكرر

        $results = [
            'student' => ['id' => $studentId, 'points' => $points],
            'teacher' => null,
            'parent' => null,
            'school' => null,
        ];

        // إضافة نقاط للمعلم
        $teacherPoints = self::calculateTeacherPoints($studentId, $points, $source, $description);
        if ($teacherPoints) {
            $results['teacher'] = $teacherPoints;
        }

        // إضافة نقاط لولي الأمر
        $parentPoints = self::calculateParentPoints($studentId, $points, $source, $description);
        if ($parentPoints) {
            $results['parent'] = $parentPoints;
        }

        // إضافة نقاط للمدرسة
        $schoolPoints = self::calculateSchoolPoints($studentId, $points, $source, $description);
        if ($schoolPoints) {
            $results['school'] = $schoolPoints;
        }

        return ['success' => true, 'results' => $results];
    }

    /**
     * حساب وإضافة نقاط المعلم
     */
    private static function calculateTeacherPoints(int $studentId, int $points, string $source, ?string $description): ?array
    {
        // جلب معلم الطالب
        $classroom = DB::table('classroom_student')
            ->join('classrooms', 'classroom_student.classroom_id', '=', 'classrooms.id')
            ->where('classroom_student.student_id', $studentId)
            ->where('classroom_student.status', 'active')
            ->select('classrooms.teacher_id')
            ->first();

        if (! $classroom || ! $classroom->teacher_id) {
            return null;
        }

        $teacherPoints = (int) floor($points * self::TEACHER_PERCENTAGE);
        if ($teacherPoints < 1) {
            $teacherPoints = 1;
        }

        // إنشاء سجل نقاط
        $record = DB::table('teacher_points')->insertGetId([
            'teacher_id' => $classroom->teacher_id,
            'points' => $teacherPoints,
            'students_total_points' => $points,
            'students_count' => 1,
            'activities_created' => 0,
            'questions_approved' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // مجموع نقاط المعلم يُحسب من جدول teacher_points

        return [
            'id' => $classroom->teacher_id,
            'points' => $teacherPoints,
            'reason' => "من نشاط الطالب: {$description}",
        ];
    }

    /**
     * حساب وإضافة نقاط ولي الأمر
     */
    private static function calculateParentPoints(int $studentId, int $points, string $source, ?string $description): ?array
    {
        // جلب ولي أمر الطالب
        $parentRelation = DB::table('parent_student')
            ->where('student_id', $studentId)
            ->first();

        if (! $parentRelation) {
            return null;
        }

        $parentPoints = (int) floor($points * self::PARENT_PERCENTAGE);
        if ($parentPoints < 1) {
            $parentPoints = 1;
        }

        // إنشاء سجل نقاط
        ParentPoint::create([
            'parent_id' => $parentRelation->parent_id,
            'points' => $parentPoints,
            'reason' => "من نشاط الطفل: {$description}",
            'reference_type' => 'student_activity',
            'reference_id' => $studentId,
        ]);

        // مجموع نقاط ولي الأمر يُحسب من جدول parent_points

        return [
            'id' => $parentRelation->parent_id,
            'points' => $parentPoints,
            'reason' => 'من نشاط الطفل',
        ];
    }

    /**
     * حساب وإضافة نقاط المدرسة
     */
    private static function calculateSchoolPoints(int $studentId, int $points, string $source, ?string $description): ?array
    {
        $student = User::find($studentId);
        if (! $student || ! $student->school_id) {
            return null;
        }

        $schoolPoints = (int) floor($points * self::SCHOOL_PERCENTAGE);
        if ($schoolPoints < 1) {
            $schoolPoints = 1;
        }

        // إنشاء سجل نقاط
        SchoolPoint::addPoints(
            $student->school_id,
            $schoolPoints,
            'student_activity',
            "من نشاط الطالب: {$description}",
            $studentId,
        );

        return [
            'id' => $student->school_id,
            'points' => $schoolPoints,
            'reason' => 'من نشاط الطالب',
        ];
    }

    /**
     * لوحة صدارة المعلمين
     */
    public static function getTeacherLeaderboard(int $limit = 20, ?int $schoolId = null): array
    {
        // النقاط من جدول teacher_points (مجموع نقاط المعلم)
        $query = User::where('users.role', 'teacher')
            ->where('users.status', 'active')
            ->leftJoinSub(
                DB::table('teacher_points')
                    ->select('teacher_id')
                    ->selectRaw('COALESCE(SUM(points), 0) as total_points')
                    ->groupBy('teacher_id'),
                'tp_sum',
                'tp_sum.teacher_id',
                '=',
                'users.id',
            )
            ->select('users.id', 'users.name', 'users.avatar', 'users.school_id')
            ->selectRaw('COALESCE(tp_sum.total_points, 0) as total_points');

        if ($schoolId) {
            $query->where('users.school_id', $schoolId);
        }

        $teachers = $query->orderByDesc('total_points')
            ->limit($limit)
            ->get();

        $rank = 1;

        return $teachers->map(function ($teacher) use (&$rank) {
            return [
                'rank' => $rank++,
                'id' => $teacher->id,
                'name' => $teacher->name,
                'avatar' => $teacher->avatar,
                'points' => (int) $teacher->total_points,
                'school' => $teacher->school?->name ?? 'غير محدد',
            ];
        })->toArray();
    }

    /**
     * لوحة صدارة أولياء الأمور
     */
    public static function getParentLeaderboard(int $limit = 20, ?int $schoolId = null): array
    {
        // النقاط من جدول parent_points
        $query = User::where('users.role', 'parent')
            ->where('users.status', 'active')
            ->leftJoinSub(
                DB::table('parent_points')
                    ->select('parent_id')
                    ->selectRaw('COALESCE(SUM(points), 0) as total_points')
                    ->groupBy('parent_id'),
                'pp_sum',
                'pp_sum.parent_id',
                '=',
                'users.id',
            )
            ->select('users.id', 'users.name', 'users.avatar', 'users.school_id')
            ->selectRaw('COALESCE(pp_sum.total_points, 0) as total_points');

        if ($schoolId) {
            $query->where('users.school_id', $schoolId);
        }

        $parents = $query->orderByDesc('total_points')
            ->limit($limit)
            ->get();

        $rank = 1;

        return $parents->map(function ($parent) use (&$rank) {
            $childrenCount = DB::table('parent_student')
                ->where('parent_id', $parent->id)
                ->count();

            return [
                'rank' => $rank++,
                'id' => $parent->id,
                'name' => $parent->name,
                'avatar' => $parent->avatar,
                'points' => (int) $parent->total_points,
                'children_count' => $childrenCount,
            ];
        })->toArray();
    }

    /**
     * لوحة صدارة المدارس
     */
    public static function getSchoolLeaderboard(int $limit = 20): array
    {
        $schools = School::where('status', 'active')
            ->select('id', 'name', 'logo', 'total_points')
            ->orderByDesc('total_points')
            ->limit($limit)
            ->get();

        $rank = 1;

        return $schools->map(function ($school) use (&$rank) {
            // إحصائيات المدرسة
            $studentsCount = User::where('school_id', $school->id)
                ->where('role', UserRole::Student->value)
                ->where('status', 'active')
                ->count();

            $teachersCount = User::where('school_id', $school->id)
                ->where('role', UserRole::Teacher->value)
                ->where('status', 'active')
                ->count();

            return [
                'rank' => $rank++,
                'id' => $school->id,
                'name' => $school->name,
                'logo' => $school->logo,
                'points' => $school->total_points,
                'students_count' => $studentsCount,
                'teachers_count' => $teachersCount,
            ];
        })->toArray();
    }

    /**
     * لوحة صدارة الطلاب
     */
    public static function getStudentLeaderboard(int $limit = 20, ?int $schoolId = null, ?int $classroomId = null): array
    {
        $query = User::where('role', 'student')
            ->where('status', 'active')
            ->select('id', 'name', 'avatar', 'school_id')
            ->withSum('points', 'points');

        if ($schoolId) {
            $query->where('school_id', $schoolId);
        }

        if ($classroomId) {
            $query->whereHas('classrooms', function ($q) use ($classroomId) {
                $q->where('classrooms.id', $classroomId);
            });
        }

        $students = $query->orderByDesc('points_sum_points')
            ->limit($limit)
            ->get();

        $rank = 1;

        return $students->map(function ($student) use (&$rank) {
            return [
                'rank' => $rank++,
                'id' => $student->id,
                'name' => $student->name,
                'avatar' => $student->avatar,
                'points' => (int) ($student->points_sum_points ?? 0),
                'school' => $student->school?->name ?? 'غير محدد',
            ];
        })->toArray();
    }
}
