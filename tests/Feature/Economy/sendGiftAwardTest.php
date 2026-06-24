<?php

namespace Tests\Feature\Economy;

use App\Models\ParentGift;
use App\Models\School;
use App\Models\User;
use App\Services\AwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — economy regression for ParentController::sendGift.
 *
 * Wiring under test (ParentController::sendGift):
 *   - Inside ONE DB::transaction it counts today's gifts for (parent, child) under a
 *     lockForUpdate (TOCTOU close on the 3/day cap), creates a ParentGift row, and credits
 *     the PARENT side once via givePointsOnce() (ParentPoint::firstOrCreate keyed on
 *     reference_type='parent_gift', reference_id=$gift->id — anchored on the already-created
 *     gift id so a transaction RETRY cannot double the parent bonus).
 *   - AFTER that transaction commits, it credits the STUDENT 10 points through the central
 *     AwardService::award primitive.
 *   - STUDENT idempotency key = ($child->id, 'parent_gift', (string) $gift->id).
 *
 * CONSISTENCY BOUNDARY: the domain write (the ParentGift row + the ParentPoint parent bonus)
 * commits in sendGift's own transaction; the student AwardService::award runs in its OWN,
 * SEPARATE transaction afterwards. This site is (b) SEPARATE BUT RETRY-IDEMPOTENT END-TO-END:
 * the per-gift student key (anchored on the created ParentGift id) makes the student credit
 * exactly-once, so a crash in the window between "gift created" and "student awarded" self-heals
 * — re-driving the award for that SAME gift id short-circuits on the award_ledger UNIQUE row
 * (no re-create of the gift, no double student award). The parent bonus is likewise anchored on
 * the gift id (firstOrCreate), so it too is exactly-once.
 *
 * The three mandated guarantees:
 *  (a) IDEMPOTENCY     — replaying the SAME event (the SAME ParentGift row id) does NOT add an
 *                        award_ledger row, a Point row, or change the student balance.
 *  (b) LEGITIMATE-REPEAT — a genuinely DIFFERENT event (a NEW gift => new ParentGift id) DOES
 *                        award again (proves the key is anchored on the gift id, not too coarse
 *                        on e.g. the child alone).
 *  (c) BALANCE-FLOOR   — no negative/garbage write: every awarded Point row is strictly positive,
 *                        the balance only ever rises, and a non-positive award for this same key
 *                        family is a true no-op (writes nothing, never goes below zero).
 */
class sendGiftAwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A parent who owns one child, both in the same school (school.access + role:parent pass).
     *
     * @return array{0: User, 1: User}
     */
    private function makeParentAndChild(School $school): array
    {
        $parent = User::factory()->parent($school)->create();
        $child = User::factory()->student($school)->create();

        // Ownership is via the parent_student pivot (sendGift authorizes through children()).
        $parent->children()->attach($child->id, ['relationship' => 'ولي أمر']);

        return [$parent, $child];
    }

    /**
     * (a) IDEMPOTENCY — replaying the SAME gift event is a true no-op on the student economy.
     */
    public function test_replaying_the_same_gift_event_does_not_double_award_the_student(): void
    {
        $school = School::factory()->create();
        [$parent, $child] = $this->makeParentAndChild($school);

        // Send a gift through the real wired HTTP endpoint.
        $this->actingAs($parent)
            ->post("/parent/children/{$child->id}/gift", [
                'gift_type' => 'star',
                'gift_message' => 'أحسنت',
            ])
            ->assertRedirect();

        // The child was credited exactly once: 10 points, 0 coins.
        $this->assertSame(10, (int) $child->totalPoints());
        $this->assertSame(0, (int) $child->totalCoins());

        $gift = ParentGift::where('parent_id', $parent->id)
            ->where('student_id', $child->id)
            ->firstOrFail();

        $ledgerAfterFirst = DB::table('award_ledger')->where('source_type', 'parent_gift')->count();
        $pointRowsAfterFirst = DB::table('points')->count();
        $this->assertSame(1, $ledgerAfterFirst);

        // Replay the SAME event (the SAME ParentGift row id) directly against the primitive —
        // simulating a retried/replayed student-award after the gift already exists. It MUST
        // short-circuit on the award_ledger UNIQUE row: no new ledger row, no new Point, no
        // balance change.
        $newlyAwarded = AwardService::award(
            (int) $child->id,
            'parent_gift',
            (string) $gift->id,
            10,
            0,
            'هدية من ولي الأمر',
        );
        $this->assertFalse($newlyAwarded, 'Replaying the same gift id must be an idempotent no-op');

        $this->assertSame($ledgerAfterFirst, DB::table('award_ledger')->where('source_type', 'parent_gift')->count());
        $this->assertSame($pointRowsAfterFirst, DB::table('points')->count());
        $this->assertSame(10, (int) $child->totalPoints());
        $this->assertSame(0, (int) $child->totalCoins());
    }

    /**
     * (b) LEGITIMATE-REPEAT — a genuinely DIFFERENT gift (new ParentGift id) DOES award again.
     * Proves the ('parent_gift', gift->id) key is anchored on the concrete gift, not coarsened
     * onto the child alone (which would swallow a legitimate second gift).
     */
    public function test_a_second_distinct_gift_awards_the_student_again(): void
    {
        $school = School::factory()->create();
        [$parent, $child] = $this->makeParentAndChild($school);

        // First legitimate gift.
        $this->actingAs($parent)
            ->post("/parent/children/{$child->id}/gift", ['gift_type' => 'star', 'gift_message' => 'هدية أولى'])
            ->assertRedirect();
        $this->assertSame(10, (int) $child->totalPoints());

        // Second, DISTINCT gift (a new ParentGift row => a new id => a new idempotency key).
        $this->actingAs($parent)
            ->post("/parent/children/{$child->id}/gift", ['gift_type' => 'medal', 'gift_message' => 'هدية ثانية'])
            ->assertRedirect();

        // The two distinct events both award: the balance accumulates to 20.
        $this->assertSame(20, (int) $child->totalPoints());
        $this->assertSame(0, (int) $child->totalCoins());

        // Two distinct ledger rows for this child (one per gift).
        $this->assertSame(2, DB::table('award_ledger')
            ->where('user_id', $child->id)
            ->where('source_type', 'parent_gift')
            ->count());
        $this->assertSame(2, ParentGift::where('student_id', $child->id)->count());
    }

    /**
     * (c) BALANCE-FLOOR — no negative/garbage write.
     * A real gift award is a fixed +10 (always strictly positive): every Point row written for
     * the child is positive, the balance only ever rises, and a non-positive award for the SAME
     * key family is a true no-op (the primitive rejects <= 0), so the balance never goes below
     * zero or gains a garbage row.
     */
    public function test_gift_award_writes_no_negative_or_garbage(): void
    {
        $school = School::factory()->create();
        [$parent, $child] = $this->makeParentAndChild($school);

        $this->actingAs($parent)
            ->post("/parent/children/{$child->id}/gift", ['gift_type' => 'star', 'gift_message' => 'هدية'])
            ->assertRedirect();

        // The single awarded Point row is strictly positive — never negative/garbage.
        $points = DB::table('points')->where('user_id', $child->id)->pluck('points');
        $this->assertCount(1, $points);
        foreach ($points as $p) {
            $this->assertGreaterThan(0, (int) $p, 'A gift Point row must be strictly positive');
        }
        $this->assertSame(10, (int) $child->totalPoints());
        $this->assertGreaterThanOrEqual(0, (int) $child->totalPoints());

        $ledgerBefore = DB::table('award_ledger')->count();
        $pointsBefore = DB::table('points')->count();

        // A non-positive award (0 points / 0 coins) for the same family is rejected by the
        // primitive: nothing written, no ledger row, balance stays put (never below zero).
        $noop = AwardService::award((int) $child->id, 'parent_gift', 'zero-floor', 0, 0, 'floor');
        $this->assertFalse($noop, 'A non-positive award must be a no-op');

        $this->assertSame($ledgerBefore, DB::table('award_ledger')->count());
        $this->assertSame($pointsBefore, DB::table('points')->count());
        $this->assertSame(10, (int) $child->totalPoints());
        $this->assertGreaterThanOrEqual(0, (int) $child->totalPoints());
    }
}
