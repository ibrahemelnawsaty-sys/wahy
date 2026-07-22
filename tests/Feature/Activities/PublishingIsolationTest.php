<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\Concept;
use App\Models\Lesson;
use App\Models\School;
use App\Models\User;
use App\Models\Value;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * عزل النشر متعدّد المدارس (fccce21) — إصلاحات المراجعة الخصمية:
 *  A) لا يتسرّب نشاطُ بنكٍ نُشِر لمدرسة أخرى إلى معلّمي/طلاب مدرسةٍ غير مستهدفة.
 *  B) هجرة التوافق تُبقي أنشطة البنك المرتبطة بدرس مرئيّة (لا تختفي).
 *  C) API الجوّال يفرض بوّابة «القيمة المفعّلة للمدرسة» كالويب.
 *  D) رفض مدير المدرسة يقتصر أثره على مدرسته ولا يعكس اعتماد الأدمن.
 */
class PublishingIsolationTest extends TestCase
{
    use RefreshDatabase;

    private function bankActivity(User $creator, string $allSchoolsMode = 'none'): Activity
    {
        return Activity::factory()->create([
            'created_by' => $creator->id,
            'is_activity_bank' => true,
            'approval_status' => 'approved',
            'all_schools_mode' => $allSchoolsMode,
        ]);
    }

    // ========================= A) عزل بنك المدارس =========================

    public function test_teacher_cannot_reference_bank_activity_published_to_other_school_only(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = User::factory()->teacher($schoolA)->create();
        $classroomA = Classroom::factory()->create(['school_id' => $schoolA->id, 'teacher_id' => $teacherA->id]);
        $teacherB = User::factory()->teacher($schoolB)->create();

        // نشاط بنك نُشِر لمدرسة B فقط (لا لكل المدارس)
        $activity = $this->bankActivity($teacherB, 'none');
        $activity->schools()->attach($schoolB->id, ['publish_mode' => 'bank', 'published_at' => now()]);

        $this->actingAs($teacherA)
            ->post(route('teacher.activity-bank.reference', $activity->id), ['classroom_ids' => [$classroomA->id]])
            ->assertForbidden();

        $this->assertDatabaseMissing('activity_classroom', [
            'activity_id' => $activity->id,
            'classroom_id' => $classroomA->id,
        ]);
    }

    public function test_teacher_cannot_clone_bank_activity_published_to_other_school_only(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = User::factory()->teacher($schoolA)->create();
        $classroomA = Classroom::factory()->create(['school_id' => $schoolA->id, 'teacher_id' => $teacherA->id]);
        $teacherB = User::factory()->teacher($schoolB)->create();

        $activity = $this->bankActivity($teacherB, 'none');
        $activity->schools()->attach($schoolB->id, ['publish_mode' => 'bank', 'published_at' => now()]);

        $this->actingAs($teacherA)
            ->post(route('teacher.activity-bank.clone', $activity->id), ['classroom_id' => $classroomA->id])
            ->assertForbidden();
    }

    public function test_teacher_can_reference_bank_activity_published_to_all_schools(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = User::factory()->teacher($schoolA)->create();
        $classroomA = Classroom::factory()->create(['school_id' => $schoolA->id, 'teacher_id' => $teacherA->id]);
        $teacherB = User::factory()->teacher($schoolB)->create();

        // نُشِر لكل المدارس ⇒ متاح في بنك مدرسة A
        $activity = $this->bankActivity($teacherB, 'bank');

        $this->actingAs($teacherA)
            ->post(route('teacher.activity-bank.reference', $activity->id), ['classroom_ids' => [$classroomA->id]])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_classroom', [
            'activity_id' => $activity->id,
            'classroom_id' => $classroomA->id,
        ]);
    }

    public function test_student_does_not_see_activity_published_to_other_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $studentA = User::factory()->student($schoolA)->create();
        $classroomA = Classroom::factory()->create(['school_id' => $schoolA->id]);
        DB::table('classroom_student')->insert(['classroom_id' => $classroomA->id, 'student_id' => $studentA->id]);

        $activity = Activity::factory()->create(['approval_status' => 'approved', 'all_schools_mode' => 'none', 'status' => 'active']);
        $activity->schools()->attach($schoolB->id, ['publish_mode' => 'direct', 'published_at' => now()]);

        $visible = Activity::query()
            ->visibleToStudent($studentA->school_id, [$classroomA->id])
            ->where('id', $activity->id)
            ->exists();

        $this->assertFalse($visible, 'طالب مدرسة A يجب ألّا يرى نشاطًا نُشِر لمدرسة B فقط');
    }

    // ========================= B) هجرة التوافق =========================

    public function test_compat_backfill_keeps_bank_activities_with_lesson_visible(): void
    {
        // نُحاكي حالة ما-قبل-الهجرة (all_schools_mode='none') لعدّة أشكال، ثم نُشغّل شرط الهجرة نفسه.
        $bankWithLesson = Activity::factory()->create(['is_activity_bank' => true, 'approval_status' => 'approved', 'all_schools_mode' => 'none', 'lesson_id' => Lesson::factory()->create()->id]);
        $bankNoLesson = Activity::factory()->create(['is_activity_bank' => true, 'approval_status' => 'approved', 'all_schools_mode' => 'none', 'lesson_id' => null, 'classroom_id' => null]);
        $bankClassroomNoLesson = Activity::factory()->create(['is_activity_bank' => true, 'approval_status' => 'approved', 'all_schools_mode' => 'none', 'lesson_id' => null, 'classroom_id' => Classroom::factory()->create()->id, 'is_homework' => false]);
        $normalWithLesson = Activity::factory()->create(['is_activity_bank' => false, 'approval_status' => 'approved', 'all_schools_mode' => 'none', 'lesson_id' => Lesson::factory()->create()->id]);
        $normalNoLesson = Activity::factory()->create(['is_activity_bank' => false, 'approval_status' => 'approved', 'all_schools_mode' => 'none', 'lesson_id' => null]);
        $notApproved = Activity::factory()->create(['is_activity_bank' => true, 'approval_status' => 'pending', 'all_schools_mode' => 'none', 'lesson_id' => Lesson::factory()->create()->id]);

        // شرط الهجرة 000001 خطوة 3 (نسخة طبق الأصل)
        DB::table('activities')
            ->where('approval_status', 'approved')
            ->where('all_schools_mode', 'none')
            ->where(function ($q) {
                $q->where('is_activity_bank', false)
                    ->orWhereNotNull('lesson_id');
            })
            ->update(['all_schools_mode' => 'direct']);

        $this->assertSame('direct', $bankWithLesson->fresh()->all_schools_mode, 'نشاط بنك بدرس يجب أن يبقى مرئيًّا');
        $this->assertSame('direct', $normalWithLesson->fresh()->all_schools_mode);
        $this->assertSame('direct', $normalNoLesson->fresh()->all_schools_mode, 'غير البنكيّ يُحفظ كسلوك الأصل');
        $this->assertSame('none', $bankNoLesson->fresh()->all_schools_mode, 'قالب بنك بلا درس يبقى مخفيًّا (سدّ ثغرة id)');
        $this->assertSame('none', $bankClassroomNoLesson->fresh()->all_schools_mode, 'بنك بفصل بلا درس يبقى مخفيًّا — لا نُعيد فتح ثغرة E عبر direct');
        $this->assertSame('none', $notApproved->fresh()->all_schools_mode, 'غير المعتمَد لا يُنشر');
    }

    // ========================= C) بوّابة القيمة في API =========================

    private function activityInValue(Value $value, string $allSchoolsMode = 'direct'): Activity
    {
        $concept = Concept::create(['value_id' => $value->id, 'name' => 'مفهوم', 'order' => 1]);
        $lesson = Lesson::factory()->create(['concept_id' => $concept->id]);

        return Activity::factory()->create([
            'lesson_id' => $lesson->id,
            'approval_status' => 'approved',
            'all_schools_mode' => $allSchoolsMode,
            'status' => 'active',
        ]);
    }

    public function test_api_activity_details_blocked_when_value_hidden_for_school(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();

        $hiddenValue = Value::create(['name' => 'قيمة مخفيّة', 'order' => 1, 'status' => 'active', 'created_by' => null]);
        $shownValue = Value::create(['name' => 'قيمة ظاهرة', 'order' => 2, 'status' => 'active', 'created_by' => null]);
        // المدرسة فعّلت قيمة أخرى فقط ⇒ hiddenValue غير مرئيّة لها
        DB::table('school_active_values')->insert(['school_id' => $school->id, 'value_id' => $shownValue->id, 'activated_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $activity = $this->activityInValue($hiddenValue);

        Sanctum::actingAs($student);
        $this->getJson("/api/v1/student/activities/{$activity->id}")->assertStatus(403);
        $this->postJson("/api/v1/student/activities/{$activity->id}/submit", ['answers' => ['x']])->assertStatus(403);
    }

    public function test_api_activities_list_excludes_hidden_value_activity(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        $classroom = Classroom::factory()->create(['school_id' => $school->id]);
        DB::table('classroom_student')->insert(['classroom_id' => $classroom->id, 'student_id' => $student->id]);

        $hiddenValue = Value::create(['name' => 'مخفيّة', 'order' => 1, 'status' => 'active', 'created_by' => null]);
        $shownValue = Value::create(['name' => 'ظاهرة', 'order' => 2, 'status' => 'active', 'created_by' => null]);
        DB::table('school_active_values')->insert(['school_id' => $school->id, 'value_id' => $shownValue->id, 'activated_at' => now(), 'created_at' => now(), 'updated_at' => now()]);

        $concept = Concept::create(['value_id' => $hiddenValue->id, 'name' => 'مفهوم', 'order' => 1]);
        $lesson = Lesson::factory()->create(['concept_id' => $concept->id]);
        $activity = Activity::factory()->create(['lesson_id' => $lesson->id, 'classroom_id' => $classroom->id, 'approval_status' => 'approved', 'all_schools_mode' => 'direct', 'status' => 'active']);

        Sanctum::actingAs($student);
        $resp = $this->getJson('/api/v1/student/activities')->assertOk();
        $ids = collect($resp->json('data.activities'))->pluck('id')->all();
        $this->assertNotContains($activity->id, $ids, 'نشاط تحت قيمة مخفيّة يجب ألّا يظهر في قائمة الجوّال');
    }

    public function test_api_activity_details_allowed_when_value_visible(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        // بلا صفوف school_active_values ⇒ كل القيم النشطة مرئيّة
        $value = Value::create(['name' => 'قيمة', 'order' => 1, 'status' => 'active', 'created_by' => null]);
        $activity = $this->activityInValue($value);

        Sanctum::actingAs($student);
        $this->getJson("/api/v1/student/activities/{$activity->id}")->assertOk();
    }

    // ========================= D) رفض مدير المدرسة =========================

    public function test_school_admin_cannot_reject_after_admin_approval(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = User::factory()->teacher($schoolA)->create();
        $schoolAdmin = User::factory()->create(['role' => 'school_admin', 'school_id' => $schoolA->id]);

        // نشاط اعتمده الأدمن نهائيًّا ونشره لكل المدارس + صفّ لمدرسة أخرى
        $activity = Activity::factory()->create([
            'created_by' => $teacherA->id,
            'school_approval_status' => 'approved',
            'approval_status' => 'approved',
            'all_schools_mode' => 'direct',
        ]);
        $activity->schools()->attach($schoolB->id, ['publish_mode' => 'direct', 'published_at' => now()]);

        $this->actingAs($schoolAdmin)
            ->post(route('school-admin.activity-approvals.reject', $activity->id), ['rejection_reason' => 'محاولة'])
            ->assertForbidden();

        // النشر العالميّ ونشر المدرسة الأخرى سليمان
        $this->assertSame('direct', $activity->fresh()->all_schools_mode);
        $this->assertDatabaseHas('activity_school', ['activity_id' => $activity->id, 'school_id' => $schoolB->id]);
    }

    public function test_school_admin_reject_only_unpublishes_own_school(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $teacherA = User::factory()->teacher($schoolA)->create();
        $schoolAdmin = User::factory()->create(['role' => 'school_admin', 'school_id' => $schoolA->id]);

        // اعتمده مدير المدرسة (نُشِر لمدرسته)، ولم يعتمده الأدمن بعد (approval_status=pending)
        $activity = Activity::factory()->create([
            'created_by' => $teacherA->id,
            'school_approval_status' => 'approved',
            'approval_status' => 'pending',
            'all_schools_mode' => 'none',
        ]);
        $activity->schools()->attach($schoolA->id, ['publish_mode' => 'direct', 'published_at' => now()]);
        $activity->schools()->attach($schoolB->id, ['publish_mode' => 'direct', 'published_at' => now()]);

        $this->actingAs($schoolAdmin)
            ->post(route('school-admin.activity-approvals.reject', $activity->id), ['rejection_reason' => 'سبب'])
            ->assertRedirect();

        $this->assertSame('rejected', $activity->fresh()->school_approval_status);
        // صفّ مدرسته حُذِف، وصفّ المدرسة الأخرى بقي، وall_schools_mode لم يُمسّ
        $this->assertDatabaseMissing('activity_school', ['activity_id' => $activity->id, 'school_id' => $schoolA->id]);
        $this->assertDatabaseHas('activity_school', ['activity_id' => $activity->id, 'school_id' => $schoolB->id]);
        $this->assertSame('none', $activity->fresh()->all_schools_mode);
    }
}
