<?php

namespace Tests\Feature\Demo;

use App\Models\Activity;
use App\Models\ActivitySubmission;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * الدفعة 3 — لوحات المنصّة + شارات الرأس + التحديث الحيّ تستثني حسابات الديمو.
 */
class DemoDashboardExclusionTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_stats_exclude_demo_students(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $school = School::factory()->create();
        User::factory()->count(2)->create(['role' => 'student', 'school_id' => $school->id]);
        User::factory()->create(['role' => 'student', 'school_id' => $school->id, 'is_demo' => true]);

        $res = $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        $stats = $res->viewData('stats');
        $this->assertSame(2, $stats['total_students'], 'إجمالي الطلاب يستثني حساب الديمو');
    }

    public function test_live_summary_admin_pending_excludes_demo(): void
    {
        $admin = User::factory()->create(['role' => 'super_admin']);
        $real = User::factory()->create(['role' => 'student']);
        $demo = User::factory()->create(['role' => 'student', 'is_demo' => true]);
        $activity = Activity::factory()->create();

        ActivitySubmission::create(['student_id' => $real->id, 'activity_id' => $activity->id, 'status' => 'pending', 'submitted_at' => now()]);
        ActivitySubmission::create(['student_id' => $demo->id, 'activity_id' => $activity->id, 'status' => 'pending', 'submitted_at' => now()]);

        $res = $this->actingAs($admin)->getJson(route('live.summary'))->assertOk();

        // شارة التحديث الحيّ (تطابق شارة الرأس) تستثني تسليم الديمو
        $this->assertSame(1, (int) $res->json('counts.admin_pending_submissions'));
    }
}
