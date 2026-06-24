<?php

namespace Tests\Feature\Economy;

use App\Models\Classroom;
use App\Models\School;
use App\Models\User;
use App\Services\AwardService;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — regression for PointsService::getStudentLeaderboard.
 *
 * The leaderboard reads each student's REAL summed points from the append-only
 * `points` ledger (withSum('points','points') -> points_sum_points) and orders by
 * that sum. This pins:
 *   (1) it runs without a 500 and orders by the true summed points (not insertion
 *       order, not a stale users.total_points column),
 *   (2) points credited through the central AwardService::award primitive land in
 *       the `points` ledger and are reflected in the board's per-student total,
 *   (3) the school filter scopes correctly,
 *   (4) a duplicate (idempotent) award does not inflate a student's leaderboard total.
 */
class getStudentLeaderboardAwardTest extends TestCase
{
    use RefreshDatabase;

    public function test_leaderboard_orders_by_real_summed_points_without_error(): void
    {
        $school = School::factory()->create();

        $low = User::factory()->student($school)->create(['name' => 'Low']);
        $high = User::factory()->student($school)->create(['name' => 'High']);
        $mid = User::factory()->student($school)->create(['name' => 'Mid']);

        // Multiple ledger rows per student must SUM (not be read as a single value).
        AwardService::award($high->id, 'activity_submission', '1', 60);
        AwardService::award($high->id, 'practice_attempt', '2', 40); // High => 100
        AwardService::award($mid->id, 'activity_submission', '3', 50); // Mid  => 50
        AwardService::award($low->id, 'activity_submission', '4', 10); // Low  => 10

        $board = PointsService::getStudentLeaderboard();

        // Ordered by true summed points, descending — not 1-each, not insertion order.
        $this->assertSame([$high->id, $mid->id, $low->id], array_column($board, 'id'));
        $this->assertSame([100, 50, 10], array_column($board, 'points'));
        $this->assertSame([1, 2, 3], array_column($board, 'rank'));
    }

    public function test_school_filter_scopes_leaderboard(): void
    {
        $schoolA = School::factory()->create();
        $schoolB = School::factory()->create();

        $inA = User::factory()->student($schoolA)->create();
        $inB = User::factory()->student($schoolB)->create();

        AwardService::award($inA->id, 'activity_submission', '10', 30);
        AwardService::award($inB->id, 'activity_submission', '11', 90);

        $board = PointsService::getStudentLeaderboard(20, $schoolA->id);

        $this->assertSame([$inA->id], array_column($board, 'id'));
        $this->assertSame(30, $board[0]['points']);
    }

    public function test_classroom_filter_scopes_leaderboard(): void
    {
        $school = School::factory()->create();
        $teacher = User::factory()->teacher($school)->create();
        $classroom = Classroom::factory()->create([
            'teacher_id' => $teacher->id,
            'school_id' => $school->id,
        ]);

        $enrolled = User::factory()->student($school)->create();
        $outsider = User::factory()->student($school)->create();

        $enrolled->classrooms()->attach($classroom->id, ['status' => 'active', 'enrollment_date' => now()]);

        AwardService::award($enrolled->id, 'activity_submission', '20', 25);
        AwardService::award($outsider->id, 'activity_submission', '21', 80);

        $board = PointsService::getStudentLeaderboard(20, null, $classroom->id);

        $this->assertSame([$enrolled->id], array_column($board, 'id'));
        $this->assertSame(25, $board[0]['points']);
    }

    public function test_duplicate_award_does_not_inflate_leaderboard_total(): void
    {
        $student = User::factory()->student()->create();

        $this->assertTrue(AwardService::award($student->id, 'activity_submission', '99', 70));
        // Same idempotency key — must be a no-op, not a +70 again.
        $this->assertFalse(AwardService::award($student->id, 'activity_submission', '99', 70));

        $board = PointsService::getStudentLeaderboard();

        $row = collect($board)->firstWhere('id', $student->id);
        $this->assertNotNull($row);
        $this->assertSame(70, $row['points']);
    }

    public function test_student_with_no_points_appears_with_zero_not_crash(): void
    {
        $student = User::factory()->student()->create();

        $board = PointsService::getStudentLeaderboard();

        $row = collect($board)->firstWhere('id', $student->id);
        $this->assertNotNull($row);
        $this->assertSame(0, $row['points']);
    }
}
