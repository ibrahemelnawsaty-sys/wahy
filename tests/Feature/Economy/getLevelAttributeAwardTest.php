<?php

namespace Tests\Feature\Economy;

use App\Models\School;
use App\Models\User;
use App\Services\AwardService;
use App\Services\PointsService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — regression for User::getLevelAttribute() (app/Models/User.php).
 *
 * The bug: level used to read the dead users.total_points column (always 0), so
 * every student reported level 1 regardless of earned points. The fix derives the
 * level from the REAL summed points ledger via SUM(points), giving floor(N/100)+1.
 *
 * getLevelAttribute() has three SUM sources, all of which must agree. This test
 * pins each one, plus the wired leaderboard path that orders by the same
 * withSum('points','points') => points_sum_points alias. Points are credited
 * through AwardService::award (the mandated primitive) so the ledger rows are
 * authentic, not hand-inserted.
 */
class getLevelAttributeAwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Path C: cold model (no preloaded alias, no loaded relation) — falls through
     * to the DB query branch $this->points()->sum('points').
     */
    public function test_level_for_cold_model_uses_summed_points_not_one(): void
    {
        $student = User::factory()->student()->create();

        // 250 real points across two award events => level floor(250/100)+1 = 3.
        AwardService::award($student->id, 'activity_submission', '1', 150, 0, 'a');
        AwardService::award($student->id, 'activity_submission', '2', 100, 0, 'b');

        // Re-fetch fresh so neither the alias nor the relation is loaded.
        $fresh = User::find($student->id);

        $this->assertSame(250, (int) $fresh->points()->sum('points'));
        $this->assertSame(3, $fresh->level, 'cold model must compute level from summed points, not the dead total_points column');
        $this->assertNotSame(1, $fresh->level);
    }

    /**
     * Path B: relation already loaded — level reads $this->points->sum('points')
     * without issuing another query.
     */
    public function test_level_from_loaded_relation(): void
    {
        $student = User::factory()->student()->create();

        AwardService::award($student->id, 'activity_submission', '1', 100, 0, 'a');
        AwardService::award($student->id, 'activity_submission', '2', 50, 0, 'b');

        $loaded = User::with('points')->find($student->id);
        $this->assertTrue($loaded->relationLoaded('points'));

        // 150 points => floor(150/100)+1 = 2.
        $this->assertSame(2, $loaded->level);
    }

    /**
     * Path A: the preloaded withSum alias (points_sum_points) is preferred — this is
     * the hot leaderboard path that must not trigger N+1. A student with 0 points
     * stays at level 1 (the boundary), proving the accessor is not hardcoded.
     */
    public function test_level_from_preloaded_withsum_alias(): void
    {
        $student = User::factory()->student()->create();
        AwardService::award($student->id, 'activity_submission', '1', 320, 0, 'a');

        $withAlias = User::whereKey($student->id)->withSum('points', 'points')->first();
        $this->assertSame(320, (int) $withAlias->getAttribute('points_sum_points'));
        // floor(320/100)+1 = 4.
        $this->assertSame(4, $withAlias->level);

        $zero = User::factory()->student()->create();
        $zeroWithAlias = User::whereKey($zero->id)->withSum('points', 'points')->first();
        $this->assertSame(1, $zeroWithAlias->level, 'a student with no points is level 1');
    }

    /**
     * The wired leaderboard orders by the same summed-points alias and returns the
     * real totals — proving the ordering does not 500 and is by true points, not by
     * the dead total_points column (which would tie everyone at 0).
     */
    public function test_leaderboard_orders_by_real_summed_points(): void
    {
        $school = School::factory()->create();

        $low = User::factory()->student($school)->create(['name' => 'Low']);
        $mid = User::factory()->student($school)->create(['name' => 'Mid']);
        $high = User::factory()->student($school)->create(['name' => 'High']);

        AwardService::award($low->id, 'activity_submission', '1', 50, 0, 'a');
        AwardService::award($mid->id, 'activity_submission', '1', 250, 0, 'a');
        AwardService::award($high->id, 'activity_submission', '1', 900, 0, 'a');

        $board = PointsService::getStudentLeaderboard(20, $school->id);

        $this->assertCount(3, $board);
        $this->assertSame('High', $board[0]['name']);
        $this->assertSame(900, $board[0]['points']);
        $this->assertSame('Mid', $board[1]['name']);
        $this->assertSame(250, $board[1]['points']);
        $this->assertSame('Low', $board[2]['name']);
        $this->assertSame(50, $board[2]['points']);

        // And the level accessor agrees on each leaderboard row.
        $this->assertSame(10, $high->fresh()->level); // floor(900/100)+1
        $this->assertSame(3, $mid->fresh()->level);    // floor(250/100)+1
        $this->assertSame(1, $low->fresh()->level);    // floor(50/100)+1
    }
}
