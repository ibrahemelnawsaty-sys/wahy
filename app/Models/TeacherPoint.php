<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class TeacherPoint extends Model
{
    protected $table = 'teacher_points';

    protected $fillable = [
        'teacher_id',
        'points',
        'students_total_points',
        'students_count',
        'activities_created',
        'questions_approved',
    ];

    protected $casts = [
        'points' => 'integer',
        'students_total_points' => 'integer',
        'students_count' => 'integer',
        'activities_created' => 'integer',
        'questions_approved' => 'integer',
    ];

    /**
     * المعلم
     */
    public function teacher(): BelongsTo
    {
        return $this->belongsTo(User::class, 'teacher_id');
    }

    /**
     * تحديث نقاط المعلم بناءً على نقاط طلابه
     */
    public static function updateTeacherPoints($teacherId)
    {
        $teacher = User::find($teacherId);

        if (! $teacher || $teacher->role !== 'teacher') {
            return;
        }

        // جلب جميع الطلاب في فصول المعلم
        $classroomIds = DB::table('classrooms')
            ->where('teacher_id', $teacherId)
            ->pluck('id');

        $studentIds = DB::table('classroom_student')
            ->whereIn('classroom_id', $classroomIds)
            ->where('status', 'active')
            ->distinct()
            ->pluck('student_id');

        // حساب إجمالي نقاط الطلاب
        $studentsTotalPoints = DB::table('points')
            ->whereIn('user_id', $studentIds)
            ->sum('points');

        // عدد الأنشطة المنشأة
        $activitiesCreated = \App\Models\Activity::where('created_by', $teacherId)->count();

        // عدد الأسئلة المعتمدة
        $questionsApproved = \App\Models\QuestionBank::where('created_by', $teacherId)
            ->where('status', 'approved')
            ->count();

        // حساب نقاط المعلم (10% من نقاط طلابه + نقاط إضافية)
        $teacherPoints = floor($studentsTotalPoints * 0.1) + ($activitiesCreated * 10) + ($questionsApproved * 5);

        // تحديث أو إنشاء سجل نقاط المعلم
        return self::updateOrCreate(
            ['teacher_id' => $teacherId],
            [
                'points' => $teacherPoints,
                'students_total_points' => $studentsTotalPoints,
                'students_count' => $studentIds->count(),
                'activities_created' => $activitiesCreated,
                'questions_approved' => $questionsApproved,
            ],
        );
    }
}
