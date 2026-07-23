<?php

namespace Tests\Feature\Activities;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\Concept;
use App\Models\Lesson;
use App\Models\School;
use App\Models\User;
use App\Models\Value;
use App\Services\ActivityPublishingService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

/**
 * إصلاحات مراجعة نشر الأدمن الخصميّة:
 *  B) نشاط ينشئه الأدمن من «إدارة الأنشطة» يُولَد مرئيّاً (all_schools_mode='direct') لا مخفيّاً.
 *  C) adminApprove يُصالِح صفوف activity_school (sync للمحدّد يفصل المُزال، detach لـ'all').
 *  A) قائمة أنشطة الجوّال تُدرِج المنشور مباشرةً لكل المدارس (لا تُسقِطه بفلتر classroom_id).
 */
class AdminPublishingFixesTest extends TestCase
{
    use RefreshDatabase;

    /** درسٌ بقيمة حقيقيّة — المدرسة بلا تخصيص school_active_values ترى كل القيم افتراضياً. */
    private function lessonWithValue(): Lesson
    {
        $value = Value::factory()->create();
        $concept = Concept::factory()->create(['value_id' => $value->id]);

        return Lesson::factory()->create(['concept_id' => $concept->id]);
    }

    // ================= B: نشاط الأدمن يُولَد مرئيّاً =================

    public function test_admin_created_lesson_activity_is_visible_to_students(): void
    {
        $lesson = $this->lessonWithValue();
        $admin = User::factory()->create(['role' => 'super_admin']);
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();

        $this->actingAs($admin)->post(route('admin.activities.store'), [
            'lesson_id' => $lesson->id,
            'title' => 'نشاط أدمن منهجيّ',
            'type' => 'quiz',
            'points' => 10,
            'status' => 'active',
        ])->assertRedirect();

        $activity = Activity::where('title', 'نشاط أدمن منهجيّ')->firstOrFail();

        $this->assertSame('direct', $activity->all_schools_mode, 'يُنشَر مباشرةً لا none');
        $this->assertTrue($activity->isAccessibleByStudent($student), 'الطالب يستطيع الوصول');
        $this->assertTrue(
            Activity::visibleToStudent($student->school_id, [])->where('id', $activity->id)->exists(),
            'يظهر في استعلام رؤية الطالب'
        );
    }

    // ================= C: مصالحة صفوف activity_school =================

    public function test_narrowing_specific_scope_detaches_removed_schools(): void
    {
        $svc = app(ActivityPublishingService::class);
        $admin = User::factory()->create(['role' => 'super_admin']);
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();
        $activity = Activity::factory()->create(['approval_status' => 'pending', 'all_schools_mode' => 'none']);

        // نشر لـ[A, B] ثمّ تضييق لـ[A] فقط
        $svc->adminApprove($activity, 'specific', 'direct', [$schoolA->id, $schoolB->id], $admin->id);
        $this->assertTrue($activity->isDirectToSchool($schoolB->id), 'B منشورة ابتداءً');

        $svc->adminApprove($activity->fresh(), 'specific', 'direct', [$schoolA->id], $admin->id);

        $this->assertTrue($activity->fresh()->isDirectToSchool($schoolA->id), 'A تبقى');
        $this->assertFalse($activity->fresh()->isDirectToSchool($schoolB->id), 'B فُصِلت (لا تسريب)');
    }

    public function test_scope_all_detaches_directed_pivot_rows(): void
    {
        $svc = app(ActivityPublishingService::class);
        $admin = User::factory()->create(['role' => 'super_admin']);
        $schoolA = School::factory()->create();
        $activity = Activity::factory()->create(['approval_status' => 'pending', 'all_schools_mode' => 'none']);

        // المرحلة 1: مدير مدرسة A ينشر direct لمدرسته (صفّ pivot)
        $svc->schoolApprove($activity, $schoolA->id, 'direct', $admin->id);
        $this->assertSame(1, $activity->fresh()->schools()->count());

        // الأدمن ينشر «للكل» بوضع bank → يجب أن يُفرَّغ صفّ direct العالق (وإلا رأى طلاب A مباشرةً)
        $svc->adminApprove($activity->fresh(), 'all', 'bank', [], $admin->id);

        $this->assertSame('bank', $activity->fresh()->all_schools_mode);
        $this->assertSame(0, $activity->fresh()->schools()->count(), 'الصفوف الموجَّهة فُرِّغت');
        $this->assertFalse($activity->fresh()->isDirectToSchool($schoolA->id), 'لا رؤية مباشرة متبقّية');
    }

    // ================= A: الجوّال يُدرِج المنشور مباشرةً =================

    public function test_mobile_api_lists_direct_to_all_activity_regardless_of_classroom(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();
        // الطالب في فصلٍ ما (فصل مختلف عن أيّ classroom_id للنشاط)
        $classroom = Classroom::factory()->create(['school_id' => $school->id]);
        $student->classrooms()->attach($classroom->id);

        $lesson = $this->lessonWithValue();
        // نشاط منشور مباشرةً لكل المدارس، classroom_id=null (كان يُسقَط بفلتر whereIn القديم)
        $activity = Activity::factory()->create([
            'lesson_id' => $lesson->id,
            'status' => 'active',
            'all_schools_mode' => 'direct',
            'classroom_id' => null,
        ]);

        Sanctum::actingAs($student);
        $res = $this->getJson('/api/v1/student/activities')->assertOk();

        $ids = collect($res->json('data.activities'))->pluck('id')->all();
        $this->assertContains($activity->id, $ids, 'المنشور مباشرةً لكل المدارس يظهر في الجوّال');
    }

    public function test_mobile_api_does_not_crash_on_direct_activity_without_lesson(): void
    {
        $school = School::factory()->create();
        $student = User::factory()->student($school)->create();

        // نشاط منشور مباشرةً بلا درس — بعد إزالة فلتر الفصل قد يظهر؛ يجب ألّا يُعطِب الخريطة
        Activity::factory()->create([
            'lesson_id' => null,
            'status' => 'active',
            'all_schools_mode' => 'direct',
        ]);

        Sanctum::actingAs($student);
        $this->getJson('/api/v1/student/activities')->assertOk();
    }

    // ========== #1: شاشة البنك تحترم وضع النشر المختار (كان مُثبَّتاً direct) ==========

    private function bankPending(): Activity
    {
        $teacher = User::factory()->teacher(School::factory()->create())->create();

        return Activity::factory()->create([
            'created_by' => $teacher->id,
            'is_activity_bank' => true,
            'approval_status' => 'pending',
            'school_approval_status' => 'approved',
            'all_schools_mode' => 'none',
        ]);
    }

    public function test_bank_approve_defaults_to_bank_mode(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $activity = $this->bankPending();

        $this->actingAs($admin)
            ->postJson("/admin/activity-bank/{$activity->id}/approve-activity", [])
            ->assertOk();

        // الافتراض «بنك» (لا يظهر مباشرةً للطلاب) — كان يُثبِّت direct
        $this->assertSame('bank', $activity->fresh()->all_schools_mode);
    }

    public function test_bank_approve_honors_direct_mode(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $activity = $this->bankPending();

        $this->actingAs($admin)
            ->postJson("/admin/activity-bank/{$activity->id}/approve-activity", ['publish_mode' => 'direct'])
            ->assertOk();

        $this->assertSame('direct', $activity->fresh()->all_schools_mode);
    }

    // ========== #2: الاعتماد المجمّع يعمل من الواجهة ==========

    public function test_bulk_approve_publishes_selected_activities(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $teacher = User::factory()->teacher(School::factory()->create())->create();
        $make = fn () => Activity::factory()->create([
            'created_by' => $teacher->id,
            'approval_status' => 'pending',
            'school_approval_status' => 'approved',
            'all_schools_mode' => 'none',
        ]);
        $a = $make();
        $b = $make();

        $this->actingAs($admin)->post(route('admin.activity-approval.bulk-approve'), [
            'activity_ids' => [$a->id, $b->id],
            'publish_mode' => 'direct',
        ])->assertRedirect();

        $this->assertSame('approved', $a->fresh()->approval_status);
        $this->assertSame('direct', $a->fresh()->all_schools_mode);
        $this->assertSame('direct', $b->fresh()->all_schools_mode);
    }

    // ========== إشعار «نشاط جديد» يحترم بوّابة القيمة ==========

    /** يبني نشاط درسٍ لفصلٍ فيه طالب، تحت القيمة $value، معلّق لاعتماد الأدمن. */
    private function classroomActivityUnderValue(School $school, \App\Models\Value $value): array
    {
        $concept = Concept::factory()->create(['value_id' => $value->id]);
        $lesson = Lesson::factory()->create(['concept_id' => $concept->id]);
        $classroom = Classroom::factory()->create(['school_id' => $school->id]);
        $student = User::factory()->student($school)->create();
        $student->classrooms()->attach($classroom->id);
        $activity = Activity::factory()->create([
            'lesson_id' => $lesson->id,
            'classroom_id' => $classroom->id,
            'status' => 'active',
            'approval_status' => 'pending',
            'school_approval_status' => 'approved',
            'all_schools_mode' => 'none',
        ]);

        return [$student, $activity];
    }

    public function test_new_activity_notification_skips_students_with_hidden_value(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $school = School::factory()->create();
        $value = Value::factory()->create(['status' => 'active']);
        $otherValue = Value::factory()->create(['status' => 'active']);
        // المدرسة خصّصت قيمها لتشمل قيمةً أخرى فقط → قيمة النشاط مُخفاة عنها
        \Illuminate\Support\Facades\DB::table('school_active_values')->insert([
            'school_id' => $school->id,
            'value_id' => $otherValue->id,
        ]);

        [$student, $activity] = $this->classroomActivityUnderValue($school, $value);

        $this->actingAs($admin)->post(route('admin.activity-approval.approve', $activity), [
            'scope' => 'all',
            'publish_mode' => 'direct',
        ])->assertRedirect();

        $this->assertDatabaseMissing('notifications', [
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $student->id,
            'type' => 'new_activity',
        ]);
    }

    public function test_new_activity_notification_sent_when_value_visible(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $school = School::factory()->create(); // بلا تخصيص → كل القيم مرئيّة
        $value = Value::factory()->create(['status' => 'active']);

        [$student, $activity] = $this->classroomActivityUnderValue($school, $value);

        $this->actingAs($admin)->post(route('admin.activity-approval.approve', $activity), [
            'scope' => 'all',
            'publish_mode' => 'direct',
        ])->assertRedirect();

        $this->assertDatabaseHas('notifications', [
            'notifiable_type' => 'App\\Models\\User',
            'notifiable_id' => $student->id,
            'type' => 'new_activity',
        ]);
    }
}
