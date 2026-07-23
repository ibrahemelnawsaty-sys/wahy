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
}
