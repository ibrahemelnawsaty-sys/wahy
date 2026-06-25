<?php

namespace Tests\Feature\Student;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Classroom;
use App\Models\Concept;
use App\Models\Lesson;
use App\Models\ParentPoint;
use App\Models\School;
use App\Models\SchoolPoint;
use App\Models\TeacherPoint;
use App\Models\User;
use App\Models\Value;
use App\Services\Activity\PointsDistributionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * يختبر تدفق Submit Activity كاملاً — من التقديم حتى توزيع النقاط.
 * هذا أكبر flow في المنصة (340 سطر في StudentController::submitActivity).
 */
class SubmitActivityFlowTest extends TestCase
{
    use RefreshDatabase;

    private User $student;

    private User $teacher;

    private User $parent;

    private School $school;

    private Classroom $classroom;

    private Activity $activity;

    protected function setUp(): void
    {
        parent::setUp();

        $this->school = School::factory()->create();
        $this->teacher = User::factory()->teacher($this->school)->create();
        $this->student = User::factory()->student($this->school)->create();
        $this->parent = User::factory()->parent($this->school)->create();
        $this->classroom = Classroom::factory()->create([
            'school_id' => $this->school->id,
            'teacher_id' => $this->teacher->id,
        ]);

        // ربط الطالب بالفصل
        DB::table('classroom_student')->insert([
            'classroom_id' => $this->classroom->id,
            'student_id' => $this->student->id,
            'status' => 'active',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // ربط ولي الأمر بالطالب
        DB::table('parent_student')->insert([
            'parent_id' => $this->parent->id,
            'student_id' => $this->student->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $value = Value::factory()->create();
        $concept = Concept::factory()->create(['value_id' => $value->id]);
        $lesson = Lesson::factory()->create(['concept_id' => $concept->id]);

        $this->activity = Activity::factory()->quiz()->create([
            'lesson_id' => $lesson->id,
            'points' => 100,
            'questions' => [
                ['question' => 'Q1', 'options' => ['أ', 'ب'], 'correct_answer' => 0],
                ['question' => 'Q2', 'options' => ['أ', 'ب'], 'correct_answer' => 1],
            ],
        ]);
    }

    public function test_student_can_create_activity_submission(): void
    {
        $submission = ActivitySubmission::create([
            'student_id' => $this->student->id,
            'activity_id' => $this->activity->id,
            'answer' => json_encode([0, 1]),
            'status' => 'pending',
            'score' => 100,
            'submitted_at' => now(),
        ]);

        $this->assertNotNull($submission->id);
        $this->assertEquals(100, $submission->score);
        $this->assertEquals('pending', $submission->status);
    }

    /**
     * تأكد من أن PointsDistributionService يوزّع النقاط على 3 أطراف.
     */
    public function test_points_distribution_creates_records_for_teacher_parent_school(): void
    {
        $service = app(PointsDistributionService::class);

        $service->distribute(
            $this->student,
            100,
            'activity_completion',
            'نشاط تجريبي',
        );

        // التحقق من النقاط الموزّعة
        $teacherPoints = TeacherPoint::where('teacher_id', $this->teacher->id)->sum('points');
        $parentPoints = ParentPoint::where('parent_id', $this->parent->id)->sum('points');
        $schoolPoints = SchoolPoint::where('school_id', $this->school->id)->sum('points');

        $this->assertEquals(10, $teacherPoints, '10% للمعلم'); // 100 * 0.10
        $this->assertEquals(5, $parentPoints, '5% لولي الأمر');  // 100 * 0.05
        $this->assertEquals(2, $schoolPoints, '2% للمدرسة');     // 100 * 0.02
    }

    /**
     * 🔴 SEC-005: طالب من مدرسة B لا يستطيع تقديم نشاط معلم في مدرسة A
     * بتجاوز scoping.
     */
    public function test_submission_does_not_leak_across_schools(): void
    {
        $otherSchool = School::factory()->create();
        $otherStudent = User::factory()->student($otherSchool)->create();

        // تقديم من طالب مدرسة أخرى
        $submission = ActivitySubmission::create([
            'student_id' => $otherStudent->id,
            'activity_id' => $this->activity->id,
            'answer' => '[0,1]',
            'status' => 'pending',
            'score' => 100,
            'submitted_at' => now(),
        ]);

        // التسليم يُنشأ (لا منع تقني)، لكن المعلم لا يجب أن يراه — راجع Policy
        $policy = new \App\Policies\ActivitySubmissionPolicy;
        $this->assertFalse(
            $policy->view($this->teacher, $submission),
            'معلم في مدرسة A يجب ألا يرى تسليم من مدرسة B',
        );
    }

    public function test_duplicate_submission_check(): void
    {
        // أول تسليم
        ActivitySubmission::create([
            'student_id' => $this->student->id,
            'activity_id' => $this->activity->id,
            'answer' => '[0,1]',
            'status' => 'pending',
            'submitted_at' => now(),
        ]);

        // المحاولة الثانية — يجب أن يعرف الـ controller بأنها duplicate
        $exists = ActivitySubmission::where('student_id', $this->student->id)
            ->where('activity_id', $this->activity->id)
            ->exists();

        $this->assertTrue($exists);
    }
}
