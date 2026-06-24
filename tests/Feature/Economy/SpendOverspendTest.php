<?php

namespace Tests\Feature\Economy;

use App\Models\Coin;
use App\Models\ShopItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch-2 — ADVERSARIAL OVERSPEND VERIFIER for the SPEND paths
 * (StudentController::redeemReward / purchaseItem).
 *
 * GOAL: DEMONSTRATE the CURRENT overspend bug, not pin the fixed behaviour. Every
 * test here is written to PASS against today's (buggy) code so it is an executable
 * proof of the defect. When the §1 design lands, the race-window test must be
 * REWRITTEN to assert the conditional UPDATE fails closed (it is the regression that
 * the fix is supposed to flip).
 *
 * Two independent defects in redeemReward are demonstrated:
 *
 *   Bug B (client-supplied price) — directly testable end-to-end via HTTP. The
 *   controller validates `cost` and debits exactly that, never loading the reward
 *   by `reward_id`. A student redeems a 9000-coin reward for cost:1.
 *
 *   Bug A (read-then-debit race / "wallet lock" does not serialize) — NOT testable
 *   by two sequential HTTP calls (the 2nd call re-reads the SUM and sees the 1st
 *   debit, so it correctly fails). The defect is a CONCURRENCY window: two spends
 *   that each read the balance BEFORE either writes both pass the affordability
 *   check and both debit. PHPUnit is single-threaded and the test DB is sqlite
 *   :memory: (a single connection where lockForUpdate is a no-op), so we cannot
 *   fire two physically simultaneous connections. Instead we reproduce the EXACT
 *   read-then-debit sequence the controller executes, interleaved, and show the
 *   resulting committed balance is NEGATIVE — proving the window exists in the
 *   current control flow. This is the honest analogue of AwardServiceTest's race
 *   note, inverted: there the DB UNIQUE closes the race; here NOTHING does.
 */
class SpendOverspendTest extends TestCase
{
    use RefreshDatabase;

    private function fund(User $u, int $coins): void
    {
        Coin::create([
            'user_id' => $u->id,
            'coins' => $coins,
            'transaction_type' => 'earn',
            'reason' => 'test funding',
        ]);
    }

    private function balance(User $u): int
    {
        return (int) Coin::where('user_id', $u->id)->sum('coins');
    }

    /**
     * Bug B — DEMONSTRATED. The current redeemReward trusts the client `cost` and
     * never derives price from the reward. A 9000-coin reward is redeemed for 1.
     *
     * This test PASSES today (the bug is live). After the §2 fix it MUST fail
     * (server derives price; forged cost ignored) and be inverted into a guard.
     */
    public function test_current_redeem_trusts_client_supplied_cost(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        // A genuinely expensive reward — server price is irrelevant to today's code.
        $reward = ShopItem::create([
            'name' => 'مكافأة غالية',
            'type' => 'special',
            'price' => 9000,
            'status' => 'active',
        ]);

        $res = $this->actingAs($student)->postJson('/student/shop/redeem', [
            'reward_id' => $reward->id,
            'cost' => 1, // forged — current code debits exactly this, ignores price
        ]);

        $res->assertOk()->assertJson(['success' => true]);

        // BUG: charged the forged 1, NOT the real 9000. Balance 99, not "rejected".
        $this->assertSame(
            99,
            $this->balance($student),
            'CURRENT BUG: client-supplied cost was trusted; only 1 coin was debited for a 9000 reward.',
        );
    }

    /**
     * Bug A — DEMONSTRATED. The read-then-debit race window allows overdraft.
     *
     * Setup: balance B = 100, cost C = 80 per redeem. 2C = 160 > B, but C = 80 <= B,
     * so EACH redeem is individually affordable while the PAIR is not. Correct
     * behaviour: exactly one succeeds, final balance 20, never negative.
     *
     * We reproduce the controller's body literally:
     *     $bal = (int) Coin::where('user_id', $id)->lockForUpdate()->sum('coins');
     *     if ($bal < $cost) return reject;
     *     Coin::create([... 'coins' => -$cost ...]);
     * but interleave two of them so BOTH read before EITHER writes — exactly what
     * two concurrent requests do on MySQL when the "wallet lock" fails to serialize
     * (empty/disjoint matching set, per design §0 Bug A). The committed balance ends
     * NEGATIVE, proving the overdraft window.
     */
    public function test_read_then_debit_race_window_allows_overdraft(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);
        $cost = 80;

        // Preconditions read from the live ledger: a single redeem fits, the pair
        // does not.
        $this->assertSame(100, $this->balance($student));
        $this->assertGreaterThan(100, 2 * $cost, 'precondition: 2C > B');
        $this->assertLessThanOrEqual(100, $cost, 'precondition: C <= B');

        // The controller's affordability READ, isolated. Returns the balance each
        // request observes at gate-time (lockForUpdate is a no-op on sqlite; on
        // MySQL the empty/disjoint matching set does not serialize either — §0 Bug A).
        $observe = fn (): int => (int) Coin::where('user_id', $student->id)
            ->lockForUpdate()->sum('coins');

        // The controller's DEBIT, isolated: insert a negative ledger row iff the
        // observed balance covered the cost. Returns 1 if it debited.
        $tryDebit = function (int $observed, string $tag) use ($student, $cost): int {
            if ($observed < $cost) {
                return 0; // controller's "عملاتك غير كافية" branch
            }
            Coin::create([
                'user_id' => $student->id,
                'coins' => -$cost,
                'transaction_type' => 'spend',
                'reason' => $tag,
            ]);

            return 1;
        };

        // THE RACE: both requests READ before either WRITES (the read-then-debit
        // window). Each then debits based on its own stale read.
        $balR1 = $observe();
        $balR2 = $observe(); // R2's read precedes R1's debit → same stale value

        $debits = $tryDebit($balR1, 'R1') + $tryDebit($balR2, 'R2');

        // BUG: both passed the gate against the same stale balance and both debited.
        $this->assertSame(2, $debits, 'both individually-affordable redeems debited (overdraft)');

        // OVERDRAFT: 100 - 80 - 80 = -60. The current logic committed a negative wallet.
        $this->assertSame(
            -60,
            $this->balance($student),
            'CURRENT BUG: two individually-affordable redeems both debited; balance is negative (overdraft).',
        );
        $this->assertLessThan(
            0,
            $this->balance($student),
            'The read-then-debit window let spendable balance go below zero.',
        );
    }

    /**
     * Control / contrast: PURELY SEQUENTIAL redeems through the controller do NOT
     * overdraft today (each call re-reads the SUM and sees the prior debit). This is
     * documented so the verifier is honest: the defect is the CONCURRENCY window
     * above, not the serial path. (This also confirms the affordability logic is
     * otherwise correct, isolating the race as the sole overdraft vector.)
     *
     * NOTE: uses the server price via `cost` only to drive the debit amount; we are
     * exercising the affordability/serialisation logic, not Bug B here.
     */
    public function test_sequential_redeems_do_not_overdraft_today(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        $reward = ShopItem::create([
            'name' => 'مكافأة', 'type' => 'special', 'price' => 80, 'status' => 'active',
        ]);

        $ok = 0;
        for ($i = 0; $i < 2; $i++) {
            $res = $this->actingAs($student)->postJson('/student/shop/redeem', [
                'reward_id' => $reward->id,
                'cost' => 80,
            ]);
            if ($res->json('success') === true) {
                $ok++;
            }
        }

        // Sequentially the 2nd correctly fails: 1 success, balance 20, never negative.
        $this->assertSame(1, $ok, 'sequential path is safe — only the race window overdrafts');
        $this->assertSame(20, $this->balance($student));
        $this->assertGreaterThanOrEqual(0, $this->balance($student));
    }
}
