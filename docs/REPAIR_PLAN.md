# Wahy Platform — Repair Plan

**As of:** 2026-06-25 · **Mode:** PLAN-ONLY / PROPOSE-ONLY (this document changes no source; another session edits the tree live).
**Inputs:** `docs/FUNCTIONAL_INTEGRATION_AUDIT.md` (functional findings) + `PASS4_CONSOLIDATED_AUDIT.md` (security backlog — **note: `docs/REMEDIATION_AUDIT.md` was deleted by the concurrent session; PASS4 is the authoritative security source**).
**Method:** 22-agent orchestration — 7 lead domains × (Hunt → Design → Verify) + a completeness critic. Every fix re-grounded in current on-disk code (located by symbol, not stale line numbers) and adversarially verified. Raw designs/verdicts: workflow `w4hgkmunl`.

---

## 0. Verified state — read this first (overrides any stale snapshot)

- **`git` is ahead of the conversation's snapshot.** Economy (Batch 2 `6efca7c`), authz/teams IDOR (Batch 3 `3edb2d6`), and auth cluster-06 (Batch 4 `59a5990` + force-2fa revert) are **committed and FROZEN — do not re-open**. The S1–S7 auth test stubs and AuthController/AuthApiController/routes edits listed as "modified" are now committed. **Every editor must `git pull`/re-read before touching a file.**
- **`F-B03` (test-notifications 500) is already RESOLVED** — the live session added `resources/views/test-notifications.blade.php`. It is downgraded from Blocker to optional hygiene. **Do NOT delete the view** (that re-introduces the 500).
- **2FA-enforcement on admins = WONTFIX** (documented shared-hosting admin-lockout decision). Do not re-open the auth files for it.
- **`routes/web.php` is a MUTEX** — the single highest-collision file; only one session edits it per phase, re-reading first.
- **`TeacherController.php` is touched by 6 items** across this plan and the security backlog — assign one owner per method-cluster and run the economy/cross-tenant regression gate after every edit (see §9).

### Ownership at a glance
| Stream | Owns |
|---|---|
| **This repair plan** | D1 blockers, D2 storage, D3 blades, D4 fields (Wave 1), D5 wiring, D6 enum/errors/orphans, F-M11 |
| **Security session (parallel)** | D7 collisions: leaderboard tenant-scope, survey authz, exercise IDOR, Setting user_id, NotificationService arg-order, landing XSS; PHASE-6 (CSV injection, shop cascade, upload hardening) |
| **Frozen (committed)** | Economy, authz/teams, auth cluster-06 |
| **Reserved schema batch (held)** | D4-03B, D4-05, D4-06, suspended-ADD (only if chosen) |

---

## 1. Global execution order (the spine — 16 steps)

| # | Batch | Owner | Why here |
|---|---|---|---|
| 1 | **FROZEN** — economy/authz/auth committed | security (done) | do not re-open |
| 2 | **D1 P0 Blockers** (F-B01, F-B02) | repair-plan | only 2 live 500s on reachable paths; blade/controller-only, zero migration |
| 3 | **D2 Storage-URL** (helper→config→35 sites) | repair-plan **+ security sign-off** | cross-cutting root; gates dependent media; PII-exposure decision (G2) |
| 4 | **D3 Schema-drift Batch A** (single-token blade fixes) | repair-plan | blank-data on live views; D3-14 becomes no-op if D1-01 done first |
| 5 | **D4 Wave 1** (pure-code field fixes) | repair-plan | silent data-loss; reuse existing columns |
| 6 | **D5 PR-A** — `->except(['show'])` ×4 | repair-plan (routes mutex) | 4 guaranteed 500s; routes/web.php edit |
| 7 | **D5 PR-B** — leaderboard layout map | repair-plan (coordinate w/ COLLISION-01) | wrong-role chrome; blade/controller only |
| 8 | **D5 PR-C** — create-activity repoint (+ lesson_id, G3) | repair-plan | wrong-destination; verifier-flagged regression risk |
| 9 | **SEC-1** — NotificationService arg-order ×12 | security | mechanical, restores dropped links; do first among open security |
| 10 | **SEC-2** — cluster-02 residual authz (leaderboard tenant, survey, exercise IDOR, Setting user_id) | security | OPEN BOLA/tenant holes |
| 11 | **D6 enum + error pages** (suspended DROP, classrooms/teams, 419/503, shop parity) | repair-plan (coordinate suspended w/ COLLISION-04) | enum-overflow 500s + branded errors |
| 12 | **D6 orphan cleanup** (after sign-off) | repair-plan | deletions are higher-regret; gate on decide-list |
| 13 | **SEC-4** — landing stored-XSS sanitizer | security | OPEN C03-4 (Batch 6); gates step 14 |
| 14 | **SEC-5 / D5-09** — landing de-shadow (LAST routes mutation) | repair-plan + security (mutex) | must follow XSS sanitizer or a 2nd unsanitized writer reappears |
| 15 | **F-M11** — API activityDetails columns | repair-plan | missed finding; pure-code, pair with D4-09 (same controller) |
| 16 | **PHASE-6** — CSV injection, shop cascade, upload hardening | security | OPEN PASS4 clusters; out of this plan's scope |

---

## 2. Pre-merge decision gates (block the relevant batch until resolved — these are for you)

| Gate | Question | Recommendation |
|---|---|---|
| **G1 — storage prefix** | Does the deployed `public/storage` symlink resolve `/storage/data/<path>`? (On this Windows dev tree `public/storage` is a real dir, not a symlink → canonical is **inferred**.) | Confirm on the deployed host before sign-off. The whole D2 batch is parameterized through 2 knobs (helper + config), so a mismatch is a 2-line change. |
| **G2 — public-disk PII** | Canonicalizing storage URLs makes **activity-submission files, chat images, and family-activity photos publicly fetchable** at `/storage/data/<path>` with no ownership gate. Is that intended? | If **not**, those specific D2 items (D2-08/11/12/27/28/33) need a **private disk + gated download routes** (separate, larger work) — do not merge them until decided. The URL fix itself is correct; the exposure is pre-existing. |
| **G3 — create-activity lesson** | `storeActivity` validates `lesson_id` **required**, but the create form's lesson select is UI-optional. A bare repoint (D5-07) bounces every lesson-less submit. | Either make `lesson_id` required in the form, **or** relax `storeActivity` to `nullable|exists:lessons,id`. Pick per product intent; D5-07 is incomplete without it. |
| **G4 — `suspended` status** | Drop the spurious `suspended` value (code-only) or add it to the `users.status` enum (held migration)? | **DROP** (recommended): no migration ever writes it; suspend is done via toggle-status routes. |
| **G5 — register `school_admin`** | The public register form offers a `school_admin` option the validator rejects. | **Remove the option** (recommended) — self-service signup as an elevated role is a privilege-escalation surface; don't widen the validator. |
| **G6 — XSS ordering** | Landing de-shadow (step 14) before the XSS sanitizer (step 13) resurrects a 2nd unsanitized writer. | Strict ordering: sanitizer first. Security-owned. |

---

## 3. Repair batches owned by this plan

### Batch D1 — P0 Blockers
| ID | Fix | Test | Effort |
|---|---|---|---|
| **D1-01** (F-B01) | `admin/reports/activities.blade.php:56` → null-safe + correct column: `{{ $activity->lesson?->concept?->value?->icon }} {{ $activity->lesson?->concept?->value?->name ?? '—' }}`. (Pre-fixes the `emoji→icon` read so D3-14 becomes a no-op.) | `ReportsActivitiesNullLessonTest` — report renders 200 with a null-lesson activity | S |
| **D1-02** (F-B02) | Create `resources/views/super-admin/featured-activity-details.blade.php` — **`@extends('layouts.admin')`** (verifier correction, not `layouts.super-admin`); render only the 4 eager-loaded relations (`featuredBy`/`lesson`/`creator`/`submissions`) with `?->`; back-link `route('admin.featured-activities')`. *(Alt: remove the route + the eye-button at `featured-activities.blade.php:394`.)* | `FeaturedActivityDetailsTest` — details page renders 200 | M |
| **D1-03** (F-B03) | **No 500 fix** (view now exists). Optional hygiene: env-gate the dev route (`web.php:404-406`) **and** the nav `<li>` (`layouts/super-admin.blade.php:137-143`) together behind `app()->environment('local','staging')`. **Do NOT delete the view.** | `TestNotificationsRouteTest` (env-conditional) | S |
| **D1-04** (bonus) | Dead-code landmine: `SurveyController@show:41` returns `view('surveys.show')` (plural, missing) → rename to `view('survey.show')`. Unreachable today; don't add a route. | static view-name assertion | S |

### Batch D2 — Storage-URL canonicalization (one root, ~35 sites, **no migrations**)
**Canonical decision (verified):** public disk root = `storage_path('app/public/data')`; symlink maps `public/storage → storage/app/public`. Therefore a disk-relative path `P` is served at **`asset('storage/data/'.P)`**. Three broken conventions exist in the tree (`storage/`, `storage/<path>`, `storage/app/public/data/`).

**Two knobs fix everything:**
- **D2-00** — add a guarded helper to `app/Helpers/SettingsHelper.php`: `function media_url(?string $p): string { $p = ltrim((string)$p,'/'); return $p===''?'':asset('storage/data/'.$p); }` (already-autoloaded file; empty→`''` preserves caller fallbacks).
- **D2-01** — `config/filesystems.php` public disk `'url' => env('APP_URL').'/storage/data'` → **auto-fixes** the 3 `Storage::url()` sites (D2-10 EditorUpload, D2-11/12 submission `file_url`) with no per-site edits.

**Then convert the sites** (all `asset('storage/data/'.$p)` or `media_url($p)`):
| Group | Sites |
|---|---|
| Accessors/resources (fix ~30 consumer blades at once) | `User::getAvatarUrl*` (D2-02), `UserResource` avatar_url (D2-03), `LeaderboardController` logo + `avatarUrl()` (D2-04/05) |
| Upload-return endpoints | Theme (D2-06), ActivityManagement uploadImage (D2-07), chatUpload (D2-08 — **G2**), LandingContent (D2-09) |
| Layout/brand | favicon (D2-13), header logo (D2-14), auth logo (D2-15), email logo (D2-16, must be absolute), landing og:image+logo (D2-17), theme previews (D2-18) |
| Lesson/value/shop/submission/attachment blades | lessons show (D2-20) + student-view (D2-21) [edit D2-19 is the canonical reference — leave], values index/edit (D2-22/23), shop index/edit/student (D2-24/25/26), submission links (D2-27/28 — **G2**), activity attachment (D2-29/30), inline leaderboard avatar (D2-31), online-users blade+JS (D2-32), family-activity photos (D2-33 — **G2**), pages/show video+icon (D2-34, **also fixes a `.`/`??` precedence bug**) |
| JS literals (no PHP helper client-side) | online-users JS (D2-32), `public/js/page-builder-pro.js` (D2-35) — hard-code `'/storage/data/'` |
| Doc | api-documentation example strings (D2-36) |

**Verifier-flagged completeness (must do):**
- **Legacy orphan files (D2-38):** some media may physically sit under `storage/app/public/<path>` (pre-`/data` convention), which would 404 after canonicalization. Run a DB path-check on `settings.site_logo/favicon/hero_background`, `lessons.video_file/audio_file/image`, `submissions.file_path` for values present under `…/public/<path>` but not `…/public/data/<path>`. On evidence these are already-dead orphans — confirm before declaring victory.
- **Ops (D2-37):** post-deploy `php artisan view:clear` + `config:clear` (for D2-01) + `cache:clear` (leaderboard cache) + `composer dump-autoload` (new `media_url` symbol).

### Batch D3 — Schema-drift (root cause: the `meanings` table removal migration `2026_01_31_181517`)
**Batch A (reachable views, single-token, ship first):**
| ID | Fix |
|---|---|
| D3-06+07 | `featured-activities.blade.php` value cell — drop the dead `->meaning` hop: guard `@if($activity->lesson && $activity->lesson->concept && $activity->lesson->concept->value)`; body `{{ $activity->lesson->concept->value->name }}` |
| D3-08 | `pending-submissions` guard → `@if($submission->activity?->lesson?->concept?->value)` (body already correct) |
| D3-09 | `review-submission` guard → same as D3-08 |
| D3-10+11 | `teacher/review-single` → `value->name` and `concept->name` (cols are `name`, not `title`); **leave `lesson->title` — that one is correct** |
| D3-12 | `student/values-tree` → `$value->icon` (not `emoji`) |
| D3-13 / D3-15 | `admin/reports/values` + `reports/dashboard` Top-Values card → `$value->icon` |
| D3-14 | `admin/reports/activities:56` `emoji→icon` — **skip if D1-01 already corrected this line** |

**Batch B (orphaned `content-management.blade.php`, D3-01..05):** the view is **unrouted** (no controller/route renders it). Confirm reachability before spending effort; if dead, the whole file is a removal candidate. If kept: `$value->emoji→icon`, remove dead `meanings_count`/`$totalMeanings`/`$concept->meanings_count` stats, and **delete** the nested `meanings` loop (lines 120-146) — option B (rewrite to lessons) is unbuildable (no lessons route). Tests can't render an unrouted view → gate on wiring it to a controller first.

### Batch D4 — Form↔backend field mismatches
**Wave 1 (pure-code, no migration):**
| ID | Fix | Note |
|---|---|---|
| D4-01 | `updateActivity` validator: add `'duration_minutes'=>'nullable|integer|min:1'` | existing column |
| D4-02 | `updateActivity`: validate + store re-uploaded `attachment`; `unset` when no new file so a null doesn't wipe it | existing column |
| D4-04 | Mirror D4-01/02 on `storeActivity` (create parity) | **bundle D4-01/02/04 as ONE TeacherController edit** to avoid conflicting rewrites |
| D4-07 | PageBuilder `is_active`: `$request->has()` → **`$request->boolean('is_active')`** on both store/update | else a page can never be saved inactive |
| D4-08 | **Remove** the `school_admin` `<option>` from `register.blade.php:154` (**G5**) — not a validator widen | privilege boundary |
| D4-09 | API `submitActivity`: rename `'answers'→'answer'` (+ `json_encode` if array) to match the column | reuses `answer` text column |
| D4-03A | Store the single `document` upload into the existing `attachment` column now | full 4-media is D4-03B (held) |

**Reserved schema batch (held — never auto-apply, see §5):** D4-03B (activities media JSON), D4-05 (question_bank media JSON), D4-06 (`users.bio` + `users.notifications_enabled`).

### Batch D5 — Routing & wiring (no migrations)
| ID | Fix | Test |
|---|---|---|
| D5-01..04 | `->except(['show'])` on `Route::resource` for **users / teachers / students / parents** (no `show()` method/view → 500 on direct URL; not UI-linked). routes/web.php **mutex** edit. | `RouteResourceShowGapsTest` — `Route::has('admin.users.show')===false`; direct GET → 404 not 500 |
| D5-05+06 | **Atomic:** add `resolveLayout()` to `LeaderboardController` (role→layout map, mirror `NotificationController`) + switch the 5 leaderboard blades `@extends('layouts.admin')→@extends($layout)` | `LeaderboardLayoutTest` per role. **Coordinate with security COLLISION-01** (same controller carries C02-4/C09-3) |
| D5-07+08 | Repoint `create-activity.blade.php` form `action` → `route('teacher.activities.store')`; de-dup the `activity-bank.create` entry. **MUST also resolve `lesson_id` (G3)** or it regresses to a validation bounce. | `TeacherCreateActivityWiringTest` (POST without lesson_id) |
| D5-09 | **(step 14, after XSS)** Remove the 3 dead shadowed `LandingPageController` routes (`web.php:206-208`); SuperAdminController block wins. Optionally remove `admin/landing-page.blade.php`. | `LandingPageRouteShadowTest` |
| D5-10 | `SurveyManagementController` orphan CRUD (resource owned by `SurveyController`) — keep+comment or delete dead methods (retain `toggleStatus`/`exportResponses`). Low priority. | route-binding assertion |
| D5-11 | Add `RouteIntegrityTest` — no show-less resource registered, no duplicate (method,uri) or name. Author after baseline green. | self |

### Batch D6 — Enum alignment + error pages + orphans
| ID | Fix |
|---|---|
| **D6-08..14** (DECIDE-ONCE, **G4**) | **DROP `suspended`**: 8 validator sites (`UserMgmt 75/120`, `TeacherMgmt 70/142`, `StudentMgmt 68/118`, `ParentMgmt 69/120`) → `required|in:active,inactive`; remove the `suspended` `<option>`/filter/`@case`/CSS in `admin/users` create/edit/index. **Security collision C05-2/C10-1 — D6 owns the full set** (D7 COLLISION-04 under-scoped it to 4). |
| D6-01..03 | classrooms `inactive→archived` (validator `690`, edit dropdown, index badge label) |
| D6-04..05 | teams `inactive→archived` (validator `1126`, edit dropdown); D6-06/07 confirmed already-correct (no change) |
| D6-15 | Add branded Arabic `errors/419.blade.php` (clone `429`); **view-only** (keep Handler's form-friendly redirect) |
| D6-16 | Add branded Arabic `errors/503.blade.php` (Laravel auto-renders it; self-contained, no DB/auth calls) |
| D6-23 | ShopManagement `store()` status parity → `required|in:active,inactive,sold_out` (match `update()` + enum) |
| D6-17 | parent `sendGift` — **KEEP** (live economy code + passing test); add the missing gift-send UI rather than delete |
| D6-18..22 | Safe-delete dead code after sign-off: `ParentController@dashboard`/`@childDetail`, `StudentController::calculateScore`, orphan blades `parent/child-details`, `parent/children-reports` (re-grep each before removal) |
| D6-24..28 | **Negatives** (verified already-correct): toggle-status routes, bulk-approve, super-admin redirect aliases, activity/lesson/survey/school/value status validators — no change |

### F-M11 — missed finding (critic)
`Api/StudentApiController::activityDetails` reads non-existent columns: `instructions` (L230, no such column) and `attachments` (L232 — real column is `attachment` singular) → always null; plus an unguarded `$activity->lesson->…` on nullable `lesson_id`. **Fix:** `→ $activity->attachment`, drop/repoint `instructions`, null-guard the lesson chain. Pure-code; pair with **D4-09** (same controller).

---

## 4. Security-owned coordination (D7 collision map — NOT this plan; sequenced for non-conflict)

| Collision | Files | Functional ↔ Security | Owner / step |
|---|---|---|---|
| 01 Leaderboard | `LeaderboardController` | F-M02 layout ↔ **C02-4** tenant-enum + **C09-3** limit-clamp/wrong-rank-table | security (step 10); D5-05/06 layout can ride or follow |
| 02 Survey display | `PagesController::showSurvey`, `SurveyController::submit` | F-M05 area ↔ **C02-5** anon leak + **C02-8** target-role re-check | security (step 10) |
| 03 Exercise IDOR | `TeacherController` store/updateExercise | — ↔ **C02-6** classroom ownership | security (step 10) |
| 04 Status enum + show | Teacher/Student/User/ParentMgmt | F-M07 ↔ **C05-2/C10-1**; D6 owns the enum DROP (8 sites), D5 owns `->except` | split: D6 + D5 |
| 05 Notification arg-order | `NotificationService` ×12 sites | F-U14/U20 ↔ **C13-1/2** (URL in arg5 not arg6) | security (step 9, mechanical) |
| 06 Landing XSS | SuperAdmin add/updateLandingBlock + `landing-dynamic.blade` | F-M10 ↔ **C03-4** stored XSS | security (step 13); de-shadow at step 14 |
| 07 Setting user_id | `Setting.php` + `updateStreakSettings` | — ↔ **C02-7/C13-8** (atomic writer+API) | security (step 10) |
| 08 Economy regression boundary | `TeacherController` | — ↔ cluster 01/02 (CLOSED) | gate: run economy/cross-tenant tests after any edit |
| 09 Auth landed | Auth files | — ↔ cluster 06 (CLOSED) | unblocks routes mutex; 2FA-enforce = WONTFIX |
| 10 routes/web.php mutex | `routes/web.php` | F-M07 + F-M10 + cluster-06 | serialize: `->except` (step 6) → de-shadow (step 14) |

---

## 5. Reserved schema batch (HELD — each migration needs explicit approval + dry-run + reversible `down()`)

| Item | Migration | Then |
|---|---|---|
| D4-03B | `activities` media JSON column | fillable + cast + store image/audio/video |
| D4-05 | `question_bank` media JSON column | fillable + cast + store in `addQuestionToBank` |
| D4-06 | `users.bio` (text, null) + `users.notifications_enabled` (bool, default true) | fillable + cast; `updateSettings` then persists (already validated) |
| G4-ALT | `users.status` enum ALTER to add `suspended` | only if product rejects the DROP recommendation |
| (latent) | `2026_05_04_000002` settings.user_id migration `down()` re-adds `unique('key')` → fails on dup rows | rollback-hazard cleanup; non-blocking (up already applied) |

---

## 6. Operational steps (post-deploy; not source edits)
`php artisan view:clear` · `config:clear` (D2-01) · `cache:clear` (leaderboard) · `composer dump-autoload` (D2-00 helper) · **runtime-confirm G1** (`ls -l public/storage` is a symlink; `curl -I <APP_URL>/storage/data/<known-file>` = 200, `/storage/app/public/data/<file>` = 404) · **legacy-orphan DB path-check** (D2-38).

---

## 7. Test inventory (regression tests to add — lock each fix)
`ReportsActivitiesNullLessonTest` · `FeaturedActivityDetailsTest` · `MediaUrlHelperTest` + per-surface URL tests (avatar/logo/favicon/lesson/shop/submission) · `PublicDiskUrlTest` · the D3 value-badge/icon render tests · `UpdateActivityDurationTest` / `UpdateActivityAttachmentTest` / `StoreActivityParityTest` · `PageBuilderIsActiveTest` · `RegisterRoleWhitelistTest` · `SubmitActivityAnswerPersistedTest` · `RouteResourceShowGapsTest` / `RouteIntegrityTest` · `LeaderboardLayoutTest` · `TeacherCreateActivityWiringTest` (POST w/o lesson_id) · `ClassroomStatusEnumTest` / `TeamStatusEnumTest` / `UserStatusEnumTest` (+ Teacher/Student/Parent) · `ErrorPageTest` (419/503) · `ShopStatusParityTest` · `OrphanCleanupTest`. **Gate after any TeacherController edit:** `EconomyIdempotencyTest` (re-grade→409) + `CrossTenantTest` (403).

---

## 8. Open decisions for you
1. **G1** deployed storage symlink — confirm `/storage/data/` resolves before merging D2.
2. **G2** public-disk PII (submission/chat/family photos) — public, or private-disk + gated routes? Blocks D2-08/11/12/27/28/33.
3. **G3** `lesson_id` required vs nullable for create-activity.
4. **G4** `suspended` — DROP (recommended) vs add-to-enum (held migration).
5. **G5** confirm removing the `school_admin` register option.
6. **Reserved schema batch** — approve D4-06 (bio/notifications), D4-03B/D4-05 (media columns) individually when ready.
7. **Orphan cleanup (D6-17..22)** — sign off the delete-vs-keep list (sendGift = keep+add UI recommended).
8. **Implementation go-ahead** — this plan implements nothing. Applying it needs your explicit green light **and** coordination with the parallel security session (shared hot files: `routes/web.php`, `TeacherController.php`).

---

*Plan-only / propose-only. No application source, test, or config file was modified; no git state changed. Security cross-refs are to `PASS4_CONSOLIDATED_AUDIT.md` (the deleted `docs/REMEDIATION_AUDIT.md` is not authoritative). Raw per-item designs + adversarial verdicts: workflow `w4hgkmunl`; condensed authoring view retained in the session scratchpad.*
