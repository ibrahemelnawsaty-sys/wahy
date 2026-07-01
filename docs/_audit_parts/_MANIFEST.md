# Functional & Integration Audit — Work Manifest

**Mode:** STRICT READ-ONLY (project untouchable; writes only under `docs/_audit_parts/` + final `docs/FUNCTIONAL_INTEGRATION_AUDIT.md`).
**Global route total (denominator):** **422** (402 web + 20 api). Authoritative list: `docs/_audit_parts/_ROUTES.txt` (`method | uri | name | action | middleware`).
**Roles:** school_admin, teacher, student, parent, anonymous.
**Finding ID scheme:** `F-U##-###`. Partial files: `docs/_audit_parts/U##__<slug>.md`.

## Work tree (L0 app → L1 route file → L2 module → leaf unit)

```
app (422 routes; routes/web.php, routes/api.php, routes/console.php)
├── CROSS-CUTTING (traced once; other units skip these files)
│   ├── UG  U-GLOBAL  — base Controller, ScopedToSchool, bootstrap/app.php middleware, Helpers, Providers, shared layouts/components, error pages, storage route
│   ├── UI  U-I18N    — lang/ar + lang/en parity across all __()/@lang/trans usage
│   └── UN  U-NAV     — per-role nav/menu/sidebar reachability across modules
├── PUBLIC / AUTH
│   ├── U01 Auth & Account     — AuthController, Api/AuthApiController, ProfileController, RoleSwitchController
│   └── U02 Public & Landing-front — PublicRegistrationController, PagesController, ContactController, EditorUploadController, GET /
├── ROLE PORTALS
│   ├── U03 Student            — StudentController
│   ├── U04 Teacher-A          — TeacherController (dashboard/classes/students/curriculum half)
│   ├── U05 Teacher-B          — TeacherController (grading/teams/exercises/pvp/messages half)
│   ├── U06 SchoolAdmin-A      — SchoolAdminController (dashboard/people-mgmt half)
│   ├── U07 SchoolAdmin-B      — SchoolAdminController (registration/import/settings/stats half)
│   └── U08 Parent             — ParentController, ParentDashboardController
├── SHARED FEATURES
│   ├── U09 Messaging          — MessagesController, BulkMessageController
│   ├── U10 Engagement-front   — NotificationController, LeaderboardController, SurveyController
│   └── U11 Mobile API & Health— Api/StudentApiController, Health/HealthCheckController, sanctum csrf, up
└── ADMIN PANEL
    ├── U12 Admin Core         — Admin/DashboardController, UserManagementController, SettingsController, ThemeController
    ├── U13 Admin Curriculum   — Admin/ValueManagementController, ConceptManagementController, LessonManagementController
    ├── U14 Admin Activities   — Admin/ActivityManagementController, ActivityBankController, ActivityApprovalController
    ├── U15 Admin People-A     — Admin/SchoolManagementController, TeacherManagementController
    ├── U16 Admin People-B     — Admin/StudentManagementController, ParentManagementController
    ├── U17 Admin Engagement   — Admin/ShopManagementController, Admin/SurveyController, SurveyManagementController
    ├── U18 Admin Reports/Logs — Admin/ReportsController, MessagesLogController
    ├── U19 SuperAdmin-A       — SuperAdminController (years/schools/settings/backups/landing-blocks half)
    ├── U20 SuperAdmin-B       — SuperAdminController (question-approval/themes/misc half)
    └── U21 PageBuilder/Landing— Admin/PageBuilderController, Admin/LandingPageController, Api/LandingContentController
```

## Unit ledger

| Unit | Title | Scope (controllers / route prefixes owned) | Est. routes | Wave | Status | Traced/Total | Findings (B/M/m/i) |
|---|---|---|---|---|---|---|---|
| UG | U-GLOBAL | Controller.php, Concerns/ScopedToSchool, bootstrap/app.php, app/Helpers/*, app/Providers/*, layouts/*, components/*, errors/*, storage.* | ~2 + shared | 1 | ✅ complete | infra | 0/1/2/1 |
| UI | U-I18N | lang/ar/*, lang/en/*, lang JSON; all __()/@lang keys | 0 (x-cut) | 1 | ✅ partial(by-design) | xcut | 0/0/1/2 |
| UN | U-NAV | role nav/sidebar/menu partials; layout includes | 0 (x-cut) | 1 | ✅ complete | xcut | 0/0/2/0 |
| U01 | Auth & Account | AuthController, Api/AuthApiController, ProfileController, RoleSwitchController | ~22 | 1 | ✅ complete | 21/21 | 0/1/2/2 |
| U02 | Public & Landing-front | PublicRegistrationController, PagesController, ContactController, EditorUploadController | ~17 | 1 | ✅ complete | 15/15 | 0/0/2/2 |
| U03 | Student | StudentController | ~31 | 1 | ✅ complete | 31/31 | 0/0/2/1 |
| U10 | Engagement-front | NotificationController, LeaderboardController, SurveyController | ~13 | 1 | ✅ complete | 12/12 | 0/1/4/2 |
| U11 | Mobile API & Health | Api/StudentApiController, Health/HealthCheckController, sanctum, up | ~11 | 1 | ✅ complete | 9/9 | 0/1/2/0 |
| U04 | Teacher-A | TeacherController (curriculum/classes half) | ~28 | 2 | ✅ complete | 28/28 | 0/3/3/0 |
| U05 | Teacher-B | TeacherController (grading/teams/pvp half) | ~27 | 2 | ✅ complete | 27/27 | 0/3/3/1 |
| U06 | SchoolAdmin-A | SchoolAdminController (people-mgmt half) | ~21 | 2 | ✅ complete | 26/26 ⚠overlap | 0/1/3/1 |
| U07 | SchoolAdmin-B | SchoolAdminController (import/settings/stats half) | ~21 | 2 | ✅ complete | 21/21 ⚠overlap | 0/0/2/2 |
| U08 | Parent | ParentController, ParentDashboardController | ~11 | 2 | ✅ complete | 11/11 | 0/0/3/1 |
| U09 | Messaging | MessagesController, BulkMessageController | ~21 | 2 | ✅ complete | 21/21 | 0/0/3/3 |
| U12 | Admin Core | Admin/Dashboard, UserManagement, Settings, Theme | ~17 | 2 | ✅ complete | 17/17 | 1/1/2/1 |
| U13 | Admin Curriculum | Admin/Value, Concept, Lesson Management | ~23 | 2 | ✅ complete | 23/23 | 0/1/2/3 |
| U14 | Admin Activities | Admin/ActivityManagement, ActivityBank, ActivityApproval | ~20 | 3 | ✅ complete | 20/20 | 0/1/4/2 |
| U15 | Admin People-A | Admin/SchoolManagement, TeacherManagement | ~18 | 3 | ✅ complete | 18/18 | 0/1/3/1 |
| U16 | Admin People-B | Admin/StudentManagement, ParentManagement | ~16 | 3 | ✅ complete | 16/16 | 0/0/3/2 |
| U17 | Admin Engagement | Admin/ShopManagement, SurveyController, SurveyManagement | ~19 | 3 | ✅ complete | 18/18 | 0/1/5/2 |
| U18 | Admin Reports/Logs | Admin/ReportsController, MessagesLogController | ~17 | 3 | ✅ complete | 18/18 | 1/2/2/1 |
| U19 | SuperAdmin-A | SuperAdminController (years/schools/backups/landing half) | ~24 | 3 | ✅ complete | 24/24 | 0/0/3/0 |
| U20 | SuperAdmin-B | SuperAdminController (approval/themes/misc half) | ~23 | 3 | ✅ complete | 23/23 | 1/1/2/2 |
| U21 | PageBuilder/Landing | Admin/PageBuilder, LandingPage, Api/LandingContent | ~14 | 3 | ✅ complete | 16/16 | 0/2/4/3 |
| LEAD | Closure reconciliation | 11 closures (storage×2, up, 7 super-admin redirects, test-notifications) | 11 | 2(post) | ✅ complete | 11/11 | 1/0/0/0 |

**COVERAGE ASSERTION:** 411 controller routes (24 units) + 11 closures (lead) = **422 / 422 traced. Zero gap.** SchoolAdmin U06/U07 union = 42 (boundary line 297/298, double-trace in middle, no gap). SuperAdmin U19/U20 = 47 gap-free.

**Coverage rule:** Phase 2 asserts every one of the 422 rows in `_ROUTES.txt` appears in exactly one unit's coverage table. Split controllers (Teacher U04/U05, SchoolAdmin U06/U07, SuperAdmin U19/U20) are reconciled against `_ROUTES.txt`; any uncovered route is re-dispatched, never dropped.
