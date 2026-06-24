<?php

namespace Tests\Feature\Economy;

use App\Models\Activity;
use App\Models\FamilyActivitySubmission;
use App\Models\School;
use App\Models\User;
use App\Services\AwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — economy regression for ParentController::approveFamilyActivity.
 *
 * Wiring under test (POST /parent/family-activities/{id}/approve):
 *   - Inside ONE outer DB::transaction the parent's approval (a) row-locks the
 *     FamilyActivitySubmission, (b) flips status pending -> approved (domain write),
 *     (c) credits the STUDENT 20 points through the central AwardService::award
 *     primitive, and (d) records a 10-point ParentPoint bonus via givePointsOnce.
 *   - Student idempotency key = (student_id, 'family_activity', (string) submission->id).
 *   - Parent-bonus idempotency = ParentPoint::firstOrCreate keyed on
 *     (reference_type='family_activity', reference_id=submission->id).
 *
 * CONSISTENCY BOUNDARY: this site is (a) DOMAIN-WRITE + AWARD ATOMIC TOGETHER. The
 * status='approved' update and the AwardService::award call run in the SAME outer
 * transaction (award() opens a nested savepoint), so a crash can never leave an
 * "approved" submission with no student credit, nor a credit with no approval.
 * Re-invocation is additionally fenced THREE ways: (1) the pending-status HTTP guard
 * returns 'info' and skips both credits, (2) the award_ledger UNIQUE row short-circuits
 * the student credit, (3) ParentPoint::firstOrCreate short-circuits the parent bonus.
 *
 * The three mandated guarantees:
 *  (a) IDEMPOTENCY     — replaying the SAME approval (same submission) does NOT add an
 *                        award_ledger row, a Point row, or a ParentPoint row, and changes
 *                        no balance; the re-POST is a benign 'info' no-op.
 *  (b) LEGITIMATE-REPEAT — approving a genuinely DIFFERENT submission DOES award again
 *                        (proves the key is keyed on submission->id, not too coarse).
 *  (c) BALANCE-FLOOR   — the reject branch (and the no-op replay) writes nothing
 *                        negative/garbage: no ledger/Point/ParentPoint row, balances >= 0.
 */
class approveFamilyActivityAwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Build a pending FamilyActivitySubmission owned by $parent for $student.
     */
    private function makePendingSubmission(User $parent, User $student): FamilyActivitySubmission
    {
        $activity = Activity::factory()->create();

        return FamilyActivitySubmission::create([
            'activity_id' => $activity->id,
            'student_id' => $student->id,
            'parent_id' => $parent->id,
            'submission_data' => ['note' => 'أنجز الطالب النشاط'],
            'parent_approved' => false,
            'status' => 'pending',
        ]);
    }

    /**
     * (a) IDEMPOTENCY — replaying the SAME approval is a true no-op on the economy.
     */
    public function test_replaying_the_same_family_activity_approval_does_not_double_award(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parent($school)->create();
        $student = User::factory()->student($school)->create();
        $submission = $this->makePendingSubmission($parent, $student);

        // First approval — through the real wired HTTP endpoint.
        $first = $this->actingAs($parent)
            ->post("/parent/family-activities/{$submission->id}/approve", [
                'praise' => 'أحسنت',
            ]);
        $first->assertRedirect();
        $first->assertSessionHas('success');

        // Student credited 20 points exactly once; parent bonus recorded once.
        $this->assertSame(20, (int) $student->totalPoints());
        $this->assertSame(0, (int) $student->totalCoins());

        $ledgerAfterFirst = DB::table('award_ledger')->where('source_type', 'family_activity')->count();
        $pointRowsAfterFirst = DB::table('points')->count();
        $parentPointRowsAfterFirst = DB::table('parent_points')->count();
        $this->assertSame(1, $ledgerAfterFirst);
        $this->assertSame(1, $parentPointRowsAfterFirst);

        $submission->refresh();
        $this->assertSame('approved', $submission->status);

        // HTTP-level guard: a naive re-POST of the same (now-approved) submission is a
        // benign 'info' no-op — it credits nothing.
        $replay = $this->actingAs($parent)
            ->post("/parent/family-activities/{$submission->id}/approve", [
                'praise' => 'إعادة',
            ]);
        $replay->assertRedirect();
        $replay->assertSessionHas('info');

        // Ledger-level guard: even calling the SAME student award key directly again
        // (simulating a retried/replayed award) must short-circuit on the UNIQUE row —
        // no new ledger row, no new Point, no balance change.
        $newlyAwarded = AwardService::award(
            $student->id,
            'family_activity',
            (string) $submission->id,
            20,
            0,
            'replay',
        );
        $this->assertFalse($newlyAwarded, 'Replayed student award must be an idempotent no-op');

        $this->assertSame($ledgerAfterFirst, DB::table('award_ledger')->where('source_type', 'family_activity')->count());
        $this->assertSame($pointRowsAfterFirst, DB::table('points')->count());
        $this->assertSame($parentPointRowsAfterFirst, DB::table('parent_points')->count());
        $this->assertSame(20, (int) $student->totalPoints());
    }

    /**
     * (b) LEGITIMATE-REPEAT — approving a genuinely DIFFERENT submission DOES award again.
     * Proves the (student_id, 'family_activity', submission->id) key is keyed on the
     * concrete submission and is NOT too coarse (not keyed on student alone).
     */
    public function test_approving_a_second_distinct_submission_awards_again(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parent($school)->create();
        $student = User::factory()->student($school)->create();

        $submissionA = $this->makePendingSubmission($parent, $student);
        $submissionB = $this->makePendingSubmission($parent, $student);

        // Approve the FIRST submission.
        $this->actingAs($parent)
            ->post("/parent/family-activities/{$submissionA->id}/approve", ['praise' => 'ممتاز'])
            ->assertRedirect();

        $this->assertSame(20, (int) $student->totalPoints());

        // Approve the SECOND, DISTINCT submission — different submission id => different
        // idempotency key => a genuine new award.
        $this->actingAs($parent)
            ->post("/parent/family-activities/{$submissionB->id}/approve", ['praise' => 'رائع'])
            ->assertRedirect();

        // Totals accumulate across the two distinct events.
        $this->assertSame(40, (int) $student->totalPoints());

        // Two distinct student ledger rows (one per submission).
        $this->assertSame(2, DB::table('award_ledger')
            ->where('user_id', $student->id)
            ->where('source_type', 'family_activity')
            ->count());

        // Two distinct parent-bonus rows (one per submission), 10 points each.
        $this->assertSame(2, DB::table('parent_points')
            ->where('parent_id', $parent->id)
            ->where('reference_type', 'family_activity')
            ->count());
    }

    /**
     * (c) BALANCE-FLOOR — the reject branch writes nothing negative/garbage.
     * Rejecting a submission must credit NEITHER the student NOR the parent: no ledger
     * row, no Point row, no ParentPoint row; balances stay at 0 (never below).
     */
    public function test_rejecting_a_family_activity_writes_no_negative_or_garbage(): void
    {
        $school = School::factory()->create();
        $parent = User::factory()->parent($school)->create();
        $student = User::factory()->student($school)->create();
        $submission = $this->makePendingSubmission($parent, $student);

        $this->actingAs($parent)
            ->post("/parent/family-activities/{$submission->id}/approve", [
                'reject' => true,
                'rejection_reason' => 'لم يكتمل النشاط',
            ])
            ->assertRedirect();

        $submission->refresh();
        $this->assertSame('rejected', $submission->status);

        // No economy rows of any kind were written.
        $this->assertSame(0, DB::table('award_ledger')->count());
        $this->assertSame(0, DB::table('points')->count());
        $this->assertSame(0, DB::table('parent_points')->count());

        // Balances stay at 0 and never go negative.
        $this->assertSame(0, (int) $student->totalPoints());
        $this->assertSame(0, (int) $student->totalCoins());
        $this->assertGreaterThanOrEqual(0, (int) $student->totalPoints());
        $this->assertGreaterThanOrEqual(0, (int) $student->totalCoins());

        // A foreign parent cannot approve someone else's submission (no credit leaks).
        $intruder = User::factory()->parent($school)->create();
        $this->actingAs($intruder)
            ->post("/parent/family-activities/{$submission->id}/approve", ['praise' => 'تسلل'])
            ->assertRedirect();

        $this->assertSame(0, DB::table('award_ledger')->count());
        $this->assertSame(0, DB::table('points')->count());
        $this->assertSame(0, DB::table('parent_points')->count());
    }
}
