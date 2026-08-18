<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * إصلاحات توجيه الأنشطة (المرحلة 1 من تشخيص docs/ACTIVITIES_DIAGNOSIS.md):
 *  - حارس موافقة الوليّ: لا تأجيل لوليٍّ غير موجود (طالب بلا وليّ يمرّ مباشرةً).
 *  - شبكة أمان مراجعة المعلّم: تسليمٌ يتيم على نشاط المعلّم (طالب بلا فصل) يبقى قابلاً للمراجعة.
 */
class ActivityRoutingFixesTest extends TestCase
{
    use RefreshDatabase;

    private function accessibleActivity(array $overrides = []): Activity
    {
        return Activity::factory()->create(array_merge([
            'type' => 'quiz',
            'status' => 'active',
            'all_schools_mode' => 'direct', // مرئيّ لكل الطلاب (يتجاوز بوّابة المدرسة في الاختبار)
            'lesson_id' => null,            // بلا قيمة → بوّابة القيمة تمرّ
            'quiz_duration' => null,
            'points' => 100,
            'passing_score' => 50,
            'requires_parent_approval' => true,
            'questions' => [
                ['type' => 'short_answer', 'question' => 'ما القيمة؟', 'correct_answer' => 'الصدق'],
            ],
        ], $overrides));
    }

    public function test_parentless_student_is_not_deferred_for_parent_approval(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create(); // بلا وليّ مرتبط
        $activity = $this->accessibleActivity();

        $this->actingAs($student)
            ->postJson(route('student.activity.submit', $activity->id), ['answer' => 'الصدق'])
            ->assertOk();

        $sub = ActivitySubmission::where('student_id', $student->id)->firstOrFail();
        $this->assertNull($sub->parent_approval_status, 'طالب بلا وليّ لا يُؤجَّل لموافقة وليّ');
        $this->assertSame('completed', $sub->status, 'يُصحَّح ويُكمَل فوراً بدل الحبس');
        $this->assertSame(100, (int) $sub->score);
    }

    public function test_student_with_parent_is_still_deferred(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $parent = User::factory()->parent($school)->create();
        DB::table('parent_student')->insert([
            'parent_id' => $parent->id, 'student_id' => $student->id,
            'created_at' => now(), 'updated_at' => now(),
        ]);
        $activity = $this->accessibleActivity();

        $this->actingAs($student)
            ->postJson(route('student.activity.submit', $activity->id), ['answer' => 'الصدق'])
            ->assertOk();

        $sub = ActivitySubmission::where('student_id', $student->id)->firstOrFail();
        $this->assertSame('pending', $sub->parent_approval_status, 'طالبٌ له وليٌّ يُؤجَّل لموافقته');
        $this->assertSame('pending', $sub->status);
    }

    public function test_teacher_can_review_orphan_submission_on_own_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        // فصلٌ للمعلّم لكنّ الطالب ليس عضواً فيه (يتيم)
        Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student = User::factory()->student($school)->create(); // بلا فصل

        $activity = Activity::factory()->create(['created_by' => $teacher->id]);
        $sub = ActivitySubmission::create([
            'student_id' => $student->id, 'activity_id' => $activity->id,
            'answer' => 'x', 'status' => 'needs_review', 'attempts' => 1, 'submitted_at' => now(),
        ]);

        $this->assertTrue($sub->isReviewableByTeacher($teacher), 'المعلّم يراجع تسليم نشاطه ولو كان الطالب بلا فصل');
        $this->assertTrue(
            ActivitySubmission::reviewableByTeacher($teacher)->whereKey($sub->id)->exists(),
            'التسليم اليتيم يظهر في طابور مراجعة المعلّم'
        );
    }

    public function test_unrelated_teacher_cannot_review(): void
    {
        $school = School::factory()->create();
        $owner = User::factory()->teacher($school)->create();
        $stranger = User::factory()->teacher($school)->create();
        $student = User::factory()->student($school)->create();

        $activity = Activity::factory()->create(['created_by' => $owner->id]);
        $sub = ActivitySubmission::create([
            'student_id' => $student->id, 'activity_id' => $activity->id,
            'answer' => 'x', 'status' => 'needs_review', 'attempts' => 1, 'submitted_at' => now(),
        ]);

        $this->assertFalse($sub->isReviewableByTeacher($stranger), 'معلّمٌ لا يملك النشاط ولا الطالب لا يراجع');
    }
}
