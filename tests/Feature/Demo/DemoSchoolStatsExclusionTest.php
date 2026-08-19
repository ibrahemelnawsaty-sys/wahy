<?php

namespace Tests\Feature\Demo;

use App\Models\Classroom;
use App\Models\School;
use App\Models\SchoolPoint;
use App\Models\TeacherPoint;
use App\Models\User;
use App\Services\AwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * الدفعة 2 — إحصاءات المدرسة + حارس الكتابة على العدّاد الحيّ.
 */
class DemoSchoolStatsExclusionTest extends TestCase
{
    use RefreshDatabase;

    public function test_school_counter_write_guard_skips_demo_points(): void
    {
        $school = School::factory()->create(['total_points' => 0]);
        $real = User::factory()->student($school)->create();
        $demo = User::factory()->student($school)->create(['is_demo' => true]);

        SchoolPoint::addPoints($school->id, 100, 'activity', null, $real->id); // يُحتسَب
        SchoolPoint::addPoints($school->id, 500, 'activity', null, $demo->id); // يُتخطّى (ديمو)
        SchoolPoint::addPoints($school->id, 30, 'system', null, null);         // نظام (يُحتسَب)

        $this->assertSame(130, (int) $school->fresh()->total_points, 'العدّاد الحيّ يستثني نقاط الديمو ويُبقي النظام');
        $this->assertSame(130, SchoolPoint::getTotalPoints($school->id), 'المجموع يستثني صفوف الديمو ويُبقي النظام');
    }

    public function test_refresh_statistics_excludes_demo_points(): void
    {
        $school = School::factory()->create(['status' => 'active', 'city' => 'مكة']);
        $real = User::factory()->student($school)->create();
        $demo = User::factory()->student($school)->create(['is_demo' => true]);
        AwardService::award($real->id, 'activity_submission', 'r1', 200);
        AwardService::award($demo->id, 'activity_submission', 'd1', 800);

        Artisan::call('schools:refresh-stats');

        $cached = DB::table('school_statistics_cache')
            ->where('entity_type', 'school')->where('entity_id', $school->id)->first();
        $this->assertNotNull($cached);
        $this->assertSame(200, (int) $cached->total_points, 'اللقطة تستثني نقاط طلاب الديمو');
    }

    public function test_teacher_points_exclude_demo_students(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $classroom = Classroom::factory()->create(['school_id' => $school->id, 'teacher_id' => $teacher->id]);
        $real = User::factory()->student($school)->create();
        $demo = User::factory()->student($school)->create(['is_demo' => true]);
        $real->classrooms()->attach($classroom->id, ['status' => 'active', 'enrollment_date' => now()]);
        $demo->classrooms()->attach($classroom->id, ['status' => 'active', 'enrollment_date' => now()]);

        AwardService::award($real->id, 'activity_submission', 'r1', 300);
        AwardService::award($demo->id, 'activity_submission', 'd1', 900);

        $tp = TeacherPoint::updateTeacherPoints($teacher->id);

        // نقاط طلاب المعلّم = نقاط الطالب الحقيقيّ فقط (300)، لا 1200
        $this->assertSame(300, (int) $tp->students_total_points, 'معلّم حقيقيّ لا يُحتسَب له طلاب الديمو');
        $this->assertSame(1, (int) $tp->students_count);
    }
}
