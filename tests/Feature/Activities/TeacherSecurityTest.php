<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\ActivityUserStreak;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * إصلاحات أمن تجربة المعلّم (المراجعة الخصميّة الشاملة):
 *  - submitReview يُزامن awarded_points (يمنع ازدواج المنح عبر تصحيح→سماح بالإعادة→إعادة تسليم).
 *  - سلسلة الأنشطة غير قابلة للحصد في اليوم الواحد (حارس last_activity_date).
 *  - إعدادات السلسلة لكل معلّم (user_id) لا عالميّة (عزل المدارس).
 */
class TeacherSecurityTest extends TestCase
{
    use RefreshDatabase;

    public function test_review_syncs_awarded_points_to_final_grade(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $student = User::factory()->student($school)->create();
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student->classrooms()->attach($classroom->id);
        $activity = Activity::factory()->create(['points' => 10, 'passing_score' => 60]);

        $submission = ActivitySubmission::create([
            'student_id' => $student->id, 'activity_id' => $activity->id, 'answer' => 'x',
            'status' => 'needs_review', 'score' => 50, 'awarded_points' => 5, 'attempts' => 1, 'submitted_at' => now(),
        ]);

        $this->actingAs($teacher)
            ->postJson(route('teacher.review.submit', $submission->id), ['score' => 100])
            ->assertOk();

        // awarded_points يُزامَن للدرجة النهائيّة (10) — فإعادةُ التسليم لاحقاً تحسب فرقاً = 0 (لا ازدواج)
        $this->assertSame(10, (int) $submission->fresh()->awarded_points);
    }

    public function test_streak_bonus_not_farmable_same_day(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $streak = ActivityUserStreak::getOrCreate($student->id);

        // اليوم الأول: يُسجَّل
        $this->assertTrue($streak->recordActivityDay());
        // صرف المكافأة + إعادة تعيين الدورة (min_days=1)
        $streak->resetStreak();
        // تسليمٌ آخر **نفس اليوم**: يجب ألّا يُسجَّل يومٌ جديد (لا مكافأة ثانية)
        $this->assertFalse($streak->recordActivityDay(), 'لا عدّ لليوم نفسه مرّتين بعد إعادة التعيين');
    }

    public function test_exercise_cannot_target_another_teachers_classroom(): void
    {
        $school = School::factory()->create();
        $teacherA = User::factory()->teacher($school)->create();
        $teacherB = User::factory()->teacher($school)->create();
        $classroomB = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacherB->id]);
        $q = \App\Models\QuestionBank::create([
            'created_by' => $teacherB->id, 'title' => 'Q', 'question_text' => 'Q?',
            'question_type' => 'true_false', 'correct_answer' => 'true', 'points' => 10,
            'difficulty' => 'easy', 'status' => 'approved',
        ]);

        // المعلّم A يحاول حقن تمرين في فصل المعلّم B → يُرفَض بخطأ تحقّق classroom_id
        $this->actingAs($teacherA)->post(route('teacher.exercises.store'), [
            'title' => 'تمرين', 'type' => 'quiz', 'difficulty' => 'easy',
            'classroom_id' => $classroomB->id, 'max_attempts' => 3, 'question_ids' => [$q->id],
        ])->assertSessionHasErrors('classroom_id');
    }

    public function test_teacher_cannot_review_submission_pending_parent_approval(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $student = User::factory()->student($school)->create();
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $student->classrooms()->attach($classroom->id);
        $activity = Activity::factory()->create(['points' => 10]);
        $submission = ActivitySubmission::create([
            'student_id' => $student->id, 'activity_id' => $activity->id, 'answer' => 'x',
            'status' => 'pending', 'parent_approval_status' => 'pending', 'attempts' => 1, 'submitted_at' => now(),
        ]);

        // بانتظار موافقة الوليّ → لا يفتحه المعلّم عبر الرابط المباشر
        $this->actingAs($teacher)->get(route('teacher.review.single', $submission->id))->assertForbidden();
    }

    public function test_team_cannot_include_students_outside_teachers_classroom(): void
    {
        $school = School::factory()->create();
        $teacherA = User::factory()->teacher($school)->create();
        $teacherB = User::factory()->teacher($school)->create();
        $classroomA = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacherA->id]);
        $classroomB = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacherB->id]);
        $studentA = User::factory()->student($school)->create();
        $studentB = User::factory()->student($school)->create(); // طالب معلّم آخر (نفس المدرسة)
        $studentA->classrooms()->attach($classroomA->id);
        $studentB->classrooms()->attach($classroomB->id);

        $this->actingAs($teacherA)->post(route('teacher.teams.store'), [
            'name' => 'فريق', 'classroom_id' => $classroomA->id,
            'leader_id' => $studentA->id, 'member_ids' => [$studentA->id, $studentB->id],
        ]);

        // studentB (خارج فصل المعلّم) لا يُضاف رغم إرساله
        $this->assertDatabaseHas('team_members', ['student_id' => $studentA->id]);
        $this->assertDatabaseMissing('team_members', ['student_id' => $studentB->id]);
    }

    public function test_email_change_requires_current_password(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create([
            'email' => 'old@example.com',
            'password' => \Illuminate\Support\Facades\Hash::make('secret123'),
        ]);

        // بلا كلمة المرور الحالية → يُرفَض تغيير البريد
        $this->actingAs($teacher)->post(route('teacher.settings.update'), [
            'name' => $teacher->name, 'email' => 'new@example.com',
        ])->assertSessionHasErrors('current_password');
        $this->assertSame('old@example.com', $teacher->fresh()->email);

        // بكلمة المرور الصحيحة → يُقبَل
        $this->actingAs($teacher)->post(route('teacher.settings.update'), [
            'name' => $teacher->name, 'email' => 'new@example.com', 'current_password' => 'secret123',
        ])->assertRedirect();
        $this->assertSame('new@example.com', $teacher->fresh()->email);
    }

    public function test_streak_settings_are_scoped_per_teacher(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();

        $this->actingAs($teacher)->put(route('teacher.streak.update'), [
            'enabled' => 1, 'min_days' => 3, 'max_days' => 7, 'bonus_points' => 50,
        ])->assertRedirect();

        // صفّ خاصّ بالمعلّم (user_id) لا عالميّ (NULL) — لا يتحكّم بطلاب مدارس أخرى
        $this->assertDatabaseHas('settings', ['key' => 'streak_bonus_points', 'user_id' => $teacher->id, 'value' => '50']);
        $this->assertDatabaseMissing('settings', ['key' => 'streak_bonus_points', 'user_id' => null]);
    }
}
