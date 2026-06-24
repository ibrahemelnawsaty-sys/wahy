<?php

namespace Tests\Feature\Economy;

use App\Models\Classroom;
use App\Models\Coin;
use App\Models\ParentPoint;
use App\Models\Point;
use App\Models\School;
use App\Models\SchoolPoint;
use App\Models\TeacherPoint;
use App\Models\User;
use App\Services\AwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — the central award primitive. Pins all three correctness
 * requirements: (1) a duplicate award is a TRUE no-op (ledger=1, Point/Coin and
 * total_points unchanged), (2) it is credit-only (no negative write), (3) gated
 * distribution fans out exactly once and is idempotent on replay.
 *
 * Honest race note: PHPUnit cannot reproduce two physically-simultaneous awards.
 * These tests prove LOGICAL idempotency and the no-op path. The actual race is
 * closed at the DB layer by UNIQUE(user_id, source_type, source_id) on
 * award_ledger — insertOrIgnore makes a concurrent duplicate fail to claim the row,
 * so the second writer short-circuits exactly as the second call does here.
 */
class AwardServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_award_credits_points_and_coins_once(): void
    {
        $student = User::factory()->student()->create();

        $this->assertTrue(AwardService::award($student->id, 'activity_submission', '42', 50, 10, 'test'));

        $this->assertSame(1, DB::table('award_ledger')->count());
        $this->assertSame(50, (int) Point::where('user_id', $student->id)->sum('points'));
        $this->assertSame(10, (int) Coin::where('user_id', $student->id)->sum('coins'));
    }

    public function test_duplicate_award_is_a_true_no_op(): void
    {
        $student = User::factory()->student()->create();
        AwardService::award($student->id, 'activity_submission', '42', 50, 10, 'first');

        $pointsRows = Point::where('user_id', $student->id)->count();
        $coinsRows = Coin::where('user_id', $student->id)->count();
        $totalBefore = (int) DB::table('users')->where('id', $student->id)->value('total_points');

        // Second identical call — must be a no-op on ALL THREE.
        $this->assertFalse(AwardService::award($student->id, 'activity_submission', '42', 50, 10, 'retry'));

        $this->assertSame(1, DB::table('award_ledger')
            ->where(['user_id' => $student->id, 'source_type' => 'activity_submission', 'source_id' => '42'])->count());
        $this->assertSame($pointsRows, Point::where('user_id', $student->id)->count(), 'Point rows must be unchanged');
        $this->assertSame($coinsRows, Coin::where('user_id', $student->id)->count(), 'Coin rows must be unchanged');
        $this->assertSame($totalBefore, (int) DB::table('users')->where('id', $student->id)->value('total_points'), 'total_points must be unchanged');
    }

    public function test_award_is_credit_only_and_writes_nothing_for_non_positive(): void
    {
        $student = User::factory()->student()->create();

        $this->assertFalse(AwardService::award($student->id, 'x', '1', 0, 0));
        $this->assertFalse(AwardService::award($student->id, 'x', '2', -5, -5));

        $this->assertSame(0, DB::table('award_ledger')->count());
        $this->assertSame(0, Point::where('user_id', $student->id)->count());
        $this->assertSame(0, Coin::where('user_id', $student->id)->count());
    }

    public function test_distribute_fans_out_once_and_is_idempotent(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $parent = User::factory()->parent($school)->create();
        $student = User::factory()->student($school)->create();

        $classroom = Classroom::factory()->create(['teacher_id' => $teacher->id, 'school_id' => $school->id]);
        $student->classrooms()->attach($classroom->id, ['status' => 'active', 'enrollment_date' => now()]);
        DB::table('parent_student')->insert(['parent_id' => $parent->id, 'student_id' => $student->id]);

        AwardService::award($student->id, 'activity_submission', '7', 100, 20, 'نشاط', distribute: true);

        $this->assertSame(100, (int) Point::where('user_id', $student->id)->sum('points'));
        $this->assertSame(10, (int) TeacherPoint::where('teacher_id', $teacher->id)->sum('points')); // 10% of 100
        $this->assertSame(5, (int) ParentPoint::where('parent_id', $parent->id)->sum('points'));     // 5% of 100
        $this->assertSame(2, (int) SchoolPoint::where('school_id', $school->id)->sum('points'));      // 2% of 100

        // Replay the same event — the whole fan-out must NOT run again.
        $this->assertFalse(AwardService::award($student->id, 'activity_submission', '7', 100, 20, 'نشاط', distribute: true));

        $this->assertSame(100, (int) Point::where('user_id', $student->id)->sum('points'));
        $this->assertSame(10, (int) TeacherPoint::where('teacher_id', $teacher->id)->sum('points'));
        $this->assertSame(5, (int) ParentPoint::where('parent_id', $parent->id)->sum('points'));
        $this->assertSame(2, (int) SchoolPoint::where('school_id', $school->id)->sum('points'));
    }
}
