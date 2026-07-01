---
unit_id: U10
title: Engagement-front (Notifications / Leaderboard / public Survey)
scope: [App\Http\Controllers\NotificationController, App\Http\Controllers\LeaderboardController, App\Http\Controllers\SurveyController]
routes_total: 12
routes_traced: 12
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 4, info: 2 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | leaderboard | leaderboard.index | LeaderboardController@index | view leaderboard.index | all authed | Major | view `@extends('layouts.admin')` for every role (F-U10-001) |
| GET | leaderboard/students | leaderboard.students | LeaderboardController@students | view leaderboard.students / JSON | all authed | Major | same admin-layout issue; vars OK |
| GET | leaderboard/teachers | leaderboard.teachers | LeaderboardController@teachers | view leaderboard.teachers / JSON | all authed | Major | same admin-layout issue; vars OK |
| GET | leaderboard/parents | leaderboard.parents | LeaderboardController@parents | view leaderboard.parents / JSON | all authed | Major | same admin-layout issue; vars OK |
| GET | leaderboard/schools | leaderboard.schools | LeaderboardController@schools | view leaderboard.schools / JSON | all authed | Major | same admin-layout issue; vars OK |
| GET | notifications | notifications.index | NotificationController@index | view notifications.index | all authed | OK | layout chosen by role map; vars OK |
| GET | notifications/fetch | notifications.fetch | NotificationController@fetch | JSON | all authed | Minor | orphan: no UI caller (F-U10-002) |
| POST | notifications/read-all | notifications.read-all | NotificationController@markAllAsRead | JSON {success} | all authed | OK | called by markAllAsRead() JS; CSRF via header |
| DELETE | notifications/{id} | notifications.delete | NotificationController@delete | JSON {success} | all authed | OK | findOrFail scoped to owner; CSRF via header |
| POST | notifications/{id}/read | notifications.read | NotificationController@markAsRead | JSON {success} | all authed | OK | CSRF via header |
| POST | survey/{survey} | survey.submit | SurveyController@submit | redirect back | all + guest | OK | form `survey/show.blade.php`; `answers[id]` matches; @csrf present |
| POST | survey/{survey}/submit | survey.ajax-submit | SurveyController@submit | JSON | all authed | OK | survey-popup component; `answers` payload matches |
| GET | api/pending-surveys | survey.pending | SurveyController@getPendingSurveys | JSON | all authed | Minor | orphan: no UI caller (F-U10-003) |

unowned (not my scope — listed per contract):
- GET `survey/{survey}` `survey.show` → `PagesController@showSurvey` (renders `survey/show.blade.php`; that view's submit form targets MY `survey.submit`). Owner: Pages unit.

## Findings detail

F-U10-001 | Leaderboard views hardcode admin layout for all roles | View/Wiring | Major | resources/views/leaderboard/{index,students,teachers,parents,schools}.blade.php:1 | all 5 blades `@extends('layouts.admin')`, yet routes carry only `auth` (web.php:159-165, inside the `auth` group at web.php:117 — no role gate; confirmed in _ROUTES.txt) so student/teacher/parent/school_admin can reach them | a non-super-admin who opens any /leaderboard* URL gets the admin chrome (admin sidebar, `css/admin.css`, admin-only nav links that 403 on click) instead of their own shell — wrong-role UX, not a 500 | Confirmed-static | give each view a role-aware `$layout` (as NotificationController@index already does via its layoutMap) and `@extends($layout)`, OR restrict the leaderboard route group to super_admin and point other roles at their own leaderboard pages (teacher already has teacher.leaderboard.* / student.leaderboard). | related: C02-4 (cross-school enumeration — security)

F-U10-002 | notifications.fetch is an orphan endpoint | Wiring/orphans | Minor | app/Http/Controllers/NotificationController.php:44 (route web.php notifications.fetch) | no blade/JS in the project references `route('notifications.fetch')` or the `/notifications/fetch` URL (grep across repo: none) | endpoint is live but unreachable from the UI; the index page renders its list server-side and never AJAX-fetches | Confirmed-static | wire a bell/dropdown poll to it, or drop the route+method if the header bell uses another source | —

F-U10-003 | survey.pending (getPendingSurveys) is an orphan endpoint | Wiring/orphans | Minor | app/Http/Controllers/SurveyController.php:138 (route web.php:46) | no reference to `/api/pending-surveys` or `route('survey.pending')` anywhere outside routes/docs (grep: only its own route def) | the mandatory-survey popup (`components/survey-popup.blade.php`, included in 4 role layouts) reads pending surveys from `session('pending_surveys')`, never from this JSON endpoint, so it is dead | Confirmed-static | remove the route+method, or have the popup hydrate from it for SPA-style refresh | —

F-U10-004 | SurveyController@show orphan method renders a missing view | Wiring/orphans | Minor | app/Http/Controllers/SurveyController.php:15-42 | method is not routed (`survey.show` → PagesController@showSurvey); it returns `view('surveys.show', ...)` but no `resources/views/surveys/` dir exists (the real public survey view is the singular `survey/show.blade.php`) | dead code today; would `View not found` 500 if ever wired | Confirmed-static | delete the unrouted `show()` method, or fix its view name to `survey.show` if intended to replace PagesController’s | —

F-U10-005 | Leaderboard nav links exist only in admin layout | Wiring/orphans | Minor | resources/views/layouts/admin.blade.php:359-379 | the 5 `route('leaderboard.*')` nav items appear only in admin chrome; student/teacher/parent layouts have no link to the canonical /leaderboard pages (teacher layout links its own teacher.leaderboard.* instead) | for non-admins the U10 leaderboard pages are reachable only by typing the URL — combined with F-U10-001 they have no correct entry point | Confirmed-static | if leaderboard is meant to be shared, add role-aware nav links and fix the layout per F-U10-001; if admin-only, gate the routes | related: F-U10-001

F-U10-101 | answers payload wiring verified clean (no answers→answer loss) | Validation↔Form | Info | survey/show.blade.php:324-434, components/survey-popup.blade.php:47-313, SurveyController@submit:72-77 | both the standalone form (POST `survey.submit`, `@csrf`) and the popup (JSON to `survey.ajax-submit`, X-CSRF-TOKEN header) send `answers[{question->id}]`; submit reads `$request->input('answers')` keyed by `$question->id`. SurveyResponse fillable casts `answers`→array. Field names match exactly. | n/a | n/a | Confirmed-static | none | distinct from C04-4 (Api submitActivity answers→answer); this web path is correct

F-U10-102 | Notification + leaderboard data/columns verified | Model/DB | Info | migrations create_notifications + add_title_message; create_points/teacher_points/parent_points/parent_student/classroom_student | notifications has notifiable_type/id, type, title, message, data(text), read_at, action_url — all model fillable/casts back. Leaderboard uses real relations (User::points, classrooms via classroom_student.student_id, school) and real tables/cols (points.points, teacher_points, parent_points, classrooms.teacher_id, schools.status/logo). | n/a | n/a | Confirmed-static | none | leaderboard `compact()` vars (data/tab/userRank, leaderboard/userRank/scope, leaderboard/schoolRank/period) all match their blades
