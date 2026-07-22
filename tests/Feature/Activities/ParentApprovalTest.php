<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Classroom;
use App\Models\ParentPoint;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * ميزة #23: نشاط «يتطلب موافقة وليّ الأمر» — لا ينتقل تسليم الطالب للمعلّم إلا بعد موافقة الوليّ،
 * ويأخذ الوليّ نقاطاً على موافقته.
 */
class ParentApprovalTest extends TestCase
{
    use RefreshDatabase;

    private function submission(User $student, Activity $activity, ?string $parentStatus): ActivitySubmission
    {
        return ActivitySubmission::create([
            'student_id' => $student->id,
            'activity_id' => $activity->id,
            'answer' => 'إجابة',
            'status' => 'pending',
            'attempts' => 1,
            'submitted_at' => now(),
            'parent_approval_status' => $parentStatus,
        ]);
    }

    public function test_teacher_queue_excludes_parent_pending_submissions(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        DB::table('classroom_student')->insert(['classroom_id' => $classroom->id, 'student_id' => $student->id]);

        $needsParent = Activity::factory()->create(['title' => 'نشاط بموافقة الوليّ', 'requires_parent_approval' => true]);
        $normal = Activity::factory()->create(['title' => 'نشاط عاديّ للمراجعة']);

        $this->submission($student, $needsParent, 'pending'); // بانتظار الوليّ → لا يظهر للمعلّم
        $this->submission($student, $normal, null);           // لا يتطلّب موافقة → يظهر

        $this->actingAs($teacher)
            ->get(route('teacher.review'))
            ->assertOk()
            ->assertSee('نشاط عاديّ للمراجعة')
            ->assertDontSee('نشاط بموافقة الوليّ');
    }

    public function test_parent_approval_moves_to_teacher_and_awards_parent(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        DB::table('classroom_student')->insert(['classroom_id' => $classroom->id, 'student_id' => $student->id]);

        $parent = User::factory()->create(['role' => 'parent', 'school_id' => $school->id]);
        $parent->children()->attach($student->id);

        $activity = Activity::factory()->create(['title' => 'مشروع الابن', 'requires_parent_approval' => true]);
        $sub = $this->submission($student, $activity, 'pending');

        // قبل الموافقة: لا يظهر للمعلّم
        $this->actingAs($teacher)->get(route('teacher.review'))->assertDontSee('مشروع الابن');

        // وليّ الأمر يوافق
        $this->actingAs($parent)
            ->post(route('parent.family-activities.parent-approve', $sub->id))
            ->assertRedirect();

        $sub->refresh();
        $this->assertSame('approved', $sub->parent_approval_status);
        $this->assertSame($parent->id, (int) $sub->parent_approved_by);
        $this->assertNotNull($sub->parent_approved_at);

        // نقاط وليّ الأمر مُنِحت (ParentPoint مثبّت على معرّف التسليم)
        $this->assertTrue(
            ParentPoint::where('parent_id', $parent->id)
                ->where('reference_type', 'parent_activity_approval')
                ->where('reference_id', $sub->id)
                ->exists()
        );

        // بعد الموافقة: يدخل طابور المعلّم
        $this->actingAs($teacher)->get(route('teacher.review'))->assertSee('مشروع الابن');
    }

    public function test_parent_cannot_approve_non_child_submission(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $strangerParent = User::factory()->create(['role' => 'parent', 'school_id' => $school->id]);
        // لا رابط أبوّة بين strangerParent والطالب

        $activity = Activity::factory()->create(['requires_parent_approval' => true]);
        $sub = $this->submission($student, $activity, 'pending');

        $this->actingAs($strangerParent)
            ->post(route('parent.family-activities.parent-approve', $sub->id))
            ->assertRedirect();

        $sub->refresh();
        $this->assertSame('pending', $sub->parent_approval_status, 'لم تُغيَّر الحالة');
        $this->assertNull($sub->parent_approved_by);
        $this->assertDatabaseCount('parent_points', 0);
    }

    public function test_double_approval_is_idempotent(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $parent = User::factory()->create(['role' => 'parent', 'school_id' => $school->id]);
        $parent->children()->attach($student->id);

        $activity = Activity::factory()->create(['requires_parent_approval' => true]);
        $sub = $this->submission($student, $activity, 'pending');

        $this->actingAs($parent)->post(route('parent.family-activities.parent-approve', $sub->id));
        $this->actingAs($parent)->post(route('parent.family-activities.parent-approve', $sub->id)); // مرّة ثانية

        // نقاط الوليّ مرّة واحدة فقط
        $this->assertSame(1, ParentPoint::where('parent_id', $parent->id)
            ->where('reference_id', $sub->id)->count());
    }
}
