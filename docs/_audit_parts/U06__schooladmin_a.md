---
unit_id: U06
title: SchoolAdmin-A (SchoolAdminController — first half: dashboard + teachers/students/parents/classrooms management)
scope: [app/Http/Controllers/SchoolAdminController.php (dashboard..deleteClassroom, lines 32-740)]
routes_total: 26
routes_traced: 26
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 3, info: 1 }
---

## Ownership / boundary
- Split rule: I own the FIRST HALF = dashboard + teachers + students + parents + classrooms clusters (controller file order, methods `dashboard`@32 … `deleteClassroom`@740). 26 route rows.
- **Boundary URI (first U07 row):** `GET school-admin/requests` → `registrationRequests`@744. U07 owns from there onward (requests, excel/import/export, statistics, settings, parent-engagement, surveys).
- unowned (U07, listed for reconciliation, NOT traced here): `school-admin/requests`, `requests/{id}/approve`, `requests/{id}/reject`, `download-template`, `excel-management`, `export-data`, `import-users`, `regenerate-token`, `registration-links`, `settings`(GET+POST), `statistics`, `toggle-registration`, `parent-engagement`, `surveys/comparisons`, `surveys/{surveyId}/comparison`. (16 rows.)

## Coverage table
| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | school-admin/dashboard | dashboard | dashboard | school-admin.dashboard | school_admin | OK | All 9 compact vars used/guarded; all route() links resolve; `recentTeachers` passed-but-unused (harmless). |
| GET | school-admin/teachers | teachers | teachers | school-admin.teachers.index | school_admin | OK | paginate; teachingClassrooms + counts eager-loaded. |
| GET | school-admin/teachers/create | teachers.create | createTeacher | school-admin.teachers.create | school_admin | OK | @csrf; fields name/email/phone/password(+confirmation) match validator. |
| POST | school-admin/teachers | teachers.store | storeTeacher | redirect teachers | school_admin | OK | validator↔form aligned; status='active' (valid enum). |
| GET | school-admin/teachers/{id}/edit | teachers.edit | editTeacher | school-admin.teachers.edit | school_admin | OK | tenant-scoped findOrFail; @method PUT. |
| PUT | school-admin/teachers/{id} | teachers.update | updateTeacher | redirect teachers | school_admin | OK | status in:active,inactive matches users enum. |
| DELETE | school-admin/teachers/{id} | teachers.delete | deleteTeacher | redirect teachers | school_admin | OK | @csrf + @method DELETE in index. |
| GET | school-admin/students | students | students | school-admin.students.index | school_admin | OK | classrooms/parents/points/counts eager-loaded; birth_date guarded. |
| GET | school-admin/students/create | students.create | createStudent | school-admin.students.create | school_admin | OK | classrooms[] matches validator `classrooms` array. |
| POST | school-admin/students | students.store | storeStudent | redirect students | school_admin | OK | tenant-scoped classroom attach (IDOR-guarded). |
| GET | school-admin/students/{id} | students.show | showStudent | school-admin.students.show | school_admin | OK | route DOES exist (web.php:425); stale "route مفقود" doc comment is wrong. badges/streak/counts loaded/guarded. |
| GET | school-admin/students/{id}/edit | students.edit | editStudent | school-admin.students.edit | school_admin | OK | classrooms[] preselect via old()/pluck. |
| PUT | school-admin/students/{id} | students.update | updateStudent | redirect students | school_admin | OK | status in:active,inactive valid; sync IDOR-guarded. |
| DELETE | school-admin/students/{id} | students.delete | deleteStudent | redirect students | school_admin | OK | @csrf+@method DELETE. |
| GET | school-admin/parents | parents | parents | school-admin.parents.index | school_admin | OK | children + nested classrooms.teacher + count loaded; pivot.relationship loaded. |
| GET | school-admin/parents/create | parents.create | createParent | school-admin.parents.create | school_admin | OK | children[]/relationship match validator. |
| POST | school-admin/parents | parents.store | storeParent | redirect parents | school_admin | OK | child link tenant-scoped (IDOR-guarded). |
| GET | school-admin/parents/{id}/edit | parents.edit | editParent | school-admin.parents.edit | school_admin | OK | children preselect via pluck. |
| PUT | school-admin/parents/{id} | parents.update | updateParent | redirect parents | school_admin | OK | status in:active,inactive valid; sync IDOR-guarded. |
| DELETE | school-admin/parents/{id} | parents.delete | deleteParent | redirect parents | school_admin | OK | @csrf+@method DELETE. |
| GET | school-admin/classrooms | classrooms | classrooms | school-admin.classrooms.index | school_admin | OK | teacher + students_count loaded. |
| GET | school-admin/classrooms/create | classrooms.create | createClassroom | school-admin.classrooms.create | school_admin | OK | educationLevels guarded with isset; students[]/teacher_id match validator. |
| POST | school-admin/classrooms | classrooms.store | storeClassroom | redirect classrooms | school_admin | OK | status='active' (valid); teacher + students tenant-validated. |
| GET | school-admin/classrooms/{id}/edit | classrooms.edit | editClassroom | school-admin.classrooms.edit | school_admin | Major | F-U06-001: status dropdown offers `inactive`, invalid for enum(active,archived). |
| PUT | school-admin/classrooms/{id} | classrooms.update | updateClassroom | redirect classrooms | school_admin | Major | F-U06-001: validator `in:active,inactive` writes invalid enum value. |
| DELETE | school-admin/classrooms/{id} | classrooms.delete | deleteClassroom | redirect classrooms | school_admin | OK | @csrf+@method DELETE. |

## Findings detail

F-U06-001 | Classroom status enum mismatch — `inactive` is not a valid value | Validation↔Model/DB | Major | app/Http/Controllers/SchoolAdminController.php:690 (validator `'status' => 'required|in:active,inactive'`) + :715 (`$classroom->update($validated)`); resources/views/school-admin/classrooms/edit.blade.php:12 (`<option value="inactive">`) | The `classrooms.status` column is `enum('status', ['active','archived'])` (database/migrations/2025_11_18_135902_create_classrooms_table.php:23) with no later migration changing it. updateClassroom validates `in:active,inactive` and the edit form's "غير نشط" option submits `inactive`. | When an admin edits a classroom and selects "غير نشط", `update()` writes `inactive` to an enum that does not contain it: on MySQL strict mode → SQLSTATE 1265/data-truncated 500 error; on non-strict mode → column silently set to `''`, after which the classroom disappears from every `where('status','active')` listing (dashboard count, createClassroom/student dropdowns, etc.) — silent data loss. Note: storeClassroom hardcodes `active` so create is safe; only the edit/update path is affected. | Confirmed-static | PROPOSE (implement nothing): make the value set consistent — either change the validator to `in:active,archived` and the edit dropdown option to `value="archived"` (label "مؤرشف"), OR add `inactive` to the classrooms.status enum via migration. The active/archived choice matches the existing schema with least churn. | related IDs: none (NEW). Cf. C05-2 (users `status=suspended` enum) is a *different* table — users enum here is `['active','inactive']` and the teacher/student/parent forms correctly use those two, so they are OK.

F-U06-002 | Orphan flat views never rendered | Wiring/orphans | Minor | resources/views/school-admin/teachers.blade.php, resources/views/school-admin/students.blade.php | Controller renders the nested `school-admin.teachers.index` / `school-admin.students.index`; the flat `teachers.blade.php` / `students.blade.php` siblings are referenced by no controller or include. | No user impact — dead files only. | Confirmed-static | PROPOSE: delete the two stale flat blades to avoid confusion. | related IDs: none.

F-U06-003 | Stale doc comment claims a live route is missing | Wiring/orphans | Minor | app/Http/Controllers/SchoolAdminController.php:389 | `showStudent`'s docblock says "Issue: school-admin.students.show route مفقود", but the route is registered at routes/web.php:425 and ordered correctly before `students/{id}/edit`. | No runtime impact; misleading comment only. | Confirmed-static | PROPOSE: remove/correct the obsolete comment. | related IDs: none.

F-U06-004 | showStudent has no UI entry point | Wiring/orphans | Minor | app/Http/Controllers/SchoolAdminController.php:391 (showStudent) | `school-admin.students.show` is routed and the view exists, but no blade in this half links to it (students/index only links to `.edit`; show.blade links back to index+edit). | Feature reachable only by typing the URL; not a broken link, just an unsurfaced page. | Confirmed-static | PROPOSE: add a "تفاصيل" button on students/index pointing to `route('school-admin.students.show', $student->id)`. | related IDs: none.

F-U06-005 | Unguarded `$school = Auth::user()->school` in non-dashboard methods | Controller | Info | app/Http/Controllers/SchoolAdminController.php:224,239,246,268,278,302,316,336,344,378,393,418,451,465,485,493,530,542,576,590,602,617,670,680,734 | All management methods use `$school = Auth::user()->school` then chain `$school->users()/->classrooms()` with no null-guard (unlike `dashboard()` which aborts 403). | In practice safe: `CheckSchoolAccess` middleware (app/Http/Middleware/CheckSchoolAccess.php:24) aborts 403 when `! $user->school_id` for non-super-admins, guaranteeing a school. Only a super_admin who somehow also held `role:school_admin` AND had null school_id could hit a null-method call — not a normal path. | Confirmed-static | PROPOSE (optional hardening): factor a shared `$this->currentSchool()` helper that aborts 403 on null, mirroring dashboard(). | related IDs: none.

## Cross-cutting notes (not audited — owned by U-GLOBAL/U-NAV)
- All views `@extends('layouts.school-admin')` — layout file exists (resources/views/layouts/school-admin.blade.php); not audited (U-GLOBAL).
- Middleware aliases `role` + `school.access` registered in bootstrap/app.php:50-51.
- Delete forms use `glassNotify.confirm()` (global JS) — present in shared layout scope; not audited here.
