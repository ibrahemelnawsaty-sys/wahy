<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\Point;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * صفحة «التقديمات المعلّقة» لمدير المدرسة (حسمٌ احتياطيّ عند عدم تجاوب المعلّم/الوليّ):
 *  - عزل صارم بالمدرسة (لا يرى/يحسم تسليمات مدرسة أخرى).
 *  - الاعتماد يمنح الفرق التصاعديّ بلا ازدواج، ويمسح بوّابة الوليّ المعلّقة.
 *  - الرفض يُنهي بلا منح. حسمُ تسليمٍ منتهٍ لا أثر له.
 */
class SchoolAdminPendingSubmissionsTest extends TestCase
{
    use RefreshDatabase;

    private function schoolAdmin(School $school): User
    {
        return User::factory()->create(['role' => 'school_admin', 'school_id' => $school->id, 'status' => 'active']);
    }

    private function submission(User $student, array $overrides = []): ActivitySubmission
    {
        $activity = Activity::factory()->create(['points' => 10, 'passing_score' => 60]);

        return ActivitySubmission::factory()->create(array_merge([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'status' => 'needs_review',
            'score' => null,
            'awarded_points' => 0,
        ], $overrides));
    }

    public function test_listing_is_scoped_to_own_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $admin = $this->schoolAdmin($schoolA);

        $studentA = User::factory()->student($schoolA)->create(['name' => 'طالب مدرستي']);
        $studentB = User::factory()->student($schoolB)->create(['name' => 'طالب مدرسة أخرى']);
        $this->submission($studentA);
        $this->submission($studentB);

        $this->actingAs($admin)->get(route('school-admin.pending-submissions'))
            ->assertOk()
            ->assertSee('طالب مدرستي')
            ->assertDontSee('طالب مدرسة أخرى');
    }

    public function test_cannot_approve_submission_of_another_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $admin = $this->schoolAdmin($schoolA);
        $studentB = User::factory()->student($schoolB)->create();
        $sub = $this->submission($studentB);

        $this->actingAs($admin)
            ->post(route('school-admin.pending-submissions.approve', $sub->id), ['score' => 100])
            ->assertNotFound();

        $this->assertSame('needs_review', $sub->fresh()->status);
        $this->assertSame(0, (int) Point::where('user_id', $studentB->id)->sum('points'));
    }

    public function test_approve_awards_and_is_not_double_credited(): void
    {
        $school = School::factory()->create();
        $admin = $this->schoolAdmin($school);
        $student = User::factory()->student($school)->create();
        $sub = $this->submission($student); // needs_review, awarded_points=0

        // اعتماد بدرجة 100 → XP = round(100/100 × 10) = 10
        $this->actingAs($admin)
            ->post(route('school-admin.pending-submissions.approve', $sub->id), ['score' => 100])
            ->assertRedirect();

        $sub->refresh();
        $this->assertSame('approved', $sub->status);
        $this->assertSame(10, (int) $sub->awarded_points);
        $this->assertSame((int) $admin->id, (int) $sub->reviewed_by);
        $this->assertSame(10, (int) Point::where('user_id', $student->id)->sum('points'));

        // اعتماد ثانٍ (محسوم) → لا أثر (مفتاح AwardService + حارس الحالة)
        $this->actingAs($admin)
            ->post(route('school-admin.pending-submissions.approve', $sub->id), ['score' => 100])
            ->assertRedirect();

        $this->assertSame(10, (int) Point::where('user_id', $student->id)->sum('points'), 'لا ازدواج عند إعادة الاعتماد');
    }

    public function test_approve_clears_pending_parent_gate_and_awards(): void
    {
        $school = School::factory()->create();
        $admin = $this->schoolAdmin($school);
        $student = User::factory()->student($school)->create();
        // بانتظار موافقة الوليّ: درجة محفوظة 80، بلا منح بعد
        $sub = $this->submission($student, [
            'status' => 'pending',
            'score' => 80,
            'parent_approval_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('school-admin.pending-submissions.approve', $sub->id))
            ->assertRedirect();

        $sub->refresh();
        $this->assertSame('approved', $sub->status);
        $this->assertSame('approved', $sub->parent_approval_status);
        $this->assertSame((int) $admin->id, (int) $sub->parent_approved_by);
        // XP = round(80/100 × 10) = 8
        $this->assertSame(8, (int) Point::where('user_id', $student->id)->sum('points'));
        $this->assertSame(8, (int) $sub->awarded_points);
    }

    public function test_reject_finalizes_without_award(): void
    {
        $school = School::factory()->create();
        $admin = $this->schoolAdmin($school);
        $student = User::factory()->student($school)->create();
        $sub = $this->submission($student);

        $this->actingAs($admin)
            ->post(route('school-admin.pending-submissions.reject', $sub->id), ['feedback' => 'غير مكتمل'])
            ->assertRedirect();

        $sub->refresh();
        $this->assertSame('rejected', $sub->status);
        $this->assertSame(0, (int) Point::where('user_id', $student->id)->sum('points'));
    }

    public function test_approve_manual_teacher_pending_without_score_is_refused(): void
    {
        $school = School::factory()->create();
        $admin = $this->schoolAdmin($school);
        $student = User::factory()->student($school)->create();
        // يدويّ بانتظار المعلّم: score=null، بلا بوّابة وليّ
        $sub = $this->submission($student, ['status' => 'needs_review', 'score' => null]);

        $this->actingAs($admin)
            ->post(route('school-admin.pending-submissions.approve', $sub->id)) // بلا score
            ->assertRedirect();

        $sub->refresh();
        // لم يُثبَّت اعتماداً صفريّاً — يبقى بانتظار المراجعة بلا نقاط
        $this->assertSame('needs_review', $sub->status);
        $this->assertSame(0, (int) Point::where('user_id', $student->id)->sum('points'));
    }

    public function test_approve_parent_pending_manual_without_score_clears_gate_and_routes_to_teacher(): void
    {
        $school = School::factory()->create();
        $admin = $this->schoolAdmin($school);
        $student = User::factory()->student($school)->create();
        // يدويّ بانتظار الوليّ: score=null + parent_approval_status='pending'
        $sub = $this->submission($student, [
            'status' => 'pending', 'score' => null, 'parent_approval_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('school-admin.pending-submissions.approve', $sub->id)) // بلا score
            ->assertRedirect();

        $sub->refresh();
        // بوّابة الوليّ مُسِحت، لكن لا تصفير: يبقى pending لطابور المعلّم، بلا منح
        $this->assertSame('approved', $sub->parent_approval_status);
        $this->assertSame('pending', $sub->status);
        $this->assertSame(0, (int) Point::where('user_id', $student->id)->sum('points'));
        // ما زال في القائمة (دلو المعلّم الآن، parent-cleared)
        $this->assertSame(1, ActivitySubmission::awaitingSchoolResolution($school->id)->count());
    }

    public function test_rejecting_parent_pending_resolves_gate_and_leaves_list(): void
    {
        $school = School::factory()->create();
        $admin = $this->schoolAdmin($school);
        $student = User::factory()->student($school)->create();
        $sub = $this->submission($student, [
            'status' => 'pending', 'score' => 80, 'parent_approval_status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post(route('school-admin.pending-submissions.reject', $sub->id))
            ->assertRedirect();

        $sub->refresh();
        $this->assertSame('rejected', $sub->status);
        // البوّابة حُسِمت (لا 'pending') فلا يستطيع الوليّ اعتماده لاحقاً (approveParentActivity يتجاوز)
        $this->assertNotSame('pending', $sub->parent_approval_status);
        // ولا يعود في قائمة المعلّقة
        $this->assertSame(0, ActivitySubmission::awaitingSchoolResolution($school->id)->count());
    }
}
