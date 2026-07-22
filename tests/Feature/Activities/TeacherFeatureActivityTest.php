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
 * ميزة #22: تمييز المعلّم لنشاط الطالب (الذي يراجعه) ليظهر ضمن الأنشطة المميّزة لدى الأدمن.
 * التمييز على تعريف النشاط عبر نظام is_featured القائم.
 */
class TeacherFeatureActivityTest extends TestCase
{
    use RefreshDatabase;

    /** يُنشئ معلّماً مع فصل وطالباً مُسجَّلاً فيه، ويعيد [teacher, student]. */
    private function teacherWithStudent(School $school): array
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        DB::table('classroom_student')->insert(['classroom_id' => $classroom->id, 'student_id' => $student->id]);

        return [$teacher, $student];
    }

    private function submission(User $student, Activity $activity): ActivitySubmission
    {
        return ActivitySubmission::create([
            'student_id' => $student->id,
            'activity_id' => $activity->id,
            'answer' => 'مشروع الطالب',
            'status' => 'pending',
            'attempts' => 1,
            'submitted_at' => now(),
        ]);
    }

    public function test_teacher_can_feature_activity_of_reviewed_student(): void
    {
        $school = School::factory()->create();
        [$teacher, $student] = $this->teacherWithStudent($school);

        // نشاط أنشأه الأدمن (created_by مختلف) لكنّ الطالب قدّم له
        $admin = User::factory()->create(['role' => 'super_admin']);
        $activity = Activity::factory()->create(['title' => 'مشروع مميّز', 'created_by' => $admin->id, 'is_featured' => false]);
        $this->submission($student, $activity);

        $this->actingAs($teacher)
            ->post(route('teacher.activities.feature', $activity->id), ['reason' => 'عمل متميّز'])
            ->assertRedirect();

        $activity->refresh();
        $this->assertTrue((bool) $activity->is_featured);
        $this->assertSame($teacher->id, (int) $activity->featured_by);
        $this->assertSame('عمل متميّز', $activity->featured_reason);
        $this->assertNotNull($activity->featured_at);

        // يظهر لاستعلام الأنشطة المميّزة لدى الأدمن
        $this->assertSame(1, Activity::where('is_featured', true)->count());
    }

    public function test_reason_is_optional_and_defaults(): void
    {
        $school = School::factory()->create();
        [$teacher, $student] = $this->teacherWithStudent($school);
        $activity = Activity::factory()->create(['created_by' => null, 'is_featured' => false]);
        $this->submission($student, $activity);

        $this->actingAs($teacher)
            ->post(route('teacher.activities.feature', $activity->id)) // بلا سبب
            ->assertRedirect();

        $activity->refresh();
        $this->assertTrue((bool) $activity->is_featured);
        $this->assertNotEmpty($activity->featured_reason); // سبب افتراضي
    }

    public function test_teacher_cannot_feature_unrelated_activity(): void
    {
        $school = School::factory()->create();
        [$teacher, $student] = $this->teacherWithStudent($school);
        $this->submission($student, Activity::factory()->create()); // نشاط آخر لطالبه

        // معلّم أجنبيّ بلا فصول ولا طلّاب قدّموا لهذا النشاط، وليس منشئه
        $stranger = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $activity = Activity::factory()->create(['created_by' => null, 'is_featured' => false]);
        $this->submission($student, $activity); // قدّمه طالبُ المعلّم الأصليّ، لا الأجنبيّ

        $this->actingAs($stranger)
            ->post(route('teacher.activities.feature', $activity->id))
            ->assertRedirect();

        $activity->refresh();
        $this->assertFalse((bool) $activity->is_featured, 'لا يُميّز الأجنبيّ نشاطاً لا صلة له به');
    }

    public function test_teacher_can_feature_own_created_activity(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $activity = Activity::factory()->create(['created_by' => $teacher->id, 'is_featured' => false]);

        $this->actingAs($teacher)
            ->post(route('teacher.activities.feature', $activity->id))
            ->assertRedirect();

        $this->assertTrue((bool) $activity->fresh()->is_featured);
    }

    public function test_only_featurer_or_creator_can_unfeature(): void
    {
        $school = School::factory()->create();
        [$teacher, $student] = $this->teacherWithStudent($school);
        $activity = Activity::factory()->create(['created_by' => null]);
        $this->submission($student, $activity);

        // المعلّم يميّزه
        $this->actingAs($teacher)->post(route('teacher.activities.feature', $activity->id));
        $this->assertTrue((bool) $activity->fresh()->is_featured);

        // معلّم آخر (له طالبٌ قدّم لكنّه ليس من ميّزه ولا المنشئ) لا يُلغي التمييز
        [$otherTeacher, $otherStudent] = $this->teacherWithStudent($school);
        $this->submission($otherStudent, $activity);
        $this->actingAs($otherTeacher)
            ->post(route('teacher.activities.unfeature', $activity->id))
            ->assertRedirect();
        $this->assertTrue((bool) $activity->fresh()->is_featured, 'لا يُلغي معلّمٌ تمييز زميله');

        // من ميّزه يُلغيه
        $this->actingAs($teacher)->post(route('teacher.activities.unfeature', $activity->id));
        $this->assertFalse((bool) $activity->fresh()->is_featured);
    }

    public function test_review_page_shows_feature_button(): void
    {
        $school = School::factory()->create();
        [$teacher, $student] = $this->teacherWithStudent($school);
        $activity = Activity::factory()->create(['created_by' => null, 'is_featured' => false]);
        $sub = $this->submission($student, $activity);

        $this->actingAs($teacher)
            ->get(route('teacher.review.single', $sub->id))
            ->assertOk()
            ->assertSee('تمييز هذا النشاط');
    }

    /** إصلاح جوهر #22: صفحة تفاصيل النشاط المميّز لدى الأدمن (كانت 500 — القالب مفقود). */
    public function test_admin_can_view_featured_activity_details(): void
    {
        $school = School::factory()->create();
        [$teacher, $student] = $this->teacherWithStudent($school);
        $activity = Activity::factory()->create(['created_by' => null, 'title' => 'مشروع للعرض']);
        $this->submission($student, $activity);

        // يميّزه المعلّم فيظهر للأدمن
        $this->actingAs($teacher)->post(route('teacher.activities.feature', $activity->id));

        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)
            ->get(route('admin.featured-activities.show', $activity->id))
            ->assertOk()
            ->assertSee('مشروع للعرض')
            ->assertSee($student->name);
    }
}
