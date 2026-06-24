<?php

namespace Tests\Feature\Economy;

use App\Models\Activity;
use App\Models\Classroom;
use App\Models\School;
use App\Models\Team;
use App\Models\TeamActivity;
use App\Models\User;
use App\Services\AwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — economy regression for TeacherController::gradeTeamActivity.
 *
 * Wiring under test (TeacherController::gradeTeamActivity):
 *   - On grade it marks the TeamActivity status='completed' (domain write) then, per
 *     team member, credits floor(score/2) points + floor(score/4) coins through the
 *     central AwardService::award primitive.
 *   - Idempotency key per member = (member_id, 'team_activity', "{teamActivity_id}:{member_id}").
 *
 * CONSISTENCY BOUNDARY: the domain write (status='completed') commits separately from
 * each per-member AwardService::award transaction. This site is (b) SEPARATE BUT
 * RETRY-IDEMPOTENT END-TO-END: the per-member ledger key makes each member's credit
 * exactly-once, so a crash mid-loop (some members awarded, some not) self-heals on a
 * direct re-invocation of the award loop — already-awarded members short-circuit on the
 * award_ledger UNIQUE row, un-awarded members get credited, never a double-award. The
 * HTTP layer adds a belt-and-suspenders status='completed' -> 409 guard against a naive
 * re-POST.
 *
 * The three mandated guarantees:
 *  (a) IDEMPOTENCY     — replaying the SAME event (same TeamActivity, same members) does
 *                        NOT add an award_ledger row, a Point row, a Coin row, or change a
 *                        balance; the HTTP re-POST is rejected 409.
 *  (b) LEGITIMATE-REPEAT — a genuinely DIFFERENT event (a NEW TeamActivity id for the same
 *                        members) DOES award again (proves the key is not too coarse).
 *  (c) BALANCE-FLOOR   — a zero-yield grade writes nothing negative/garbage: no ledger row,
 *                        no Point/Coin row, balances stay at 0 (never below).
 */
class gradeTeamActivityAwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a graded-ready TeamActivity: a teacher owns the Activity, a Team in the
     * teacher's school holds $memberCount student members, and a TeamActivity links them.
     *
     * @return array{0: User, 1: TeamActivity, 2: \Illuminate\Support\Collection<int, User>}
     */
    private function makeTeamActivity(School $school, int $memberCount = 2): array
    {
        $teacher = User::factory()->teacher($school)->create();

        $classroom = Classroom::factory()->create([
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
        ]);

        $team = Team::create([
            'classroom_id' => $classroom->id,
            'name' => 'فريق النجوم',
            'created_by' => $teacher->id,
            'status' => 'active',
        ]);

        $members = collect();
        for ($i = 0; $i < $memberCount; $i++) {
            $member = User::factory()->student($school)->create();
            $team->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);
            $members->push($member);
        }

        // created_by must equal the teacher: gradeTeamActivity authorizes on activity->created_by.
        $activity = Activity::factory()->create(['created_by' => $teacher->id]);

        $teamActivity = TeamActivity::create([
            'team_id' => $team->id,
            'activity_id' => $activity->id,
            'assigned_by' => $teacher->id,
            'status' => 'assigned',
        ]);

        return [$teacher, $teamActivity, $members];
    }

    /**
     * (a) IDEMPOTENCY — replaying the SAME grade event is a true no-op on the economy.
     */
    public function test_replaying_the_same_team_grade_does_not_double_award(): void
    {
        $school = School::factory()->create();
        [$teacher, $teamActivity, $members] = $this->makeTeamActivity($school, 2);

        $score = 80; // floor(80/2)=40 points, floor(80/4)=20 coins per member
        $expectedPoints = 40;
        $expectedCoins = 20;

        // First grade — through the real wired HTTP endpoint.
        $first = $this->actingAs($teacher)
            ->postJson("/teacher/teams/activities/{$teamActivity->id}/grade", [
                'total_score' => $score,
                'teacher_feedback' => 'عمل ممتاز',
            ]);
        $first->assertOk();

        // Each member was credited exactly once.
        foreach ($members as $member) {
            $this->assertSame($expectedPoints, (int) $member->totalPoints());
            $this->assertSame($expectedCoins, (int) $member->totalCoins());
        }

        $ledgerAfterFirst = DB::table('award_ledger')->where('source_type', 'team_activity')->count();
        $pointRowsAfterFirst = DB::table('points')->count();
        $coinRowsAfterFirst = DB::table('coins')->count();
        $this->assertSame($members->count(), $ledgerAfterFirst);

        // HTTP-level guard: a naive re-POST of the same activity is rejected 409 and
        // touches nothing.
        $teamActivity->refresh();
        $this->assertSame('completed', $teamActivity->status);
        $replay = $this->actingAs($teacher)
            ->postJson("/teacher/teams/activities/{$teamActivity->id}/grade", [
                'total_score' => $score,
                'teacher_feedback' => 'إعادة',
            ]);
        $replay->assertStatus(409);

        // Ledger-level guard: even calling the SAME award keys directly again (simulating a
        // retried/replayed award loop) must short-circuit on the UNIQUE row — no new ledger
        // row, no new Point/Coin, no balance change.
        foreach ($members as $member) {
            $newlyAwarded = AwardService::award(
                $member->id,
                'team_activity',
                $teamActivity->id . ':' . $member->id,
                $expectedPoints,
                $expectedCoins,
                'replay',
            );
            $this->assertFalse($newlyAwarded, 'Replayed award must be an idempotent no-op');
        }

        $this->assertSame($ledgerAfterFirst, DB::table('award_ledger')->where('source_type', 'team_activity')->count());
        $this->assertSame($pointRowsAfterFirst, DB::table('points')->count());
        $this->assertSame($coinRowsAfterFirst, DB::table('coins')->count());

        foreach ($members as $member) {
            $this->assertSame($expectedPoints, (int) $member->totalPoints());
            $this->assertSame($expectedCoins, (int) $member->totalCoins());
        }
    }

    /**
     * (b) LEGITIMATE-REPEAT — a genuinely DIFFERENT team activity (new id) for the same
     * members DOES award again. Proves the (team_activity_id:member_id) key is not too
     * coarse (it is not keyed on the member alone).
     */
    public function test_a_second_distinct_team_activity_awards_again(): void
    {
        $school = School::factory()->create();

        $teacher = User::factory()->teacher($school)->create();
        $classroom = Classroom::factory()->create([
            'school_id' => $school->id,
            'teacher_id' => $teacher->id,
        ]);
        $team = Team::create([
            'classroom_id' => $classroom->id,
            'name' => 'فريق النجوم',
            'created_by' => $teacher->id,
            'status' => 'active',
        ]);
        $member = User::factory()->student($school)->create();
        $team->members()->attach($member->id, ['role' => 'member', 'joined_at' => now()]);

        $activityA = Activity::factory()->create(['created_by' => $teacher->id]);
        $activityB = Activity::factory()->create(['created_by' => $teacher->id]);

        $taA = TeamActivity::create([
            'team_id' => $team->id,
            'activity_id' => $activityA->id,
            'assigned_by' => $teacher->id,
            'status' => 'assigned',
        ]);
        $taB = TeamActivity::create([
            'team_id' => $team->id,
            'activity_id' => $activityB->id,
            'assigned_by' => $teacher->id,
            'status' => 'assigned',
        ]);

        // Grade the FIRST team activity: 60 -> 30 points, 15 coins.
        $this->actingAs($teacher)
            ->postJson("/teacher/teams/activities/{$taA->id}/grade", [
                'total_score' => 60,
                'teacher_feedback' => 'جيد',
            ])
            ->assertOk();

        $this->assertSame(30, (int) $member->totalPoints());
        $this->assertSame(15, (int) $member->totalCoins());

        // Grade the SECOND, DISTINCT team activity: 40 -> 20 points, 10 coins.
        // Different TeamActivity id => different idempotency key => a genuine new award.
        $this->actingAs($teacher)
            ->postJson("/teacher/teams/activities/{$taB->id}/grade", [
                'total_score' => 40,
                'teacher_feedback' => 'جيد جدا',
            ])
            ->assertOk();

        // Totals accumulate across the two distinct events.
        $this->assertSame(50, (int) $member->totalPoints());
        $this->assertSame(25, (int) $member->totalCoins());

        // Two distinct ledger rows for this member (one per team activity).
        $this->assertSame(2, DB::table('award_ledger')
            ->where('user_id', $member->id)
            ->where('source_type', 'team_activity')
            ->count());
    }

    /**
     * (c) BALANCE-FLOOR — a zero-yield grade writes nothing negative or garbage.
     * total_score=0 => floor(0/2)=0 points, floor(0/4)=0 coins; AwardService treats a
     * non-positive award as a no-op, so no ledger/Point/Coin row appears and the member's
     * balance stays at 0 (never below).
     */
    public function test_zero_score_grade_writes_no_negative_or_garbage(): void
    {
        $school = School::factory()->create();
        [$teacher, $teamActivity, $members] = $this->makeTeamActivity($school, 2);

        $this->actingAs($teacher)
            ->postJson("/teacher/teams/activities/{$teamActivity->id}/grade", [
                'total_score' => 0,
                'teacher_feedback' => 'لم يكتمل',
            ])
            ->assertOk();

        // No economy rows of any kind were written.
        $this->assertSame(0, DB::table('award_ledger')->count());
        $this->assertSame(0, DB::table('points')->count());
        $this->assertSame(0, DB::table('coins')->count());

        foreach ($members as $member) {
            $this->assertSame(0, (int) $member->totalPoints());
            $this->assertSame(0, (int) $member->totalCoins());
            // Never negative.
            $this->assertGreaterThanOrEqual(0, (int) $member->totalPoints());
            $this->assertGreaterThanOrEqual(0, (int) $member->totalCoins());
        }

        // Negative/garbage scores are rejected at validation (min:0|max:100) — nothing written.
        $this->actingAs($teacher)
            ->postJson("/teacher/teams/activities/{$teamActivity->id}/grade", [
                'total_score' => -5,
                'teacher_feedback' => 'سالب',
            ])
            ->assertStatus(422);

        $this->assertSame(0, DB::table('award_ledger')->count());
        $this->assertSame(0, DB::table('points')->count());
        $this->assertSame(0, DB::table('coins')->count());
    }
}
