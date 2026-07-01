---
unit_id: U12
title: Admin Core (Dashboard / UserManagement / Settings / Theme)
scope: [app/Http/Controllers/Admin/DashboardController.php, app/Http/Controllers/Admin/UserManagementController.php, app/Http/Controllers/Admin/SettingsController.php, app/Http/Controllers/Admin/ThemeController.php]
routes_total: 17
routes_traced: 17
status: complete
blockers: none
findings: { blocker: 1, major: 1, minor: 2, info: 1 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/dashboard | admin.dashboard | DashboardController@index | admin.dashboard | super_admin | OK | All 13 compact() vars passed; all referenced admin.* routes resolve; points/users relations + cols exist |
| GET | admin/pending-submissions | admin.pending-submissions | DashboardController@pendingSubmissions | admin.pending-submissions | super_admin | Major | `$stats`,`$submissions` OK; but value badge gated by non-existent `lesson->meaning` rel (F-U12-002) |
| GET | admin/review-submission/{id} | admin.review-submission | DashboardController@reviewSubmission | admin.review-submission | super_admin | Major | findOrFail OK; same `meaning` always-false guard hides value block (F-U12-002) |
| POST | admin/review-submission/{id} | admin.save-review | DashboardController@saveReview | redirect admin.pending-submissions | super_admin | OK | Form @csrf + fields status/score/feedback match validator; PointsService::awardStudentPoints exists |
| GET | admin/settings | admin.settings | SettingsController@index | admin.settings | super_admin | OK | `$settings` array passed; setting() helper exists |
| POST | admin/settings | admin.settings.update | SettingsController@update | redirect admin.settings | super_admin | OK | @csrf; 11 name= fields match $settingsToSave keys; Setting::set/clearCache exist |
| GET | admin/theme | admin.theme | ThemeController@index | admin.theme | super_admin | OK | `$settings` passed; Setting::whereIn pluck OK |
| POST | admin/theme | admin.theme.update | ThemeController@update | redirect admin.theme | super_admin | OK | @csrf; fields match validator; layout_style hidden=wide (valid enum) |
| POST | admin/theme/upload | admin.theme.upload | ThemeController@upload | JSON | super_admin | Minor | JSON consumed via fetch() OK; response url path `storage/app/public/data/` likely wrong public path (F-U12-003) |
| GET | admin/users | admin.users.index | UserManagementController@index | admin.users.index | super_admin | OK | `$users`,`$schools` passed; school rel, filter cols, avatar_url accessor exist |
| POST | admin/users | admin.users.store | UserManagementController@store | redirect admin.users.index | super_admin | OK | @csrf; all fields match validator; all fillable; two_factor_enabled checkbox handled |
| GET | admin/users/create | admin.users.create | UserManagementController@create | admin.users.create | super_admin | OK | `$schools` passed |
| GET | admin/users/{user} | admin.users.show | UserManagementController@show | — | super_admin | Blocker | `show()` method DOES NOT EXIST + no users/show view (F-U12-001) |
| PUT/PATCH | admin/users/{user} | admin.users.update | UserManagementController@update | redirect admin.users.index | super_admin | OK | @csrf + @method('PUT'); fields match validator; two_factor_enabled handled |
| DELETE | admin/users/{user} | admin.users.destroy | UserManagementController@destroy | redirect admin.users.index | super_admin | OK | @csrf + @method('DELETE'); self-delete guard |
| GET | admin/users/{user}/edit | admin.users.edit | UserManagementController@edit | admin.users.edit | super_admin | OK | `$user`,`$schools` passed; 2FA edit checkbox wired (central to enrollment flow) |
| POST | admin/users/{user}/toggle-status | admin.users.toggle-status | UserManagementController@toggleStatus | back() | super_admin | OK | @csrf; status toggle |

## Findings detail

F-U12-001 | `admin.users.show` route bound to non-existent controller method | Routing/Controller | Blocker | app/Http/Controllers/Admin/UserManagementController.php (no `show` method; route at _ROUTES.txt:174) | GET `admin/users/{user}` resolves to `UserManagementController@show` which is not defined; no `resources/views/admin/users/show.blade.php` either | Any direct hit on `admin/users/{id}` (e.g. typed URL, or a future "view" link) throws 500 (method/binding resolution error) before rendering | Confirmed-static | Add a `show(User $user)` method returning an `admin.users.show` view, OR drop the route from the resource registration (it has no UI entry point in index.blade.php — only edit/toggle/delete links exist) | none

F-U12-002 | Value badge gated by non-existent `lesson->meaning` relation — silently never renders | View/Blade vs Model | Major | resources/views/admin/pending-submissions.blade.php:95 and resources/views/admin/review-submission.blade.php:57 | `@if($submission->activity?->lesson?->meaning?->concept?->value)` — `Lesson` has NO `meaning` relation (only `concept()`), so `?->meaning` is always null → guard always false; the inner body (line 97 / 61) correctly uses `lesson->concept->value->name` but is unreachable | The "القيمة/Value" column/block always shows `-` (pending list) or is omitted (review page) even when the value exists — admin never sees the linked value | Confirmed-static | Drop the `?->meaning` hop: change guard to `$submission->activity?->lesson?->concept?->value` to match both the controller eager-load (`activity.lesson.concept.value`) and the body expression | none

F-U12-003 | Theme upload returns asset URL with extra `app/public/data/` segment | Controller | Minor | app/Http/Controllers/Admin/ThemeController.php:112 | `asset('storage/app/public/data/' . $path)` — Laravel's `public` disk is symlinked at `/storage/<path>`; prefixing `app/public/data/` yields a 404 image URL | Uploaded logo/favicon/hero preview returned by the AJAX uploader points at a non-resolving URL (broken image in the live preview); the stored DB path itself is fine, so saved theme assets still render elsewhere | Needs-runtime-confirm | Return `asset('storage/' . $path)` (matches the `public` disk symlink). Same suspicious prefix appears in review-submission.blade.php:106 for `file_path` — flag for U-owner of that view | none

F-U12-004 | `admin.users.show` is an orphan route (no UI entry point) | Wiring/orphans | Minor | resources/views/admin/users/index.blade.php:443-465 | index action column links only to edit / toggle-status / destroy; nothing links to `admin.users.show` | No user-facing path reaches it; combined with F-U12-001 it is both unrouteable-by-UI and unimplemented | Confirmed-static | Resolve together with F-U12-001 (implement or remove) | F-U12-001

F-U12-005 | `store` permits creating a `super_admin` with no school but validator strings still list it | Validation | Info | app/Http/Controllers/Admin/UserManagementController.php:71-72 | `role` allows `super_admin`; `school_id` is `required_if:role,school_admin,teacher,student,parent` (correctly excludes super_admin) | No defect — noted only because `update` (line 117) drops the `required_if` entirely, so a teacher/student could be saved with null school via the edit form (data-integrity, not a crash) | Needs-runtime-confirm | Consider mirroring the `required_if` rule from `store` into `update` for school-bound roles | none
