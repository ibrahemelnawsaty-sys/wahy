---
unit_id: UN
title: U-NAV — role nav/sidebar links resolvability + role-permission cross-check
scope: [resources/views/layouts/{admin,super-admin,teacher,parent,school-admin,student-app,student,app}.blade.php, resources/views/components/role-switcher.blade.php]
routes_total: 0   # cross-cutting unit — traces nav LINKS, not owned routes; denominator = nav links per live layout
routes_traced: 0
status: complete
blockers: none
findings: { blocker: 0, major: 0, minor: 2, info: 2 }
---

## Method / role→layout map (confirmed static)
- Roles in system (AuthController@dashboard switch, l.393): `super_admin`, `school_admin`, `teacher`, `student`, `parent`. No plain `admin` role; `default:` → logout. So every layout's viewer role is known.
- `super_admin` → `admin.dashboard` ⇒ **layouts.admin** is super_admin's primary nav. `super-admin/*` views ⇒ **layouts.super-admin**.
- `school_admin`→school-admin.dashboard (**layouts.school-admin**); `teacher`→**layouts.teacher**; `student`→student.dashboard (**layouts.student-app**); `parent`→**layouts.parent**.
- Gate `access-admin` (AppServiceProvider l.89) = `$user->role === 'super_admin'` ONLY. All `admin.*` routes are `Authorize:access-admin` ⇒ reachable only by super_admin — which is exactly who renders layouts.admin. No mismatch.
- Middleware `CheckRole` (app/Http/Middleware/CheckRole.php): **super_admin bypasses every CheckRole** (explicit `in_array('super_admin',...)` short-circuit). So super-admin layout's link to `school-admin.test-notifications` (CheckRole:school_admin) is permitted for super_admin.
- Cross-check: extracted every `route('...')` in the 6 LIVE layouts + role-switcher and matched against `_ROUTES.txt` names. 100% resolve except 3 names that live inside `{{-- … --}}` Blade comments (stripped at compile → never called).
- `layouts.student` and `layouts.app` have ZERO `@extends` users (grep) ⇒ orphaned layouts; their navs are dead code (Info, not live-broken).
- Role-switcher (included in admin/teacher/school-admin; inline variants in parent/student/app layouts) is data-driven from `getAllRoles()`/`getCurrentRole()`; POSTs to `switch.role` (auth-only, all roles). User only sees buttons for roles they hold → Needs-runtime-confirm, no static break.

## Coverage table (Role-layout × nav-link → resolves? / role-permitted? / verdict)
| Role-layout | Link label | route() | resolves? | role-permitted? | Verdict |
|---|---|---|---|---|---|
| admin (super_admin) | لوحة البيانات | admin.dashboard | yes | yes (access-admin=super_admin) | OK |
| admin | التقديمات المعلقة | admin.pending-submissions | yes | yes | OK |
| admin | الرسائل | messages.index | yes | yes (auth) | OK |
| admin | تخصيص الثيم/بناء الصفحات/الإعدادات/المراحل | admin.theme, admin.pages.index, admin.settings, admin.education-levels | yes | yes | OK |
| admin | افتح المحرر | href="/" (landing) | n/a (public) | yes | OK |
| admin | المستخدمين/المدارس/المعلمين/الطلاب/أولياء/المتصلين | admin.users.index, admin.schools.index, admin.teachers.index, admin.students.index, admin.parents.index, admin.online-users | yes | yes | OK |
| admin | القيم/المفاهيم/الدروس/الأنشطة/الاستبيانات | admin.values.index, admin.concepts.index, admin.lessons.index, admin.activities.index, admin.surveys.index | yes | yes | OK |
| admin | تحديات PvP (@if super_admin) | admin.pvp-challenges.index | yes | yes | OK |
| admin | تقارير (dashboard/students/schools/activities/values) | admin.reports.* | yes | yes | OK |
| admin | Excel/بنك الأنشطة/الموافقة/النسخ/سجل الأنشطة/سجل الرسائل/المميزة/المتجر | admin.excel-management, admin.activity-bank.index, admin.activity-approval.index, admin.backups, admin.activity-logs, admin.messages-log.index, admin.featured-activities, admin.shop.index | yes | yes | OK |
| admin | الرسائل الجماعية/صندوق الوارد | messages.bulk.index, messages.bulk.inbox | yes | yes (auth) | OK |
| admin | لوحات الصدارة (index/students/teachers/parents/schools) | leaderboard.* | yes | yes (auth) | OK |
| admin | العودة للموقع / خروج / avatar | landing, logout, profile.update-avatar | yes | yes | OK |
| super-admin | لوحة الأدمن الرئيسية | admin.dashboard | yes | yes (super_admin) | OK |
| super-admin | لوحة السوبر أدمن / Excel / Backups / Logs / API docs | super-admin.dashboard, super-admin.excel-management, super-admin.backups, super-admin.activity-logs, super-admin.api-documentation | yes | yes (CheckRole:super_admin) | OK |
| super-admin | بنك الأنشطة | admin.activity-bank.index | yes | yes (super_admin passes access-admin) | OK |
| super-admin | اختبار الإشعارات | school-admin.test-notifications | yes | yes (super_admin bypasses CheckRole) | OK |
| super-admin | إدارة الإشعارات | notifications.index | yes | yes (auth) | OK |
| super-admin | المدارس / إدارة المحتوى / الإعدادات العامة | super-admin.schools, super-admin.content-management, super-admin.settings | NO route | n/a | Minor — inside {{-- --}} comment, not rendered (F-UN-001) |
| teacher | dashboard/messages/bulk-inbox/review/students/classrooms/streak/teams/messages/parent-engagement/surveys-comparisons/ratings/analytics/activity-bank/settings | teacher.*, messages.index, messages.bulk.inbox | yes | yes (CheckRole:teacher / auth) | OK |
| teacher | avatar / switch role / logout | profile.update-avatar, switch.role, logout | yes | yes (auth) | OK |
| parent | الرئيسية/تقدّم أبنائي/الأنشطة العائلية/مراسلة المعلم | parent.dashboard, parent.surveys.comparisons, parent.family-activities.pending, parent.messages | yes | yes (CheckRole:parent) | OK |
| parent | الرسائل / رسائل جماعية | messages.index, messages.bulk.inbox | yes | yes (auth) | OK |
| parent | switch role / logout | switch.role, logout | yes | yes (auth) | OK |
| school-admin | dashboard/teachers/students/parents/classrooms/messages/parent-engagement/surveys-comparisons/excel/statistics/requests/registration-links/settings | school-admin.* | yes | yes (CheckRole:school_admin) | OK |
| school-admin | رسائل جماعية / إشعارات | messages.bulk.inbox, notifications.index, notifications.read-all | yes | yes (auth) | OK |
| student-app | التعلم/الخريطة/التمرين/حسابي | student.dashboard, student.path, student.practice, student.profile | yes | yes (CheckRole:student) | OK |
| student-app | الرسائل / الإشعارات | messages.index, notifications.index | yes | yes (auth) | OK |
| student-app | coins modal / profile update (JS fetch) | student.coins.history, student.profile.update | yes | yes (CheckRole:student) | OK |
| role-switcher (all) | تبديل الدور buttons | switch.role | yes | yes (auth; data-driven by user's own roles) | OK / Needs-runtime-confirm |
| layouts.student (orphan) | header msgs/bulk/switch/logout | messages.index, messages.bulk.inbox, switch.role, logout | yes | n/a — no @extends user | Info (F-UN-002) |
| layouts.app (orphan) | — | — | — | no @extends user; entire file unused | Info (F-UN-002) |

## Findings detail
F-UN-001 | Commented-out nav items reference 3 non-existent super-admin routes | View/Blade | Minor | resources/views/layouts/super-admin.blade.php:71,78,164 | `route('super-admin.schools')`, `route('super-admin.content-management')`, `route('super-admin.settings')` | NOT user-visible: all three sit inside `{{-- … --}}` Blade comments (l.70-83, l.160-171), which Blade strips before compilation, so `route()` is never invoked and no RouteNotFoundException occurs. Pure dead code. | Confidence: Confirmed-static | Proposed fix (PROPOSE ONLY): delete the commented blocks, or if these dashboards are planned, register the routes before un-commenting. | related: none

F-UN-002 | Orphaned role layouts (`layouts.student`, `layouts.app`) have no @extends consumers | Wiring/orphans | Info | resources/views/layouts/student.blade.php, resources/views/layouts/app.blade.php | their header nav links (messages.index, messages.bulk.inbox, switch.role, logout — all valid & auth-permitted) | No user reaches these layouts: `grep @extends('layouts.student')`/`('layouts.app')` → 0 hits. Live student nav is `layouts.student-app`. So the navs render for nobody; not a broken-link risk, just dead files. | Confidence: Confirmed-static | Proposed fix (PROPOSE ONLY): remove the two unused layouts (or confirm they are intentional fallbacks). | related: none

## Notes
- All link route NAMES across the 6 live role navs resolve in `_ROUTES.txt` (verified by automated name cross-check; the only 3 "misses" are the commented ones in F-UN-001).
- No role-mismatch links found: every layout is rendered only by the role(s) whose middleware its links require; super_admin's universal CheckRole bypass + access-admin=super_admin make the admin/super-admin navs internally consistent.
- Shared partials referenced but NOT audited here (owned by U-GLOBAL): partials.head-meta, partials.flash, partials.theme-toggle, partials.theme-vars, partials.brand, components.survey-popup. components.role-switcher audited (it is the cross-cutting nav element).
