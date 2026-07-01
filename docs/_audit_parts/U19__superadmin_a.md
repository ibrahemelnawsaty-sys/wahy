---
unit_id: U19
title: SuperAdmin-A (SuperAdminController — first half)
scope: [app/Http/Controllers/SuperAdminController.php (rows 1–24: backups/restore, activity-logs, api-docs, excel export/template, education-levels, academic-years)]
routes_total: 24
routes_traced: 24
status: complete
blockers: none
findings: { blocker: 0, major: 0, minor: 3, info: 2 }
---

## Ownership / boundary
Owned = first 24 of 47 SuperAdminController rows in `_ROUTES.txt` file order.
**Boundary URI (last I own): `GET admin/export/teachers` (admin.export.teachers → exportTeachers).**
U20 owns from `GET admin/featured-activities` (admin.featured-activities) onward.
unowned (U20): featured-activities*, import/students, landing-page*, online-users*, pvp-challenges*, question-bank*.
Shared deps noted, not audited: `layouts.admin` (csrf-token meta), `@push('scripts')` (U-GLOBAL).

## Coverage table
| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| POST | admin/academic-years/store | admin.academic-years.store | storeYear | JSON | super_admin | OK | validates education_level_id+name; AcademicYear.create; fields match AJAX body |
| PUT | admin/academic-years/{id} | admin.academic-years.update | updateYear | JSON | super_admin | OK | findOrFail; name+nullable status; fillable OK |
| DELETE | admin/academic-years/{id} | admin.academic-years.delete | deleteYear | JSON | super_admin | OK | findOrFail+delete |
| GET | admin/activity-logs | admin.activity-logs | activityLogs | super-admin.activity-logs | super_admin | OK | logs/models/users passed; Spatie Activity cols (subject_type/causer_id/event) exist |
| POST | admin/activity-logs/clean | admin.activity-logs.clean | cleanActivityLogs | redirect | super_admin | OK | redirects super-admin.activity-logs (registered, bounces to admin.*); days field matches |
| GET | admin/api-documentation | admin.api-documentation | apiDocumentation | super-admin.api-documentation | super_admin | OK | static view, no vars |
| GET | admin/backups | admin.backups | backups | super-admin.backups | super_admin | OK | $backups compact; all forms @csrf |
| POST | admin/backups/cleanup | admin.backups.cleanup | cleanupBackups | redirect | super_admin | OK | try/catch → admin.backups |
| POST | admin/backups/create | admin.backups.create | createBackup | redirect | super_admin | OK | BackupService::create($type) exists; type hidden field |
| DELETE | admin/backups/delete/{filename} | admin.backups.delete | deleteBackup | redirect | super_admin | OK | basename allowlist; BackupService::delete exists; @method DELETE |
| GET | admin/backups/download/{filename} | admin.backups.download | downloadBackup | file download | super_admin | OK | path-traversal guarded; response()->download |
| POST | admin/backups/restore | admin.backups.restore | restoreBackup | redirect | super_admin | OK | C08-2 wiring confirmed; form sends backup_file (matches validator) + enctype; helpers exist |
| GET | admin/download/students-template | admin.download.students-template | downloadStudentsTemplate | xlsx stream | super_admin | OK | PhpSpreadsheet → save php://output |
| GET | admin/education-levels | admin.education-levels | educationLevels | admin.education-levels | super_admin | OK | levels(withCount schools)+schools passed; ordered/academicYears/schools relations exist |
| POST | admin/education-levels/link-school | admin.education-levels.link-school | linkSchoolLevels | JSON | super_admin | OK | school_id+education_level_ids[]; School::educationLevels()->sync; pivot school_education_level exists |
| POST | admin/education-levels/store | admin.education-levels.store | storeLevel | JSON | super_admin | OK | name unique; create sort_order |
| PUT | admin/education-levels/{id} | admin.education-levels.update | updateLevel | JSON | super_admin | OK | name unique-ignore-id + nullable status; fillable OK |
| DELETE | admin/education-levels/{id} | admin.education-levels.delete | deleteLevel | JSON | super_admin | OK | findOrFail+delete |
| GET | admin/excel-management | admin.excel-management | excelManagement | super-admin.excel-management | super_admin | OK | static view; inline model counts (User/Activity/School/ActivitySubmission all exist) |
| GET | admin/export/activities | admin.export.activities | exportActivities | xlsx download | super_admin | OK | ActivitiesExport($schoolId) exists |
| GET | admin/export/parents | admin.export.parents | exportParents | xlsx download | super_admin | Minor | ParentsExport exists; NO UI entry in excel-management view (F-U19-001) |
| GET | admin/export/schools | admin.export.schools | exportSchools | xlsx download | super_admin | Minor | SchoolsExport exists; NO UI entry (F-U19-001) |
| GET | admin/export/students | admin.export.students | exportStudents | xlsx download | super_admin | OK | StudentsExport($schoolId) exists; form wired |
| GET | admin/export/teachers | admin.export.teachers | exportTeachers | xlsx download | super_admin | Minor | TeachersExport exists; NO UI entry (F-U19-001) — BOUNDARY URI |

## Findings detail

F-U19-001 | Three export endpoints have no UI entry point | Wiring/orphan | Minor | resources/views/super-admin/excel-management.blade.php:79-155 | `admin.export.teachers` / `admin.export.parents` / `admin.export.schools` are routed + controller methods + Export classes all exist and work, but the excel-management view only renders forms for Students and Activities exports | A super_admin cannot trigger teacher/parent/school export from the UI; reachable only by typing the URL | Confirmed-static | Add three more export cards (mirroring the Students/Activities forms) to excel-management.blade.php, GET to the respective routes | none

F-U19-002 | cleanActivityLogs redirects through the bounce alias | Routing | Info | app/Http/Controllers/SuperAdminController.php:563,568 | redirect()->route('super-admin.activity-logs') which is a registered route (web.php:382) that itself redirects to admin.activity-logs | Works, but causes a double redirect (302→302) instead of going straight to admin.activity-logs | Confirmed-static | Change target to 'admin.activity-logs' for a single hop (cosmetic) | none

F-U19-003 | restoreBackup pre-restore safety backup MySQL-only | Model/flow | Info | app/Http/Controllers/SuperAdminController.php:269-300 | SQLite branch copies a .backup before overwriting; MySQL branch builds a pre-restore zip; both paths covered. No defect — recorded for completeness re C08-2 | n/a | Confirmed-static | none (C08-2 wiring confirmed complete: BackupService::create/delete + restoreMySQLDump/createMySQLDump/copyDirectory/deleteDirectory all present) | C08-2

## Cross-ref notes
- C08-2 restoreBackup: wiring CONFIRMED complete (delegates create/delete to BackupService which exists; restore uses 4 in-file private helpers all present; ZIP-slip + path-traversal guards present). No functional break.
- C13-2 NotificationService arg-order: the only NotificationService::create calls (lines 760, 790) are OUTSIDE my half (U20's featured/landing/pvp range). Not applicable to U19.
- C03-4 landing-block sinks: landing-page* routes (rows 58–65) are U20's half. Not applicable to U19.

## Verification basis (static)
- Views exist: super-admin/{backups,activity-logs,api-documentation,excel-management}.blade.php, admin/education-levels.blade.php — all present, no conflict markers.
- Models/relations: EducationLevel(scopeOrdered, academicYears HasMany, schools BelongsToMany), AcademicYear(scopeOrdered, fillable name/sort_order/status/education_level_id), School(educationLevels via school_education_level pivot) — all defined.
- Columns: education_levels/academic_years have sort_order+status (migration 2026_03_07_000001); activity_log has nullableMorphs subject+causer, event col (migrations 2025_12_17_194033/194034).
- Exports: ActivitiesExport, StudentsExport, TeachersExport($schoolId), ParentsExport($schoolId), SchoolsExport (no-arg) — all present, ctors compatible with controller calls.
- Services: App\Services\Backup\BackupService::create(string):string + delete(string):bool — both present.
- All forms: @csrf present; restore form has enctype + field name backup_file matching validator; AJAX (education-levels) uses meta csrf-token + body field names matching validators; all route()/url() targets resolve to registered routes with matching verbs.
