<?php

namespace Tests\Feature\Demo;

use App\Models\School;
use App\Models\User;
use App\Services\AwardService;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 1 — استثناء الديمو من الصدارة. اختبار الثبات: بلا حساب ديمو الأرقام مطابقة تماماً؛
 * وبتعليم حساب ديمو يختفي هو فقط دون أن تتزحزح رتب الحقيقيّين.
 */
class DemoLeaderboardExclusionTest extends TestCase
{
    use RefreshDatabase;

    public function test_zero_demo_leaderboard_is_unchanged(): void
    {
        $school = School::factory()->create();
        $a = User::factory()->student($school)->create(['name' => 'A']);
        $b = User::factory()->student($school)->create(['name' => 'B']);
        $c = User::factory()->student($school)->create(['name' => 'C']);
        AwardService::award($a->id, 'activity_submission', 'k1', 300);
        AwardService::award($b->id, 'activity_submission', 'k2', 200);
        AwardService::award($c->id, 'activity_submission', 'k3', 100);

        $board = PointsService::getStudentLeaderboard(20, $school->id);

        // بلا أيّ ديمو: الترتيب والنقاط كما هي تماماً (الفلتر لا يُنقص شيئاً)
        $this->assertSame([$a->id, $b->id, $c->id], array_column($board, 'id'));
        $this->assertSame([300, 200, 100], array_column($board, 'points'));
    }

    public function test_demo_student_excluded_and_real_ranks_preserved(): void
    {
        $school = School::factory()->create();
        $a = User::factory()->student($school)->create(['name' => 'A']);
        $b = User::factory()->student($school)->create(['name' => 'B']);
        $c = User::factory()->student($school)->create(['name' => 'C']);
        // حساب ديمو بأعلى نقاط — يجب ألّا يظهر ولا يزيح أحداً
        $demo = User::factory()->student($school)->create(['name' => 'DEMO', 'is_demo' => true]);

        AwardService::award($a->id, 'activity_submission', 'k1', 300);
        AwardService::award($b->id, 'activity_submission', 'k2', 200);
        AwardService::award($c->id, 'activity_submission', 'k3', 100);
        AwardService::award($demo->id, 'activity_submission', 'k4', 9999);

        $board = PointsService::getStudentLeaderboard(20, $school->id);

        $ids = array_column($board, 'id');
        $this->assertNotContains($demo->id, $ids, 'حساب الديمو غائب عن الصدارة');
        // رتب الحقيقيّين مطابقة لسيناريو بلا ديمو (لم تتزحزح)
        $this->assertSame([$a->id, $b->id, $c->id], $ids);
        $this->assertSame([300, 200, 100], array_column($board, 'points'));
    }

    public function test_school_board_http_excludes_demo_points_from_school_total(): void
    {
        $school = School::factory()->create();
        $real = User::factory()->student($school)->create();
        $demo = User::factory()->student($school)->create(['is_demo' => true]);
        AwardService::award($real->id, 'activity_submission', 'r1', 200);
        AwardService::award($demo->id, 'activity_submission', 'd1', 1000);

        $viewer = User::factory()->student($school)->create();
        $res = $this->actingAs($viewer)->getJson(route('leaderboard.schools'))->assertOk();

        $row = collect($res->json('leaderboard'))->firstWhere('id', $school->id);
        $this->assertNotNull($row);
        // مجموع المدرسة = نقاط الطالب الحقيقيّ فقط (200)، لا 1200
        $this->assertSame(200, (int) $row['points'], 'نقاط طالب الديمو لا تُضخّم مجموع المدرسة');
    }

    public function test_student_board_http_hides_demo(): void
    {
        $school = School::factory()->create();
        $real = User::factory()->student($school)->create(['name' => 'RealTop']);
        $demo = User::factory()->student($school)->create(['name' => 'DemoTop', 'is_demo' => true]);
        AwardService::award($real->id, 'activity_submission', 'r1', 500);
        AwardService::award($demo->id, 'activity_submission', 'd1', 9000);

        $res = $this->actingAs($real)->getJson(route('leaderboard.students', ['scope' => 'school']))->assertOk();

        $ids = collect($res->json('leaderboard'))->pluck('id')->all();
        $this->assertContains($real->id, $ids);
        $this->assertNotContains($demo->id, $ids);
    }
}
