# Wahy Platform — Functional & Integration Audit

**As of:** 2026-06-25 · **Mode:** STRICT READ-ONLY (no project file modified; writes confined to `docs/_audit_parts/` + this file).
**Method:** orchestrated multi-agent static trace. 24 worker units across 3 waves + a lead closure-reconciliation pass; each route traced controller→validation→model→view per role; first broken link flagged. Verified against actual code on disk.
**Denominator:** **422 routes** (402 web + 20 api) — authoritative list `docs/_audit_parts/_ROUTES.txt`. **Coverage: 422/422 (100%), zero gap.**
**Distinct from** the security audit (`docs/REMEDIATION_AUDIT.md`); known security-wiring bugs are cross-referenced by ID, not re-discovered.

---

## 1. Executive summary

The application is **broadly wired and renders correctly** — the five role portals (student, teacher, parent, school-admin, super-admin) and the public/auth surface all resolve their routes, forms carry `@csrf` with matching field names (no 419 risk found), navigation links resolve and are role-permitted, and the app is uniformly hardcoded-Arabic (no i18n missing-key risk). Against that healthy baseline the trace found **3 confirmed Blockers** (pages that 500 on a normal path: an admin report that dies on any activity lacking a lesson chain; a "view details" button bound to a non-existent view; a nav-linked `test-notifications` route whose view does not exist) and **~19 Majors** dominated by **four cross-cutting root causes** that each clear several rows at once: (a) an inconsistent **storage-URL convention** breaking media across avatars/logo/lesson-media/activity-images/shop/chat; (b) **schema-drift in Blade** — views reading renamed/removed model members (`value->title`, `value->emoji`, `lesson->meaning`) so data renders silently blank; (c) **form↔backend field mismatches** silently dropping submitted data (activity duration, media uploads, teacher bio/notifications, page `is_active`, API answer); and (d) `Route::resource` registering **`show` routes with no method/view** on four admin resources (500 on direct URL). The headline broken-chain count: **3 Blocker + ~19 Major + ~38 Minor + ~16 Info**. None are i18n or CSRF; almost all are render-time data wiring. Fixing the four cross-cutting roots resolves the large majority.

---

## 2. Work-tree & coverage ledger

Decomposition (full tree + per-unit scope in `docs/_audit_parts/_MANIFEST.md`):

```
app (422 routes)
├── cross-cutting:  UG global · UI i18n · UN nav
├── public/auth:    U01 auth · U02 public
├── role portals:   U03 student · U04/U05 teacher · U06/U07 school-admin · U08 parent
├── shared:         U09 messaging · U10 engagement · U11 mobile-api
└── admin panel:    U12 core · U13 curriculum · U14 activities · U15/U16 people ·
                    U17 engagement · U18 reports · U19/U20 super-admin · U21 page-builder
```

| Unit | Scope | Traced/Total | B/M/m/i |
|---|---|---|---|
| UG U-GLOBAL | shared infra (middleware, helpers, layouts, errors) | infra | 0/1/2/1 |
| UI U-I18N | lang parity | by-design clean | 0/0/1/2 |
| UN U-NAV | role nav reachability | 6 navs | 0/0/2/0 |
| U01 Auth & Account | Auth/AuthApi/Profile/RoleSwitch | 21/21 | 0/1/2/2 |
| U02 Public & Landing-front | Pages/PublicReg/Contact/EditorUpload | 15/15 | 0/0/2/2 |
| U03 Student | StudentController | 31/31 | 0/0/2/1 |
| U04 Teacher-A | TeacherController (1st half) | 28/28 | 0/3/3/0 |
| U05 Teacher-B | TeacherController (2nd half) | 27/27 | 0/3/3/1 |
| U06 SchoolAdmin-A | SchoolAdminController (1st half) | 26/26* | 0/1/3/1 |
| U07 SchoolAdmin-B | SchoolAdminController (2nd half) | 21/21* | 0/0/2/2 |
| U08 Parent | Parent/ParentDashboard | 11/11 | 0/0/3/1 |
| U09 Messaging | Messages/BulkMessage | 21/21 | 0/0/3/3 |
| U10 Engagement-front | Notification/Leaderboard/Survey | 12/12 | 0/1/4/2 |
| U11 Mobile API & Health | Api/Student, Health, sanctum, up | 9/9 | 0/1/2/0 |
| U12 Admin Core | Dashboard/UserMgmt/Settings/Theme | 17/17 | 1/1/2/1 |
| U13 Admin Curriculum | Value/Concept/Lesson Mgmt | 23/23 | 0/1/2/3 |
| U14 Admin Activities | ActivityMgmt/Bank/Approval | 20/20 | 0/1/4/2 |
| U15 Admin People-A | School/Teacher Mgmt | 18/18 | 0/1/3/1 |
| U16 Admin People-B | Student/Parent Mgmt | 16/16 | 0/0/3/2 |
| U17 Admin Engagement | Shop/Survey/SurveyMgmt | 18/18 | 0/1/5/2 |
| U18 Admin Reports/Logs | Reports/MessagesLog | 18/18 | 1/2/2/1 |
| U19 SuperAdmin-A | SuperAdminController (1st half) | 24/24 | 0/0/3/0 |
| U20 SuperAdmin-B | SuperAdminController (2nd half) | 23/23 | 1/1/2/2 |
| U21 PageBuilder/Landing | PageBuilder/LandingPage/Api LandingContent | 16/16 | 0/2/4/3 |
| LEAD Closure reconciliation | 11 closures | 11/11 | 1/0/0/0 |

**Coverage assertion — PASSED.** 411 controller routes + 11 closures = **422/422**. `*`SchoolAdmin U06∪U07 = the 42 SchoolAdminController routes (clean boundary at `_ROUTES.txt` line 297/298; the 26+21 sum is mid-range double-tracing, **no gap**). SuperAdmin U19∪U20 = 47 gap-free (boundary `admin/export/teachers` → `admin/featured-activities`). The 11 closures: `storage/{path}`×2 (UG), `up` (U11), 7 `super-admin/*` backward-compat redirects to existing `admin.*` routes (OK), and `school-admin/test-notifications` (**Blocker** — see F-B03). No route is unaccounted; no category truncated.

> **Section-3 scoping (transparent, not a silent cap):** the exhaustive *one-row-per-route* coverage tables (all 422, including every `OK` row) live in the 24 partial files under `docs/_audit_parts/` (each unit's `## Coverage table`). This deliverable carries the **coverage proof** (ledger above) plus the **complete enumeration of every non-OK route** (§4). Every OK route is recorded in its unit partial; nothing is dropped.

---

## 3. Route coverage — verdict rollup

| Verdict | Count | Where enumerated |
|---|---|---|
| OK (renders & wired) | ~378 | per-unit partials in `docs/_audit_parts/` |
| Blocker | 3 | §4 (F-B01..F-B03) |
| Major | ~19 | §4 |
| Minor | ~38 | §4 + partials |
| Info | ~16 | partials |

Per-route OK rows: see the `## Coverage table` in each `docs/_audit_parts/U##__*.md`.

---

## 4. Per-finding detail (problem rows only)

### 🔴 Blockers (page 500s on a normal path)

**F-B01** (was F-U18-001) · **Activities report 500s on any lesson-less activity** · Layer: View/Blade · `resources/views/admin/reports/activities.blade.php:56` · Confirmed-static.
The Value badge chains `$activity->lesson->concept->value->emoji/->name` with **bare `->`**, but `activities.lesson_id` is nullable. Any activity with no lesson chain throws "attempt to read property on null" → the **whole admin activities report 500s** for super_admin. The sibling PDF template and `ActivitiesExport` already null-guard this; the blade doesn't. **Fix (propose):** null-safe operators `?->` (and fix `emoji`→`icon`, see F-M04). Related: F-M04. Source: U18.

**F-B02** (was F-U20-001) · **"View details" on Featured Activities → missing view → 500** · Layer: Controller/View · `SuperAdminController@showFeaturedActivity` returns `view('super-admin.featured-activity-details')` which **does not exist** · Confirmed-static.
Clicking the details icon on `admin/featured-activities` 500s with ViewNotFoundException. **Fix:** create the view or remove the route + UI trigger. Source: U20.

**F-B03** (lead closure pass) · **`school-admin/test-notifications` → missing view → 500, and it is nav-linked** · Layer: Routing/View · `routes/web.php:404-406` `return view('test-notifications')`; view absent anywhere (glob confirmed); link present in the super-admin nav (per U-NAV) · Confirmed-static.
A school_admin (or super_admin via the nav) clicking it 500s. **Fix:** remove this dev/test route + its nav link, or add the view. Source: LEAD/UN.

### 🟠 Majors

**F-M01** · **Cross-cutting storage-URL inconsistency → broken media** · Layer: View/asset · Needs-runtime-confirm.
The `public` disk root is `storage/app/public/data` (config/filesystems.php), but URLs are built three different ways across the app: `asset('storage/'.$p)` (lessons/show, theme), `asset('storage/data/'.$p)` (lesson edit — correct via symlink), `asset('storage/app/public/data/'.$p)` (avatars, logo, favicon, activity uploadImage, shop image, chat upload). At most one convention resolves through the `public/storage`→`storage/app/public` symlink; the others 404. `UserResource.php:19` uses the symlink-correct form, proving the inconsistency. **User-visible:** blank avatars, missing site logo/favicon, broken lesson video/audio, broken activity/shop images, broken chat images. Marked Needs-runtime-confirm because a non-standard webserver alias could validate one variant — **confirm the deployed alias before changing.** **Fix:** one canonical URL accessor/helper; route every media URL through it. Subsumes F-UG-001, F-U13-001, F-U14-001, U12-theme, U17-shop, F-U09-001. Source: UG, U09, U12, U13, U14, U17.

**F-M02** · **Leaderboard renders admin chrome for non-admin roles** · Layer: View · `resources/views/leaderboard/*.blade.php` all `@extends('layouts.admin')`; routes carry only `auth` (no role gate) · Confirmed-static.
A student/teacher/parent opening any `leaderboard/*` URL gets the admin sidebar + admin-only nav whose links 403 on click — wrong-role UX (not a 500). `NotificationController@index` does this correctly via a role→layout map; leaderboard doesn't. **Fix:** role→layout map for leaderboard views. Source: U10.

**F-M03** · **Schema-drift: blades read renamed/removed model members → silent blank data** · Layer: Model/View · Confirmed-static. A family of view bugs:
- `value->title` / `concept->title` on the grading page (`review-single.blade.php`) — column is **`name`** → labels blank (F-U05-001).
- `value->emoji` in reports dashboard/values/activities views — column is **`icon`** → value icons blank (F-U18-002; the controller comment already admits this).
- `lesson->meaning` in `admin/pending-submissions` & `review-submission` value badge — **no `meaning` relation** on `Lesson` → badge always `-` (F-U12-002).
- `concept->meaning->value` in `featured-activities.blade.php:374` — **no `meaning` relation** on `Concept` → badge always "غير محدد" (F-U20-002).
**User-visible:** value/concept names, icons, and badges silently render blank/placeholder on grading, review, reporting, and featured pages. **Fix:** correct to `name` / `icon` / `concept->value`; the same `meaning` ghost relation recurs — grep and purge. Source: U05, U12, U18, U20.

**F-M04** · **`$value->emoji` non-existent column** — folded into F-M03 (icons blank across 3 report views). Source: U18.

**F-M05** · **Teacher "New activity" silently creates a pending bank submission, not a class activity** · Layer: Wiring/Form · `teacher.activities.create` renders a form posting to `activity-bank.store`; `storeActivity`/`teacher.activities.store` is consequently an orphan route · Confirmed-static.
A teacher publishing an activity actually files an admin-approval bank request — the activity never appears for the class. **Fix:** point the create form at `teacher.activities.store` (or make the bank flow explicit in the UI). Source: U04.

**F-M06** · **Silent data-loss cluster: form sends data the backend drops** · Layer: Validation↔Form/Model · Confirmed-static:
- `updateActivity` ignores `duration_minutes` (validator only allows `quiz_duration`; both are real columns) → edited duration dropped (F-U04-002).
- Activity `attachment` upload + create-activity media (image/audio/video/document) never validated/stored (F-U04-003/004).
- `create-question` media uploads discarded (no columns) (F-U05-003).
- `updateSettings` accepts `bio` + `notifications_enabled` but neither is in `User::$fillable`/schema → teacher bio & notification toggle never save despite a success message (F-U05-002).
- Page-builder `store/update` use `$request->has('is_active')` on a `<select>` that always submits `1`/`0` → `has()` always true → **a built page can never be disabled/hidden** (F-U21-001). Fix: `$request->boolean('is_active')`.
- API `submitActivity` writes `answers` vs fillable `answer` → mobile student answer lost (F-U11-001 = **C04-4**, security audit).
**Fix:** reconcile each form's fields with validator + `$fillable` + columns; add the missing upload handling. Source: U04, U05, U11, U21.

**F-M07** · **`Route::resource` registers `show` with no method/view → 500 on direct URL** · Layer: Routing · Confirmed-static.
`admin.users.show` (F-U12-001), `admin.teachers.show` (F-U15-001), `admin.students.show` & `admin.parents.show` (F-U16-002) all auto-register a GET `{id}` route, but the controllers define no `show()` and no `show.blade.php` exists → direct hit 500s. **Not UI-linked** (index links only to edit/delete), so reachable by typed URL / stale bookmark only — hence Major, not Blocker (U12's worker rated it Blocker; reconciled to Major on the orphan basis). **Fix:** `->except(['show'])` on these four resources, or implement the method+view. Source: U12, U15, U16.

**F-M08** · **Classroom status enum mismatch** · Layer: Validation↔Model · `classrooms.status` enum is `('active','archived')` but `updateClassroom` validates `in:active,inactive` and `classrooms/edit.blade.php` offers an `inactive` option · Confirmed-static.
Selecting "غير نشط" writes an out-of-enum value → 500 (MySQL strict) or a classroom that silently vanishes from all `status='active'` listings (non-strict). `storeClassroom` hardcodes `active`, so only edit/update is affected. **Fix:** align validator + dropdown to `active`/`archived`. Distinct from the users-table enum (which is correct). Source: U06.

**F-M09** · **Register form offers an unaccepted role** · Layer: Validation↔Form · `register.blade.php` lists a `school_admin` ("مدير مدرسة") option, but `AuthController::register` validates `role` as `in:teacher,student,parent` · Confirmed-static.
Choosing it always bounces back with a confusing validation error. **Fix:** remove the option (or support the role). Source: U01.

**F-M10** · **`LandingPageController` fully route-shadowed (dead code, latent trap)** · Layer: Routing · `routes/web.php:206-208` register `admin/landing-page|theme|content` URIs identical to `SuperAdminController`'s block (354-356) in the same group; the later SuperAdmin routes win → all three `LandingPageController` methods are unreachable · Confirmed-static.
No visible breakage today (near-identical duplicate), but a maintenance trap. **Fix:** remove the shadowed controller/routes or de-duplicate. Source: U21.

**F-M11** · **API `activityDetails` reads non-existent columns** · Layer: Model · `Api/StudentApiController` reads `instructions`/`attachments` (columns are `attachment` singular; no `instructions`) → always null · Confirmed-static. Also F-U11-002: unguarded `$activity->lesson->...` on nullable `lesson_id` (Needs-runtime-confirm). **Fix:** correct column names + null-guard. Source: U11.

### Security-audit cross-references (functional manifestation — listed for completeness, detailed in `REMEDIATION_AUDIT.md`)
- **F-U11-001 = C04-4** answers→answer (folded into F-M06). · **F-U15-002/F-U16-001 = C05-2/C10-1** `status=suspended` not in users enum. · **F-U17-001 = C12-1** shop hard-delete cascades student purchases. · **F-U17-002 / U18 CSV = C03-5** export formula-injection. · **F-U14-005 / F-U20-003 = C13-1/C13-2** `NotificationService::create` arg-order (action_url null). · **F-U21 restoreVersion = C13-5** non-transactional truncate.

### 🟡 Minor (orphans + cosmetic — full list in partials)
Orphaned routes/methods with no UI entry: parent `sendGift` (F-U08-001), dead `ParentController@dashboard/childDetail` (F-U08-002), `toggle-status` on schools/teachers/values (F-U15-003, F-U13-003), `bulkApprove` (F-U14-004), `feature/unfeature` teacher routes (F-U04-006), `SurveyManagementController` entirely orphaned (shadowed by `Route::resource('surveys', SurveyController::class)`) with a divergent var contract (F-U17), `StudentController@calculateScore` dead (F-U03-001), `student.analytics` no nav entry (F-U03-002), `notifications.fetch`/`survey.pending`/`unreadCount` no callers (F-U10/F-U09). Orphaned blades: `parent/child-details`, `parent/children-reports`, `auth/two-factor-verify-new`, flat `teachers.blade`/`students.blade`, `layouts/student`/`layouts/app` (F-UN-002). Cosmetic/edge: missing branded `errors/419` & `errors/503` pages (F-UG-002/003), 3 dead `route()` calls inside Blade comments (F-UN-001), reject form omits `rejected_reason` (F-U07-001), `json_decode` on an already-cast array (F-U07-002), unguarded edge nullables (F-U14-002/003, F-U18 `read_at` sort fallback), status dropdowns omitting `archived` (F-U13-002), garbled heading char (F-U16). Full rows: `docs/_audit_parts/`.

---

## 5. Per-role flow summary (core flows wired end-to-end?)

| Role | Core flow | E2E? | Broken links |
|---|---|---|---|
| **Anonymous** | landing → register → login → reset | ✅ mostly | register offers dead `school_admin` role (F-M09); `showSurvey` leak = C02-5 |
| **Student** | login → dashboard → lesson/exercise/PvP/shop | ✅ web wired | mobile API loses answer (F-M06/C04-4); `activityDetails` null cols (F-M11); leaderboard shows admin chrome (F-M02) |
| **Teacher** | dashboard → classes → grade/teams/activities | ⚠️ renders, silent data-loss | new-activity→bank (F-M05); duration/media/bio dropped (F-M06); grading labels blank (F-M03) |
| **Parent** | dashboard → children → gift/praise/approve | ✅ wired | gift unreachable from UI (F-U08-001, orphan) |
| **School-admin** | dashboard → people CRUD → import/stats | ✅ wired | classroom status enum (F-M08); `test-notifications` 500 (F-B03); shared storage/relation issues |
| **Super-admin** | dashboard → CMS/people/reports/super tools | ⚠️ 2 Blockers | activities report 500 (F-B01); featured-details 500 (F-B02); value badges blank (F-M03); resource-show 500s (F-M07); page un-disable (F-M06) |

---

## 6. Wiring-integrity summary

- **Unresolved `route()` names:** none live (3 dead calls exist only inside Blade comments — F-UN-001).
- **Missing views:** `super-admin.featured-activity-details` (F-B02), `test-notifications` (F-B03), and the four resource `show` views (F-M07). Several orphan blades render for nobody.
- **i18n parity:** ✅ clean — app is uniformly hardcoded-Arabic; zero `__()/@lang` user-facing keys outside vendor pagination (which resolves in `ar`); locale hard-pinned to `ar`; `ar/validation.php` complete. No missing-key risk.
- **Broken assets:** the storage-URL convention split (F-M01) is the only asset-integrity issue — but it's broad.
- **Verb/CSRF:** ✅ every POST/PUT/PATCH/DELETE form carries `@csrf`/`@method`; no 419 risk found.
- **Orphans:** numerous routes/methods/blades defined but unreachable (§4 Minor) — cleanup, not breakage. Notably `SurveyManagementController` and `LandingPageController` are wholesale shadowed (F-M10).

---

## 7. Prioritized fix roadmap (cross-cutting first — each clears several rows)

| Priority | Fix | Clears | Type |
|---|---|---|---|
| **P0 — Blockers** | Null-guard the activities-report value chain (F-B01); create-or-remove `featured-activity-details` (F-B02); remove-or-create `test-notifications` + its nav link (F-B03) | 3 Blockers | render 500s |
| **P1 — Storage URL** | One canonical media-URL helper; route avatars/logo/favicon/lesson/activity/shop/chat through it (**confirm deployed webserver alias first**) | F-M01 (~6 sites) | broken media |
| **P2 — Blade schema-drift** | Replace `value->title`→`name`, `value->emoji`→`icon`, purge `lesson->meaning`/`concept->meaning` (use `concept->value`); audit for other renamed members | F-M03/M04 (4+ views) | silent blank data |
| **P3 — Form↔backend fields** | Reconcile `is_active` (boolean), `updateSettings` fillable, `updateActivity` duration, activity/question media storage, register role list, API `answer` | F-M06, F-M09 | silent data-loss |
| **P4 — Resource `show`** | `->except(['show'])` (or implement) on users/teachers/students/parents | F-M07 (4 routes) | direct-URL 500 |
| **P5 — Wrong-target wiring** | Leaderboard role→layout map (F-M02); new-activity→`teacher.activities.store` (F-M05); de-shadow `LandingPageController`/`SurveyManagementController` (F-M10) | F-M02/M05/M10 | UX/dead code |
| **P6 — Enum + cosmetics** | Classroom status enum (F-M08); branded `errors/419`+`503`; orphan cleanup | F-M08 + Minors | edge/cosmetic |

---

## 8. Open questions / not statically verifiable

1. **Storage URL (F-M01):** needs runtime confirmation of the deployed webserver alias — one of the three conventions may be valid in production. Verify before mass-editing.
2. **`test-notifications` (F-B03):** intended dev-only route to delete, or a feature needing a view? (Currently nav-linked → 500.)
3. **Resource `show` routes (F-M07):** intended (implement detail pages) or accidental (remove)? Four resources affected.
4. **F-B01 reproduction:** the activities report only 500s if at least one activity has a null lesson chain — confirm such rows exist in production data (the bug is in the code regardless; the trigger is data-dependent).
5. **Files-in-flux caveat:** the auth controllers/routes were being edited concurrently earlier in this session; the trace read stable on-disk versions, but re-confirm U01/U06 findings if those files moved again.
6. **`is_active` / parent-child linking:** parent-management CRUD never writes the `parent_student` link (F-U16 Info) — confirm linking lives elsewhere (intentional) vs missing.

---

*Appendix — traceability:* per-unit partials with full one-row-per-route coverage tables and raw findings: `docs/_audit_parts/U*__*.md`; work manifest + coverage assertion: `docs/_audit_parts/_MANIFEST.md`; route denominator: `docs/_audit_parts/_ROUTES.txt`. Generated in read-only mode — no application code, test, or config file was modified; no git state changed.
