---
unit_id: U07
title: SchoolAdmin-B — SchoolAdminController (second half: requests approve/reject, settings, statistics, Excel import/export, survey comparisons, students/teachers CRUD tail)
scope: [app/Http/Controllers/SchoolAdminController.php (rows ~22..42)]
routes_total: 21
routes_traced: 21
status: complete
blockers: none
findings: { blocker: 0, major: 0, minor: 2, info: 2 }
---

## Boundary & ownership
- The 42 `SchoolAdminController@*` rows in `_ROUTES.txt` (lines 270–319, excluding MessagesController 281–287 and the test-notifications Closure 318) are split: **U06 owns rows 1–21** (`_ROUTES.txt` lines 270–297, ending at `GET school-admin/requests`), **U07 owns rows 22–42**.
- **First URI I own (boundary): `POST school-admin/requests/{id}/approve`** (`_ROUTES.txt` line 298).
- My 21 owned `_ROUTES.txt` lines: 298, 299, 300, 301, 302, 303, 304, 305, 306, 307, 308, 309, 310, 311, 312, 313, 314, 315, 316, 317, 319.

unowned (other controllers under `school-admin/` prefix, NOT in my scope): lines 281–287 = `MessagesController@*`; line 318 = test-notifications `Closure`. (Lead: these belong to the Messages unit / a Closure-routes unit.)

## Coverage table
| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| POST | school-admin/requests/{id}/approve | school-admin.requests.approve | @approveRequest | redirect back + success/error | school_admin | OK | DB::transaction creates User + updates request; RegistrationApprovedMail exists; StudentRegistered event fired for students; school-scoped findOrFail |
| POST | school-admin/requests/{id}/reject | school-admin.requests.reject | @rejectRequest | redirect back + success | school_admin | OK | RegistrationRejectedMail exists; `rejected_reason` nullable; UI reject form sends no reason (see F-U07-001 Minor) |
| GET | school-admin/settings | school-admin.settings | @settings | school-admin.settings | school_admin | OK | passes `$school`,`$user`; both used & guarded |
| POST | school-admin/settings | school-admin.settings.update | @updateSettings | redirect route(settings) | school_admin | OK | section=school/account; form field names match validators exactly; both forms @csrf + hidden `section` |
| GET | school-admin/statistics | school-admin.statistics | @statistics | school-admin.statistics | school_admin | OK | heavy page; all 4 vars (school, schoolStats, teacherStats, studentStats) passed; view null-guards badges/grade_rankings; N+1 grade loop = C09-4 |
| GET | school-admin/students | school-admin.students | @students | school-admin.students.index | school_admin | OK | paginated; eager-loaded |
| POST | school-admin/students | school-admin.students.store | @storeStudent | redirect route(students) | school_admin | OK | create view fields match validator; @csrf; classroom attach IDOR-guarded |
| GET | school-admin/students/create | school-admin.students.create | @createStudent | school-admin.students.create | school_admin | OK | passes school, classrooms |
| GET | school-admin/students/{id} | school-admin.students.show | @showStudent | school-admin.students.show | school_admin | OK | User streak/badges/points rels exist; school-scoped findOrFail |
| PUT | school-admin/students/{id} | school-admin.students.update | @updateStudent | redirect route(students) | school_admin | OK | edit view @csrf + @method('PUT'); fields match; sync IDOR-guarded |
| DELETE | school-admin/students/{id} | school-admin.students.delete | @deleteStudent | redirect route(students) | school_admin | OK | school-scoped findOrFail |
| GET | school-admin/students/{id}/edit | school-admin.students.edit | @editStudent | school-admin.students.edit | school_admin | OK | passes student, school, classrooms |
| GET | school-admin/surveys/comparisons | school-admin.surveys.comparisons | @surveyComparisonsList | school-admin.surveys.comparisons-list | school_admin | OK | passes `$surveys` paginated; school-scoped where; links resolve to comparison route |
| GET | school-admin/surveys/{surveyId}/comparison | school-admin.surveys.comparison | @surveyComparison | school-admin.surveys.comparison | school_admin | OK | isAssessment()/getComparisonData() exist; 403 cross-school guard; error key handled; includes shared partial |
| GET | school-admin/teachers | school-admin.teachers | @teachers | school-admin.teachers.index | school_admin | OK | paginated |
| POST | school-admin/teachers | school-admin.teachers.store | @storeTeacher | redirect route(teachers) | school_admin | OK | create view fields match validator; @csrf |
| GET | school-admin/teachers/create | school-admin.teachers.create | @createTeacher | school-admin.teachers.create | school_admin | OK | passes school |
| PUT | school-admin/teachers/{id} | school-admin.teachers.update | @updateTeacher | redirect route(teachers) | school_admin | OK | edit view @csrf + @method('PUT'); fields match |
| DELETE | school-admin/teachers/{id} | school-admin.teachers.delete | @deleteTeacher | redirect route(teachers) | school_admin | OK | school-scoped findOrFail |
| GET | school-admin/teachers/{id}/edit | school-admin.teachers.edit | @editTeacher | school-admin.teachers.edit | school_admin | OK | passes teacher, school |
| POST | school-admin/toggle-registration | school-admin.toggle-registration | @toggleRegistration | redirect back + success | school_admin | OK | toggles enable_{role}_registration boolean |

Note on Excel routes (download-template/excel-management/export-data/import-users = `_ROUTES.txt` 277–280) are **U06-owned** rows, but their UI entry is `school-admin/excel-management`. I verified the targets that ARE invoked from that view resolve: `download-template` (sends `role`), `export-data` (sends `type` → 4 Export classes all exist & ctor `($schoolId)` matches), `import-users` (@csrf + multipart, `file`+`role` match validator; `BulkUsersImport($schoolId,$role)` ctor + getSuccessCount()/getErrors() match). No defects in those handlers.

## Findings detail

F-U07-001 | Reject form captures no rejection reason | Validation↔Form | Minor | resources/views/school-admin/requests/index.blade.php:64-69 | reject `<form>` posts only `@csrf`, no `rejected_reason` input | School admin can reject but the optional reason is always empty/null; rejection email + record never carry a reason | Confirmed-static | (PROPOSE) add an optional `rejected_reason` textarea (e.g. in the confirm modal) posted with the reject form; controller already validates it `nullable` | (index view rendered by U06's registrationRequests route; logged here as it concerns my approve/reject endpoints)

F-U07-002 | `json_decode()` on already-array cast field | Model/DB | Minor | resources/views/school-admin/requests/index.blade.php:122 | `json_decode($request->data, true)` but `RegistrationRequest::$casts['data'=>'array']` already returns an array | On PHP 8, `json_decode(array,…)` throws TypeError → the "view data" modal block could fatal when `data` is non-null. Needs-runtime-confirm (depends on whether `data` is ever populated; if always null, `json_decode(null)` is tolerated) | Needs-runtime-confirm | (PROPOSE) use `$extraData = (array) ($request->data ?? [])` instead of json_decode, since the cast already decodes | (this view is rendered by U06's `registrationRequests`; surfaced here because the same model feeds my approve/reject flow — lead should assign to U06)

F-U07-003 | Statistics grade-ranking N+1 | Model/DB/Perf | Info | app/Http/Controllers/SchoolAdminController.php:1166-1185 | per-grade loop issues 2 DB queries + 1 Eloquent withSum query each | Slow stats page on schools with many grade levels; functional OK (page renders) | Confirmed-static | = C09-4 (security/perf audit) — no action here | C09-4 |

F-U07-004 | Bulk import column correctness deferred | Wiring | Info | app/Imports/BulkUsersImport.php | import maps Excel heading rows to User columns | functional wiring OK (ctor/methods match controller); deep column/heading correctness out of scope | Confirmed-static | = C08 (imports, security audit) | C08 |
