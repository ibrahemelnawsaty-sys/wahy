<?php

namespace Tests\Feature\Economy;

use App\Models\Coin;
use App\Models\User;
use App\Services\SpendService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — the SPEND primitive (SpendService::spend), the only place value
 * leaves a balance. Pins the four required guarantees:
 *  (a) no overdraft across repeated debits — second over-budget debit fails closed,
 *      balance never negative;
 *  (b) a replayed spend (same idempotency event) is charged exactly once;
 *  (c) the charge equals the SERVER-provided cost (the primitive takes a typed int; it
 *      has NO client-cost input, so a forged client price cannot reach it — the wiring,
 *      held, will pass ShopItem::price, never $request->cost);
 *  (d) the concurrency residual is named.
 *
 * Honest concurrency-residual note (d): PHPUnit is single-threaded and cannot fire two
 * physically simultaneous spends. These tests prove the DECISION logic (affordability +
 * idempotency) serially. The real two-simultaneous race is closed at the DB layer by the
 * `SELECT ... FOR UPDATE` on the spender's single `users` row inside SpendService::spend:
 * concurrent spends for one user serialize on that row, so the second re-reads the
 * already-debited SUM and fails closed. That lock is the guarantee; this suite pins that
 * the logic under it is correct.
 */
class SpendServiceTest extends TestCase
{
    use RefreshDatabase;

    private function fund(User $u, int $coins): void
    {
        Coin::create(['user_id' => $u->id, 'coins' => $coins, 'transaction_type' => 'earn', 'source' => 'test']);
    }

    private function balance(User $u): int
    {
        return (int) Coin::where('user_id', $u->id)->sum('coins');
    }

    public function test_repeated_debits_cannot_overdraft(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        // 100 / 30 = 3 affordable; each is a DISTINCT spend event (distinct source_id).
        $results = [];
        for ($i = 1; $i <= 5; $i++) {
            $results[] = SpendService::spend($student->id, 'reward_redemption', "evt-{$i}", 30, 'redeem');
        }

        $ok = count(array_filter($results, fn ($r) => $r['success'] === true));
        $this->assertSame(3, $ok, 'only 3 of 5 debits are affordable');
        $this->assertSame('insufficient_balance', $results[3]['reason']);
        $this->assertSame('insufficient_balance', $results[4]['reason']);

        $this->assertSame(10, $this->balance($student));
        $this->assertGreaterThanOrEqual(0, $this->balance($student), 'balance must NEVER go negative');
        // exactly 3 negative ledger rows + the funding row.
        $this->assertSame(3, Coin::where('user_id', $student->id)->where('coins', '<', 0)->count());
    }

    public function test_replayed_spend_is_charged_once(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        // Same event id twice (a double-submitted redeem with the same token).
        $first = SpendService::spend($student->id, 'reward_redemption', 'token-A', 30, 'redeem');
        $second = SpendService::spend($student->id, 'reward_redemption', 'token-A', 30, 'redeem');

        $this->assertTrue($first['success']);
        $this->assertFalse($first['duplicate']);
        $this->assertTrue($second['success']);
        $this->assertTrue($second['duplicate'], 'replay must be a no-op');

        $this->assertSame(70, $this->balance($student), 'charged exactly once');
        $this->assertSame(1, DB::table('award_ledger')
            ->where(['user_id' => $student->id, 'source_type' => 'reward_redemption', 'source_id' => 'token-A'])
            ->count());
        $this->assertSame(1, Coin::where('user_id', $student->id)->where('coins', '<', 0)->count(), 'one debit row');
    }

    public function test_charge_equals_the_server_cost_exactly(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        // The primitive charges exactly the server-derived cost it is given (here 90).
        // There is no client-cost parameter: $cost is a typed int the caller computes
        // from ShopItem::price. A forged client value cannot reach this charge.
        $res = SpendService::spend($student->id, 'reward_redemption', 'evt', 90, 'redeem');

        $this->assertTrue($res['success']);
        $this->assertSame(10, $this->balance($student), 'charged the server cost 90, not anything else');
        $this->assertSame(-90, (int) Coin::where('user_id', $student->id)->where('coins', '<', 0)->value('coins'));
    }

    public function test_non_positive_cost_is_rejected(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        $this->assertFalse(SpendService::spend($student->id, 'x', '1', 0)['success']);
        $this->assertFalse(SpendService::spend($student->id, 'x', '2', -5)['success']);
        $this->assertSame(100, $this->balance($student), 'no debit for a non-positive cost');
        $this->assertSame(0, Coin::where('user_id', $student->id)->where('coins', '<', 0)->count());
    }
}
