<?php

namespace Tests\Feature\Demo;

use App\Models\School;
use App\Models\User;
use App\Services\AwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 5 — تحليلات الأدوار العابرة للمدارس (صدارة المعلّم للطلاب، رتب الوليّ) تستثني الديمو.
 */
class DemoRoleAnalyticsExclusionTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_student_leaderboard_country_excludes_demo(): void
    {
        $school = School::factory()->create(['country' => 'SA']);
        $teacher = User::factory()->teacher($school)->create();
        $real = User::factory()->student($school)->create(['name' => 'RealStu', 'status' => 'active']);
        $demo = User::factory()->student($school)->create(['name' => 'DemoStu', 'status' => 'active', 'is_demo' => true]);
        AwardService::award($real->id, 'activity_submission', 'r1', 300);
        AwardService::award($demo->id, 'activity_submission', 'd1', 9000);

        $res = $this->actingAs($teacher)
            ->get(route('teacher.leaderboard.students', ['scope' => 'country']))
            ->assertOk();

        $ids = collect($res->viewData('leaders')->items())->pluck('id')->all();
        $this->assertContains($real->id, $ids);
        $this->assertNotContains($demo->id, $ids, 'صدارة المعلّم للطلاب (دولة) تستثني الديمو');
    }

    public function test_parent_child_country_rank_ignores_demo_students(): void
    {
        $school = School::factory()->create(['country' => 'SA', 'city' => 'مكة']);
        $parent = User::factory()->parent($school)->create();
        $child = User::factory()->student($school)->create(['status' => 'active']);
        $parent->children()->attach($child->id);
        AwardService::award($child->id, 'activity_submission', 'c1', 100);

        // حساب ديمو بنقاط أعلى بكثير — يجب ألّا يزيح رتبة الابن الحقيقيّ
        $demo = User::factory()->student($school)->create(['status' => 'active', 'is_demo' => true]);
        AwardService::award($demo->id, 'activity_submission', 'd1', 9000);

        $res = $this->actingAs($parent)->get(route('parent.dashboard'))->assertOk();
        $children = collect($res->viewData('childrenData') ?? []);
        $row = $children->firstWhere('id', $child->id);
        $this->assertNotNull($row);
        $this->assertSame(1, (int) $row['country_rank'], 'الديمو لا يُحتسَب في مقام رتبة الدولة للابن');
    }
}
