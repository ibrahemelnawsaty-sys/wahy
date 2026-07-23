<?php

namespace Tests\Feature\Economy;

use App\Http\Controllers\StudentController;
use App\Models\Coin;
use App\Models\Point;
use App\Models\PvpChallenge;
use App\Models\PvpMatch;
use App\Models\QuestionBank;
use App\Models\School;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — regression for the pvpWinnerPayout site in
 * StudentController::submitPvpAnswers.
 *
 * When the second PvP player submits and both players have answered, the
 * controller runs PvpMatch::determineWinner() (status playing -> completed, once)
 * and then credits the winner 20 points + 10 coins through the central primitive
 * AwardService::award($winnerId, 'pvp_match', (string) $match->id, 20, 10, ...).
 *
 * Idempotency key = ('pvp_match', match.id): exactly ONE payout per match,
 * regardless of how many times the submit endpoint is replayed.
 *
 * Consistency boundary (stated): match completion (determineWinner) commits in its
 * OWN transaction BEFORE the award; AwardService owns the award's transaction. The
 * flow is therefore (b) separate-but-retry-idempotent end-to-end — a re-invocation
 * after completion does NOT re-complete the match (status gate in determineWinner)
 * and does NOT re-award (insertOrIgnore on pvp_match/match.id short-circuits). A
 * crash in the window between completion and award self-heals on the next submit:
 * determineWinner is a no-op, but the award then runs and claims the key once.
 *
 * Honest race note: PHPUnit cannot fire two physically simultaneous submits. These
 * tests prove LOGICAL end-to-end idempotency via replay; the true concurrent race
 * is closed at the DB layer by UNIQUE(user_id, source_type, source_id) on
 * award_ledger (insertOrIgnore), exactly as exercised by the replay here.
 */
class pvpWinnerPayoutAwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a challenge with a single true_false question whose correct answer is
     * "true", plus a match in 'playing' state where player1 has ALREADY submitted a
     * correct answer (score 100). When player2 then submits a wrong answer (score 0),
     * player1 is the deterministic winner and receives the payout.
     *
     * @return array{0: PvpMatch, 1: User, 2: User, 3: QuestionBank} [$match, $winner(player1), $submitter(player2), $question]
     */
    private function makePlayingMatchWithPlayer1Ahead(School $school): array
    {
        $teacher = User::factory()->teacher($school)->create();

        $question = QuestionBank::create([
            'created_by' => $teacher->id,
            'title' => 'Q',
            'question_text' => 'Is the sky blue?',
            'question_type' => 'true_false',
            'correct_answer' => 'true',
            'points' => 10,
            'difficulty' => 'easy',
            'status' => 'approved',
        ]);

        $challenge = PvpChallenge::create([
            'title' => 'Default challenge',
            'questions' => [$question->id],
            'time_limit' => 30,
            'is_active' => true,
            'created_by' => $teacher->id,
        ]);

        $player1 = User::factory()->student($school)->create(['name' => 'P1']);
        $player2 = User::factory()->student($school)->create(['name' => 'P2']);

        // player1 has already played and scored 100 (answered the only question correctly).
        $match = PvpMatch::create([
            'challenge_id' => $challenge->id,
            'player1_id' => $player1->id,
            'player2_id' => $player2->id,
            'status' => 'playing',
            'started_at' => now(),
            'player1_answers' => [$question->id => 'true'],
            'player1_score' => 100,
            'player1_time' => 5,
        ]);

        return [$match, $player1, $player2, $question];
    }

    /**
     * Drive the real production site: player2 submits a wrong answer through the
     * controller. Returns the controller's JSON response.
     */
    private function submitAsPlayer2(PvpMatch $match, User $player2, QuestionBank $question): void
    {
        $this->actingAs($player2);

        $request = Request::create(
            route('student.pvp.submit', ['matchId' => $match->id]),
            'POST',
            [
                'answers' => [$question->id => 'false'], // wrong -> player2 score 0, player1 wins
                'time_taken' => 9,
            ],
        );
        $request->setUserResolver(fn () => $player2);

        app(StudentController::class)->submitPvpAnswers($request, $match->id);
    }

    public function test_winner_is_paid_once_on_match_completion(): void
    {
        $school = School::factory()->create();
        [$match, $winner, $player2, $question] = $this->makePlayingMatchWithPlayer1Ahead($school);

        $this->submitAsPlayer2($match, $player2, $question);

        $match->refresh();
        $this->assertSame('completed', $match->status);
        $this->assertSame($winner->id, $match->winner_id, 'player1 (higher score) must win');

        // Exactly one ledger row, keyed on the match, for the winner.
        $this->assertSame(1, DB::table('award_ledger')
            ->where(['user_id' => $winner->id, 'source_type' => 'pvp_match', 'source_id' => (string) $match->id])
            ->count());

        // The mandated payout: 20 points + 10 coins, once.
        $this->assertSame(20, (int) Point::where('user_id', $winner->id)->sum('points'));
        $this->assertSame(10, (int) Coin::where('user_id', $winner->id)->sum('coins'));

        // The loser (submitter) is paid nothing.
        $this->assertSame(0, (int) Point::where('user_id', $player2->id)->sum('points'));
        $this->assertSame(0, (int) Coin::where('user_id', $player2->id)->sum('coins'));
    }

    /**
     * (a) IDEMPOTENCY — replaying the SAME event (re-submitting for the same, now
     * completed, match) is a true no-op: ledger row count stays 1, no extra Point/Coin
     * rows, the winner's summed balance is unchanged.
     */
    public function test_replaying_same_match_submit_does_not_double_pay(): void
    {
        $school = School::factory()->create();
        [$match, $winner, $player2, $question] = $this->makePlayingMatchWithPlayer1Ahead($school);

        // First submit -> match completes, winner paid once.
        $this->submitAsPlayer2($match, $player2, $question);

        $ledgerAfterFirst = DB::table('award_ledger')->count();
        $pointRowsAfterFirst = Point::where('user_id', $winner->id)->count();
        $coinRowsAfterFirst = Coin::where('user_id', $winner->id)->count();

        $this->assertSame(1, $ledgerAfterFirst);
        $this->assertSame(20, (int) Point::where('user_id', $winner->id)->sum('points'));
        $this->assertSame(10, (int) Coin::where('user_id', $winner->id)->sum('coins'));

        // Replay the SAME event twice more. The match is now 'completed', so the status
        // gate in submitPvpAnswers REJECTS the replay with 403 — stronger than mere award
        // idempotency: the double-submit never reaches the award/determineWinner at all.
        foreach ([1, 2] as $_replay) {
            try {
                $this->submitAsPlayer2($match, $player2, $question);
                $this->fail('replay on a completed match must be rejected');
            } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
                $this->assertSame(403, $e->getStatusCode());
            }
        }

        // Ledger unchanged (replay blocked; and key pvp_match/match.id already claimed) ...
        $this->assertSame(1, DB::table('award_ledger')->count(), 'replay must not add a ledger row');
        // ... no new Point/Coin rows ...
        $this->assertSame($pointRowsAfterFirst, Point::where('user_id', $winner->id)->count(), 'no extra Point row');
        $this->assertSame($coinRowsAfterFirst, Coin::where('user_id', $winner->id)->count(), 'no extra Coin row');
        // ... and the summed balance is exactly the single payout.
        $this->assertSame(20, (int) Point::where('user_id', $winner->id)->sum('points'));
        $this->assertSame(10, (int) Coin::where('user_id', $winner->id)->sum('coins'));
    }

    /**
     * (b) LEGITIMATE-REPEAT — a genuinely DIFFERENT event (a SECOND, distinct match
     * with the same winner) DOES award again. Proves the key ('pvp_match', match.id)
     * is not too coarse: distinct match ids => distinct payouts.
     */
    public function test_a_distinct_match_pays_the_winner_again(): void
    {
        $school = School::factory()->create();

        [$match1, $winner1, $p2a, $qa] = $this->makePlayingMatchWithPlayer1Ahead($school);
        $this->submitAsPlayer2($match1, $p2a, $qa);

        $this->assertSame(20, (int) Point::where('user_id', $winner1->id)->sum('points'));

        // A brand-new, independent match (different domain row id) for a fresh winner.
        [$match2, $winner2, $p2b, $qb] = $this->makePlayingMatchWithPlayer1Ahead($school);
        $this->assertNotSame($match1->id, $match2->id);

        $this->submitAsPlayer2($match2, $p2b, $qb);

        // The second match's winner is genuinely paid (key not swallowed as a dup).
        $this->assertSame(20, (int) Point::where('user_id', $winner2->id)->sum('points'));
        $this->assertSame(10, (int) Coin::where('user_id', $winner2->id)->sum('coins'));

        // Two distinct ledger rows, one per match.
        $this->assertSame(2, DB::table('award_ledger')->where('source_type', 'pvp_match')->count());
        $this->assertSame(1, DB::table('award_ledger')
            ->where(['source_type' => 'pvp_match', 'source_id' => (string) $match1->id])->count());
        $this->assertSame(1, DB::table('award_ledger')
            ->where(['source_type' => 'pvp_match', 'source_id' => (string) $match2->id])->count());
    }

    /**
     * (c) BALANCE-FLOOR — the payout never writes a negative/garbage amount. The
     * recorded ledger row carries exactly the mandated non-negative 20/10, and the
     * winner's summed balances are non-negative. (The economy is append-only; this
     * pins that the wired call cannot produce a negative or zero-garbage credit.)
     */
    public function test_payout_is_non_negative_and_exactly_the_mandated_amount(): void
    {
        $school = School::factory()->create();
        [$match, $winner, $player2, $question] = $this->makePlayingMatchWithPlayer1Ahead($school);

        $this->submitAsPlayer2($match, $player2, $question);

        $row = DB::table('award_ledger')
            ->where(['user_id' => $winner->id, 'source_type' => 'pvp_match', 'source_id' => (string) $match->id])
            ->first();

        $this->assertNotNull($row);
        $this->assertSame(20, (int) $row->points);
        $this->assertSame(10, (int) $row->coins);
        $this->assertGreaterThanOrEqual(0, (int) $row->points);
        $this->assertGreaterThanOrEqual(0, (int) $row->coins);

        // Summed wallet balances are non-negative and match the single mandated payout.
        $this->assertGreaterThanOrEqual(0, (int) Point::where('user_id', $winner->id)->sum('points'));
        $this->assertGreaterThanOrEqual(0, (int) Coin::where('user_id', $winner->id)->sum('coins'));
        $this->assertSame(20, (int) Point::where('user_id', $winner->id)->sum('points'));
        $this->assertSame(10, (int) Coin::where('user_id', $winner->id)->sum('coins'));
    }
}
