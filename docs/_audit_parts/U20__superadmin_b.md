---
unit_id: U20
title: SuperAdmin-B (SuperAdminController — second half, rows 25..47)
scope: [app/Http/Controllers/SuperAdminController.php]
routes_total: 23
routes_traced: 23
status: complete
blockers: none
findings: { blocker: 1, major: 1, minor: 2, info: 2 }
---

## Boundary & ownership
- **First URI I own (boundary):** `GET admin/featured-activities` (`admin.featured-activities`, _ROUTES.txt line 54).
- U19 owns the first 24 SuperAdminController rows (ending at `GET admin/export/teachers`, line 53). I (U20) own rows 25..47 = **23 routes** (lines 54..107).
- All 23 owned routes' controller methods exist in `SuperAdminController.php`. No SuperAdminController route falls outside the U19/U20 split (47 total = 24 + 23). Gap-free.
- All routes gated by `Authorize:access-admin` → super_admin ONLY (AppServiceProvider:89-91). Other roles 403; not in their nav, so no broken UX for non-super-admin.

## Coverage table
| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/featured-activities | admin.featured-activities | featuredActivities | super-admin.featured-activities | super_admin | Major | view uses `concept->meaning->value` but no `meaning` relation → value badge always "غير محدد" (F-U20-002) |
| GET | admin/featured-activities/{id} | admin.featured-activities.show | showFeaturedActivity | super-admin.featured-activity-details | super_admin | Blocker | view file does NOT exist → 500 View not found (F-U20-001) |
| POST | admin/featured-activities/{id}/unfeature | admin.featured-activities.unfeature | unfeatureActivity | redirect back | super_admin | OK | form @csrf present; cols is_featured/featured_by/featured_at/featured_reason exist |
| POST | admin/import/students | admin.import.students | importStudents | redirect admin.excel-management | super_admin | OK | validates file+school_id; StudentsImport exists; redirects resolve |
| GET | admin/landing-page | admin.landing-page | landingPage | super-admin.landing-page | super_admin | OK | $themeSettings keys + $landingPage->json_data all used/passed |
| POST | admin/landing-page/add-block | admin.landing-page.add-block | addLandingBlock | JSON | super_admin | OK | AJAX; firstOrFail on slug=home (created by landingPage GET) |
| PUT | admin/landing-page/block/{id} | admin.landing-page.update-block | updateLandingBlock | JSON | super_admin | OK | AJAX; 404 JSON if block id missing |
| DELETE | admin/landing-page/block/{id} | admin.landing-page.delete-block | deleteLandingBlock | JSON | super_admin | OK | AJAX |
| POST | admin/landing-page/content | admin.landing-page.content | updateLandingContent | JSON | super_admin | OK | view calls via route() + fetch |
| POST | admin/landing-page/import-current | admin.landing-page.import-current | importCurrentLanding | JSON | super_admin | OK | parseLandingPageToBlocks() private helper present |
| POST | admin/landing-page/reorder-blocks | admin.landing-page.reorder-blocks | reorderLandingBlocks | JSON | super_admin | OK | AJAX |
| POST | admin/landing-page/theme | admin.landing-page.theme | updateLandingTheme | JSON | super_admin | OK | Setting::set/clearCache exist; view fields site_name/tagline/colors/font match |
| GET | admin/online-users | admin.online-users | onlineUsers | admin.online-users | super_admin | OK | data keys onlineUsers/totalOnline/stats all used; User::getRoleNameAr/Icon exist |
| GET | admin/online-users/api | admin.online-users.api | onlineUsersApi | JSON | super_admin | OK | same data source |
| GET | admin/pvp-challenges | admin.pvp-challenges.index | pvpChallenges | super-admin.pvp-challenges.index | super_admin | OK | $challenges passed; question_count accessor + matches_count(withCount) ok |
| POST | admin/pvp-challenges | admin.pvp-challenges.store | storePvpChallenge | redirect index | super_admin | OK | fields title/value_id/time_limit/difficulty/question_ids[]/is_active all match form |
| GET | admin/pvp-challenges/create | admin.pvp-challenges.create | createPvpChallenge | super-admin.pvp-challenges.create | super_admin | OK | $approvedQuestions + $values passed & used |
| DELETE | admin/pvp-challenges/{id} | admin.pvp-challenges.destroy | destroyPvpChallenge | redirect index | super_admin | OK | form @csrf+@method(DELETE) |
| POST | admin/pvp-challenges/{id}/toggle | admin.pvp-challenges.toggle | togglePvpChallenge | redirect back | super_admin | OK | form @csrf |
| GET | admin/question-bank | admin.question-bank.index | questionBank | super-admin.question-bank | super_admin | OK | $questions+$stats passed; rels creator/lesson/approver exist; status filter ok |
| POST | admin/question-bank/store | admin.question-bank.store | storeQuestion | redirect index | super_admin | OK | all 10 validated fields match modal form names incl options[*][text]/[is_correct] |
| POST | admin/question-bank/{id}/approve | admin.question-bank.approve | approveQuestion | JSON | super_admin | Minor | = C13-2: route() string passed in $data slot (arg 5) not $actionUrl (arg 6) → notification action_url null |
| POST | admin/question-bank/{id}/reject | admin.question-bank.reject | rejectQuestion | JSON | super_admin | Minor | = C13-2: same arg-order; reason field matches modal |

## Findings detail

F-U20-001 | Missing view `super-admin.featured-activity-details` | Controller/View | Blocker | app/Http/Controllers/SuperAdminController.php:1311 | `return view('super-admin.featured-activity-details', compact('activity'));` — no such blade exists (`resources/views/super-admin/` has featured-activities.blade.php but NOT featured-activity-details.blade.php) | Clicking the eye/"عرض التفاصيل" icon (featured-activities.blade.php:394, `route('admin.featured-activities.show', $activity->id)`) → 500 "View [super-admin.featured-activity-details] not found" | Confirmed-static | Create `resources/views/super-admin/featured-activity-details.blade.php` consuming `$activity` (with featuredBy/lesson/creator/submissions), OR repoint controller to an existing detail view | related: none

F-U20-002 | Featured-activities value badge uses non-existent `meaning` relation | View/Model | Major | resources/views/super-admin/featured-activities.blade.php:374-376 | `$activity->lesson->concept->meaning->value->name` — `Concept` model has only `value()` + `lessons()`, NO `meaning()` relation and no `Meaning` model exists; controller eager-loads `lesson.concept.value` (SuperAdminController:1284) confirming intended path is `concept->value` | Eloquent returns null for undefined `->meaning`, so the `@if` guard falls to `@else` → value badge ALWAYS renders "غير محدد" even when a value is correctly linked. No crash, silent wrong data | Confirmed-static | Change view path to `$activity->lesson->concept->value->name` and guard `$activity->lesson && $activity->lesson->concept && $activity->lesson->concept->value` | related: none

F-U20-003 | approveQuestion/rejectQuestion NotificationService arg-order | Controller | Minor | app/Http/Controllers/SuperAdminController.php:760-766, 790-796 | `NotificationService::create($userId,$type,$title,$message, route('teacher.question-bank.index'))` — the route() string lands in `$data` (5th param, cast to array) instead of `$actionUrl` (6th param) | Notification saved with `action_url=null`; clickable deep-link to teacher question-bank lost (data column stores a quoted string). Approve/reject itself succeeds | Confirmed-static | Pass `null` (or `[]`) as 5th arg and the route() as 6th: `create($id,$type,$title,$msg, [], route(...))`, or use `NotificationService::send(...)` | related: C13-2 (security audit)

F-U20-004 | Landing-page block AJAX routes have no in-view named route() reference | Wiring | Minor | resources/views/super-admin/landing-page.blade.php (blocks JS ~line 723+) | add-block/update-block/delete-block/reorder-blocks named routes are not emitted via `route(...)` in the blade (URLs appear hard-built in JS or partially wired); routes + controller methods all exist and are valid | Not user-visible by itself; if JS URL strings drift from the `admin/landing-page/...` paths the block editor calls would 404/405. Endpoints themselves are sound | Needs-runtime-confirm | Confirm JS builds correct method+URI per route; prefer emitting `@json(route(...))` | related: none

## Info / notes (not defects)
- I-U20-A: Shared layout `@extends('layouts.admin')` used by all my GET views — owned by U-GLOBAL; AJAX approve/reject and landing JS read CSRF from `meta[name="csrf-token"]`, which must be present in that layout (out of scope; note only).
- I-U20-B: `parseLandingPageToBlocks()` (private, SuperAdminController:1168) and `getOnlineUsersData()`/`getBackupsList()` helpers are internal, correctly used — not orphans. No orphan methods found in my half (all 23 routed; private helpers all called).
- Verified backing models/columns OK: question_bank (all storeQuestion cols), pvp_challenges (title/questions/time_limit/is_active/created_by/value_id/difficulty + fillable), activities featured_* cols, values.status enum, PageBuilder.json_data array cast, Setting::set/clearCache, TeacherPoint::updateTeacherPoints, QuestionBank::approve/reject. `teacher.question-bank.index` route resolves.
