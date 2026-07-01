---
unit_id: U15
title: Admin People-A — School & Teacher Management (super_admin)
scope: [App\Http\Controllers\Admin\SchoolManagementController, App\Http\Controllers\Admin\TeacherManagementController]
routes_total: 18
routes_traced: 18
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 3, info: 1 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/schools | admin.schools.index | SchoolManagementController@index | admin.schools.index | super_admin | OK | `$schools` passed; filter status active/inactive |
| POST | admin/schools | admin.schools.store | SchoolManagementController@store | redirect index | super_admin | OK | create form sends all fields + @csrf; status in:active,inactive matches enum |
| GET | admin/schools/create | admin.schools.create | SchoolManagementController@create | admin.schools.create | super_admin | OK | static form |
| GET | admin/schools/{school} | admin.schools.show | SchoolManagementController@show | admin.schools.show | super_admin | OK | `$school`,`$stats` passed; load users/branches OK |
| PUT/PATCH | admin/schools/{school} | admin.schools.update | SchoolManagementController@update | redirect index | super_admin | OK | edit form sends fields + @csrf + @method PUT; Rule::unique ignore |
| DELETE | admin/schools/{school} | admin.schools.destroy | SchoolManagementController@destroy | redirect index | super_admin | OK | guarded: blocks delete if users exist |
| GET | admin/schools/{school}/active-values | admin.schools.active-values | SchoolManagementController@activeValues | admin.schools.active-values | super_admin | OK | `$school`,`$allValues`,`$activeIds` passed; Value cols exist |
| PUT | admin/schools/{school}/active-values | admin.schools.active-values.update | SchoolManagementController@updateActiveValues | redirect active-values | super_admin | OK | form sends value_ids[] = validator `value_ids`; @csrf+@method PUT |
| GET | admin/schools/{school}/edit | admin.schools.edit | SchoolManagementController@edit | admin.schools.edit | super_admin | OK | `$school` passed |
| POST | admin/schools/{school}/toggle-status | admin.schools.toggle-status | SchoolManagementController@toggleStatus | back() | super_admin | Minor | orphan: no view links/posts to this route |
| GET | admin/teachers | admin.teachers.index | TeacherManagementController@index | admin.teachers.index | super_admin | OK | `$teachers`,`$schools` passed; @csrf on delete form |
| POST | admin/teachers | admin.teachers.store | TeacherManagementController@store | redirect index | super_admin | Minor | enum mismatch (C05-2) latent — form never sends `suspended` |
| GET | admin/teachers/create | admin.teachers.create | TeacherManagementController@create | admin.teachers.create | super_admin | OK | `$schools` passed; status dropdown active/inactive only |
| GET | admin/teachers/{teacher} | admin.teachers.show | TeacherManagementController@show (MISSING) | — | super_admin | Major | resource route registered but no show() method + no view → 500 on direct hit |
| PUT/PATCH | admin/teachers/{teacher} | admin.teachers.update | TeacherManagementController@update | redirect index | super_admin | OK | role!=teacher → 404 guard; password optional handled |
| DELETE | admin/teachers/{teacher} | admin.teachers.destroy | TeacherManagementController@destroy | redirect index | super_admin | OK | hard delete (C12-2 cascade — security audit) |
| GET | admin/teachers/{teacher}/edit | admin.teachers.edit | TeacherManagementController@edit | admin.teachers.edit | super_admin | OK | `$teacher`,`$schools` passed; 404 guard |
| POST | admin/teachers/{teacher}/toggle-status | admin.teachers.toggle-status | TeacherManagementController@toggleStatus | back() | super_admin | Minor | orphan: no view links/posts to this route |

## Findings detail

F-U15-001 | `admin.teachers.show` route has no controller method | Routing/Controller | Major | app/Http/Controllers/Admin/TeacherManagementController.php (no show()) ; routes/web.php:221 (`Route::resource('teachers', ...)`) | `Route::resource` auto-registers `admin.teachers.show` → GET admin/teachers/{teacher}, but TeacherManagementController defines no `show()` and there is no `resources/views/admin/teachers/show.blade.php` | super_admin who types/lands on `/admin/teachers/{id}` (or any future link) gets a 500 (BadMethodCallException). No UI currently links to it, so not hit on the normal index→edit/delete path. | Confirmed-static | PROPOSE: add `->except(['show'])` to the resource, or implement `show(User $teacher)` + a `teachers/show.blade.php` mirroring schools/show. | related: none

F-U15-002 | Teacher status enum mismatch (validation accepts `suspended`, DB enum lacks it) | Validation/DB | Minor | app/Http/Controllers/Admin/TeacherManagementController.php:70 (store) & :142 (update) — `status => required|in:active,inactive,suspended`; DB enum app/.../2025_11_18_134600_create_users_table.php:25 `enum('status',['active','inactive'])` | A POST with `status=suspended` passes validation, then `User::create/update` writes an out-of-enum value → MySQL truncation/SQL error (strict) or silent empty (non-strict). | Confirmed-static | PROPOSE: drop `suspended` from the `in:` rule (no UI offers it) OR add `suspended` to the users.status enum if a 3rd state is intended. Both create/edit dropdowns only render active/inactive, so the live UI never sends it — latent only. | related: C05-2 (security audit)

F-U15-003 | Orphaned toggle-status routes (schools + teachers) | Wiring/orphans | Minor | routes/web.php:216 & :222 ; SchoolManagementController@toggleStatus:153 ; TeacherManagementController@toggleStatus:178 | No blade under resources/views links or POSTs to `admin.schools.toggle-status` / `admin.teachers.toggle-status` (status is changed only via the edit form's select). Methods + POST routes are defined but unreachable from the UI. | Confirmed-static | PROPOSE: either add a toggle button on the index cards/rows, or remove the dead routes+methods. Low priority. | related: none

F-U15-004 | Teacher hard-delete with no cascade handling | DB/integrity | Info | app/Http/Controllers/Admin/TeacherManagementController.php:168 `$teacher->delete()` | Hard delete of a teacher User with no soft-delete and no cleanup of dependent rows (homework/points/etc.); FK behavior depends on migrations — data-integrity concern owned by security audit. | Needs-runtime-confirm | PROPOSE: assess FK onDelete behavior / add guards like the schools destroy() users-exist check. | related: C12-2 (security audit)
