---
unit_id: U18
title: Admin Reports/Logs
scope: [app/Http/Controllers/Admin/ReportsController.php, app/Http/Controllers/Admin/MessagesLogController.php]
routes_total: 18
routes_traced: 18
status: complete
blockers: none
findings: { blocker: 1, major: 2, minor: 3, info: 2 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/reports | admin.reports.index | ReportsController@index | →dashboard() | super_admin | OK | delegates to dashboard |
| GET | admin/reports/dashboard | admin.reports.dashboard | ReportsController@dashboard | admin.reports.dashboard | super_admin | Major | `$value->emoji` undefined col (F-U18-002) |
| GET | admin/reports/students | admin.reports.students | ReportsController@students | admin.reports.students | super_admin | OK | all vars passed; relations/cols exist |
| GET | admin/reports/students/{id} | admin.reports.students.detail | ReportsController@studentDetail | admin.reports.student-detail | super_admin | OK | progressByValue try/catch-guarded; emoji hardcoded 💎 |
| GET | admin/reports/schools | admin.reports.schools | ReportsController@schools | admin.reports.schools | super_admin | OK | withCount aliases match blade |
| GET | admin/reports/schools/{id} | admin.reports.schools.detail | ReportsController@schoolDetail | admin.reports.school-detail | super_admin | OK | all stats/vars passed |
| GET | admin/reports/activities | admin.reports.activities | ReportsController@activities | admin.reports.activities | super_admin | Blocker | unguarded `$activity->lesson->concept->value->emoji` on nullable lesson_id (F-U18-001) |
| GET | admin/reports/values | admin.reports.values | ReportsController@values | admin.reports.values | super_admin | Major | `$value->emoji` undefined col (F-U18-002) |
| POST | admin/reports/export | admin.reports.export | ReportsController@export | Excel download | super_admin | OK | all 6 Export classes exist; cols valid |
| POST | admin/reports/export-pdf | admin.reports.export-pdf | ReportsController@exportPdf | DomPDF download | super_admin | OK | pdf.report view exists, guarded |
| GET | admin/reports/export-pdf | admin.reports.export-pdf.get | ReportsController@exportPdf | DomPDF download | super_admin | Minor | dup name on GET+POST same target; no UI hits GET (F-U18-005) |
| GET | admin/messages-log | admin.messages-log.index | MessagesLogController@index | admin.messages-log.index | super_admin | OK | sort allowlist guards; relations exist |
| GET | admin/messages-log/conversation/{conversationId} | admin.messages-log.conversation | MessagesLogController@showConversation | admin.messages-log.conversation | super_admin | OK | user1/user2 relations exist |
| GET | admin/messages-log/export | admin.messages-log.export | MessagesLogController@export | CSV stream | super_admin | OK | = C03-5/C13-4 (security); cols valid (F-U18-006 info) |
| GET | admin/messages-log/statistics | admin.messages-log.statistics | MessagesLogController@statistics | admin.messages-log.statistics | super_admin | OK | all chart vars passed & plucked |
| GET | admin/messages-log/{id} | admin.messages-log.show | MessagesLogController@show | admin.messages-log.show | super_admin | OK | sender/receiver null-guarded |
| DELETE | admin/messages-log/{id} | admin.messages-log.destroy | MessagesLogController@destroy | redirect back | super_admin | OK | @csrf + @method present in blades |

## Findings detail

F-U18-001 | Activities report crashes on activity with null lesson/concept/value | View/Blade | Blocker | resources/views/admin/reports/activities.blade.php:56 | `{{ $activity->lesson->concept->value->emoji }} {{ $activity->lesson->concept->value->name }}` chains with `->` (no `optional()`/`?->`); `activities.lesson_id` is nullable (database/migrations/2025_11_18_140503_create_activities_table.php:16) so any bank/standalone activity with null lesson — or a lesson with null concept — throws "Attempt to read property on null" | Activities report page 500s entirely for super_admin if ≥1 activity lacks a lesson chain | Confidence: Confirmed-static (crash) / Needs-runtime-confirm (depends on data having a null-lesson activity) | Guard the chain: `{{ optional(optional(optional($activity->lesson)->concept)->value)->name ?? '—' }}` and drop/guard `->emoji`. (PDF template report.blade.php:152 and ActivitiesExport already guard with `?? '—'`/`optional()` — apply same.) | related: F-U18-002

F-U18-002 | `$value->emoji` reads a column that does not exist | Model/DB | Major | resources/views/admin/reports/dashboard.blade.php:163; resources/views/admin/reports/values.blade.php:23; resources/views/admin/reports/activities.blade.php:56 | Views render `$value->emoji`, but the `values` table has `name` + `icon` (no `emoji`) — database/migrations/2025_11_18_140433_create_values_table.php:18 — and Value model has no `emoji` accessor (app/Models/Value.php). Eloquent returns null for undefined attribute → blank, no crash | The value icon silently never displays on dashboard "top values", values report cards, and activities-report value column | Confidence: Confirmed-static | Replace `$value->emoji` with `$value->icon`. ValuesExport.php:53 already correctly uses `$value->icon`; ReportsController studentDetail comment (line 234) already acknowledges "values.emoji (العمود غير موجود)" | related: F-U18-001

F-U18-003 | Messages-log sort offers `read_at` not in controller allowlist | View/Blade | Minor | resources/views/admin/messages-log/index.blade.php:447 vs app/Http/Controllers/Admin/MessagesLogController.php:60 | Sort dropdown offers `<option value="read_at">تاريخ القراءة`, but the controller allowlist `$allowedSortColumns` excludes `read_at`, so it silently falls back to `created_at` | User picks "sort by read date", list is sorted by send date instead (no error, just ignored) | Confidence: Confirmed-static | Add `'read_at'` to `$allowedSortColumns` (it is a real nullable column) or remove the option | related: none

F-U18-004 | Activities-report type/status filter options narrower than data | View/Blade | Minor | resources/views/admin/reports/activities.blade.php:20-33 | Type filter only lists quiz/exercise/project and status only active/inactive, but Activity supports 8 types (per dashboard.blade map) and other statuses; badge label ternary (line 55) collapses every non-quiz/exercise type to "مشروع" | Activities of type project/creative/upload/etc. are mislabeled and unfilterable | Confidence: Confirmed-static | Expand the `<option>` lists (and badge map) to the full type set used in dashboard.blade.php:673 | related: none

F-U18-005 | Duplicate route name pattern for export-pdf (GET + POST) | Routing | Minor | routes (_ROUTES.txt rows 112-113) | Both `admin.reports.export-pdf` (POST) and `admin.reports.export-pdf.get` (GET) map to `exportPdf`; no blade references the `.get` GET variant (all export buttons POST) — orphan entry point | No user-facing break; dead GET route | Confidence: Confirmed-static | Drop the GET `export-pdf` route if unused, or wire a UI link | related: F-U18-007

F-U18-006 | MessagesLog CSV export builds entire result set in memory | Controller | Info | app/Http/Controllers/Admin/MessagesLogController.php:185 | `export()` does `->get()` (no `chunk`/cursor) then streams — full table loaded into memory; functionally returns a valid CSV response with existing columns (id/sender/receiver/message/is_read/read_at/created_at all real) | Works; potential memory pressure on very large logs (runtime/data-dependent) | Confidence: Needs-runtime-confirm | Use `->cursor()` inside the stream callback | related: C03-5 / C13-4 (security audit — memory export & CSV export, cross-ref only)

F-U18-007 | Orphan-ish: `exportPdf` GET + no nav entry; `index()` is thin delegate | Wiring/orphans | Info | ReportsController.php:50-53, 431 | `index()` only forwards to `dashboard()` (fine). The GET `export-pdf` route has no blade trigger (F-U18-005). No PDF export button found in any traced report blade — only Excel POST forms exist — so `exportPdf` may be UI-orphaned despite working | PDF export feature reachable only by direct URL/POST, not via UI | Confidence: Confirmed-static (no blade ref to exportPdf in unit views) | Add a "تصدير PDF" button or confirm intentional | related: F-U18-005

## Notes
- Shared layout `layouts.admin` and Chart.js (CDN) are out of unit scope (U-GLOBAL/assets) — noted, not audited.
- All POST/DELETE forms in messages-log + reports blades carry `@csrf` (and `@method('DELETE')` where needed) — no 419 risk found.
- `getSchoolFilter()` returns null for super_admin → all-school scope; per-role tracing limited to super_admin per dispatch. School-admin reachability of these `access-admin` routes is a separate authz concern (not in this functional unit).
