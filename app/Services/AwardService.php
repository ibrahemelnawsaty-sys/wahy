<?php

namespace App\Services;

use App\Models\Coin;
use App\Models\Point;
use App\Models\User;
use App\Services\Activity\PointsDistributionService;
use Illuminate\Support\Facades\DB;

/**
 * Pass-4 Batch 2 — the single, atomic, idempotent award primitive.
 *
 * Every points/coins CREDIT must flow through this service so a duplicate award
 * (re-grade, replayed POST, retry) can never double-credit.
 *
 * ── Idempotency (requirement #1) ──────────────────────────────────────────────
 * award() does insertOrIgnore into award_ledger keyed on
 * (user_id, source_type, source_id). insertOrIgnore returning 0 (the row already
 * exists) SHORT-CIRCUITS before any Point/Coin write — the duplicate call is a true
 * no-op: ledger row count stays 1, no Point/Coin row is added, nothing else changes.
 *
 * ── Transaction boundary (requirement #2) ─────────────────────────────────────
 * award() OWNS its DB::transaction and is self-contained. Callers MUST NOT pre-open
 * a transaction merely to wrap an award — the award is its own atomic unit. When a
 * caller must keep OTHER writes atomic with the award (e.g. marking a submission
 * graded + awarding), it should perform those writes and THEN call award(); the
 * award's ledger claim is what makes the economy effect exactly-once even if the
 * surrounding flow is retried. distribute:true fans out to teacher/parent/school
 * INSIDE this same transaction (PointsDistributionService::distributeWithin, which
 * throws rather than swallows), so a downstream failure rolls back the student award
 * too — no partial fan-out.
 *
 * ── Locking / deadlocks (requirement #3) ──────────────────────────────────────
 * Credits are APPEND-ONLY (we never read a balance then write it back — balances are
 * SUM(points)/SUM(coins)). There is therefore no read-modify-write race and NO row
 * lock is taken, so two concurrent awards (even to the same or to two different users)
 * cannot deadlock. The UNIQUE(user_id, source_type, source_id) index is the sole
 * idempotency mechanism. (Deterministic lock ordering only matters for SPEND/debit
 * paths — purchaseItem/redeemReward — which read-then-debit a balance and are OUT OF
 * SCOPE for this credit primitive.)
 *
 * ── Denormalized counter ──────────────────────────────────────────────────────
 * users.total_points is intentionally NOT written: it is a dead/stale column (nothing
 * in the codebase maintains it; balances are computed as SUM). Reviving it as a live
 * counter needs a backfill (a data mutation) which belongs to the held schema batch.
 */
final class AwardService
{
    /**
     * Atomically and idempotently credit points and/or coins to one user.
     *
     * @param  string  $sourceType  stable event family, e.g. 'activity_submission', 'practice_attempt', 'pvp_match'
     * @param  string  $sourceId  stable id of the concrete event (the domain row PK), e.g. (string) $submission->id
     * @param  bool  $distribute  also fan out teacher/parent/school points for this student event (same transaction)
     * @return bool true if newly awarded; false if it was already awarded (idempotent no-op)
     */
    public static function award(
        int $userId,
        string $sourceType,
        string $sourceId,
        int $points = 0,
        int $coins = 0,
        ?string $description = null,
        bool $distribute = false,
    ): bool {
        // Credit-only primitive: nothing to do for a non-positive award. A negative
        // amount is never written (that would be a spend, which this service rejects).
        if ($points <= 0 && $coins <= 0) {
            return false;
        }

        return DB::transaction(function () use ($userId, $sourceType, $sourceId, $points, $coins, $description, $distribute) {
            // (1) Claim the idempotency key FIRST. 0 rows affected => already awarded.
            $claimed = DB::table('award_ledger')->insertOrIgnore([
                'user_id' => $userId,
                'source_type' => $sourceType,
                'source_id' => $sourceId,
                'points' => max(0, $points),
                'coins' => max(0, $coins),
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // SHORT-CIRCUIT before any Point/Coin write or distribution.
            if (! $claimed) {
                return false;
            }

            if ($points > 0) {
                Point::create([
                    'user_id' => $userId,
                    'points' => $points,
                    'source' => $sourceType,
                    'description' => $description,
                ]);
            }

            if ($coins > 0) {
                Coin::create([
                    'user_id' => $userId,
                    'coins' => $coins,
                    'source' => $sourceType,
                    'transaction_type' => 'earn',
                    'description' => $description,
                ]);
            }

            if ($distribute) {
                app(PointsDistributionService::class)->distributeWithin(
                    User::findOrFail($userId),
                    $points,
                    $sourceType,
                    (string) ($description ?? ''),
                );
            }

            return true;
        });
    }
}
