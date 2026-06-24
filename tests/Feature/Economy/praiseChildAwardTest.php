<?php

namespace Tests\Feature\Economy;

use App\Models\School;
use App\Models\User;
use App\Services\AwardService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — economy regression for ParentController::praiseChild.
 *
 * Wiring under test (ParentController::praiseChild):
 *   - In ONE DB::transaction it enforces the per-day limit (max 5), inserts a
 *     `parent_praises` row (the domain event) and a `parent_points` parent-side bonus
 *     keyed on (reference_type='parent_praise', reference_id=parent_praises.id).
 *   - AFTER that transaction commits, it credits the STUDENT 5 points through the
 *     central AwardService::award primitive, keyed on
 *     (student_id, 'parent_praise', (string) parent_praises.id).
 *
 * CONSISTENCY BOUNDARY: the domain write (the parent_praises/parent_points insert) owns
 * its own DB::transaction and commits SEPARATELY from the AwardService::award transaction.
 * This site is (b) SEPARATE BUT RETRY-IDEMPOTENT END-TO-END: the student-award key is the
 * already-created parent_praises.id, so the student credit is exactly-once. A crash in the
 * window between the praise commit and the award self-heals on a direct re-invocation of
 * AwardService::award with that same praise id — the award_ledger UNIQUE row short-circuits
 * it, never re-marking the praise and never double-crediting. A genuinely new praise mints
 * a fresh parent_praises.id => a fresh key => a legitimate new award. The parent-side
 * parent_points bonus is itself anchored on the same praise id via insert into a row created
 * once, so a transaction RETRY cannot double it either.
 *
 * The three mandated guarantees:
 *  (a) IDEMPOTENCY     — replaying the SAME praise event (same parent_praises row id) does
 *                        NOT add an award_ledger row, a Point row, or change the student
 *                        balance; the parent_points bonus stays single.
 *  (b) LEGITIMATE-REPEAT — a genuinely DIFFERENT praise (a NEW parent_praises id) DOES award
 *                        again (proves the key is the per-praise id, not too coarse).
 *  (c) BALANCE-FLOOR   — nothing negative/garbage is ever written: balances only ever rise,
 *                        an empty message is rejected (422) and writes nothing, and the
 *                        award amount is the fixed positive 5 (AwardService floors a
 *                        non-positive award to a no-op anyway).
 */
class praiseChildAwardTest extends TestCase
{
    use RefreshDatabase;

    /**
     * A parent and their child in the same school, wired via the parent_student pivot
     * that ParentController::children() reads.
     *
     * @return array{0: User, 1: User}
     */
    private function makeParentAndChild(School $school): array
    {
        $parent = User::factory()->parent($school)->create();
        $child = User::factory()->student($school)->create();
        DB::table('parent_student')->insert([
            'parent_id' => $parent->id,
            'student_id' => $child->id,
        ]);

        return [$parent, $child];
    }

    /**
     * (a) IDEMPOTENCY — replaying the SAME praise event (same parent_praises row id) is a
     * true no-op on the student economy.
     */
    public function test_replaying_the_same_praise_event_awards_exactly_once(): void
    {
        $school = School::factory()->create();
        [$parent, $child] = $this->makeParentAndChild($school);

        // First praise — through the real wired HTTP endpoint.
        $first = $this->actingAs($parent)
            ->postJson("/parent/children/{$child->id}/praise", [
                'praise_message' => 'أحسنت يا بطل',
                'praise_type' => 'achievement',
            ]);
        $first->assertOk();
        $first->assertJson(['success' => true]);

        // Exactly one praise, one ledger row, one Point row, +5 to the student.
        $praiseId = (int) DB::table('parent_praises')
            ->where('student_id', $child->id)
            ->value('id');
        $this->assertGreaterThan(0, $praiseId);

        $this->assertSame(5, (int) $child->totalPoints());
        $this->assertSame(1, DB::table('award_ledger')
            ->where('user_id', $child->id)
            ->where('source_type', 'parent_praise')
            ->where('source_id', (string) $praiseId)
            ->count());
        $this->assertSame(1, DB::table('points')->where('user_id', $child->id)->count());

        // Parent-side bonus is single and anchored on the praise id.
        $this->assertSame(1, DB::table('parent_points')
            ->where('reference_type', 'parent_praise')
            ->where('reference_id', $praiseId)
            ->count());

        $ledgerBefore = DB::table('award_ledger')->count();
        $pointRowsBefore = DB::table('points')->count();
        $parentPointsBefore = DB::table('parent_points')->count();

        // Ledger-level guard: replaying the SAME award key directly (simulating a retried
        // award after the praise was already committed) must short-circuit on the UNIQUE row.
        $newlyAwarded = AwardService::award(
            $child->id,
            'parent_praise',
            (string) $praiseId,
            5,
            0,
            'replay',
        );
        $this->assertFalse($newlyAwarded, 'Replayed praise award must be an idempotent no-op');

        // Nothing changed: no new ledger row, no new Point row, no balance change, and the
        // parent-side bonus stays single.
        $this->assertSame($ledgerBefore, DB::table('award_ledger')->count());
        $this->assertSame($pointRowsBefore, DB::table('points')->count());
        $this->assertSame($parentPointsBefore, DB::table('parent_points')->count());
        $this->assertSame(5, (int) $child->totalPoints());
    }

    /**
     * (b) LEGITIMATE-REPEAT — a genuinely DIFFERENT praise (a new parent_praises id) DOES
     * award again. Proves the key is the per-praise id and is NOT collapsed to
     * (child_id + date), which would (wrongly) swallow a second same-day praise.
     */
    public function test_a_second_distinct_praise_awards_again(): void
    {
        $school = School::factory()->create();
        [$parent, $child] = $this->makeParentAndChild($school);

        // First praise.
        $this->actingAs($parent)
            ->postJson("/parent/children/{$child->id}/praise", [
                'praise_message' => 'عمل رائع اليوم',
                'praise_type' => 'encouragement',
            ])
            ->assertOk();

        $this->assertSame(5, (int) $child->totalPoints());

        // Second, genuinely DISTINCT praise — same child, same day, NEW parent_praises id.
        // A too-coarse (child+date) key would swallow this; the per-praise key does not.
        $this->actingAs($parent)
            ->postJson("/parent/children/{$child->id}/praise", [
                'praise_message' => 'استمر في التميز',
                'praise_type' => 'behavior',
            ])
            ->assertOk();

        // Balance accumulates across the two distinct events.
        $this->assertSame(10, (int) $child->totalPoints());

        // Two distinct praise rows => two distinct ledger rows for this student.
        $praiseIds = DB::table('parent_praises')
            ->where('student_id', $child->id)
            ->orderBy('id')
            ->pluck('id');
        $this->assertCount(2, $praiseIds);

        $this->assertSame(2, DB::table('award_ledger')
            ->where('user_id', $child->id)
            ->where('source_type', 'parent_praise')
            ->count());

        // Each ledger row is keyed on its own praise id (the per-event key).
        foreach ($praiseIds as $praiseId) {
            $this->assertSame(1, DB::table('award_ledger')
                ->where('user_id', $child->id)
                ->where('source_type', 'parent_praise')
                ->where('source_id', (string) $praiseId)
                ->count());
        }

        // Parent-side bonus also accumulated once per distinct praise.
        $this->assertSame(2, DB::table('parent_points')
            ->where('parent_id', $parent->id)
            ->where('reference_type', 'parent_praise')
            ->count());
    }

    /**
     * (c) BALANCE-FLOOR — no negative/garbage write. An empty message is rejected (422) and
     * writes nothing; a valid praise only ever raises the balance (the fixed +5), never
     * below zero. AwardService independently floors any non-positive award to a no-op.
     */
    public function test_no_negative_or_garbage_is_ever_written(): void
    {
        $school = School::factory()->create();
        [$parent, $child] = $this->makeParentAndChild($school);

        // Empty message => rejected at the guard with 422, nothing written anywhere.
        $this->actingAs($parent)
            ->postJson("/parent/children/{$child->id}/praise", [
                'praise_message' => '   ',
                'praise_type' => 'encouragement',
            ])
            ->assertStatus(422);

        $this->assertSame(0, DB::table('parent_praises')->count());
        $this->assertSame(0, DB::table('award_ledger')->count());
        $this->assertSame(0, DB::table('points')->count());
        $this->assertSame(0, DB::table('parent_points')->count());
        $this->assertSame(0, (int) $child->totalPoints());

        // A valid praise raises the balance to exactly +5 and never below zero.
        $this->actingAs($parent)
            ->postJson("/parent/children/{$child->id}/praise", [
                'praise_message' => 'أحسنت',
                'praise_type' => 'custom',
            ])
            ->assertOk();

        $this->assertSame(5, (int) $child->totalPoints());
        $this->assertGreaterThanOrEqual(0, (int) $child->totalPoints());

        // No Point row is ever negative.
        $minPoints = (int) (DB::table('points')->where('user_id', $child->id)->min('points') ?? 0);
        $this->assertGreaterThanOrEqual(0, $minPoints);

        // The single ledger row carries the fixed positive amount (5 points, 0 coins).
        $row = DB::table('award_ledger')->where('user_id', $child->id)->first();
        $this->assertNotNull($row);
        $this->assertSame(5, (int) $row->points);
        $this->assertSame(0, (int) $row->coins);
    }
}
