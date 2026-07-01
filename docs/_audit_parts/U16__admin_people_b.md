---
unit_id: U16
title: Admin People-B (Student & Parent Management)
scope: [App\Http\Controllers\Admin\StudentManagementController, App\Http\Controllers\Admin\ParentManagementController]
routes_total: 16
routes_traced: 16
status: complete
blockers: none
findings: { blocker: 0, major: 0, minor: 3, info: 2 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/students | admin.students.index | StudentManagementController@index | admin.students.index | super_admin | OK | passes `students`,`schools`; `$student->school->name ?? '-'` null-guarded; status display active/inactive only |
| POST | admin/students | admin.students.store | StudentManagementController@store | redirect admin.students.index | super_admin | OK | form fields match validator; @csrf present; `status in:...suspended` latent (F-U16-001) |
| GET | admin/students/create | admin.students.create | StudentManagementController@create | admin.students.create | super_admin | OK | passes `schools`; form only offers active/inactive |
| GET | admin/students/{student} | admin.students.show | StudentManagementController@show | — | super_admin | Minor | NO show() method, NO show.blade.php (F-U16-002); registered by Route::resource; no UI link |
| PUT/PATCH | admin/students/{student} | admin.students.update | StudentManagementController@update | redirect admin.students.index | super_admin | OK | role-guard 404; Rule::unique ignore self; @method('PUT'),@csrf ok |
| DELETE | admin/students/{student} | admin.students.destroy | StudentManagementController@destroy | redirect admin.students.index | super_admin | OK | role-guard 404; DELETE form @csrf+@method ok |
| GET | admin/students/{student}/edit | admin.students.edit | StudentManagementController@edit | admin.students.edit | super_admin | OK | passes `student`,`schools`; qr_code readonly |
| POST | admin/students/{student}/toggle-status | admin.students.toggle-status | StudentManagementController@toggleStatus | back() | super_admin | OK | active<->inactive toggle; no UI button (F-U16-004) |
| GET | admin/parents | admin.parents.index | ParentManagementController@index | admin.parents.index | super_admin | OK | passes `parents`,`schools`; `$parent->phone`,`school->name ?? '-'` ok |
| POST | admin/parents | admin.parents.store | ParentManagementController@store | redirect admin.parents.index | super_admin | OK | form fields incl. phone match validator; @csrf ok; status latent (F-U16-001) |
| GET | admin/parents/create | admin.parents.create | ParentManagementController@create | admin.parents.create | super_admin | OK | passes `schools` |
| GET | admin/parents/{parent} | admin.parents.show | ParentManagementController@show | — | super_admin | Minor | NO show() method, NO show.blade.php (F-U16-002); no UI link |
| PUT/PATCH | admin/parents/{parent} | admin.parents.update | ParentManagementController@update | redirect admin.parents.index | super_admin | OK | role-guard 404; phone required; @method('PUT'),@csrf ok |
| DELETE | admin/parents/{parent} | admin.parents.destroy | ParentManagementController@destroy | redirect admin.parents.index | super_admin | OK | role-guard 404; DELETE form ok |
| GET | admin/parents/{parent}/edit | admin.parents.edit | ParentManagementController@edit | admin.parents.edit | super_admin | OK | passes `parent`,`schools` |
| POST | admin/parents/{parent}/toggle-status | admin.parents.toggle-status | ParentManagementController@toggleStatus | back() | super_admin | OK | active<->inactive toggle; no UI button (F-U16-004) |

Notes:
- All 16 routes gated `web > auth > authorize:access-admin` (super_admin only). No per-tenant scoping (super_admin is global by design) — not flagged.
- `User::$fillable` includes name,email,password,role,qr_code,school_id,phone,status → every `$validated` key is mass-assignable. `school()` belongsTo School exists. School has name+status columns. All `view()`/`route()`/redirect targets resolve.

## Findings detail

F-U16-001 | status validation allows `suspended` but DB enum is active/inactive | Validation↔Model/DB | Minor | StudentManagementController.php:68 & :118; ParentManagementController.php:69 & :120 vs database/migrations/2025_11_18_134600_create_users_table.php:25 (`enum('status',['active','inactive'])`) | validators accept `status=suspended`; column has no `suspended` member | A crafted/non-UI POST with status=suspended passes validation then errors at INSERT/UPDATE (truncation in non-strict, SQLSTATE in strict mode); UNREACHABLE via create/edit forms (they offer only active/inactive). | Confirmed-static | Drop `suspended` from the `in:` rule (align to enum) OR add `suspended` to the migration enum + form options — pick one source of truth. | = C10-1 (cross-ref)

F-U16-002 | resource `show` route registered with no method and no view | Routing/View | Minor | routes/web.php:225 (`Route::resource('students',...)`) & :229 (parents); StudentManagementController / ParentManagementController have no `show()`; no resources/views/admin/{students,parents}/show.blade.php | named routes admin.students.show / admin.parents.show resolve but `show()` is undefined | Direct navigation to `/admin/students/{id}` (or parents) → `BadMethodCallException`/missing-method fatal. No UI element links to show, so unreachable through normal navigation. | Confirmed-static | Either `Route::resource(...)->except(['show'])` for both, or add a `show()` + show.blade.php if a detail page is intended. | —

F-U16-003 | parent↔child linking absent from parent CRUD | Wiring/Feature gap | Info | ParentManagementController.php store/update (no `children()`/`parent_student` writes); create/edit forms send no child field | parent records are created/edited with no way to attach students (`parent_student` pivot / `children()` relation exists on User but is never populated here) | Admin cannot link a parent to their child via this screen; relationship must be set elsewhere or stays empty. May be intentional (linking handled in another flow). | Needs-runtime-confirm | If admin-side linking is desired, add a multi-select of students + sync `children()` in store/update. Otherwise document that linking lives elsewhere. | —

F-U16-004 | toggleStatus routed but no UI trigger in index views | Wiring/orphan | Minor | StudentManagementController@toggleStatus / ParentManagementController@toggleStatus; resources/views/admin/students/index.blade.php (only edit+destroy actions) & parents/index.blade.php | POST admin.students.toggle-status / admin.parents.toggle-status have no button/form/link in any blade | Feature (quick status toggle) is unreachable from the UI; status can still be changed via the edit form. Dead endpoint. | Confirmed-static | Add a toggle button/form to the index action cells, or remove the unused route. | —

F-U16-005 | mojibake in students/create heading | View/cosmetic | Info | resources/views/admin/students/create.blade.php:71 (`<h2>...� إضافة طالب جديد</h2>`) | a replacement/garbled char `�` renders before the heading text (lost emoji) | Minor visual glitch — a broken character shows next to "إضافة طالب جديد". | Confirmed-static | Replace the stray byte with the intended emoji (e.g. 👤) or remove it. | —
