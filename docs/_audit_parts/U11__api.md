---
unit_id: U11
title: Mobile API & Health
scope: [app/Http/Controllers/Api/StudentApiController.php, app/Http/Controllers/Health/HealthCheckController.php, Laravel\Sanctum\Http\Controllers\CsrfCookieController@show (vendor), framework up/health route]
routes_total: 9
routes_traced: 9
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 2, info: 1 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | api/v1/student/dashboard | — | StudentApiController@dashboard | JSON | student (auth:sanctum, CheckRole:student) | OK | stats use `points()/coins()/badges()/activitySubmissions()/streak?->current_streak`; all relations+cols verified on disk (streaks.current_streak, points.points, coins.coins exist). `DONE_STATUSES` const exists. Eager `activity.lesson.concept.value` chain all resolve. |
| GET | api/v1/student/values-tree | — | StudentApiController@valuesTree | JSON | student | OK | `Value::with(['concepts.lessons'])`; relations Value::concepts, Concept::lessons exist. cols name/icon/image exist (image added 2026_01_07_133312). |
| GET | api/v1/student/activities | — | StudentApiController@activities | JSON | student | Minor | Works. `$activity->lesson->id` (line 177) is UNGUARDED — `activities.lesson_id` is nullable; a null-lesson activity would fatal here (F-U11-002). Needs-runtime-confirm (depends on data). |
| GET | api/v1/student/activities/{id} | — | StudentApiController@activityDetails | JSON | student | Minor | findOrFail+school check OK. `$activity->instructions` (l230) & `$activity->attachments` (l232) are NON-existent columns → always serialize as null (no `instructions` migration; column is `attachment` singular). Non-fatal data gap (F-U11-003). `$activity->lesson->id/title/content` unguarded same as F-U11-002. |
| POST | api/v1/student/activities/{id}/submit | — | StudentApiController@submitActivity | JSON | student | Major | C04-4 (security audit): validates+writes `answers` but fillable/column is `answer` (singular) → mass-assign drops it, answer lost silently (F-U11-001). |
| GET | api/v1/student/badges | — | StudentApiController@badges | JSON | student | OK | `badges()` belongsToMany user_badges withTimestamps; `$badge->pivot->created_at` valid (pivot has timestamps). badges cols name/desc/icon exist. |
| GET | api/v1/student/leaderboard | — | StudentApiController@leaderboard | JSON | student | OK | `withSum('points','points')` → points_sum_points alias; col exists. School-scoped (`where school_id`), so C02-4 cross-school enum does NOT apply to this API copy. user_rank false-handling correct. |
| GET | health | health.ping | HealthCheckController@ping | JSON | web (anonymous) | OK | static JSON, no DB touch. |
| GET | health/detailed | health.detailed | HealthCheckController@detailed | JSON | super_admin (auth + CheckRole) | OK | each check wrapped in try/catch → never fatals; returns 200/503. |
| — | up | — | Closure (framework health) | framework response | anonymous | OK | framework default `health:'/up'` in bootstrap/app.php; no app code. |
| — | sanctum/csrf-cookie | sanctum.csrf-cookie | CsrfCookieController@show (vendor) | sets cookie | web | OK | vendor default, out of unit scope, no fatal. |

## Findings detail

F-U11-001 | submitActivity writes `answers` but fillable/column is `answer` | Validation↔Model | Major | app/Http/Controllers/Api/StudentApiController.php:271,290 | `$request->validate(['answers'=>...])` + `'answers' => $request->answers` in mass-assign array; ActivitySubmission `$fillable` (model l24) and migration column are `answer` (singular); whole rest of app (StudentController, SubmitActivityAction, ActivityGradingService) reads `answer` | mobile student submits an activity, gets 200 "تم تقديم النشاط بنجاح", but `answer` persists NULL — answer is silently lost, teacher review/auto-grading sees nothing | Confirmed-static | rename validation key + payload key to `answer` (`'answer' => $request->answer`) to match fillable column; or add accessor mapping | = C04-4 (security audit)

F-U11-002 | Unguarded `$activity->lesson->...` on nullable lesson_id | Controller/Model | Minor | app/Http/Controllers/Api/StudentApiController.php:177-179,235-238 | `activities.lesson_id` is `nullable()` (create_activities migration l16); code does `$activity->lesson->id` with no null-guard (only the inner `->concept->value->title ?? null` is guarded, not `lesson` itself) | if any active activity has lesson_id=NULL, the dashboard/activities/details endpoints throw (500 attempt-to-read-property-on-null) for that row | Needs-runtime-confirm | null-guard: `$activity->lesson?->id` / `optional($activity->lesson)` | —

F-U11-003 | activityDetails serializes non-existent `instructions`/`attachments` columns | Model/DB | Minor | app/Http/Controllers/Api/StudentApiController.php:230,232 | `$activity->instructions` — no `instructions` column in any migration; `$activity->attachments` — column is `attachment` (singular, added 2025_12_17_154641) | mobile app always receives `instructions:null, attachments:null` even when an attachment exists → attachment feature appears broken on detail screen | Confirmed-static | use existing column `attachment`; drop or implement `instructions` | —

## Notes
- `unowned:` api/v1 AuthApiController routes (change-password/logout/profile/updateProfile, _ROUTES.txt l194-198) belong to U01 — skipped per dispatch.
- Health `up` route + sanctum csrf-cookie are framework/vendor defaults; no project code to fail.
- Info: submitActivity re-submit guard checks `status === 'completed'` (l280) but creates with `'pending'` and teachers set `'approved'` (not `completed`) — guard may never trigger on the approved path; functional edge, not a wiring fault.
- Verified the previously-reported crash bugs are FIXED on disk: leaderboard uses `points_sum_points` (not `amount`); dashboard uses `streak?->current_streak` (streaks.current_streak exists); valuesTree uses `concepts.lessons` after meanings→concepts migration (no `meanings` reference). No `amount`/`streaks`/`meanings` crash present.
