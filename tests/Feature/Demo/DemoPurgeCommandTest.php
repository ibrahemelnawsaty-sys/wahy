<?php

namespace Tests\Feature\Demo;

use App\Models\ActivitySubmission;
use App\Models\Activity;
use App\Models\School;
use App\Models\User;
use App\Services\AwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * الدفعة 8 — أمر demo:purge يحذف حسابات الديمو وبياناتها ولا يمسّ الحقيقيّين.
 */
class DemoPurgeCommandTest extends TestCase
{
    use RefreshDatabase;

    public function test_purge_removes_demo_accounts_and_data_only(): void
    {
        $school = School::factory()->create();
        $real = User::factory()->student($school)->create();
        $demo = User::factory()->student($school)->create(['is_demo' => true]);
        $activity = Activity::factory()->create();

        AwardService::award($real->id, 'activity_submission', 'r1', 100);
        AwardService::award($demo->id, 'activity_submission', 'd1', 500);
        ActivitySubmission::create(['student_id' => $real->id, 'activity_id' => $activity->id, 'status' => 'completed', 'submitted_at' => now()]);
        ActivitySubmission::create(['student_id' => $demo->id, 'activity_id' => $activity->id, 'status' => 'completed', 'submitted_at' => now()]);

        Artisan::call('demo:purge', ['--force' => true]);

        // حساب الديمو وبياناته حُذفت
        $this->assertDatabaseMissing('users', ['id' => $demo->id]);
        $this->assertSame(0, DB::table('points')->where('user_id', $demo->id)->count());
        $this->assertSame(0, ActivitySubmission::where('student_id', $demo->id)->count());

        // الحقيقيّ وبياناته سليمة
        $this->assertDatabaseHas('users', ['id' => $real->id]);
        $this->assertSame(100, (int) DB::table('points')->where('user_id', $real->id)->sum('points'));
        $this->assertSame(1, ActivitySubmission::where('student_id', $real->id)->count());
    }

    public function test_purge_schools_flag_removes_demo_schools(): void
    {
        $demoSchool = School::factory()->create(['is_demo' => true]);
        $realSchool = School::factory()->create(['is_demo' => false]);
        // مستخدم الديمو تحت المدرسة (يرث العلم) — يُحذف قبل المدرسة
        User::factory()->create(['school_id' => $demoSchool->id]);

        Artisan::call('demo:purge', ['--force' => true, '--schools' => true]);

        $this->assertDatabaseMissing('schools', ['id' => $demoSchool->id]);
        $this->assertDatabaseHas('schools', ['id' => $realSchool->id]);
    }
}
