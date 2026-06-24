# Pass-4 Batch-2 — Spend Critical-Section Design (`redeemReward` / `purchaseItem`)

**Status:** DESIGN ONLY. No live controller/service is modified by this document.
**Scope:** the two student-facing SPEND paths that read a SUM balance then debit it:

- `StudentController::redeemReward` (≈ L1172–1219) — `POST /student/shop/redeem`
- `StudentController::purchaseItem`  (≈ L1311–1380) — `POST /student/shop/purchase`

Sibling spend helper `GamificationService::deductCoins` (L95–118) shares the same
flaw and the same fix; it is referenced where relevant but is also out-of-scope to
edit here.

> The credit primitive `AwardService::award` is **append-only** and correctly takes
> **no lock** (balances are `SUM`, credits never read-modify-write). That reasoning
> **does not transfer to spend.** A spend is a read-then-debit against the same
> `SUM` balance, so two concurrent spends form a classic check-then-act race and a
> debit ledger row is, economically, a *write that depends on a prior read*. Spend
> needs explicit concurrency control; credit does not. This document specifies it.

---

## 0. The two real bugs (verified against source)

### Bug A — the "wallet lock" does not actually serialize spends

All three spend sites attempt to serialize with:

```php
$bal = (int) Coin::where('user_id', $id)->lockForUpdate()->sum('coins');
```

`lockForUpdate()` on an aggregate over `coins WHERE user_id = ?` takes
`FOR UPDATE` **row locks on exactly the coin rows that match right now**. It does
**not** lock a wallet — there is no wallet row, and `SUM` has no row of its own to
lock. Consequences on MySQL/InnoDB (this project is `DB_CONNECTION=mysql`):

1. **Empty / first-spend wallets:** a user whose ledger has *zero* coin rows for the
   predicate (e.g. coins only ever arrived and the matching set is empty, or a fresh
   wallet) locks **nothing**. Two concurrent transactions both lock the empty set,
   both read `bal = 0` or both read the same positive `SUM`, both pass the
   affordability check, both `INSERT` a negative row → **overdraft**. `FOR UPDATE`
   only blocks writers on rows that *exist*; it does not reserve the "gap" the way a
   single canonical row would.
2. **Disjoint future inserts:** the lock covers existing rows, but each spend then
   `INSERT`s a *new* negative row that the other transaction never locked, so there
   is no shared row forcing serialization of the *decision*.
3. Even where InnoDB next-key/gap locks happen to help under one index, the behavior
   is index- and isolation-level dependent and must **not** be relied on for money.

> **Net:** the current lock is "load-bearing by accident." Under `REPEATABLE READ`
> (MySQL default) two simultaneous redeems can both pass and both debit. This is the
> overdraft the task flags.

### Bug B — `redeemReward` trusts a **client-supplied price**

```php
$request->validate(['reward_id' => 'required|integer', 'cost' => 'required|integer|min:1']);
$cost = (int) $request->cost;            // ← attacker controls this
// ... debits exactly $cost, and never loads the reward by reward_id at all
```

`reward_id` is validated but **never used**; the debit amount is whatever the client
posts. A student can redeem a 5000-coin reward for `cost: 1`. Price **must** be
derived server-side from `ShopItem`. (`purchaseItem` already does this correctly via
`$item->price` — `redeemReward` must be brought to the same standard.)

There is also no **redemption record** for `redeemReward`: nothing makes a retried /
double-submitted POST a no-op, and there is no audit row tying the debit to a reward.

---

## 1. Concurrency control for the spend critical section

**Chosen mechanism: (A) atomic conditional decrement against a dedicated
`wallet_balances` row — i.e. options (b)+(c) from the brief combined: a single
canonical balance row, mutated with `UPDATE … WHERE balance >= cost`.**

We adopt **one canonical, mutable balance row per user** (`wallet_balances`) and make
the spend decision *be* the write:

```sql
UPDATE wallet_balances
   SET coins = coins - :cost, updated_at = NOW()
 WHERE user_id = :uid
   AND coins  >= :cost;          -- affordability is the WHERE, not a prior SELECT
```

- **affected rows = 1** → the debit happened **and** it was affordable (the two are
  now the *same* atomic act; no check-then-act gap exists).
- **affected rows = 0** → insufficient funds (or the wallet row is missing). No
  write occurred. Return "insufficient balance." Never insert a negative ledger row.

This is the canonical money-safe primitive: the database evaluates `coins >= cost`
and applies `coins - cost` **inside a single row-level-locked statement**, so two
concurrent spends are serialized by InnoDB on the *same physical row*. The second one
re-evaluates `coins >= cost` against the *already-decremented* value and fails closed.

The **append-only `coins` ledger is retained** as the immutable audit/history log
(history UI, `coinsHistory`, reporting). The flow per spend is:

1. `UPDATE wallet_balances … WHERE coins >= :cost` (the gate + the debit).
2. If 0 rows → abort, insufficient funds.
3. If 1 row → write the matching **negative `coins` ledger row** (audit) **and** the
   **redemption/purchase record** (§3), all in the *same* `DB::transaction`.

`wallet_balances.coins` is the **source of truth for spend gating**; the `coins`
ledger `SUM` remains the source of truth for *display* and is reconciled to the
balance row (§4, invariant I3). Credits via `AwardService::award` continue to append
to the `coins` ledger **and** must `UPDATE wallet_balances SET coins = coins + :n`
(an unconditional, lock-free-by-row increment — credits never fail the `WHERE`).

> **Note on AwardService:** `AwardService.php` is shared and MUST NOT be edited here.
> The balance-row increment for *credits* is part of the eventual implementation of
> the wallet and is called out as a follow-up requirement (§6), not done in this
> design. Until the wallet row exists, the conditional-decrement form below can also
> be expressed directly over the ledger as a fallback (see §1.1) so the spend sites
> can be hardened independently of touching AwardService.

### Why this over the alternatives

| Option | Verdict | Why |
|---|---|---|
| **(A) `UPDATE … WHERE balance >= cost` on a dedicated `wallet_balances` row** | **CHOSEN** | Affordability and debit are one atomic statement on one locked row. No check-then-act window. O(1) — no `SUM` over a growing ledger on every spend. Overdraft is structurally impossible (negative-balance argument in §4). |
| (B) `lockForUpdate()` on the ledger SUM (status quo) | Rejected | Bug A: locks rows that may be empty/disjoint; does not serialize the *decision*; relies on index/isolation-specific gap-lock behavior. Also O(n) `SUM` per spend. |
| (C) `SELECT … FOR UPDATE` a synthesizable "anchor" row then debit | Rejected as primary | Works only if a guaranteed-present per-user row is locked first; that *is* `wallet_balances`, so it collapses into (A). Pure SELECT-FOR-UPDATE-then-INSERT keeps a (tiny) check-then-act window in app code; the conditional `UPDATE` removes it entirely. |

### 1.1 Migration-order fallback (no `wallet_balances` yet)

If hardening the spend sites must land **before** the `wallet_balances` table/backfill
(a schema change in the held batch), the *same guarantee* is achievable directly on
the ledger by making the **insert of the debit row itself conditional** on a freshly
locked, in-transaction balance, anchored on a row that is guaranteed to exist and to
be the *same* for every spend by this user. Concretely, lock the user's own
`users` row first to serialize that user's spends:

```php
DB::transaction(function () use ($userId, $cost) {
    // Serialize this user's spends on a row that ALWAYS exists and is shared:
    DB::table('users')->where('id', $userId)->lockForUpdate()->first();

    $balance = (int) Coin::where('user_id', $userId)->sum('coins'); // now race-free: holder of users row
    if ($balance < $cost) {
        return ['success' => false, 'message' => 'الرصيد غير كافٍ'];
    }
    // ... insert negative coin row + redemption record ...
});
```

Locking the **`users` row** (which always exists, exactly one per user) is what the
current code *intended* to do; it gives a real serialization point that the empty-set
ledger lock does not. This is the minimal, correct interim fix; `wallet_balances` +
conditional `UPDATE` is the durable form and supersedes it.

---

## 2. Server-derived price (reject client `cost`)

`redeemReward` must derive price from `ShopItem`, exactly as `purchaseItem` does:

```php
$validated = $request->validate([
    'reward_id' => 'required|integer|exists:shop_items,id',
]);
// NOTE: 'cost' is NO LONGER accepted. If present in the request it is IGNORED.

$item = ShopItem::findOrFail((int) $validated['reward_id']);
if (! $item->isAvailable()) {
    return response()->json(['success' => false, 'message' => 'هذه المكافأة غير متاحة حالياً']);
}
$cost = (int) $item->price;   // ← server-derived, authoritative
```

Rules:

- **`cost` is removed from the validation rules and never read.** The price is
  `ShopItem::price` for the validated `reward_id`. The `reward_id` is now *used*
  (previously dead).
- `reward_id` gains `exists:shop_items,id`; the item is loaded with `findOrFail`.
- `isAvailable()` is checked (status `active`, stock, time-limit) — mirrors
  `purchaseItem`.
- Price is read **inside** the transaction (or the item re-fetched there) so a
  concurrent admin price change cannot be straddled. The debit, the audit ledger row,
  and the redemption record all use this one server value.

This closes Bug B: the client can no longer influence the debit amount.

---

## 3. Redemption record (idempotent + auditable)

We make every spend **claim a unique key first**, identical in spirit to
`award_ledger` for credits. A debit may only proceed if it newly claims its key;
a replayed/double-submitted POST finds the key taken and is a **true no-op**.

### 3a. `purchaseItem` — reuse `user_purchases` as the idempotency key

`user_purchases(user_id, shop_item_id)` already models "this user owns this item,"
and shop items are one-per-user (`hasPurchased` enforces it). Make that the key:

> **Add a UNIQUE index `user_purchases(user_id, shop_item_id)`** (new migration).
> Today the "already purchased" guard is **application-level only** (`->exists()`
> under the flaky lock); two racing buys can both pass it. With the unique index, the
> *second* `attach()` throws on the constraint and the transaction rolls back —
> the debit is undone. Order inside the txn: conditional debit → `attach()` (unique) →
> stock decrement. (Equivalently, `insertOrIgnore` the pivot first and treat 0 rows
> as "already purchased," mirroring `award_ledger`.)

### 3b. `redeemReward` — new `reward_redemptions` table

Rewards may be **repeatable** (unlike one-shot shop items), so they need their own
record with a caller-supplied idempotency token, not just `(user, reward)`:

```
reward_redemptions
  id              bigint pk
  user_id         fk users    cascadeOnDelete
  shop_item_id    fk shop_items (the reward)
  cost            unsignedInteger     -- server-derived price actually charged (audit)
  idempotency_key string(64)          -- per logical redemption attempt (client token or server request id)
  created_at / updated_at
  UNIQUE (user_id, idempotency_key)   -- the replay guard
  index (user_id, shop_item_id)       -- audit / history
```

Flow (all in one `DB::transaction`):

1. `insertOrIgnore` into `reward_redemptions` keyed on
   `(user_id, idempotency_key)`. **0 rows ⇒ replay ⇒ return the prior result as a
   no-op** (do not debit again). This is the exact pattern `AwardService::award` uses
   on `award_ledger`, applied to the spend side.
2. Conditional debit (§1). If it fails (insufficient funds), the whole transaction —
   including the just-claimed redemption row — **rolls back**, so the key is freed and
   an honest later retry (after top-up) can succeed.
3. Write the negative `coins` audit row.

> **Idempotency-key choice — the #1 risk, stated explicitly.**
> *Too coarse* (`(user_id, shop_item_id)`) would make a *legitimate repeat redemption*
> of a repeatable reward a false no-op. *Too fine* (a fresh server uuid every request)
> would let a double-click double-charge. **Mandated key:** the **client-supplied
> redemption token** for the user's "press redeem" action (one token per intent,
> resent unchanged on retry), namespaced per user: `UNIQUE(user_id, idempotency_key)`.
> The client generates the token once when the redeem button is pressed and replays
> the *same* token on network retry, so retries collapse and distinct intentional
> redemptions stay distinct. If a token cannot be supplied by the client in this
> batch, the interim key is `(user_id, shop_item_id, <coarse time bucket>)` — documented
> as weaker — but the durable answer is the client token.

### Consistency boundary (mandated one-line statement)

- **`redeemReward`:** *(a) domain-write + debit atomic together.* The redemption
  record, the conditional debit, and the audit ledger row are written in **one
  `DB::transaction`** owned by the controller. There is no separate domain mark to
  reconcile; the redemption row **is** the domain write. A crash before commit rolls
  back all three (no charge, key freed); after commit all three are durable.
- **`purchaseItem`:** *(a) domain-write + debit atomic together.* Conditional debit,
  `user_purchases` insert (unique), and stock decrement are one transaction; the
  unique pivot is both the ownership record and the replay guard.

(Neither spend routes through `AwardService` — these are **debits**, which that
credit-only service rejects — so there is no cross-service transaction boundary to
reconcile, unlike the credit sites.)

---

## 4. Negative-balance impossibility argument

**Claim:** under the design of §1+§3, a committed transaction can never leave a
user's spendable balance below zero, regardless of concurrency.

Let `B` = `wallet_balances.coins` for a user (the canonical spend balance).

**Invariant I1 (non-negativity):** `B >= 0` at every committed state.

*Proof.*
- **Base:** the wallet row is created with `coins = 0` (or a non-negative backfilled
  balance, §6). `B0 >= 0`.
- **Credits:** `B := B + n`, `n > 0` (`AwardService` credits are positive). Preserves
  `B >= 0`.
- **Debits (the only decreasing step):** the *sole* way `B` decreases is
  `UPDATE wallet_balances SET coins = coins - cost WHERE user_id = ? AND coins >= cost`.
  - If the row's current `coins < cost`, the `WHERE` matches **0 rows**: no write,
    `B` unchanged, and the code path returns "insufficient funds" **without** inserting
    any negative ledger row. So no debit that would breach zero is ever applied.
  - If `coins >= cost`, exactly one row updates to `coins - cost >= 0`. Still `>= 0`.
- **Concurrency:** the `UPDATE` takes an InnoDB exclusive **row lock on the single
  `wallet_balances` row**. Concurrent spends on the same user are therefore strictly
  serialized on that row. The second `UPDATE` re-evaluates `coins >= cost` against the
  **post-first-debit** value (not a stale `SELECT`), so it can only succeed if funds
  *still* remain. There is **no check-then-act window**: the check is the `WHERE` of
  the writing statement. Two redeems that each individually fit but jointly exceed the
  balance ⇒ the first succeeds, the second sees the decremented balance and gets 0
  rows ⇒ fails closed. ∎

**Invariant I2 (idempotency ⇒ no replay double-spend):** a debit is preceded by a
claim of a unique key (`reward_redemptions(user_id, idempotency_key)` for redeem;
`user_purchases(user_id, shop_item_id)` for purchase). A replay's `insertOrIgnore`
claims 0 rows and **short-circuits before the `UPDATE`**, so a retried POST cannot
apply a second debit for the same logical intent. (And because the claim and the
debit share one transaction, a crash between them rolls back the claim — no
"charged-but-no-record" or "record-but-no-charge" state survives.)

**Invariant I3 (ledger ↔ balance reconciliation):** for every user,
`wallet_balances.coins == SUM(coins.coins)`. Maintained because every mutation of
`B` is paired, in the *same* transaction, with the corresponding ±ledger row (credit:
`+n` row via the credit path; debit: `-cost` row in the spend txn). A monitoring job
asserts I3 and alarms on drift; any drift is a bug, not an overdraft, because I1 is
guaranteed by `B` alone.

**Corollary:** with `wallet_balances` as the spend gate, even the *display* SUM can
never justify a debit that breaches zero — the gate never consults the SUM. The
status-quo failure mode (Bug A) is removed because affordability no longer depends on
locking a possibly-empty ledger set; it depends on one always-present, always-locked
balance row.

---

## 5. Overspend test (the deliverable test)

A logical/serial test that **fails against the current code** (client-cost trust +
empty-set lock) and **passes against this design**. Mirrors the honesty note in
`AwardServiceTest` (PHPUnit is single-threaded; the *physical* race is closed at the
DB layer by the conditional `UPDATE` / `users`-row lock — the serial test pins the
decision logic and the server-price rule, which is where the real bugs live).

Proposed file: `tests/Feature/Economy/SpendOverspendTest.php`

```php
<?php

namespace Tests\Feature\Economy;

use App\Models\Coin;
use App\Models\ShopItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Pass-4 Batch-2 — SPEND safety (redeemReward / purchaseItem).
 * Pins: (1) no overdraft across repeated debits, (2) server-derived price
 * (client 'cost' is ignored), (3) replayed redemption is a no-op.
 *
 * Honest race note: PHPUnit is single-threaded and cannot fire two physically
 * simultaneous redeems. These tests prove the DECISION logic and the price rule —
 * exactly where the live bugs are. The physical race is closed at the DB layer by
 * the conditional UPDATE (`... WHERE coins >= cost`) on the single wallet row.
 */
class SpendOverspendTest extends TestCase
{
    use RefreshDatabase;

    private function fund(User $u, int $coins): void
    {
        Coin::create([
            'user_id' => $u->id, 'coins' => $coins,
            'transaction_type' => 'earn', 'source' => 'test',
        ]);
        // When wallet_balances exists, also seed/backfill it here.
    }

    private function balance(User $u): int
    {
        return (int) Coin::where('user_id', $u->id)->sum('coins');
    }

    /** Repeated redeems can never push the balance below zero. */
    public function test_repeated_redeems_cannot_overdraft(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        $reward = ShopItem::create([
            'name' => 'مكافأة', 'type' => 'special', 'price' => 30, 'status' => 'active',
        ]);

        $ok = 0;
        for ($i = 0; $i < 5; $i++) {
            $res = $this->actingAs($student)->postJson('/student/shop/redeem', [
                'reward_id' => $reward->id,
                // distinct idempotency token per intent so each is a real attempt
                'idempotency_key' => "redeem-{$i}",
            ]);
            if ($res->json('success') === true) {
                $ok++;
            }
        }

        // 100 / 30 = 3 affordable; the 4th and 5th must fail closed.
        $this->assertSame(3, $ok);
        $this->assertSame(10, $this->balance($student));
        $this->assertGreaterThanOrEqual(0, $this->balance($student), 'NEVER negative');
    }

    /** Server derives price from ShopItem; a forged client 'cost' is ignored. */
    public function test_client_supplied_cost_is_ignored(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        $reward = ShopItem::create([
            'name' => 'مكافأة غالية', 'type' => 'special', 'price' => 90, 'status' => 'active',
        ]);

        $res = $this->actingAs($student)->postJson('/student/shop/redeem', [
            'reward_id' => $reward->id,
            'cost'      => 1,                 // forged — must be ignored
            'idempotency_key' => 'forge-1',
        ]);

        $res->assertOk()->assertJson(['success' => true]);
        // Charged the real 90, NOT the forged 1 → balance 10, not 99.
        $this->assertSame(10, $this->balance($student));
    }

    /** A replayed redeem (same idempotency_key) is a no-op: charged once. */
    public function test_replayed_redemption_is_idempotent(): void
    {
        $student = User::factory()->student()->create();
        $this->fund($student, 100);

        $reward = ShopItem::create([
            'name' => 'مكافأة', 'type' => 'special', 'price' => 30, 'status' => 'active',
        ]);

        $payload = ['reward_id' => $reward->id, 'idempotency_key' => 'same-token'];

        $first  = $this->actingAs($student)->postJson('/student/shop/redeem', $payload);
        $second = $this->actingAs($student)->postJson('/student/shop/redeem', $payload);

        $first->assertJson(['success' => true]);
        $second->assertJson(['success' => true]); // replay returns prior result, not a new charge

        // Charged exactly once.
        $this->assertSame(70, $this->balance($student));
        $this->assertSame(1, DB::table('reward_redemptions')
            ->where('user_id', $student->id)->where('idempotency_key', 'same-token')->count());
    }
}
```

> Against today's code, `test_client_supplied_cost_is_ignored` fails (it would charge
> 1 → balance 99) and `test_replayed_redemption_is_idempotent` fails (no redemption
> table; double charge). `test_repeated_redeems_cannot_overdraft` passes *serially*
> today but is the regression guard for the concurrency fix; the physical-race
> guarantee is the DB-level conditional `UPDATE`, as noted.

---

## 6. Implementation checklist (for the eventual live change — NOT done here)

1. **Migration:** `wallet_balances (user_id pk/unique, coins unsignedInteger default 0,
   points unsignedInteger default 0, timestamps)`; **backfill** each from the existing
   `SUM(coins)` / `SUM(points)` (data mutation → belongs with the held schema batch).
2. **Migration:** UNIQUE `user_purchases(user_id, shop_item_id)`.
3. **Migration:** `reward_redemptions` table per §3b (UNIQUE `(user_id, idempotency_key)`).
4. **`redeemReward`:** drop `cost` from validation; load `ShopItem` by `reward_id`;
   `isAvailable()`; claim `reward_redemptions` (insertOrIgnore); conditional debit
   (`UPDATE wallet_balances … WHERE coins >= cost`, or the §1.1 `users`-row-lock
   fallback until the wallet exists); write negative `coins` audit row; all in one txn.
5. **`purchaseItem`:** replace the `lockForUpdate()->sum()` gate with the conditional
   `UPDATE` (or `users`-row lock); rely on the new UNIQUE pivot as the replay guard.
6. **Credit path (follow-up, NOT in this batch, do NOT edit `AwardService.php` here):**
   when `wallet_balances` lands, every credit must also
   `UPDATE wallet_balances SET coins = coins + n` in the award transaction to keep I3.
7. **`GamificationService::deductCoins`:** same conditional-decrement fix (same Bug A).
8. **Reconciliation job:** assert invariant I3 (`wallet_balances == SUM(ledger)`) and
   alarm on drift.

---

## 7. Summary of guarantees

| Requirement | Mechanism | Result |
|---|---|---|
| No concurrent overdraft | `UPDATE wallet_balances SET coins=coins-cost WHERE coins>=cost` on one locked row (interim: `users`-row `lockForUpdate`) | Affordability = the write; second racer fails closed (§1, §4-I1) |
| Server-derived price | Load `ShopItem` by `reward_id`; ignore/remove client `cost` | Forged `cost` cannot change the charge (§2) |
| Idempotent + auditable spend | `reward_redemptions(user_id, idempotency_key)` + UNIQUE `user_purchases(user_id, shop_item_id)`, claimed before debit in the same txn | Replayed POST is a true no-op; every debit has an audit row (§3) |
| Negative balance impossible | Conditional decrement + non-negativity invariant I1 | `B >= 0` at every committed state, under any interleaving (§4) |
