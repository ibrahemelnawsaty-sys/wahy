<?php

namespace App\Services;

use App\Models\Coin;
use Illuminate\Support\Facades\DB;

/**
 * Pass-4 Batch 2 — the single atomic, idempotent, overdraft-proof DEBIT primitive.
 *
 * Spend is the ONLY place value leaves a balance, and it is a read-then-debit against
 * a SUM(coins) balance — a classic check-then-act race that the credit path's
 * "append-only, no lock" reasoning does NOT cover. Every coin debit MUST flow through
 * this service.
 *
 * ── Concurrency control (the design verdict) ──────────────────────────────────
 * MECHANISM: lock the user's `users` row (SELECT ... FOR UPDATE) as the per-user spend
 * mutex, then read SUM(coins), check affordability, and insert the negative ledger row
 * — ALL inside ONE critical section under that lock. The affordability check and the
 * debit are therefore one indivisible step (no check-then-act gap). The `users` row is
 * chosen because it ALWAYS exists (exactly one per user) and is SHARED by every spend
 * for that user, so it is a real serialization point — unlike `Coin::...->lockForUpdate()`
 * over the SUM, which locks an empty/disjoint set on a fresh wallet and serializes
 * nothing (the live bug).
 *
 * Why NOT a dedicated `wallet_balances` row + `UPDATE ... WHERE coins >= cost`: that is
 * structurally O(1) and overdraft-proof, but it (1) needs a schema + a balance backfill
 * (a held data migration), and (2) reintroduces a denormalized balance that must be
 * dual-written on EVERY credit and debit and reconciled forever — the exact drift class
 * we just eliminated by deleting the dead users.total_points. Locking the `users` row
 * keeps SUM(coins) as the single source of truth (no second counter to drift) at the
 * cost of an O(n) SUM per spend (acceptable: spends are infrequent vs the integrity win).
 * If profiling later proves the SUM hot, wallet_balances is a clean held-schema upgrade.
 *
 * ── Idempotency ──────────────────────────────────────────────────────────────
 * The spend event claims an `award_ledger` row (the generic per-user event ledger)
 * keyed on (user_id, source_type, source_id) under the same lock; a replay finds the
 * key taken and is a TRUE no-op (charged once), mirroring AwardService on the credit
 * side. No new table is required.
 *
 * ── Server-authoritative cost ─────────────────────────────────────────────────
 * $cost is a typed int the CALLER must derive server-side (e.g. ShopItem::price). This
 * service never reads a client value, so a forged client `cost` cannot influence the
 * charge.
 *
 * ── Deadlock ordering ─────────────────────────────────────────────────────────
 * A spend locks exactly ONE row (the spender's `users` row), so no multi-row lock
 * ordering is needed and no deadlock is possible from this path.
 */
final class SpendService
{
    /**
     * Atomically and idempotently debit `cost` coins from one user.
     *
     * @param  string  $sourceType  spend family, e.g. 'shop_purchase', 'reward_redemption'
     * @param  string  $sourceId  idempotency id of the concrete spend event (item id, or a
     *                            client redemption token for repeatable redeems)
     * @param  int  $cost  SERVER-derived price (never a client value)
     * @return array{success: bool, reason: string, balance: int, duplicate: bool}
     */
    public static function spend(int $userId, string $sourceType, string $sourceId, int $cost, ?string $description = null): array
    {
        if ($cost <= 0) {
            return ['success' => false, 'reason' => 'invalid_cost', 'balance' => 0, 'duplicate' => false];
        }

        return DB::transaction(function () use ($userId, $sourceType, $sourceId, $cost, $description) {
            // (1) Serialize this user's spends on the always-present users row.
            DB::table('users')->where('id', $userId)->lockForUpdate()->first();

            // (2) Idempotency: under the lock, a replay of the same event is a no-op.
            $alreadyDone = DB::table('award_ledger')
                ->where(['user_id' => $userId, 'source_type' => $sourceType, 'source_id' => $sourceId])
                ->exists();
            if ($alreadyDone) {
                return [
                    'success' => true,
                    'reason' => 'duplicate',
                    'balance' => (int) Coin::where('user_id', $userId)->sum('coins'),
                    'duplicate' => true,
                ];
            }

            // (3) Affordability + debit in the SAME critical section (under the lock).
            $balance = (int) Coin::where('user_id', $userId)->sum('coins');
            if ($balance < $cost) {
                // No claim, no debit, no negative row — fail closed.
                return ['success' => false, 'reason' => 'insufficient_balance', 'balance' => $balance, 'duplicate' => false];
            }

            // (4) Claim the event (audit + replay guard) and write the negative ledger row.
            DB::table('award_ledger')->insert([
                'user_id' => $userId,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'points' => 0,
                'coins' => $cost, // audit: amount spent on this event
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            Coin::create([
                'user_id' => $userId,
                'coins' => -$cost,
                'transaction_type' => 'spend',
                'source' => $sourceType,
                'description' => $description,
            ]);

            return ['success' => true, 'reason' => 'charged', 'balance' => $balance - $cost, 'duplicate' => false];
        });
    }
}
