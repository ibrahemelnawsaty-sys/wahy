<?php

namespace Tests\Feature\Economy;

use App\Models\Coin;
use App\Models\ShopItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch 2 — SPEND safety, INVERTED. This file previously asserted the live
 * overspend/forged-cost bugs (proof-of-defect). After wiring redeemReward/purchaseItem
 * through SpendService and dropping the client cost, the SAME scenarios now assert the
 * bugs are IMPOSSIBLE — end-to-end through the real controller (the Major was that the
 * CONTROLLER trusted client cost, so these hit the endpoint, not just the service).
 *
 * Honest residual note: the physical two-simultaneous race is closed at the DB layer by
 * SpendService's `SELECT ... FOR UPDATE` on the spender's single `users` row; these
 * single-threaded tests pin the decision logic and the server-price rule, where the
 * live bugs were.
 */
class SpendOverspendTest extends TestCase
{
    use RefreshDatabase;

    private function fund(User $u, int $coins): void
    {
        Coin::create(['user_id' => $u->id, 'coins' => $coins, 'transaction_type' => 'earn', 'reason' => 'test funding']);
    }

    private function balance(User $u): int
    {
        return (int) Coin::where('user_id', $u->id)->sum('coins');
    }

    /** Bug B closed: a forged client `cost` is ignored; the server ShopItem price is charged. */
    public function test_forged_client_cost_is_ignored_server_price_wins(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);
        $reward = ShopItem::create(['name' => 'مكافأة', 'type' => 'special', 'price' => 90, 'status' => 'active']);

        $res = $this->actingAs($student)->postJson('/student/shop/redeem', [
            'reward_id' => $reward->id,
            'cost' => 1,                       // forged — must be ignored entirely
            'idempotency_key' => 'k1',
        ]);

        $res->assertOk()->assertJson(['success' => true]);
        // Charged the server 90, NOT the forged 1 → balance 10.
        $this->assertSame(10, $this->balance($student));
    }

    /** Forged cost cannot buy an unaffordable reward: server price gates it, balance untouched. */
    public function test_forged_cost_cannot_drain_unaffordable_reward(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);
        $reward = ShopItem::create(['name' => 'غالية', 'type' => 'special', 'price' => 9000, 'status' => 'active']);

        $res = $this->actingAs($student)->postJson('/student/shop/redeem', [
            'reward_id' => $reward->id, 'cost' => 1, 'idempotency_key' => 'k2',
        ]);

        $res->assertOk()->assertJson(['success' => false]); // server price 9000 > 100 → rejected
        $this->assertSame(100, $this->balance($student), 'forged cost cannot drain an unaffordable reward');
    }

    /** A double-submitted redeem (same idempotency token) is charged exactly once. */
    public function test_double_submitted_redeem_is_charged_once(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);
        $reward = ShopItem::create(['name' => 'مكافأة', 'type' => 'special', 'price' => 30, 'status' => 'active']);

        $payload = ['reward_id' => $reward->id, 'idempotency_key' => 'same-token'];
        $first = $this->actingAs($student)->postJson('/student/shop/redeem', $payload);
        $second = $this->actingAs($student)->postJson('/student/shop/redeem', $payload);

        $first->assertOk()->assertJson(['success' => true]);
        $second->assertOk()->assertJson(['success' => true, 'duplicate' => true]); // replay = no-op
        $this->assertSame(70, $this->balance($student), 'charged exactly once');
        $this->assertSame(1, DB::table('award_ledger')
            ->where(['user_id' => $student->id, 'source_type' => 'reward_redemption', 'source_id' => 'same-token'])
            ->count());
    }

    /** Bug A closed: repeated redeems can never push the balance below zero. */
    public function test_sequential_redeems_cannot_overdraft(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);
        $reward = ShopItem::create(['name' => 'مكافأة', 'type' => 'special', 'price' => 80, 'status' => 'active']);

        $ok = 0;
        for ($i = 0; $i < 2; $i++) {
            $res = $this->actingAs($student)->postJson('/student/shop/redeem', [
                'reward_id' => $reward->id, 'idempotency_key' => "redeem-{$i}",
            ]);
            if ($res->json('success') === true) {
                $ok++;
            }
        }

        // 100 / 80: first succeeds (balance 20), second is insufficient → 1 success, never negative.
        $this->assertSame(1, $ok);
        $this->assertSame(20, $this->balance($student));
        $this->assertGreaterThanOrEqual(0, $this->balance($student), 'balance never negative');
    }
}
