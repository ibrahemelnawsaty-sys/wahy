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
 * ميزة #22 (على مستوى **تسليم الطالب**): يميّز المعلّم عملَ أحد طلّابه المتميّز، فتستعرضه
 * الإدارة ضمن «التسليمات المميّزة» للتقارير وتكريم الطلاب.
 */
class TeacherFeatureActivityTest extends TestCase
{
    use RefreshDatabase;

    /** معلّم + فصل + طالب مُسجَّل + تسليم للطالب في نشاطٍ ما. يعيد [teacher, student, submission]. */
    private function reviewedSubmission(School $school): array
    {
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        DB::table('classroom_student')->insert(['classroom_id' => $classroom->id, 'student_id' => $student->id]);

        $activity = Activity::factory()->create(['title' => 'مشروع القيم']);
        $submission = ActivitySubmission::create([
            'student_id' => $student->id, 'activity_id' => $activity->id,
            'answer' => 'عمل الطالب', 'status' => 'pending', 'attempts' => 1, 'submitted_at' => now(),
        ]);

        return [$teacher, $student, $submission];
    }

    public function test_teacher_can_feature_own_students_submission(): void
    {
        $school = School::factory()->create();
        [$teacher, $student, $submission] = $this->reviewedSubmission($school);

        $this->actingAs($teacher)
            ->post(route('teacher.review.feature', $submission->id), ['reason' => 'إبداع لافت'])
            ->assertRedirect();

        $submission->refresh();
        $this->assertTrue((bool) $submission->is_featured);
        $this->assertSame($teacher->id, (int) $submission->featured_by);
        $this->assertSame('إبداع لافت', $submission->featured_reason);
        $this->assertNotNull($submission->featured_at);
        $this->assertSame(1, ActivitySubmission::where('is_featured', true)->count());
    }

    public function test_reason_is_optional(): void
    {
        $school = School::factory()->create();
        [$teacher, , $submission] = $this->reviewedSubmission($school);

        $this->actingAs($teacher)->post(route('teacher.review.feature', $submission->id))->assertRedirect();
        $this->assertTrue((bool) $submission->fresh()->is_featured);
    }

    public function test_teacher_cannot_feature_unrelated_students_submission(): void
    {
        $school = School::factory()->create();
        [, , $submission] = $this->reviewedSubmission($school);
        // معلّم أجنبيّ بلا فصولٍ تضمّ الطالب
        $stranger = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);

        $this->actingAs($stranger)
            ->post(route('teacher.review.feature', $submission->id))
            ->assertRedirect();

        $this->assertFalse((bool) $submission->fresh()->is_featured, 'لا يُميّز الأجنبيّ تسليمًا لا يراجعه');
    }

    public function test_only_featurer_can_unfeature(): void
    {
        $school = School::factory()->create();
        [$teacher, , $submission] = $this->reviewedSubmission($school);
        $this->actingAs($teacher)->post(route('teacher.review.feature', $submission->id));
        $this->assertTrue((bool) $submission->fresh()->is_featured);

        // معلّم أجنبيّ لا يُلغي التمييز
        $stranger = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $this->actingAs($stranger)->post(route('teacher.review.unfeature', $submission->id))->assertRedirect();
        $this->assertTrue((bool) $submission->fresh()->is_featured, 'الأجنبيّ لا يُلغي تمييز غيره');

        // من ميّزه يُلغيه
        $this->actingAs($teacher)->post(route('teacher.review.unfeature', $submission->id));
        $this->assertFalse((bool) $submission->fresh()->is_featured);
    }

    public function test_review_page_shows_submission_feature_button(): void
    {
        $school = School::factory()->create();
        [$teacher, , $submission] = $this->reviewedSubmission($school);

        $this->actingAs($teacher)
            ->get(route('teacher.review.single', $submission->id))
            ->assertOk()
            ->assertSee('تمييز تسليم الطالب');
    }

    public function test_admin_featured_page_lists_featured_submission(): void
    {
        $school = School::factory()->create();
        [$teacher, $student, $submission] = $this->reviewedSubmission($school);
        $this->actingAs($teacher)->post(route('teacher.review.feature', $submission->id), ['reason' => 'عمل نموذجيّ']);

        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)
            ->get(route('admin.featured-activities'))
            ->assertOk()
            ->assertSee($student->name)
            ->assertSee('مشروع القيم')
            ->assertSee('عمل نموذجيّ');
    }

    public function test_admin_can_unfeature_submission(): void
    {
        $school = School::factory()->create();
        [$teacher, , $submission] = $this->reviewedSubmission($school);
        $this->actingAs($teacher)->post(route('teacher.review.feature', $submission->id));

        $admin = User::factory()->create(['role' => 'super_admin']);
        $this->actingAs($admin)
            ->post(route('admin.featured-activities.unfeature', $submission->id))
            ->assertRedirect();

        $this->assertFalse((bool) $submission->fresh()->is_featured);
    }
}
