---
unit_id: UG
title: U-GLOBAL — Cross-cutting shared infrastructure
scope: [bootstrap/app.php, app/Http/Controllers/Controller.php, app/Http/Controllers/Concerns/ScopedToSchool.php, app/Helpers/SettingsHelper.php, app/Providers/AppServiceProvider.php, app/Providers/AuthServiceProvider.php, app/View/Composers/HeaderDataComposer.php, app/Http/Middleware/*, resources/views/layouts/*, resources/views/partials/*, resources/views/components/*, resources/views/errors/*, resources/views/maintenance.blade.php, config/filesystems.php]
routes_total: 0
routes_traced: 0
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 2, info: 1 }
---

## Coverage table

| Artifact | Type | Referenced-by | Verdict | Note |
|---|---|---|---|---|
| bootstrap/app.php middleware aliases (`role`,`school.access`,`force-2fa`) | Middleware wiring | every protected route | OK | All 3 map to existing classes (CheckRole, CheckSchoolAccess, Force2FAForAdmins) |
| bootstrap/app.php web group append (SetArabicLocale, CheckMaintenanceMode, ApplyTheme, SecurityHeaders, CheckPasswordChangeRequired, CheckPendingSurveys) | Global middleware | every web request | OK | All 6 classes exist & sound; cache-guarded; redirect targets (`login`,`password.change`) resolve |
| bootstrap/app.php api group prepend (throttle:api, SetArabicLocale, SecurityHeaders) | Global middleware | every api request | OK | `throttle:api` limiter defined in AppServiceProvider |
| withExceptions render() JSON shield | Exception handler | all api/json responses | OK | Defers to framework for HTTP/validation/auth; masks 500 for json — sound |
| app/Http/Controllers/Controller.php | Base controller | all controllers | OK | Empty abstract base; no methods promised |
| Concerns/ScopedToSchool (currentSchool/studentsInMySchool/…) | Trait | school_admin/teacher controllers | OK | Uses School/User/UserRole enum + Auth — all exist |
| app/Helpers/SettingsHelper.php (`setting`,`set_setting`,`safe_html`,`safe_mail_subject`,`social_links`,`html_excerpt`,`hexToRgb(a)`,`adjustBrightness`) | Global helpers | blades + providers | OK | Autoloaded via composer `files`; all guarded with function_exists |
| AppServiceProvider Gate (`access-admin`, Gate::before) | Gate defs | authz checks | OK | Reference role strings only — no missing class |
| AppServiceProvider View::composer (`layouts.admin`,`layouts.super-admin` → HeaderDataComposer; `layouts.student-app`,`student.*` closure) | View composers | admin/student layouts | OK | HeaderDataComposer exists; targets exist; queries try/catch-guarded |
| AppServiceProvider Event::listen (8 listeners across 6 events) | Event wiring | gamification/notifications | OK | All Event + Listener classes exist on disk |
| AppServiceProvider View::share('branding') | Shared view var | head-meta/brand/theme-vars | OK | Setting::getMany exists; try/catch fallback before migrate |
| AuthServiceProvider $policies (Activity/ActivitySubmission/Lesson/Message) | Policy map | authz | OK | All 4 policy classes exist |
| resources/views/layouts/{auth,app,admin,student-app,teacher,school-admin,super-admin,parent,auth-clean,student} | Shared layouts | @extends-ed app-wide | OK | All `route()`/`asset()`/`@include` targets resolve (see note on storage URL below) |
| resources/views/partials/{head-meta,brand,flash,theme-vars,theme-toggle} | Shared partials | layouts | OK | All exist; reference valid helpers/branding |
| resources/views/components/{role-switcher,survey-popup,footer} | Shared components | layouts | OK | exist; User role helpers (hasMultipleRoles/getCurrentRole/getAllRoles/getRoleIcon/getRoleNameAr) all defined |
| resources/views/errors/{403,404,429,500} | Error pages | framework error render | OK | Branded Arabic, @extends layouts.auth; route('dashboard')/route('login') resolve |
| resources/views/errors/419 + 503 | Error pages | CSRF expiry / maintenance | Minor | No 419 page (CSRF mismatch → framework English page); 503 handled by maintenance.blade via CheckMaintenanceMode (custom) |
| resources/views/maintenance.blade.php | Maintenance page | CheckMaintenanceMode (503) | OK | exists; rendered with title/message |
| config/filesystems.php `public` disk + symlink + storage URL convention | Storage wiring | avatars/logo/favicon/uploads | Major | doubled `app/public` path segment vs symlink — see F-UG-001 |
| storage symlink (`public/storage` → `storage/app/public`) | Route/link | asset('storage/...') | Major | See F-UG-001 |

## Findings detail

F-UG-001 | Storage public-URL convention doubles `app/public` relative to the symlink | Wiring/Storage | Major | config/filesystems.php:43-44, app/Models/User.php:509, resources/views/partials/head-meta.blade.php:21, resources/views/partials/brand.blade.php:8, resources/views/layouts/auth.blade.php:91 (+ several controllers e.g. MessagesController.php:471, Admin/ActivityManagementController.php:186, Api/LandingContentController.php:139) | The `public` disk root is `storage/app/public/data`; `store('avatars','public')` writes to `storage/app/public/data/avatars/x`. The published symlink is `public/storage → storage/app/public`, so the correct public URL is `/storage/data/avatars/x`. But the app builds URLs as `asset('storage/app/public/data/'.$path)` = `/storage/app/public/data/avatars/x`, which through the symlink resolves to physical `storage/app/public/app/public/data/avatars/x` (does NOT exist). The disk's own `'url'` key is wrong the same way. Internal proof of the bug: `app/Http/Resources/UserResource.php:19` uses the CORRECT path `asset('storage/data/avatars/...')`, contradicting every other call site. | User-uploaded avatars, the site logo, and the favicon would 404 / show the broken-image / emoji fallback on every page — unless production has a non-standard webserver alias mapping `/storage/app/public/data` straight to the data dir (which would make the convention "work" only by accident of deploy config). | Needs-runtime-confirm | PROPOSE: standardize on the symlink-correct form `asset('storage/data/'.$path)` (and set disk `'url' => env('APP_URL').'/storage/data'`) everywhere, OR document/commit the custom webserver alias the convention relies on. Do not change without confirming how prod serves these files. | related: none

F-UG-002 | No branded 419 (CSRF/session-expired) error page | View/Blade | Minor | resources/views/errors/ (419.blade.php absent) | Laravel renders `errors/419.blade.php` on TokenMismatchException when present; here only 403/404/429/500 exist. | A user who leaves a form open past session lifetime and submits sees the framework default English "419 Page Expired" page instead of the branded Arabic shell — jarring but non-blocking and edge-case-only. | Confirmed-static | PROPOSE: add `resources/views/errors/419.blade.php` extending `layouts.auth` mirroring the 429 page (with a "refresh and retry" CTA). | related: none

F-UG-003 | No 503 page under errors/ (relies on custom maintenance middleware only) | View/Blade | Minor | resources/views/errors/ (503.blade.php absent); resources/views/maintenance.blade.php present | Maintenance is gated by the app-level `CheckMaintenanceMode` middleware which renders `view('maintenance',…,503)` — that path is covered. But a 503 raised by Laravel's native `php artisan down` (or any HttpException 503 not from that middleware) would fall back to the framework default page since no `errors/503.blade.php` exists. | If the team ever uses `artisan down`, visitors get the unbranded English maintenance page. Low likelihood given the custom toggle. | Confirmed-static | PROPOSE: add `resources/views/errors/503.blade.php` (can simply `@include`/mirror `maintenance.blade.php`) for parity. | related: none

F-UG-004 | CSP shipped Report-Only (not enforced) in production | Info | app/Http/Middleware/SecurityHeaders.php:35-53 | The middleware only sets `Content-Security-Policy-Report-Only`, never the enforcing header, with an inline TODO to flip after a week of monitoring. | No functional/user-facing effect (Report-Only does not block anything). Noted so it is not mistaken for active XSS hardening. | Confirmed-static | PROPOSE: after console-monitoring window, switch to enforcing `Content-Security-Policy`. Out of scope for a functional pass — informational only. | related: none
