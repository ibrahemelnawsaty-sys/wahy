# Wahy Platform — Pass-4 Consolidated Security & Correctness Audit

**Date:** 2026-06-24 · **Target:** Wahy (وحي) — Laravel 12 / PHP 8.2 multi-role education platform
**Scope:** Server-side logic, security (OWASP Top 10 2021), data integrity, performance, error handling. UI/theme/branding/architecture were covered by three prior passes and are **out of scope** here.

> **How to read this report.** Findings are grouped by **root cause**, not by file. Each Blocker/Major cluster gets full treatment (vulnerable code → fix-as-code → regression test → remediation tag). Minor/Nitpick are in the Appendix table (`file:line` + one-line fix). Every remediation item is tagged **[mechanical]** (safe, behavior-preserving) or **[behavior-changing]** (alters runtime semantics — scrutinize before approving). Nothing here has been applied; this report is for authorization.

---

## 1. Methodology & agent census

Three independent waves, each **two-tier**: specialist **worker** agents discover, then an **adversarial skeptic** re-reads the cited code per finding and tries to refute it (default stance: false-positive). Only independently-reproduced findings are reported. Pipelined: verification began the instant each worker reported.

| Wave | Lens | Workers | Total agents | Tool-uses | Raw → Confirmed | False-pos ruled out |
|---|---|---|---|---|---|---|
| 1 | 10 OWASP/quality dimensions | 10 | 52 | 773 | 42 → 41 | 1 |
| 2 | Crown-jewel files, line-by-line | 10 | 82 | 883 | 71 → 58 | 13 |
| 3 | **Full-tree** line-by-line (every remaining file) | 72 | 378 | 4,588 | 300 → 269 | 31 |
| **Total** | | **92** | **512** | **6,244** | **413 → 368** | **45** |

45 candidate findings were adversarially **rejected** as false positives (e.g. an apparent `submitActivity` double-award is actually blocked by the `ActivitySubmission::updating` model hook that `abort(403)`s student status-changes inside the transaction). That rejection rate is the audit's quality signal.

## 2. Confirmed findings — grand rollup

**368 confirmed** (pre-dedup, across waves) + **2 `needs_context`** (held for your judgment, §8):

| Severity | Count |
|---|---|
| 🔴 Blocker | 14 (→ **~6 distinct root causes** after dedup) |
| 🟠 Major | 94 |
| 🟡 Minor | 197 |
| ⚪ Nitpick | 62 |

Several issues were **independently confirmed by multiple waves** — a confidence signal, noted per cluster (e.g. the team-membership IDOR: W1+W3×2; `submitExercise` farming: W2+W3; `submitReview` double-award: W2+W3; the XSS sanitizer: W1+W3).

## 3. Coverage matrix — zero unreviewed files

286 in-scope PHP files; **every one owned by ≥1 wave (0 gaps).** W1 is a dimension lens over the high-risk subset; W2+W3 are file/range-scoped and together span the entire tree.

| Category | Files | Coverage |
|---|---|---|
| Controllers (incl. Admin/Api) | 45 | W2 full (6) · W2-methods+W3-ranges (3 giants) · W3 (rest, Teacher 3-way split) |
| Models | 50 | W3 (10 domain groups) |
| Middleware | 10 | W3 (2 groups) |
| Form Requests / Policies / Resources | 4 / 4 / 2 | W3 |
| Services / Actions / Support / Helpers | 6 / 1 / 1 / 1 | W2 (4 economy svcs) · W3 (rest) |
| Providers / bootstrap / Exceptions | 2 / 1 / 1 | W3 |
| Events / Listeners / Notifications / Mail | 6 / 5 / 1 / 10 | W3 |
| Console commands / Enums / Livewire / View | 4 / 1 / 1 / 2 | W3 |
| Exports / Imports | 7 / 2 | W2 (2 touched) · W3 (all) |
| Routes (web/api/console) + Config | 3 + 21 | W3 |
| Migrations | 95 | W3 (8 slices) |
| **Total in-scope** | **286** | **286 covered — 0 gaps** |

**Deliberately deferred** to the remediation test-coverage phase (not line-reviewed by design): factories (10), seeders (16), tests (19).

## 4. Root-cause clusters (confirmed, all waves)

| # | Cluster | 🔴 | 🟠 | 🟡 | ⚪ | Total | Central remediation |
|---|---|---|---|---|---|---|---|
| 01 | **Economy integrity** (idempotency, atomicity, trusted client amounts) | 5 | 24 | 24 | 6 | 59 | One atomic+idempotent award primitive + DB unique constraint |
| 02 | **Broken object-level authz** (IDOR/BOLA, tenant scoping, escalation) | 3 | 14 | 10 | 2 | 29 | Ownership/tenant scoping helper + policies |
| 03 | **Stored XSS / output sanitization** | 2 | 6 | 4 | 2 | 14 | Replace regex blocklist with allowlist sanitizer |
| 04 | **Mobile API non-functional** (wrong columns/relations → 500s) | 3 | 3 | 6 | 0 | 12 | Correct identifiers + tests |
| 05 | **Sensitive-data exposure** | 0 | 1 | 10 | 1 | 12 | `$hidden` + API Resources |
| 06 | **Auth / session / 2FA / throttle** | 0 | 12 | 15 | 2 | 29 | Throttle, lockout redesign, 2FA enforcement |
| 07 | **Error handling / info leakage** | 1 | 1 | 10 | 2 | 14 | Centralized exception render |
| 08 | **File upload / path traversal / RCE** | 0 | 12 | 10 | 6 | 28 | Upload validation, allowlist, CSV-injection escape |
| 09 | **N+1 / performance** | 0 | 5 | 11 | 5 | 21 | Eager loading, pagination, DB aggregates |
| 10 | **Mass-assignment / validation** | 0 | 2 | 5 | 7 | 14 | `$fillable` tightening, Form Requests |
| 11 | **Config / secrets** | 0 | 0 | 3 | 1 | 4 | Config hardening (`.env` documented-only) |
| 12 | **Schema / migrations** | 0 | 5 | 30 | 9 | 44 | **Schema batch (approval-required)** |
| 13 | Misc correctness | 0 | 9 | 59 | 19 | 87 | Per-item (Appendix) |
| | **Total** | **14** | **94** | **197** | **62** | **367** | |

---

## 5. Blocker roster (deduped — 6 root causes)

| # | Root cause | Files | Waves | Tag |
|---|---|---|---|---|
| B1 | **Economy non-idempotency/atomicity** — `submitReview`, `submitExercise`, `PointsDistributionService`, `awardStudentPoints` re-award/partial-award; double-spend & unlimited farming | TeacherController, StudentController, PointsService, PointsDistributionService | W1+W2+W3 | [behavior-changing] |
| B2 | **Team-membership IDOR/BOLA** — `storeTeam`/`updateTeam` insert arbitrary `user_id`s (no school/role/classroom scope), then `gradeTeamActivity` mints points into foreign accounts | TeacherController:996-1135 | W1+W3×2 | [behavior-changing] |
| B3 | **Stored XSS** — `safe_html()` regex blocklist is bypassable; renders raw cross-role authored content via `{!! !!}` | SettingsHelper:181-206 | W1+W3 | [behavior-changing] |
| B4 | **Mobile API dead** — wrong column (`amount`) & relations (`streaks`, `meanings`) → guaranteed HTTP 500 on dashboard/valuesTree/leaderboard | Api/StudentApiController | W3 | [mechanical]\* |
| B5 | **`.env` exposed over HTTP** via root `.htaccess` (DB/SMTP password, `APP_KEY`) | .htaccess:1-15 | W1 | **DOCUMENTED-ONLY** (server/document-root; you handle outside the codebase) |
| B6 | *(rolled into B1)* `PointsDistributionService.distribute()` no idempotency key | PointsDistributionService:34-43 | W2 | [behavior-changing] |

\* B4 is a pure identifier correction (no design change), but the endpoints currently **500**, so output changes from error→data — flagged for your awareness.

---

## 6. Blocker / Major treatment by cluster

### Cluster 01 — Economy integrity 🔴×5 🟠×24  ·  [behavior-changing]  ·  **fix once, centrally**

**Root cause.** Every points/coins mutation is hand-rolled: no wrapping transaction, no row lock, no idempotency key, and the denormalized `users.points` column drifts from the `points` ledger table. Confirmed members include: `submitReview` re-awards on every call (TeacherController:172-267), `submitExercise` re-awards + ignores `max_attempts` (StudentController:1866-1951), `PointsDistributionService.distribute()` no idempotency (34-43), `awardStudentPoints` 4 un-transacted writes + never updates `users.points` (PointsService:35-82), PvP payout non-atomic `Cache::has/put` double-payout (StudentController:2167-2200), `gradeTeamActivity` check-then-act (TeacherController:1262-1300), `sendGift` daily-cap outside lock (ParentController:416-444), `redeemReward`/shop trust client-supplied cost (StudentController:1148-1194), `time_taken=0` wins PvP ties (PvpMatch:42-50), `awardParent` only first parent (PointsDistributionService:83-99), `awardTeacher` arbitrary classroom (48-71).

**Central fix (code-level guard).** Introduce ONE primitive and route every mutation through it:

```php
// app/Services/AwardService.php  (NEW)
final class AwardService
{
    /**
     * Atomic, idempotent award. Idempotency key = (user_id, source_type, source_id).
     * Returns false (no-op) if this exact award was already granted.
     */
    public static function award(
        int $userId, string $sourceType, string $sourceId,
        int $points = 0, int $coins = 0, ?string $description = null,
        bool $distribute = false
    ): bool {
        return DB::transaction(function () use ($userId,$sourceType,$sourceId,$points,$coins,$description,$distribute) {
            // 1) lock the wallet row to serialize concurrent awards to this user
            $user = User::whereKey($userId)->lockForUpdate()->first();
            if (!$user || $user->role !== 'student') return false;

            // 2) claim the idempotency key atomically; insertOrIgnore hits the UNIQUE index
            $claimed = DB::table('award_ledger')->insertOrIgnore([
                'user_id' => $userId, 'source_type' => $sourceType, 'source_id' => $sourceId,
                'points' => $points, 'coins' => $coins,
                'created_at' => now(), 'updated_at' => now(),
            ]);
            if (!$claimed) return false; // already awarded -> idempotent no-op

            // 3) write ledgers + keep denormalized columns in sync IN THE SAME TX
            if ($points) { Point::create(['user_id'=>$userId,'points'=>$points,'source'=>$sourceType,'description'=>$description]); $user->increment('points', $points); }
            if ($coins)  { Coin::create(['user_id'=>$userId,'coins'=>$coins,'source'=>$sourceType,'description'=>$description]); }

            // 4) downstream distribution runs INSIDE this tx (not after commit)
            if ($distribute) PointsService::distributeWithin($userId, $points, $sourceType, $description);
            return true;
        });
    }
}
```

Call sites become, e.g.:
```php
// TeacherController::submitReview  — source_id is the submission, so a re-grade is a no-op
AwardService::award($submission->student_id, 'activity_submission', (string) $submission->id,
    $activity->points, $activity->coins, "تصحيح: {$activity->title}", distribute: true);

// StudentController::redeemReward — derive price from the DB, never the client
$reward = ShopItem::findOrFail($request->reward_id);
abort_if($user->coins_balance < $reward->cost, 422);
// ... debit inside AwardService-style locked transaction, source 'redeem:'.$reward->id
```

**Central fix (DB-level guard) — schema batch, approval-required (§7).** A UNIQUE constraint makes a double-award *structurally impossible* even if a call site is missed:
```php
Schema::create('award_ledger', function (Blueprint $t) {
    $t->id();
    $t->foreignId('user_id')->constrained()->cascadeOnDelete();
    $t->string('source_type', 64);
    $t->string('source_id', 64);
    $t->unsignedInteger('points')->default(0);
    $t->unsignedInteger('coins')->default(0);
    $t->timestamps();
    $t->unique(['user_id', 'source_type', 'source_id']); // ← the real guarantee
});
```

**Regression tests** (honest limits per directive #8):
```php
// idempotency: a second identical award does NOT add a second ledger row
public function test_award_is_idempotent_on_resubmit() {
    AwardService::award($student->id, 'activity_submission', '42', 50, 10);
    AwardService::award($student->id, 'activity_submission', '42', 50, 10); // duplicate
    $this->assertSame(1, DB::table('award_ledger')->where(['user_id'=>$student->id,'source_type'=>'activity_submission','source_id'=>'42'])->count());
    $this->assertSame(50, (int) Point::where('user_id',$student->id)->sum('points'));
}
// balance floor: a purchase cannot drive coins negative
public function test_redeem_cannot_overspend() { /* assert 422 + balance unchanged */ }
```
**Residual risk (stated honestly):** PHPUnit cannot deterministically reproduce two *truly simultaneous* requests, so these tests prove **logical** idempotency and the balance floor, not the physical race. The **`UNIQUE(user_id,source_type,source_id)` constraint + `lockForUpdate`** are what close the race at the DB layer; the test suite documents this and asserts the no-op path the constraint produces.

**Integration note (§9):** `AwardService` runs on the teacher-review path (allowed by the `ActivitySubmission::updating` `abort(403)` hook, which only blocks *student* status edits). Verify the hook still fires for student resubmits after the refactor.

---

### Cluster 02 — Broken object-level authorization 🔴×3 🟠×14  ·  [behavior-changing]

**Root cause.** Role middleware (`role:*`, `school.access`) gates the *requester* but controllers don't re-check that the *target object* belongs to them. `exists:users,id` validates existence, not ownership.

**B2 — Team injection (Blocker).** `storeTeam`/`updateTeam` (TeacherController:996-1135) accept `leader_id`/`member_ids` validated only with `exists:users,id`, then insert them into `team_members`; `gradeTeamActivity` later mints XP/coins to all members → cross-tenant IDOR **and** economy mint.
```php
// FIX: constrain submitted ids to this teacher's school + role student (+ ideally the classroom)
$classroom = Classroom::where('id',$validated['classroom_id'])->where('teacher_id',$user->id)->firstOrFail();
$allowed = User::where('school_id',$user->school_id)->where('role','student')
    ->whereIn('id', array_merge([$validated['leader_id']], $validated['member_ids']))->pluck('id');
abort_unless($allowed->contains($validated['leader_id']), 422, 'قائد غير صالح');
$memberIds = $allowed->intersect($validated['member_ids'])->push($validated['leader_id'])->unique();
// insert only $memberIds — never the raw request ids
```
Other confirmed members (Major): `practice start/submit` no ownership (StudentController:1841-1951), `lesson/{id}` no school-visibility (463-469), `submitExercise/startExercise` IDOR (1841-1869), `joinPvpMatch` ignores school scoping (2024), parent `child/{id}` paths, BulkMessage null-`school_id` IS-NULL cross-leak (BulkMessageController:103-208), `RoleSwitchController` (verify target role ∈ assigned roles).

**Fix pattern.** A reusable scoped-finder on the `ScopedToSchool` concern + Policies for the high-traffic models, e.g.:
```php
protected function studentInMySchool(int $id): User {
    return User::where('school_id', auth()->user()->school_id)->where('role','student')->findOrFail($id);
}
```
**Regression test:** actor from school A requests object owned by school B → assert `403/404`; owner → `200`.

---

### Cluster 03 — Stored XSS / output sanitization 🔴×2 🟠×6  ·  [behavior-changing]

**Root cause.** `safe_html()` (SettingsHelper:181-206) is a **regex blocklist**. Blocklists are bypassable — e.g. `<a href="jav&#x09;ascript:alert(1)">` (HTML entity hides `javascript:` from the regex; the browser decodes and executes), or non-listed handler tags. Output is rendered raw via `{!! safe_html(...) !!}` on cross-role authored content (messages, bulk inbox, lessons, activities).

**Fix.** Replace with an **allowlist** sanitizer (HTMLPurifier via `mews/purifier`) — a dependency addition (composer require → [behavior-changing] infra):
```php
function safe_html(?string $html): string {
    if ($html === null || $html === '') return '';
    return clean($html, [
        'HTML.Allowed' => 'p,br,b,strong,i,em,u,ul,ol,li,a[href|title],blockquote,code,pre',
        'URI.AllowedSchemes' => ['http'=>true,'https'=>true,'mailto'=>true],
    ]);
}
```
**Defence-in-depth:** sanitize on **input** (store clean) as well as output, so a missed `{!! !!}` sink can't render a stored payload. **Regression test:** feed the entity-encoded `javascript:` and handler-tag payloads; assert the rendered output contains no executable vector.

---

### Cluster 04 — Mobile API non-functional 🔴×3 🟠×3  ·  [mechanical]\*

**Root cause.** `Api/StudentApiController` was written against wrong identifiers; the endpoints throw on every call.
```php
// dashboard() lines 51-52, leaderboard() 352/369/382 — column is points/coins, NOT amount:
'total_points' => $user->points()->sum('points'),   // was sum('amount')  -> SQL error
'total_coins'  => $user->coins()->sum('coins'),      // was sum('amount')
->withSum('points', 'points')->orderBy('points_sum_points','desc')   // was 'amount'
// dashboard() line 56 — relation is singular:
'current_streak' => $user->streak()->latest()->first()?->current_streak ?? 0,  // was streaks()
// valuesTree() lines 106,119 — Concept has no 'meanings' relation:
Value::with(['concepts.lessons'])->get();            // was concepts.meanings.lessons
'lessons_count' => $concept->lessons->count(),       // was meanings_count/meanings
```
\*Pure name corrections, but the endpoints currently 500 → output changes error→data. **Regression test:** authenticated student hits each endpoint → assert `200` + schema. (These also belong in the test-coverage gap list — the whole mobile API has **zero tests**, which is why three fatal bugs shipped.)

---

### Clusters 05–10 — Major treatment summary

> Full per-finding tables (file:line) are in the Appendix; the central fix per cluster:

- **05 Data exposure 🟠×1 (+10 Minor)** [mechanical] — message JSON endpoints serialize the full sender `User` (email/phone/birth_date) to recipients (MessagesController:268,343-398). Fix: `$hidden` on User for sensitive columns **and** return a narrow API Resource/array, never the raw model.
- **06 Auth/session/2FA/throttle 🟠×12** [behavior-changing] — (a) account-lockout DoS keyed on victim user-id (AuthController:41-92) → key the *rate* on IP+hashed-email, never a remote-drivable hard per-account lock; (b) API login no per-credential throttle/2FA (AuthApiController:55-96); (c) password-reset user-enumeration via `exists` rule (AuthController:530-538); (d) `Force2FAForAdmins` registered but applied to no route; (e) public-registration POSTs unthrottled (web.php:83-90); (f) API `changePassword` doesn't revoke sibling tokens. Fix: Laravel RateLimiter + Fortify-style throttle, apply 2FA middleware, neutral reset responses, add `throttle:` to registration routes.
- **07 Error handling 🔴×1\* 🟠×1 (+10 Minor)** [mechanical] — raw `$e->getMessage()` returned to users across ~20 controllers; scheduler failure callback calls an undefined `error()` (routes/console.php:27-29) — itself crashes the failure handler. Fix: centralized exception render (Laravel 12 `bootstrap/app.php` `->withExceptions()`), generic user messages + logged detail. (\*the XSS sanitizer was the cluster-07 "Blocker" mis-bucketed by keyword; the true error-handling items are Major and below.)
- **08 Upload/traversal/RCE 🟠×12** [behavior-changing] — `restoreBackup` copies arbitrary uploaded ZIP files into web-served `public/uploads` **and** executes arbitrary SQL from the zip with no transaction (SuperAdminController:218-313) → webshell RCE; `ActivityManagementController::uploadImage` allows unsanitized SVG into a public path (181-188); CSV/formula-injection unescaped in all 7 exports; `BulkUsersImport` hard-codes password `123456` and loads the whole file into memory (no row cap). Fix: strict mime/extension allowlist (no SVG/active types), randomized filenames, validate backup archive contents, escape `=+-@` leading cells, chunked import with row cap, random per-user passwords.
- **09 N+1/perf 🟠×5 (+11 Minor)** [mechanical] — student/teacher dashboards lazy-load `lesson->activities`/`submission->activity->lesson`; `checkAllNewMessages` runs a per-conversation query on a 5-second poll (MessagesController:363-393); analytics issues 30+8 COUNT queries in loops (TeacherController:1473-1526); exports unbounded `->get()`. Fix: `with()` eager loads, grouped DB aggregates, pagination.
- **10 Mass-assignment/validation 🟠×2 (+5 Minor)** [behavior-changing] — shop redeem trusts client cost; `Point::create` with `reference_type/reference_id` silently dropped (not in `$fillable`). Fix: derive prices server-side, tighten `$fillable`, add Form Requests.

---

## 7. Remediation batch plan (proposed order)

**Batch 0 — Version control (pre-approved, runs first).** `git init`; `.gitignore` excludes `/vendor /node_modules .env .env.* /storage/*.key` + build artifacts; verify `.env` not staged; baseline commit `"baseline before pass-4 remediation"`. Then one commit per batch.

| Batch | Clusters | Tag | Gate |
|---|---|---|---|
| 1 | **Mechanical safe fixes** — 04 (API identifiers), 05 (`$hidden`/Resources), 07 (centralized exception render), 09 (eager-load/pagination), 08-CSV-escape | [mechanical] | `composer ci` green + adjacent-fix check |
| 2 | **Central economy primitive** — 01 (AwardService + route all call sites) | [behavior-changing] | ci green; idempotency+balance-floor tests |
| 3 | **Object-level authz** — 02 (scoped finders, team scoping, role-switch) | [behavior-changing] | ci green; cross-tenant 403 tests |
| 4 | **Auth hardening** — 06 (throttle, lockout redesign, 2FA, enumeration) | [behavior-changing] | ci green |
| 5 | **Upload/RCE** — 08 (allowlist, restoreBackup, import passwords) | [behavior-changing] | ci green |
| 6 | **XSS sanitizer** — 03 (HTMLPurifier; adds dependency) | [behavior-changing] | ci green |
| **S** | **SCHEMA — approval-required, never auto-applied** — `award_ledger` unique constraint, missing indexes/FKs, float→decimal for money/points (cluster 12) | [behavior-changing] | each migration reversible (`down()`), dry-run on a DB copy, **explicit approval** |

After **every** batch: full `composer ci` (Pint → Larastan → PHPUnit) green **and** a one-line confirmation the batch didn't break an adjacent confirmed-fix. Nothing is Done until ci is green.

## 8. `needs_context` bucket — held for your judgment (never auto-fixed)

2 items depend on runtime facts I can't determine statically. Each is listed in the Appendix with the exact deciding question (e.g. "is route X actually reachable by role Y in production config?"). These wait for your call.

## 9. Locked exclusions

- **`.env` / `.env.*` / `.env.testing`** — absolutely untouched. No key rotation, no `key:generate`, no secret edits. The `.htaccess`→`.env` exposure (B5) and the `DB_PASSWORD==MAIL_PASSWORD` reuse remain **documented findings with recommended fix only**; you resolve them at the server/document-root level.

---

*Appendix (auto-generated): Blocker/Major member tables per cluster, the full Minor/Nitpick table (`file:line` + finding), and the `needs_context` items follow below.*

---

# Appendix

## A. Blocker / Major findings by cluster

### 01-economy  (29)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Blocker | W3 | app/Http/Controllers/StudentController.php:1866-1951 | submitExercise re-awards points every POST and never re-checks max_attempts â€” unlimited point farming |
| Blocker | W2 | app/Http/Controllers/StudentController.php:1866-1951 (esp. 1919-1948); compare guard at 1847-1854 | submitExercise never re-enforces max_attempts â€” unlimited point farming by replaying the POST |
| Blocker | W3 | app/Http/Controllers/TeacherController.php:172-267 | submitReview re-awards XP/coins/points on every call (no grading idempotency) â€” economy break |
| Blocker | W2 | app/Http/Controllers/TeacherController.php:186-235 | Teacher can re-grade an already-approved submission, double-awarding XP/coins and teacher/parent/school points each time |
| Blocker | W2 | app/Services/Activity/PointsDistributionService.php:34-43 | PointsDistributionService.distribute() has no idempotency key â€” re-distribution always re-awards |
| Major | W3 | app/Actions/Activity/SubmitActivityAction.php:142-177 | awardXpAndCoins swallows Point/Coin failures inside the transaction â†’ submission committed without (or with partial) award |
| Major | W2 | app/Actions/Activity/SubmitActivityAction.php:106-122 | Student is credited inside the transaction but teacher/parent/school distribution runs after commit with no atomicity, causing silent partial awards |
| Major | W3 | app/Console/Commands/CheckHomeworkDueDates.php:110-124 | Overdue idempotency never matches because activity_id is stored null; alerts re-fire |
| Major | W3 | app/Http/Controllers/Admin/DashboardController.php:196-235 | saveReview: submission status update and economy award are not atomic; award failure is silently swallowed (approved with zero points) |
| Major | W3 | app/Http/Controllers/LeaderboardController.php:320-348 | getUserRankInCategory computes teacher/parent rank against the student points table instead of teacher_points/parent_points |
| Major | W1 | app/Http/Controllers/MessagesController.php:268 (send), 343-355 (checkNewMessages), 379-398 (checkAllNewMessages) | Sensitive-data exposure: message JSON endpoints serialize the full sender User model, leaking email/phone/birth_date to recipients |
| Major | W2 | app/Http/Controllers/ParentController.php:416-444 | sendGift daily-limit check is outside the transaction and unlocked (TOCTOU double-spend / point inflation) |
| Major | W3 | app/Http/Controllers/StudentController.php:2101-2168 | submitPvpAnswers has no status guard and trusts client time_taken as the win tiebreaker |
| Major | W2 | app/Http/Controllers/StudentController.php:2101-2164 (missing guard), contrast pvpPlay 2088 | submitPvpAnswers has no status/already-submitted guard â€” a player can overwrite their own score multiple times |
| Major | W2 | app/Http/Controllers/StudentController.php:2111, 2156/2163; PvpMatch::determineWinner app/Models/PvpMatch.php:42-50 | PvP winner tie-break trusts client-supplied time_taken (time=0 wins ties) |
| Major | W1 | app/Http/Controllers/StudentController.php:1148-1185 | redeemReward trusts a client-supplied cost and never looks up the reward's real price |
| Major | W1 | app/Http/Controllers/StudentController.php:2101-2164 | submitPvpAnswers has no match-status/idempotency guard, allowing score overwrite and post-completion mutation |
| Major | W1 | app/Http/Controllers/StudentController.php:1866-1951 | submitExercise awards points on every attempt with no max_attempts/idempotency/throttle (unlimited XP+coin farming) |
| Major | W1 | app/Http/Controllers/StudentController.php:2167-2200 | PvP winner reward idempotency uses non-atomic Cache::has/Cache::put, allowing concurrent double-payout |
| Major | W1 | app/Http/Controllers/StudentController.php:1148-1194 | Shop redeem trusts client-supplied coin cost; reward_id never used to derive price |
| Major | W3 | app/Http/Controllers/TeacherController.php:1262-1300 | gradeTeamActivity uses non-atomic check-then-act on status, allowing concurrent double award of points/coins |
| Major | W1 | app/Http/Controllers/TeacherController.php:186-227 | submitReview re-awards XP and coins on every call with no 'already graded' guard |
| Major | W3 | app/Http/Controllers/TeacherController.php:1262-1300 | gradeTeamActivity idempotency guard is a non-atomic check-then-act, allowing concurrent double-award of XP/coins |
| Major | W3 | app/Models/TeacherPoint.php:41-87 | TeacherPoint updateTeacherPoints overwrites the unique-per-teacher points row vs incremental writers |
| Major | W2 | app/Services/Activity/PointsDistributionService.php:48-71 | awardTeacher attributes points to an arbitrary classroom's teacher via ->first() with no active-status filter |
| Major | W2 | app/Services/Activity/PointsDistributionService.php:83-99 | awardParent rewards only the first parent though a student may have multiple parents |
| Major | W2 | app/Services/PointsService.php:46-52 | awardStudentPoints never updates users.total_points; column read by level/leaderboard drifts from the points ledger |
| Major | W2 | app/Services/PointsService.php:35-82 | awardStudentPoints runs 4 dependent ledger writes with no wrapping transaction (partial-distribution on failure) |
| Major | W2 | app/Services/PointsService.php:35-82 (and callers: app/Http/Controllers/TeacherController.php:216-235) | Award path is non-idempotent; re-approving a submission re-awards XP/coins/distribution with no duplicate guard |

### 02-authz-idor  (17)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Blocker | W3 | app/Http/Controllers/TeacherController.php:996-1036 | storeTeam adds arbitrary user IDs as team members/leader without classroom or school scoping (BOLA/IDOR) |
| Blocker | W3 | app/Http/Controllers/TeacherController.php:1101-1135 | updateTeam rebuilds team membership from unscoped leader_id/member_ids (BOLA/IDOR) |
| Blocker | W1 | app/Http/Controllers/TeacherController.php:992-1046 (storeTeam) and 1093-1146 (updateTeam); critical inserts at 1027-1036 and 1126-1135 | storeTeam/updateTeam add arbitrary user IDs as team members without school/classroom ownership check (cross-tenant IDOR + economy break) |
| Major | W3 | app/Http/Controllers/LeaderboardController.php:40-102, 127-256 | Any authenticated user can enumerate other schools' students/teachers/parents (request-supplied school_id + scope=global, no tenant middleware) |
| Major | W1 | app/Http/Controllers/MessagesController.php:363-393 | checkAllNewMessages runs a per-conversation query inside a loop on a 5-second poll (N+1 on a hot polling endpoint) |
| Major | W2 | app/Http/Controllers/MessagesController.php:459-471 | chatUpload accepts uploads from any authenticated user with no conversation/permission check and no rate limit |
| Major | W3 | app/Http/Controllers/PagesController.php:48-52 | showSurvey() leaks all surveys (any school, draft/closed) to anonymous users â€” no status check, no scoping (BOLA/IDOR + data leakage) |
| Major | W3 | app/Http/Controllers/StudentController.php:463-547 | lesson($id) has no school/value-visibility check â€” cross-tenant lesson & activity disclosure (IDOR/BOLA) |
| Major | W3 | app/Http/Controllers/StudentController.php:1841-1861, 1866-1869, 2079-2096, 2101-2108 | startExercise/submitExercise/pvpPlay/submitPvpAnswers don't verify exercise/challenge belongs to student's classroom or school (cross-school BOLA) |
| Major | W1 | app/Http/Controllers/StudentController.php:1841-1951 | practice start submit missing ownership |
| Major | W2 | app/Http/Controllers/StudentController.php:1841-1844 (startExercise), 1866-1869 (submitExercise) | submitExercise (and startExercise) do not verify the exercise belongs to the student's classroom or is active/in-window (IDOR) |
| Major | W2 | app/Http/Controllers/StudentController.php:2024 | joinPvpMatch ignores school scoping â€” student can join a challenge tied to a value not visible to their school |
| Major | W3 | app/Http/Controllers/SurveyController.php:47-104 | submit() does not verify the user is targeted by the survey (BOLA / function-level authz gap) |
| Major | W3 | app/Http/Controllers/TeacherController.php:547-576 | updateStreakSettings writes platform-global settings (no school/user scoping) â€” cross-tenant tampering and broken read/write contract |
| Major | W3 | app/Http/Controllers/TeacherController.php:1915, 1924-1937, 1973, 1983-1995 | storeExercise/updateExercise do not verify classroom_id belongs to the teacher (cross-classroom/cross-school IDOR) |
| Major | W3 | app/Models/PracticeAttempt.php:7-27 (model); enabling abuse at app/Http/Controllers/StudentController.php:1866-1950 | PracticeAttempt has mass-assignable student_id/score, no query scope, and no idempotency â€” controller re-awards points and never checks exercise ownership |
| Major | W1 | routes/web.php:83-90 | Public token-registration POST endpoints have no rate limiting (mass request creation + email amplification) |

### 03-xss-output  (8)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Blocker | W3 | app/Helpers/SettingsHelper.php:181-206 | safe_html() regex sanitizer is bypassable â†’ stored XSS via {!! !!} |
| Blocker | W1 | app/Helpers/SettingsHelper.php:181-206 (sink: resources/views/messages/show.blade.php:593, resources/views/messages/bulk/inbox.blade.php:369; source stored raw at app/Http/Controllers/MessagesController.php:256-261 and app/Http/Controllers/BulkMessageController.php:99,128) | Stored XSS: regex-based safe_html() sanitizer is bypassable on raw user-authored message/lesson/activity content rendered cross-role |
| Major | W2 | app/Exports/StudentsExport.php:53-69 | Exports write user-controlled fields without formula/CSV-injection sanitization |
| Major | W2 | app/Http/Controllers/Admin/ActivityManagementController.php:181-188 | ActivityManagementController::uploadImage allows unsanitized SVG into a directly-servable public path (stored XSS) |
| Major | W3 | app/Http/Controllers/Admin/MessagesLogController.php:203-213 | CSV formula injection: attacker-controlled message body exported unescaped |
| Major | W3 | app/Http/Controllers/Admin/SurveyManagementController.php:263-304 (esp. 265, 277-281, 290-298) | CSV/formula injection in exportResponses (user-supplied answers & question text written unescaped) |
| Major | W3 | app/Http/Controllers/Admin/ValueManagementController.php:56, 65, 109, 120 | Image upload permits SVG stored on public disk (active-content / stored-XSS via direct URL) |
| Major | W3 | app/Http/Controllers/SuperAdminController.php:1016-1047 (addLandingBlock); 1052-1079 (updateLandingBlock) | Stored XSS/HTML injection: landing-block content rendered unescaped into HTML tag-name and href/src sinks on the public homepage |

### 04-api-broken  (6)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Blocker | W3 | app/Http/Controllers/Api/StudentApiController.php:106, 119 | valuesTree eager-loads/reads non-existent `meanings` relation -> RelationNotFoundException 500 |
| Blocker | W3 | app/Http/Controllers/Api/StudentApiController.php:56 | dashboard calls $user->streaks() but relation is streak() (singular) -> undefined method 500 |
| Blocker | W3 | app/Http/Controllers/Api/StudentApiController.php:51-52, 352, 369, 382 | dashboard & leaderboard query non-existent `amount` column on points/coins -> guaranteed 500 |
| Major | W3 | app/Console/Commands/CheckHomeworkDueDates.php:112-115 | Overdue idempotency query references non-existent notifications.user_id - SQL error aborts loop |
| Major | W3 | app/Http/Controllers/Api/StudentApiController.php:271-293 | submitActivity writes `answers` key that is non-fillable and has no column -> student answer silently lost |
| Major | W3 | app/Listeners/SendBadgeEarnedNotification.php; app/Listeners/SendActivityGradedNotification.php; app/Listeners/SendWelcomeNotification.php:SendBadgeEarnedNotification.php:52-61; SendActivityGradedNotification.php:56-66; SendWelcomeNotification.php:60-70 | Listeners reference non-existent $user->parent relation; parent notifications silently never sent |

### 05-data-exposure  (1)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Major | W3 | app/Http/Controllers/Admin/TeacherManagementController.php:69, 138 | store()/update() accept status='suspended' but users.status is enum('active','inactive') with strict mode â†’ write fails (store leaks raw SQL, update returns 500) |

### 06-auth-session  (12)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Major | W2 | app/Http/Controllers/Api/AuthApiController.php:198-213 | changePassword does not revoke existing/sibling tokens |
| Major | W2 | app/Http/Controllers/Api/AuthApiController.php:62-96 | Mobile API login bypasses 2FA entirely (issues full token on password match) |
| Major | W2 | app/Http/Controllers/Api/AuthApiController.php:55-69 | API login has no per-credential brute-force lockout (only global 60/min/IP) |
| Major | W2 | app/Http/Controllers/AuthController.php:41-92 | Failed-login throttle keyed on victim user id enables targeted account-lockout DoS |
| Major | W1 | app/Http/Controllers/AuthController.php:44-46, 68-75 | Web login lockout keys on the victim's user id, enabling targeted account-lockout DoS |
| Major | W2 | app/Http/Controllers/MessagesController.php:236-308 | send() / canMessage allow unthrottled DMs, including any user spamming super_admin |
| Major | W3 | app/Http/Middleware/Force2FAForAdmins.php:22-64 (alias at bootstrap/app.php:51; admin group at routes/web.php:168) | Force2FAForAdmins middleware is registered but applied to no route â€” admin 2FA is never enforced |
| Major | W1 | bootstrap/app.php / routes/web.php:bootstrap/app.php:51 (alias) ; routes/web.php (no usage) | Force2FAForAdmins middleware is registered but applied to no route, so admin 2FA is never enforced |
| Major | W3 | routes/api.php:18-23 | Mobile API login endpoint lacks a per-credential rate limiter (only 60/min-per-IP global) |
| Major | W1 | routes/api.php / app/Http/Controllers/Api/AuthApiController.php:routes/api.php:21 ; AuthApiController.php:55-69 | Mobile API login has no dedicated rate limit or account lockout (credential-stuffing weak) |
| Major | W3 | routes/web.php:83-90 | No rate limiting on any public registration POST (mass-creation + email flooding) |
| Major | W3 | routes/web.php:168, 384 | Force2FAForAdmins middleware is registered and tested but never attached to any admin route group |

### 07-error-handling  (2)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Blocker | W1 | .htaccess:1-15 | Root .htaccess serves production .env (DB/SMTP password, APP_KEY) and other root files directly over HTTP |
| Major | W1 | app/Http/Controllers/StudentController.php:894 (and ParentController.php:277,393,449,525; MessagesController.php:280; BulkMessageController.php:161; SchoolAdminController.php:890; SuperAdminController.php:53,211,325,409,565,671; Admin/DashboardController.php:243; Admin/SurveyManagementController.php:126,225; Admin/TeacherManagementController.php:104; Admin/SurveyController.php:384; PagesController.php:113; Api/LandingContentController.php:112) | Raw exception messages ($e->getMessage()) returned to end users across many controllers |

### 08-uploads-files  (12)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Major | W1 | app/Exports/StudentsExport.php:53-68 | Excel/CSV exports write user-controlled fields without formula-injection neutralization |
| Major | W2 | app/Exports/StudentsExport.php:23-68 | exportStudents: per-row N+1 aggregate queries and unbounded ->get() across all schools |
| Major | W3 | app/Exports/StudentsExport.php; app/Exports/SchoolsExport.php; app/Exports/TeachersExport.php:StudentsExport.php:32,64-66; SchoolsExport.php:33,56-60; TeachersExport.php:32,53-55 | Per-row aggregate queries (N+1) and unbounded ->get() in exporters |
| Major | W3 | app/Exports/StudentsExport.php; app/Exports/TeachersExport.php; app/Exports/ParentsExport.php; app/Exports/SchoolsExport.php; app/Exports/ValuesExport.php; app/Exports/ActivitiesExport.php:StudentsExport.php:53-69; TeachersExport.php:50-68; ParentsExport.php:50-65; SchoolsExport.php:54-75; ValuesExport.php:45-57; ActivitiesExport.php:53-84 | CSV/formula injection unmitigated in all data exports (=,+,-,@ leading cells not neutralized) |
| Major | W3 | app/Http/Controllers/Admin/SurveyController.php:446-477 (esp. 448, 473, 476) | CSV/formula injection in survey export (untrusted guest/student answers written raw to CSV) |
| Major | W1 | app/Http/Controllers/SuperAdminController.php:218-313 | restoreBackup copies arbitrary file types from an uploaded ZIP into the public webroot (public/uploads) and storage = remote code execution |
| Major | W2 | app/Http/Controllers/SuperAdminController.php:287-302 | restoreBackup executes arbitrary SQL from uploaded zip over the live DB with no transaction or rollback on partial failure |
| Major | W2 | app/Http/Controllers/SuperAdminController.php:304-313 | restoreBackup writes attacker-controlled archive files into web-served public/uploads (no type/content filter) -> webshell RCE |
| Major | W2 | app/Imports/BulkUsersImport.php:14-55 | Bulk import loads entire file into memory (ToCollection, no chunk/row cap) â€” DoS amplification |
| Major | W2 | app/Imports/BulkUsersImport.php:183-196 | All imported users created with hard-coded password '123456' |
| Major | W3 | app/Imports/StudentsImport.php:48, 73-82 | StudentsImport sets default password '123456' and does NOT force a password change |
| Major | W3 | routes/console.php:27-29 | Scheduler backup onFailure calls undefined global error() - swallows failure alert |

### 09-perf-nplus1  (5)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Major | W3 | app/Console/Commands/CheckHomeworkDueDates.php:39-127 | CheckHomeworkDueDates: N+1 lookups and per-homework classroom re-fetch over unbounded get() |
| Major | W3 | app/Http/Controllers/LeaderboardController.php:46, 67, 88, 108 | limit query param is an unbounded, unclamped integer passed straight to SQL LIMIT |
| Major | W3 | app/Http/Controllers/SchoolAdminController.php:949-1149 | statistics() runs unbounded platform-wide queries with correlated subqueries on every page load |
| Major | W1 | app/Http/Controllers/StudentController.php:114-117, 173-175 | Student dashboard lazy-loads lesson->activities per lesson (eager load stops at concepts.lessons) |
| Major | W3 | app/Http/Controllers/SurveyController.php:82-99 | Guest (user_id NULL) submissions have no duplicate prevention â€” unbounded responses |

### 10-mass-assign-validation  (2)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Major | W3 | app/Http/Controllers/Admin/StudentManagementController.php:67, 116, 159 | Validation accepts status='suspended' but DB enum only allows active/inactive (write failure / silent corruption) |
| Major | W3 | app/Http/Controllers/SurveyController.php:72-98 | Answer payload stored verbatim with no validation against real questions, types, or options |

### 12-schema-migrations  (5)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Major | W3 | app/Http/Controllers/Admin/ShopManagementController.php:95-106 | Hard-delete of shop item cascade-deletes all student purchase records, orphaning paid coin debits with no refund |
| Major | W3 | app/Http/Controllers/Admin/TeacherManagementController.php:157-169 | destroy() hard-deletes a teacher (no soft delete, no transaction, no relation guard) â€” cascades delete ratings/points/messages and orphans classrooms |
| Major | W3 | database/migrations/2025_11_18_140931_create_activity_submissions_table.php:14-27 | No unique constraint on activity_submissions(student_id, activity_id) â€” economy double-award race relies on insufficient app-level lock |
| Major | W3 | database/migrations/2025_12_13_232623_add_smart_performance_indexes.php:75-111 | add_smart_performance_indexes silently creates ZERO indexes on MySQL (sqlite_master existence check) |
| Major | W3 | database/migrations/2026_02_26_100000_create_school_statistics_cache_table.php:50-53 | school_statistics_cache lacks UNIQUE on (entity_type, entity_id) used by concurrent updateOrCreate -> duplicate cache rows |

### 13-misc-correctness  (9)
| Sev | Wave | File:Line | Finding |
|---|---|---|---|
| Major | W3 | app/Http/Controllers/Admin/ActivityBankController.php:187-193, 211-217 | NotificationService::create() called with route URL as $data instead of $actionUrl in approveQuestion/rejectQuestion (broken link + wrong type into array-cast column) |
| Major | W3 | app/Http/Controllers/Admin/MessagesLogController.php:185-218 | export() loads all matching messages into memory, negating the streamed response |
| Major | W3 | app/Http/Controllers/Api/LandingContentController.php:166-175 | restoreVersion truncates all landing content then recreates without a transaction or snapshot-integrity check â€” partial/malformed restore permanently wipes content |
| Major | W3 | app/Http/Controllers/PublicRegistrationController.php:39 | email unique-against-registration_requests permanently locks out / squats any address |
| Major | W3 | app/Http/Controllers/RoleSwitchController.php:23 (call into app/Models/User.php:582; guard at app/Models/User.php:38-63) | switchRole() persists active_role via update(), which the User model's saving-guard rejects with 403 for the non-admin users the feature targets |
| Major | W3 | app/Http/Controllers/SuperAdminController.php:754-761, 784-790 | approveQuestion/rejectQuestion pass the action URL into the wrong NotificationService::create parameter (drops link, corrupts JSON data column) |
| Major | W3 | app/Listeners/SendBadgeEarnedNotification.php; app/Listeners/SendActivityGradedNotification.php; app/Listeners/SendWelcomeNotification.php:SendBadgeEarnedNotification.php:34-61; SendActivityGradedNotification.php:38-66; SendWelcomeNotification.php:42-70 | Action URL passed as wrong positional argument to NotificationService::create â€” stored in data column, action_url always null |
| Major | W3 | app/Models/Setting.php:15-117 (get/getMany/set/clearCache); see writers TeacherController.php:567 and reader StudentController.php:1120 | Setting static API ignores user_id: per-teacher settings collide / never resolve (economy) |
| Major | W3 | app/Models/User.php:40-63, 570-585 | booted() saving guard aborts legitimate self-service role switching for non-admin users |

## C. needs_context — deciding question (held for your judgment)
| Wave | File:Line | Finding | Deciding question / runtime fact needed |
|---|---|---|---|
| wc003b4xi | database/migrations/2026_05_04_000002_add_user_id_to_settings.php:42-47 | add_user_id_to_settings down() re-adds single-column UNIQUE on key without dedup, breaking rollback once per-user rows exist | The migration code is quoted accurately. In database/migrations/2026_05_04_000002_add_user_id_to_settings.php, up() (lines 24-33) drops UNIQUE(key) and adds composite UNIQUE(key,user_id) to permit duplicate keys across users; down() (lines ... |

## B. Minor & Nitpick (compact — file:line + finding)

| Cluster | Sev | File:Line | Finding |
|---|---|---|---|
| economy | Minor | app/Http/Controllers/Admin/ActivityManagementController.php:63-65, 124-126 | Activity points/passing fields have no upper bound and flow directly into the student virtual economy |
| economy | Minor | app/Http/Controllers/Admin/LessonManagementController.php:74-76, 158-160 | No cross-field check that streak_min_days <= streak_max_days |
| economy | Minor | app/Http/Controllers/Admin/ReportsController.php:206-233 | studentDetail() eager-loads all submissions+activities and all points that are never used |
| economy | Minor | app/Http/Controllers/LeaderboardController.php:129, 169, 218, 263, 322, 353 | Leaderboard/rank caches are never invalidated â€” Point::created forgets keys that don't match the controller's keys, and teacher/parent point awards forget nothing |
| economy | Nitpick | app/Http/Controllers/LeaderboardController.php:8-9 | Unused imports ParentPoint and PointsService |
| economy | Minor | app/Http/Controllers/ParentController.php:416-444 | sendGift checks its daily cap outside the transaction/lock, allowing the cap to be exceeded under concurrency |
| economy | Minor | app/Http/Controllers/ParentController.php:277, 393, 449, 525 | Raw exception messages returned to client in error responses (information disclosure) |
| economy | Nitpick | app/Http/Controllers/ParentController.php:403-432 | gift_type accepts any arbitrary string (no enum/allowlist validation) |
| economy | Nitpick | app/Http/Controllers/ParentDashboardController.php:291-292 | Level/progress math breaks for negative point balances |
| economy | Nitpick | app/Http/Controllers/ParentDashboardController.php:66-103 | Cached classRank goes stale when a child changes classroom without a points event |
| economy | Minor | app/Http/Controllers/SchoolAdminController.php:751-808 | approveRequest/rejectRequest do not verify the request is still pending (non-idempotent) |
| economy | Minor | app/Http/Controllers/SchoolAdminController.php:1152-1178 | Grade-level student rankings leak students from OTHER schools (missing school_id scope) |
| economy | Minor | app/Http/Controllers/StudentController.php:1321-1326 | purchaseItem writes the spend Coin row without transaction_type, so coinsHistory mislabels purchases |
| economy | Minor | app/Http/Controllers/StudentController.php:2021-2053 | joinPvpMatch has no transaction/lock: concurrent joins overwrite the same waiting match and a student can spawn duplicate matches |
| economy | Nitpick | app/Http/Controllers/StudentController.php:794-797 | submitActivity validates an `xp` request field that is unbounded (nullable/integer, no min/max) |
| economy | Minor | app/Http/Controllers/StudentController.php:1321-1326 | purchaseItem Coin::create omits transaction_type/reason and uses source/description â€” inconsistent ledger rows |
| economy | Minor | app/Http/Controllers/StudentController.php:1871-1872, 1890; 2110-2111, 2118 | submitExercise / submitPvpAnswers take answers raw with no request validation |
| economy | Minor | app/Http/Controllers/StudentController.php:1148-1194 | redeemReward deducts a fully client-supplied cost with no server-side reward lookup |
| economy | Minor | app/Http/Controllers/SuperAdminController.php:740-767, 772-796 | approveQuestion/rejectQuestion have no current-state check or idempotency guard |
| economy | Minor | app/Http/Controllers/TeacherController.php:1291-1299 | Team-graded notification passes action URL into the $data (array) parameter, losing the link and storing malformed data |
| economy | Minor | app/Http/Controllers/TeacherController.php:1262-1300 | gradeTeamActivity idempotency check is not atomic (check-then-act without lock), enabling concurrent double-grade |
| economy | Minor | app/Http/Controllers/TeacherController.php; app/Listeners/SendActivityGradedNotification.php:TeacherController.php:186-256; SendActivityGradedNotification.php:28-49 | Re-grading re-fires ActivityGraded/ActivityCompleted; queued ActivityGraded listener can null-deref on deleted activity/student |
| economy | Minor | app/Listeners/UpdateStreak.php; app/Listeners/CheckBadgeEligibility.php; app/Providers/AppServiceProvider.php:UpdateStreak.php:10 (no ShouldQueue); CheckBadgeEligibility.php:11 (no ShouldQueue); AppServiceProvider.php:120-138 | UpdateStreak and CheckBadgeEligibility run synchronously with multiple queries/writes inside the HTTP request |
| economy | Minor | app/Models/ActivityUserStreak.php:72-111 (ActivityUserStreak::checkAndClaimBonus); also LessonUserStreak.php:78-112 | checkAndClaimBonus() does a non-locked, non-transactional check-then-act on the points economy; award + resetStreak run outside any transaction |
| economy | Minor | app/Models/ParentGift.php:9-15 | ParentGift.points_cost / ParentPraise.points_awarded and parent_id/student_id are mass-assignable |
| economy | Minor | app/Models/SchoolPoint.php:30-47 | SchoolPoint::addPoints does create() then a separate increment() outside any transaction |
| economy | Nitpick | app/Models/ShopItem.php:26-30 | Missing $casts for integer money/points and boolean-like columns across reward models |
| economy | Minor | app/Models/Team.php:Team.php:9-12; PvpMatch.php:9-15; PvpChallenge.php:9-11 | Economy/status columns are mass-assignable across PvpMatch, PvpChallenge, and Team |
| economy | Minor | app/Services/Activity/PointsDistributionService.php:55, 91, 115 | max(1, floor(points * pct)) leaks points to all three parties even when the percentage rounds to zero |
| economy | Minor | app/Services/PointsService.php:101-102, 139-140, 170-171 (and GamificationService.php:15, 70) | Services accept negative XP/coins and force a minimum of 1 distributed point even for zero/negative awards |
| authz-idor | Minor | app/Http/Controllers/Concerns/ScopedToSchool.php:35-41 | currentSchool() returns an arbitrary 'first' active school for super_admin; helpers then read/mutate cross-tenant data |
| authz-idor | Minor | app/Http/Controllers/MessagesController.php:253-264 | send() creates the message and updates the conversation outside a DB transaction |
| authz-idor | Minor | app/Http/Controllers/MessagesController.php:288-308 | canMessage re-runs the full getAvailableUsers permission query on every send/show/getConversation/checkNewMessages call |
| authz-idor | Minor | app/Http/Controllers/ParentController.php:194-208 | getConversation marks messages read across all of a parent's children, ignoring student_id filter |
| authz-idor | Nitpick | app/Http/Controllers/SchoolAdminController.php:1366 (also TeacherController.php:2146) | surveyComparison uses strict (!==) comparison of school_id (no int cast) â€” fail-secure denial, not an IDOR |
| authz-idor | Minor | app/Http/Controllers/StudentController.php:463-469 | lesson missing school visibility check |
| authz-idor | Minor | app/Http/Controllers/StudentController.php:86-100 | dashboard/learn 'current lesson' fallback query is not scoped to the student's school/visible values |
| authz-idor | Minor | app/Http/Middleware/CheckSchoolAccess.php:29-35 | CheckSchoolAccess does not actually block cross-school access to route-model-bound resources |
| authz-idor | Nitpick | app/Policies/ActivityPolicy.php:28-33 | ActivityPolicy::view returns true for any activity without a classroom (cross-school read) |
| authz-idor | Minor | app/View/Composers/HeaderDataComposer.php:22-31 | HeaderDataComposer exposes platform-wide pending counts to school_admins (cross-tenant leak / wrong metric) |
| authz-idor | Minor | resources/views/messages/index.blade.php:346-347 | Conversation list view runs a COUNT query per conversation for the unread badge (N+1) |
| authz-idor | Minor | routes/web.php:117, 125 | Editor image upload route is reachable by every authenticated user (student/parent), not just content authors |
| xss-output | Minor | app/Http/Controllers/Admin/LandingPageController.php:59-81 | updateContent() stores arbitrary page JSON with no structural validation; rendered unescaped on public home page (stored XSS) |
| xss-output | Nitpick | app/Http/Controllers/Admin/MessagesLogController.php:32-34 | Unescaped LIKE wildcards in message search filter |
| xss-output | Minor | app/Http/Controllers/Admin/PageBuilderController.php:55-63 / 112-120 (og_image at 36/93; json_data at 33/90) | URL/content fields (og_image, image/video/link/button/gallery URLs) are stored with no scheme validation and reflected into href/src on public pages; safe_html does not cover these branches |
| xss-output | Minor | app/Http/Controllers/Admin/ThemeController.php:75-101 | upload() accepts SVG for logo/favicon/icon/image and stores it on a publicly served disk (active-content stored XSS when fetched directly) |
| xss-output | Nitpick | app/Http/Controllers/Api/LandingContentController.php:52-64 | update/bulkUpdate accept an arbitrary 'type' (including 'html') and an unbounded 'value' with no whitelist â€” latent stored XSS if any view renders the value raw |
| xss-output | Minor | app/Http/Controllers/MessagesController.php:239-261 (and app/Http/Controllers/BulkMessageController.php:95-128) | Message and bulk-message bodies accept and persist arbitrary HTML with no input sanitization |
| api-broken | Minor | app/Http/Controllers/Api/StudentApiController.php:110-118 | valuesTree reads non-existent title/color columns on values/concepts -> null fields |
| api-broken | Minor | app/Http/Controllers/Api/StudentApiController.php:138-146, 261-269 | activities & submitActivity omit classroom-membership and approval_status scoping enforced by web flow (BOLA) |
| api-broken | Minor | app/Http/Controllers/Api/StudentApiController.php:277-305 | submitActivity duplicate guard mismatched to created status, no transaction/lock -> resubmit overwrite + race |
| api-broken | Minor | app/Http/Controllers/Api/StudentApiController.php:46-385 | No try/catch on any endpoint -> raw DB/exception messages leak to API clients |
| api-broken | Minor | app/Http/Controllers/Api/StudentApiController.php:178-180, 237, 242 | Unguarded dereference of nullable creator/lesson relations can 500 |
| api-broken | Minor | app/Http/Controllers/Api/StudentApiController.php:231, 233 | activityDetails exposes instructions/attachments columns that do not exist -> always null |
| data-exposure | Minor | app/Http/Controllers/Api/LandingContentController.php:108-115 | bulkUpdate leaks raw exception message (always) and full stack trace (when app.debug) to the HTTP client |
| data-exposure | Minor | app/Http/Controllers/BulkMessageController.php:168-213, 133-148, 263 | Recipient resolution loads full User models (and counts in PHP) â€” unbounded memory on 'all'/'school_all' and on the count endpoint |
| data-exposure | Minor | app/Http/Controllers/PublicRegistrationController.php:60 | Applicant-chosen password is collected and stored but never used (needless sensitive-data retention) |
| data-exposure | Minor | app/Http/Middleware/CheckPendingSurveys.php:53-58 | CheckPendingSurveys runs an unbounded eager-loaded query and serializes full Eloquent models into the session on every web request |
| data-exposure | Nitpick | app/Http/Resources/UserResource.php:15-16 | UserResource serializes email and phone (PII) bypassing the model's $hidden |
| data-exposure | Minor | app/Models/FamilyActivitySubmission.php:9-27 | FamilyActivitySubmission exposes parent_approved and status to mass assignment with no model-level guard |
| data-exposure | Minor | app/Models/Message.php:12-19 | Message $fillable exposes is_read/read_at (read-receipt state mass-assignable) |
| data-exposure | Minor | app/Models/ParentTeacherMessage.php:9-17 | ParentTeacherMessage $fillable exposes sender_type/is_read/read_at and model has no $hidden/$guarded |
| data-exposure | Minor | app/Models/QuestionBank.php:12-28 | QuestionBank exposes status/approved_by/approved_at/usage_count as mass-assignable (defense-in-depth gap) |
| data-exposure | Minor | app/Models/RegistrationRequest.php:10-23 (no $hidden); password is fillable line 16 | RegistrationRequest stores bcrypt password but does not declare it in $hidden |
| data-exposure | Minor | app/Models/SurveyResponse.php:8-21 (also Survey.php, SurveyQuestion.php, QuestionBank.php, TeacherRating.php â€” none define $hidden) | No $hidden on any of the five models (low-sensitivity exposure only) |
| auth-session | Minor | app/Http/Controllers/Api/AuthApiController.php:80 | Mobile tokens minted with ['*'] abilities and no expiration |
| auth-session | Minor | app/Http/Controllers/Api/AuthApiController.php:191-214 | API changePassword does not revoke other Sanctum tokens after a password change |
| auth-session | Minor | app/Http/Controllers/Api/AuthApiController.php:170-173 | updateProfile overwrites avatar without deleting the previous file |
| auth-session | Minor | app/Http/Controllers/AuthController.php:107-116, 290-300 | Plaintext 2FA login code serialized into queue payload and stored unhashed in DB |
| auth-session | Nitpick | app/Http/Controllers/AuthController.php:236-238 | Expired-code branch in verifyTwoFactor leaves session and code state without cleanup |
| auth-session | Minor | app/Http/Controllers/AuthController.php:534, 538 | resetPassword leaks account existence, undermining sendResetLink's neutral response |
| auth-session | Minor | app/Http/Controllers/AuthController.php:530-538 | resetPassword email validation re-enables user enumeration that sendResetLink prevents |
| auth-session | Nitpick | app/Http/Controllers/AuthController.php:68-69, 207-209 | Login and 2FA attempt counters use non-atomic cache read-modify-write (race under concurrency) |
| auth-session | Minor | app/Http/Controllers/AuthController.php:107-112, 206-238 | 2FA OTP stored in plaintext and stale code left in DB on expiry/lockout paths |
| auth-session | Minor | app/Http/Controllers/AuthController.php:89-99 | Login responses leak remaining-attempt counts and distinguish inactive accounts (enumeration oracle) |
| auth-session | Minor | app/Http/Controllers/BulkMessageController.php:110-118 | Per-sender bulk rate limit is non-atomic (check-then-act) and uses a resetting TTL, so it is racy and weak |
| auth-session | Minor | app/Providers/AppServiceProvider.php:74-80 | Per-email login RateLimiter defined but never applied |
| auth-session | Minor | app/Providers/AppServiceProvider.php / routes/web.php / app/Http/Requests/Auth/LoginRequest.php:AppServiceProvider.php:74-80 ; routes/web.php:99-101 ; LoginRequest.php:1-40 | Purpose-built 'login' RateLimiter (email+IP) and LoginRequest are defined but never used by the live login route |
| auth-session | Minor | bootstrap/app.php:48-52 | force-2fa alias registered but on no route; admin 2FA not enforced |
| auth-session | Minor | routes/web.php:41, 44-47 | Authenticated survey.ajax-submit POST has no rate limit while the public submit does |
| auth-session | Minor | routes/web.php:83-90 | Token-based public registration POSTs have no rate limit |
| auth-session | Minor | routes/web.php:41-46 | survey.ajax-submit route has no rate limiting (asymmetric with survey.submit) |
| error-handling | Minor | .env:28, 57 | Same secret reused for DB_PASSWORD and MAIL_PASSWORD in production .env |
| error-handling | Minor | app/Exceptions/Handler.php:63-109 | app/Exceptions/Handler.php production error-masking is dead code (never registered under Laravel 12) |
| error-handling | Minor | app/Http/Controllers/Admin/DashboardController.php:236-244 | saveReview: raw exception message ($e->getMessage()) returned to the user |
| error-handling | Minor | app/Http/Controllers/Admin/SurveyController.php:384 | Raw exception message flashed to the UI in responses() |
| error-handling | Nitpick | app/Http/Controllers/Admin/SurveyManagementController.php:124-127, 223-226 | Raw exception message echoed to user in store()/update() error flash |
| error-handling | Minor | app/Http/Controllers/Admin/TeacherManagementController.php:100-105 | store() returns raw exception getMessage() to the user (information disclosure) |
| error-handling | Nitpick | app/Http/Controllers/Api/LandingContentController.php:110-114 | Landing content bulkUpdate returns stack trace (debug-gated) and raw exception message to caller |
| error-handling | Minor | app/Http/Controllers/BulkMessageController.php:159-162 | Raw exception message echoed back to the user on send failure |
| error-handling | Minor | app/Http/Controllers/Health/HealthCheckController.php:66-70 | Raw exception messages returned to client in detailed health JSON |
| error-handling | Minor | app/Http/Controllers/MessagesController.php:277-282 | send() returns the raw exception message to the client in the 500 response |
| error-handling | Minor | app/Http/Controllers/PagesController.php:107-115 | landingSnapshot() returns raw exception message to the client |
| error-handling | Minor | app/Http/Controllers/StudentController.php:1247-1252 | updateProfile returns the raw exception message to the client |
| uploads-files | Nitpick | app/Http/Controllers/Admin/ActivityManagementController.php:182 | `svg` listed in uploadImage mimes is dead/misleading (the `image` rule rejects SVG in this Laravel version) |
| uploads-files | Nitpick | app/Http/Controllers/Admin/LessonManagementController.php:196-214 | On image replacement, partial-failure of new uploads can lose all old images |
| uploads-files | Minor | app/Http/Controllers/Admin/LessonManagementController.php:94-117, 178-216 | File uploads and DB write are not wrapped in a transaction; failures orphan files or leave stale references |
| uploads-files | Nitpick | app/Http/Controllers/Admin/ThemeController.php:76-90 | favicon upload limited to image mimes (excludes .ico) and stored under generic 'theme' folder; mismatched with typical favicon format |
| uploads-files | Minor | app/Http/Controllers/Api/LandingContentController.php:166-175 | LandingContentController::restoreVersion truncates then re-inserts content without a transaction (data-loss window) |
| uploads-files | Nitpick | app/Http/Controllers/ProfileController.php:22-31 | Avatar update deletes the old file then writes the new path outside a transaction |
| uploads-files | Nitpick | app/Http/Controllers/SchoolAdminController.php:841-848, 907-926 | exportData/downloadTemplate accept unvalidated type/role input |
| uploads-files | Minor | app/Http/Controllers/SchoolAdminController.php:888-891 | Raw import exception message returned verbatim to the user |
| uploads-files | Minor | app/Http/Controllers/SuperAdminController.php:53,211,325,373,409,565,671 | Backup/restore/import handlers echo raw $e->getMessage() to the user (path / SQL / DB internals disclosure) and restoreBackup swallows failures unlogged |
| uploads-files | Minor | app/Http/Controllers/SuperAdminController.php:390-403 | cleanupBackups unlinks every entry older than 30 days in storage/app/Laravel without is_file/extension checks, destroying pre-restore safety backups |
| uploads-files | Nitpick | app/Http/Controllers/SuperAdminController.php:166,194 | downloadBackup/deleteBackup allowlist regex permits a dot-only stem like '..zip' (defence-in-depth nit; traversal already blocked) |
| uploads-files | Minor | app/Imports/BulkUsersImport.php; app/Http/Controllers/SchoolAdminController.php; app/Http/Controllers/SuperAdminController.php:BulkUsersImport.php:14,27-55; SchoolAdminController.php:857-864; SuperAdminController.php:644-651 | Bulk import loads entire file into memory with no row cap (DoS) |
| uploads-files | Minor | app/Imports/StudentsImport.php:43-83 | importStudents: per-row existence query, no in-file duplicate guard, no row cap, no DB::transaction; defaults blank passwords to a known weak value |
| uploads-files | Minor | app/Imports/StudentsImport.php; app/Imports/BulkUsersImport.php:StudentsImport.php:59-69; BulkUsersImport.php:71-73,115-117,144-146 | In-file duplicate emails and case/whitespace variants bypass the exists() pre-check |
| uploads-files | Minor | config/backup.php:189-198, 174-177 | Backup archive encryption is effectively off when BACKUP_ARCHIVE_PASSWORD is unset, while archives land on local disk |
| uploads-files | Minor | routes/console.php:27-29 | Backup-failure scheduler callback calls undefined error() function, crashing the failure handler |
| perf-nplus1 | Minor | app/Http/Controllers/Admin/ReportsController.php:294-328 | schoolDetail() eager-loads every user of a school but never reads the collection |
| perf-nplus1 | Minor | app/Http/Controllers/Admin/ReportsController.php:365-380 | values() loads the full concepts->lessons->activities content tree unbounded |
| perf-nplus1 | Nitpick | app/Http/Controllers/Admin/SchoolManagementController.php:86-96 | show runs five COUNT queries plus full eager loads |
| perf-nplus1 | Minor | app/Http/Controllers/Admin/SettingsController.php:67-72 | update() persists 11 settings in a loop without a wrapping DB transaction (non-atomic, partial-write on failure) |
| perf-nplus1 | Nitpick | app/Http/Controllers/Admin/ThemeController.php:49-56 | update() writes N settings in a loop with no DB::transaction; partial failure leaves inconsistent theme state |
| perf-nplus1 | Nitpick | app/Http/Controllers/Admin/ValueManagementController.php:85-87 | show() runs redundant concept/lesson aggregation queries after eager-loading |
| perf-nplus1 | Nitpick | app/Http/Controllers/Api/LandingContentController.php:92-94, 166 | createSnapshot inserts a full-content snapshot on every bulkUpdate/restore with no pruning â€” landing_content_versions grows unbounded |
| perf-nplus1 | Nitpick | app/Http/Controllers/NotificationController.php:44-58 | fetch() always returns only the latest 10 with no pagination cursor, so older unread notifications are unreachable via AJAX |
| perf-nplus1 | Minor | app/Http/Controllers/StudentController.php:48-53 | Student dashboard view accesses recentActivities->activity->lesson but only activity:id,title,lesson_id is eager-loaded |
| perf-nplus1 | Minor | app/Http/Controllers/TeacherController.php:908-924 | exportClassroomReport computes 4 aggregate queries per student inside a map loop (N+1) |
| perf-nplus1 | Minor | app/Http/Controllers/TeacherController.php:1473-1482, 1515-1526 | Teacher analytics issues 30 daily + 8 weekly COUNT queries in loops instead of one grouped query |
| perf-nplus1 | Minor | app/Http/Controllers/TeacherController.php:54-81 | dashboard runs per-classroom queries inside foreach (residual N+1) |
| perf-nplus1 | Minor | app/Http/Controllers/TeacherController.php:908-935 | exportClassroomReport runs per-student aggregate queries inside ->map() and loads all submissions into memory |
| perf-nplus1 | Minor | app/Models/SchoolStatisticsCache.php:41-47 | SchoolStatisticsCache::entity() relationship branches on instance state and cannot eager-load |
| perf-nplus1 | Minor | app/Models/Team.php:32-36 | Team::leader() applies limit(1) to a belongsToMany relation â€” breaks under eager loading |
| perf-nplus1 | Minor | resources/views/teacher/dashboard.blade.php:259 | Teacher dashboard view accesses submission->activity->lesson but only activity:id,title is eager-loaded |
| mass-assign-validation | Minor | app/Http/Controllers/Admin/ShopManagementController.php:42-43, 76-77 | Validation allows is_limited=true with no available_until and allows available_until in the past, creating items that are silently never available |
| mass-assign-validation | Nitpick | app/Http/Controllers/Admin/StudentManagementController.php:63, 66, 80 | TOCTOU between unique validation and create on email/qr_code surfaces a raw DB 500 under concurrency |
| mass-assign-validation | Nitpick | app/Http/Controllers/Admin/TeacherManagementController.php:64, 68, 134 | qr_code (store) and email lack max length validation, allowing over-length input to reach the DB as an unhandled error |
| mass-assign-validation | Minor | app/Http/Controllers/Admin/UserManagementController.php:74, 89, 118, 131 | `status` validation accepts 'suspended' but DB column is enum('active','inactive') â€” write fails or silently truncates |
| mass-assign-validation | Nitpick | app/Http/Controllers/Api/LandingContentController.php:172-175 | restoreVersion re-creates rows from a snapshot blob via mass-assignment without validating shape, types, or fillable-stripped fields |
| mass-assign-validation | Minor | app/Http/Controllers/ParentController.php:436-442, 512-518 | Point::create() with reference_type/reference_id silently drops those values (not in $fillable) |
| mass-assign-validation | Nitpick | app/Http/Controllers/SuperAdminController.php:1018-1037 | addLandingBlock trusts client-supplied 'position' with no range/negative bound for array_splice |
| mass-assign-validation | Minor | app/Http/Requests/Auth/RegisterRequest.php, app/Http/Requests/Auth/LoginRequest.php, app/Http/Requests/Profile/UpdateProfileRequest.php, app/Http/Requests/Student/SubmitActivityRequest.php:RegisterRequest.php:54 (safeUserAttributes); whole files | All 4 Form Requests are unwired; live validation is inline and diverges from them |
| mass-assign-validation | Nitpick | app/Models/Badge.php:9 | Badge.status mass-assignable with no validating writer |
| mass-assign-validation | Nitpick | app/Models/LandingContent.php:13-22 | LandingContent.updated_by is mass-assignable, enabling forged/stale attribution on restore |
| mass-assign-validation | Minor | app/Models/Setting.php:18-34 (get), 39-78 (getMany), 92-103 (set) | Setting::get caches defaults/nulls and direct updateOrCreate writes bypass cache invalidation |
| mass-assign-validation | Nitpick | app/View/Composers/HeaderDataComposer.php:22-29 | HeaderDataComposer: second count query unguarded, can break every admin page render |
| config-secrets | Minor | app/Exceptions/Handler.php:63-119 | Custom Exceptions Handler dead under L12; production masking never runs |
| config-secrets | Nitpick | app/Http/Controllers/NotificationController.php:17, 46, 65, 89 | Hardcoded notifiable_type literal 'App\Models\User' bypasses morph map and will silently break if a morph map is ever registered |
| config-secrets | Minor | config/sanctum.php:50 | Sanctum API token expiration disabled (tokens valid forever) |
| config-secrets | Minor | config/telescope.php:52-55 (and config/horizon.php:31) | Telescope/Horizon dashboards configured with no authorization gate (latent auth bypass once installed) |
| schema-migrations | Minor | app/Http/Controllers/Admin/ActivityManagementController.php:41, 48, 110 | Unbounded Lesson::...->get() loads every lesson (with nested concept.value) on index/create/edit |
| schema-migrations | Minor | app/Http/Controllers/Admin/ConceptManagementController.php:17-33 | index() eager-loads full lessons collection per concept only to display counts (N+1-style over-fetch) |
| schema-migrations | Minor | app/Http/Controllers/Admin/DashboardController.php:94-111 | index: dashboard chart loops issue 14 sequential COUNT queries (bounded N+1 / redundant queries) |
| schema-migrations | Minor | app/Http/Controllers/Admin/MessagesLogController.php:86-89 | index() loads every platform user for the filter dropdown |
| schema-migrations | Minor | app/Http/Controllers/Admin/SchoolManagementController.php:135-149 | destroy non-atomic guard plus FK cascade no transaction |
| schema-migrations | Minor | app/Http/Controllers/Admin/StudentManagementController.php:135-147 | destroy() hard-deletes the student (and cascade-deletes all economy/history) with no soft delete or transaction |
| schema-migrations | Minor | app/Http/Controllers/Admin/SurveyController.php:507-524 | Non-atomic check-then-delete in destroy() can silently cascade-delete responses submitted concurrently |
| schema-migrations | Minor | app/Http/Controllers/Admin/ThemeController.php:41-44 | Color settings validated only with max:7 / string, no hex-format constraint â†’ malformed CSS values persisted |
| schema-migrations | Minor | app/Http/Controllers/Api/LandingContentController.php:40-44 | Public index() exposes internal updated_by user IDs and timestamps via the 'grouped' payload |
| schema-migrations | Minor | app/Http/Controllers/MessagesController.php:405-414 | getStudentStats uses SQLite-only DATE('now') against a MySQL database, throwing on student index/show |
| schema-migrations | Minor | app/Http/Controllers/ParentDashboardController.php:40-116 | Per-child fan-out of rank and completed-lesson queries in index() |
| schema-migrations | Minor | app/Http/Controllers/PublicRegistrationController.php:39 | Check-then-act on email uniqueness with no DB constraint allows concurrent duplicate requests |
| schema-migrations | Minor | app/Http/Controllers/StudentController.php:2027-2046 | joinPvpMatch find-waiting-or-create has no lock/unique constraint â€” concurrent joins create duplicate waiting matches or double player2 |
| schema-migrations | Minor | app/Http/Controllers/TeacherController.php:1275-1289 | Team award passes float floor() result as points and can award 0 |
| schema-migrations | Minor | app/Models/Activity.php:12-14 | Activity has no SoftDeletes while activity_submissions cascade-delete on activity removal |
| schema-migrations | Nitpick | app/Models/PracticeExercise.php:PracticeExercise.php:7; PvpChallenge.php:7; Team.php:7; TeamActivity.php:7 | Practice/PvP/Team models lack SoftDeletes while their tables cascade-delete dependent economy/attempt data |
| schema-migrations | Minor | app/Models/Survey.php:136-198 | Survey::getComparisonData scores pre/post by positional index, silently mis-scoring when question counts differ |
| schema-migrations | Minor | database/migrations/2025_11_18_134600_create_users_table.php:23 | users.school_id created without FK or index (only added ~6 months later) |
| schema-migrations | Minor | database/migrations/2025_11_18_134708_create_schools_table.php:25 | schools.created_by has no foreign key and no index (orphan references, unindexed joins) |
| schema-migrations | Minor | database/migrations/2025_11_18_135939_create_classroom_student_table.php:18 | classroom_student.enrollment_date default(now()) bakes a static migration-time date as the column default |
| schema-migrations | Nitpick | database/migrations/2025_11_18_140931_create_activity_submissions_table.php:16 | No standalone index on activity_submissions.activity_id for per-activity (teacher review) queries |
| schema-migrations | Minor | database/migrations/2025_11_18_141409_create_team_activities_table.php:14-24 | No unique constraint on team_activities(team_id, activity_id) â€” duplicate assignment guard is unlocked check-then-act |
| schema-migrations | Nitpick | database/migrations/2025_12_02_000002_create_surveys_tables.php:71-92 | Duplicate prevention depends on a unique index only created in the table-create migration branch |
| schema-migrations | Nitpick | database/migrations/2025_12_13_000001_add_performance_indexes.php:17-50 | add_performance_indexes has no existence guard; re-run or partially-applied state throws |
| schema-migrations | Minor | database/migrations/2025_12_13_232623_add_smart_performance_indexes.php:44,68 | down() drops a notifications index name that up() never created (orphaned index on rollback) |
| schema-migrations | Minor | database/migrations/2025_12_13_232623_add_smart_performance_indexes.php:16-48 | Duplicate indexes on activity_submissions/points/coins/classrooms created by two consecutive index migrations and base tables |
| schema-migrations | Minor | database/migrations/2025_12_17_160229_create_user_purchases_table.php:14-25 | user_purchases lacks unique (user_id, shop_item_id) â€” no DB-level guard against duplicate purchase of one-time items |
| schema-migrations | Nitpick | database/migrations/2025_12_17_160229_create_user_purchases_table.php:23-24 | Redundant indexes declared on FK columns already indexed by constrained() |
| schema-migrations | Minor | database/migrations/2026_01_09_000001_add_quiz_project_fields_to_activities_table.php:37-39 | add_quiz_project_fields down() shrinks activities.type/status enums â€” irreversible and can fail or truncate data |
| schema-migrations | Nitpick | database/migrations/2026_01_09_142048_create_landing_content_table.php:30-35 | landing_content_versions.created_at is NOT NULL with no default/useCurrent and table has no updated_at |
| schema-migrations | Minor | database/migrations/2026_01_09_144446_add_new_question_types_to_activities_table.php:14-27 | Dead no-op migration duplicate-named with the real add_new_question_types migration |
| schema-migrations | Nitpick | database/migrations/2026_01_09_145131_create_parent_points_table.php:19-20, 27-39, 41-52 | parent_points polymorphic reference (reference_type/reference_id) has no index; parent_praises/parent_gifts lack any uniqueness for award idempotency |
| schema-migrations | Minor | database/migrations/2026_01_31_181517_remove_meanings_and_update_lessons_to_use_concepts.php:63-72, 93-98 | remove_meanings down() can create a duplicate concept-style FK pattern and never restores idx_meaning_order; up() data backfill silently no-ops if meaning_id absent |
| schema-migrations | Nitpick | database/migrations/2026_02_12_100000_add_school_id_to_bulk_messages.php:31-39 | bulk_messages down() drops school_id index that backs the FK and does not restore recipient_type type/default on SQLite |
| schema-migrations | Minor | database/migrations/2026_02_12_112900_add_completed_to_activity_submissions_status.php:20-22 | activity_submissions status enum down() removes 'completed' value with no data guard â€” fails or truncates existing rows |
| schema-migrations | Minor | database/migrations/2026_02_26_200000_create_practice_system_tables.php:46-76 | pvp_challenges/pvp_matches defined twice with divergent onDelete behavior across two create migrations |
| schema-migrations | Nitpick | database/migrations/2026_05_11_153712_add_school_id_fk_to_users.php:80-89 | add_school_id_fk_to_users down() drops FK but not the index it created (asymmetric reversal) |
| schema-migrations | Minor | database/migrations/2026_06_03_130000_add_status_to_family_activity_submissions.php:16-23 | New family_activity_submissions.status column has no index; hot pending-list query is unindexed and prior parent_approved index is stale |
| schema-migrations | Minor | database/migrations/2026_06_03_140000_add_missing_columns_to_team_activities.php:18-32 | team_activities now carries duplicate/redundant score+total_score and feedback+teacher_feedback columns |
| misc-correctness | Nitpick | app/Console/Commands/OptimizeImages.php:47-54 | OptimizeImages: DivisionByZeroError/TypeError are Errors not Exceptions - uncaught, abort run |
| misc-correctness | Minor | app/Http/Controllers/Admin/ActivityApprovalController.php:56-119 | Route-model binding {activity} accepts any Activity id; approve()/reject()/show() are not restricted to is_activity_bank bank submissions |
| misc-correctness | Nitpick | app/Http/Controllers/Admin/ActivityApprovalController.php:136-140 | bulkApprove() does not clear rejection_reason on approval (inconsistent with approve()) |
| misc-correctness | Minor | app/Http/Controllers/Admin/ActivityApprovalController.php:66-119 | approve()/reject() have no pending-status guard â€” already-reviewed activities can be re-processed, clobbering approver attribution and re-notifying |
| misc-correctness | Nitpick | app/Http/Controllers/Admin/ActivityApprovalController.php:68-115 | Approval status update and teacher notification are not wrapped in a DB transaction |
| misc-correctness | Minor | app/Http/Controllers/Admin/ActivityApprovalController.php:131-152 | bulkApprove() performs N individual update() calls and N notification existence checks inside a loop |
| misc-correctness | Minor | app/Http/Controllers/Admin/ActivityBankController.php:118-127, 147-159, 179-184, 202-208 | approveActivity/rejectActivity/approveQuestion/rejectQuestion lack a current-status guard (re-runs blindly overwrite approver/timestamp) |
| misc-correctness | Minor | app/Http/Controllers/Admin/ActivityBankController.php:123-139, 154-171, 184-194, 208-218 | Approve/Reject perform a model update followed by an external notification write with no surrounding DB::transaction |
| misc-correctness | Minor | app/Http/Controllers/Admin/ActivityManagementController.php:79-81, 93 | Auto-order computation is a non-atomic read-then-write (check-then-act) under concurrency |
| misc-correctness | Minor | app/Http/Controllers/Admin/ConceptManagementController.php:99-106 | update() can move a concept to a different value without re-sequencing order, allowing duplicate/colliding order |
| misc-correctness | Minor | app/Http/Controllers/Admin/ConceptManagementController.php:63-67 | Check-then-act race when auto-assigning concept order (non-atomic max+1) |
| misc-correctness | Minor | app/Http/Controllers/Admin/LessonManagementController.php:255-261 | toggleStatus promotes an 'archived' lesson straight to 'active' |
| misc-correctness | Minor | app/Http/Controllers/Admin/MessagesLogController.php:149-155 | destroy() permanently hard-deletes a message with no audit trail |
| misc-correctness | Nitpick | app/Http/Controllers/Admin/PageBuilderController.php:158 | preview() passes raw request value to json_decode without ensuring it is a string, unlike store/update which validate first |
| misc-correctness | Nitpick | app/Http/Controllers/Admin/PageBuilderController.php:47-53 / 104-110 / 158-160 | json_data decoded without size/depth bound; deeply nested or huge JSON can exhaust memory/CPU |
| misc-correctness | Minor | app/Http/Controllers/Admin/ParentManagementController.php:65 | school_id validated only with exists:schools,id â€” a parent can be assigned to an inactive/suspended school |
| misc-correctness | Minor | app/Http/Controllers/Admin/ParentManagementController.php:169-176 | QR-code generator uses a 4-digit rand() keyspace; uniqueness loop can hang once the namespace fills |
| misc-correctness | Minor | app/Http/Controllers/Admin/ReportsController.php:62-67 | Unvalidated Carbon::parse on request date params crashes the reports dashboard (500 / DoS) |
| misc-correctness | Minor | app/Http/Controllers/Admin/ReportsController.php:183-188 | students() applies raw unparsed request date strings to created_at filters |
| misc-correctness | Minor | app/Http/Controllers/Admin/SchoolManagementController.php:165-172 | QR code uses low-entropy rand in a check-then-act loop |
| misc-correctness | Minor | app/Http/Controllers/Admin/SettingsController.php:34-44, 64 | maintenance_message is collected and stored but omitted from the validator, bypassing the length cap applied to every other text field |
| misc-correctness | Minor | app/Http/Controllers/Admin/ShopManagementController.php:83-90 | update() deletes old image and stores new one outside a transaction; a failed DB update loses the image |
| misc-correctness | Minor | app/Http/Controllers/Admin/ShopManagementController.php:76-90 | is_limited / stock cannot be turned off via the form because absent checkbox keys are excluded from the validated update payload |
| misc-correctness | Nitpick | app/Http/Controllers/Admin/StudentManagementController.php:71, 80 | store() sets the string role column but never calls assignRole('student'); spatie role table left unsynced |
| misc-correctness | Minor | app/Http/Controllers/Admin/StudentManagementController.php:65, 115 | school_id only validated with exists:schools,id â€” an inactive or arbitrary school can be assigned via crafted request |
| misc-correctness | Minor | app/Http/Controllers/Admin/StudentManagementController.php:167-174 | QR code generator uses rand(1,9999): tiny namespace, predictable, collision/loop-exhaustion risk |
| misc-correctness | Nitpick | app/Http/Controllers/Admin/SurveyManagementController.php:232-243 | destroy() performs non-atomic check-then-act on response count |
| misc-correctness | Minor | app/Http/Controllers/Admin/SurveyManagementController.php:259-298 | exportResponses loads all responses and builds full row set in memory before streaming |
| misc-correctness | Minor | app/Http/Controllers/Admin/TeacherManagementController.php:189-196 | generateQRCode() uses non-cryptographic rand() over only ~9,999 codes â€” collision-retry loop can spin and the namespace can effectively exhaust |
| misc-correctness | Minor | app/Http/Controllers/Admin/TeacherManagementController.php:180-181 | toggleStatus() binary flip silently turns any non-'active' state (suspended / corrupted empty) into 'active' |
| misc-correctness | Minor | app/Http/Controllers/Admin/UserManagementController.php:182-186, 81, 89 | QR-code generator uses tiny rand(1,9999) space with check-then-act race; concurrent stores collide and 500 |
| misc-correctness | Minor | app/Http/Controllers/Admin/UserManagementController.php:108-136, 141-155, 160-166 | destroy()/update()/toggleStatus() can remove or demote the last super_admin, locking everyone out of /admin |
| misc-correctness | Nitpick | app/Http/Controllers/Admin/ValueManagementController.php:69-71 | Default order computed via non-atomic Value::max('order') + 1 (check-then-act race) |
| misc-correctness | Minor | app/Http/Controllers/Admin/ValueManagementController.php:141-145 | destroy() deletes the image file before deleting the row, no transaction |
| misc-correctness | Minor | app/Http/Controllers/Admin/ValueManagementController.php:115-123 | update() deletes/overwrites old image on disk before the DB update, no transaction |
| misc-correctness | Minor | app/Http/Controllers/Api/LandingContentController.php:92-102 | bulkUpdate performs snapshot + N setValue writes with no wrapping transaction â€” partial failure leaves content in an inconsistent half-saved state |
| misc-correctness | Minor | app/Http/Controllers/AuthController.php:300 (resend), 356, 507; app/Http/Controllers/SchoolAdminController.php:792,828; app/Http/Controllers/PublicRegistrationController.php:72,86,160,169,184 | Security and user-facing emails sent synchronously inside the HTTP request (inconsistent with queued path) |
| misc-correctness | Minor | app/Http/Controllers/BulkMessageController.php:103-108, 197-208 | school_admin with NULL school_id targets all orphaned (school-less) users platform-wide |
| misc-correctness | Minor | app/Http/Controllers/ContactController.php:58-86 | DB insert and email sends are not separated by a commit/queue boundary â€” failed send leaves a stored row but returns 500 (duplicate on retry) |
| misc-correctness | Minor | app/Http/Controllers/ContactController.php:63-72 | Public form synchronously sends a branded confirmation email to an arbitrary unverified address (mail-relay / backscatter / DoS amplifier) |
| misc-correctness | Minor | app/Http/Controllers/Health/HealthCheckController.php:114-130 | checkQueue() never checks the queue and always reports healthy |
| misc-correctness | Nitpick | app/Http/Controllers/Health/HealthCheckController.php:94-112 | checkStorage() uses a fixed filename on a shared disk -> concurrency race / false negatives |
| misc-correctness | Minor | app/Http/Controllers/LeaderboardController.php:261-307 | getSchoolLeaderboard cache key includes period but the closure ignores period, so different periods share/serve the wrong cached result |
| misc-correctness | Minor | app/Http/Controllers/MessagesController.php:164-358 | MessagePolicy exists but is never invoked; all authorization is ad-hoc canMessage logic |
| misc-correctness | Minor | app/Http/Controllers/NotificationController.php:63-72, 87-96 | AJAX markAsRead/delete return HTML 404 instead of JSON on missing/foreign id, breaking the JSON contract |
| misc-correctness | Minor | app/Http/Controllers/ParentDashboardController.php:435-456 | surveyComparison renders a view for a survey none of the parent's children took |
| misc-correctness | Minor | app/Http/Controllers/ParentDashboardController.php:31-35 | withCount('completed_submissions_count') is computed on every load but never used |
| misc-correctness | Minor | app/Http/Controllers/PublicRegistrationController.php:167-173 | Unauthenticated mail sent to attacker-supplied parent_email (mail-abuse / spoofing relay) |
| misc-correctness | Minor | app/Http/Controllers/SchoolAdminController.php:909, 933, 1202, 1213 | Methods dereference Auth::user()->school without null guard (dangling school_id) |
| misc-correctness | Minor | app/Http/Controllers/SchoolAdminController.php:187-218 | regenerate and toggle no-op on bad role yet flash success |
| misc-correctness | Minor | app/Http/Controllers/StudentController.php:308-314 | getStudentStats 'completed_today' counts every submission today regardless of status |
| misc-correctness | Minor | app/Http/Controllers/StudentController.php:228-233 | learningPath() uses 'completed'-only completion, inconsistent with DONE_STATUSES used elsewhere |
| misc-correctness | Minor | app/Http/Controllers/SuperAdminController.php:740-767 | approveQuestion performs three dependent writes without a DB transaction |
| misc-correctness | Minor | app/Http/Controllers/SuperAdminController.php:801-837 | storeQuestion can create answerable questions with no correct answer (true_false / multiple_choice with no correct option) |
| misc-correctness | Minor | app/Http/Controllers/SurveyController.php:73-76 | Required-answer check via empty() rejects legitimate 0 / '0' answers |
| misc-correctness | Minor | app/Http/Controllers/TeacherController.php:1217-1233 | assignTeamActivity creates assignment and fans out member notifications without a transaction |
| misc-correctness | Minor | app/Http/Controllers/TeacherController.php:1474-1482, 1516-1526 | analytics() runs ~38 sequential COUNT queries in daily/weekly loops |
| misc-correctness | Minor | app/Http/Middleware/CheckMaintenanceMode.php:21-32 | CheckMaintenanceMode admin bypass keys off only the primary role column and exempts registration |
| misc-correctness | Nitpick | app/Http/Middleware/CheckSchoolAccess.php:24-33 | CheckSchoolAccess uses loose != comparison and dereferences $user without a null guard on line 31 |
| misc-correctness | Minor | app/Http/Middleware/SecurityHeaders.php:52-53 | Content-Security-Policy is sent Report-Only in every environment â€” CSP never blocks anything |
| misc-correctness | Nitpick | app/Http/Middleware/SecurityHeaders.php:20-25 | SecurityHeaders does not strip Server / X-Powered-By fingerprinting headers |
| misc-correctness | Minor | app/Http/Requests/Profile/UpdateProfileRequest.php:25-26 | UpdateProfileRequest only checks current_password presence, never that it matches |
| misc-correctness | Minor | app/Listeners/SendBadgeEarnedNotification.php; app/Listeners/SendActivityGradedNotification.php; app/Listeners/SendWelcomeNotification.php; app/Services/NotificationService.php:NotificationService.php:15; SendBadgeEarnedNotification.php:13-21,34; SendActivityGradedNotification.php:13-21,38; SendWelcomeNotification.php:13-21,42 | NotificationService::create is static but invoked as an instance method via injected dependency |
| misc-correctness | Nitpick | app/Models/ParentTeacherMessage.php:39-45 | ParentTeacherMessage::markAsRead writes unconditionally (no already-read guard) |
| misc-correctness | Minor | app/Models/QuestionBank.php:30-35 (cast); corrupting call site app/Http/Controllers/TeacherController.php:1614-1624 | QuestionBank.options is double-JSON-encoded from TeacherController, corrupting stored options |
| misc-correctness | Minor | app/Models/RegistrationRequest.php:27 (cast); writers PublicRegistrationController.php:63,148,242 | RegistrationRequest casts data=>array but controllers store json_encode(...), defeating the cast |
| misc-correctness | Nitpick | app/Models/School.php:49-53 | Foreign-key / sort columns lack integer casts on School, SchoolBranch, EducationLevel, AcademicYear |
| misc-correctness | Nitpick | app/Models/School.php:133-148 | School::visibleValueIds() runs the activeValues query twice (existence check then fetch) |
| misc-correctness | Nitpick | app/Models/School.php:74-85 | School::students()/teachers() are role-constrained HasMany relations that would create role-less users if used for inserts |
| misc-correctness | Nitpick | app/Models/SchoolStatisticsCache.php:69-77 | getPercentile() reads attributes via a dynamic property name without whitelisting the scope |
| misc-correctness | Minor | app/Models/ShopItem.php:45-60 | Purchase availability (status/available_until) checked only before the locked transaction (TOCTOU) |
| misc-correctness | Minor | app/Models/User.php:521-543 | getActiveRoleAttribute reads HTTP session inside a model accessor and returns an un-revalidated role |
| misc-correctness | Nitpick | app/Models/Value.php:62-82 | Value::scopeVisibleForSchool issues a separate exists() probe on every call |
| misc-correctness | Nitpick | app/Models/ValueAssessment.php:7-32 | ValueAssessment model is defined but never written by any controller/route |
| misc-correctness | Minor | app/Policies/ActivityPolicy.php, app/Policies/ActivitySubmissionPolicy.php, app/Policies/LessonPolicy.php, app/Policies/MessagePolicy.php, app/Providers/AuthServiceProvider.php:AuthServiceProvider.php:15-20; all policy methods | All 4 Policies are registered but never invoked â€” authorization layer is inert |
| misc-correctness | Minor | app/Services/Backup/BackupService.php:156-196 | Temp MySQL dump cleanup relies on register_shutdown_function; DB password passed on mysqldump command line |
| misc-correctness | Nitpick | app/Services/Backup/BackupService.php:222-229 | PHP MySQL fallback dump uses addslashes() â€” corrupts binary/blob data and produces unreliable restores |
| misc-correctness | Minor | app/Services/NotificationService.php:22-32 | Notification dedup on (user_id,type,title) silently drops distinct messages within 5 minutes |
