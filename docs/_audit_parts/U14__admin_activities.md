---
unit_id: U14
title: Admin Activities (Management / Bank / Approval)
scope: [app/Http/Controllers/Admin/ActivityManagementController.php, app/Http/Controllers/Admin/ActivityBankController.php, app/Http/Controllers/Admin/ActivityApprovalController.php]
routes_total: 20
routes_traced: 20
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 4, info: 2 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/activities | admin.activities.index | ActivityManagementController@index | admin.activities.index | super_admin | OK | vars activities,lessons passed; csrf on toggle/delete; F-U14-002 (unguarded concept->value chain) |
| POST | admin/activities | admin.activities.store | ActivityManagementController@store | redirect index | super_admin | OK | validator↔form fields match; @csrf present |
| GET | admin/activities/create | admin.activities.create | ActivityManagementController@create | admin.activities.create | super_admin | OK | vars lessons,selectedLesson passed; F-U14-002 (concept->value->icon unguarded l.256) |
| POST | admin/activities/upload-image | admin.activities.upload-image | ActivityManagementController@uploadImage | json {url} | super_admin | Major | F-U14-001 wrong public URL prefix `storage/app/public/data/` → image 404 |
| GET | admin/activities/{activity} | admin.activities.show | ActivityManagementController@show | admin.activities.show | super_admin | OK | activity,submissionsCount passed; lesson null-guarded; F-U14-003 back-btn |
| PUT/PATCH | admin/activities/{activity} | admin.activities.update | ActivityManagementController@update | redirect index | super_admin | OK | @csrf+@method PUT; fields match validator |
| DELETE | admin/activities/{activity} | admin.activities.destroy | ActivityManagementController@destroy | redirect/back | super_admin | OK | @csrf+@method DELETE present |
| GET | admin/activities/{activity}/edit | admin.activities.edit | ActivityManagementController@edit | admin.activities.edit | super_admin | OK | null-safe `concept?->value?->icon`; uses safe_html (U-GLOBAL) |
| POST | admin/activities/{activity}/toggle-status | admin.activities.toggle-status | ActivityManagementController@toggleStatus | back | super_admin | OK | @csrf present |
| GET | admin/activity-approval | admin.activity-approval.index | ActivityApprovalController@index | admin.activity-approval.index | super_admin | OK | activities,stats,status passed; concept->value->name null-guarded (`?? ''`) |
| POST | admin/activity-approval/bulk-approve | admin.activity-approval.bulkApprove | ActivityApprovalController@bulkApprove | redirect index | super_admin | Minor | F-U14-004 routed but no UI trigger (orphan endpoint) |
| GET | admin/activity-approval/{activity} | admin.activity-approval.show | ActivityApprovalController@show | admin.activity-approval.show | super_admin | OK | activity loaded w/ creator.school,lesson.concept.value,approver |
| POST | admin/activity-approval/{activity}/approve | admin.activity-approval.approve | ActivityApprovalController@approve | redirect index | super_admin | OK | @csrf; NotificationService::send 5-arg OK |
| POST | admin/activity-approval/{activity}/reject | admin.activity-approval.reject | ActivityApprovalController@reject | redirect index | super_admin | OK | rejection_reason field matches validator; @csrf |
| GET | admin/activity-bank | admin.activity-bank.index | ActivityBankController@index | admin.activity-bank | super_admin | OK | all 7 compact vars passed; Value/Lesson select cols exist |
| POST | admin/activity-bank/store | admin.activity-bank.store | ActivityBankController@storeActivity | redirect index | super_admin | OK | form fields exactly match validator; @csrf |
| POST | admin/activity-bank/{id}/approve-activity | admin.activity-bank.approve-activity | ActivityBankController@approveActivity | json | super_admin | OK | AJAX+CSRF header; NotificationService::create 6-arg OK |
| POST | admin/activity-bank/{id}/approve-question | admin.activity-bank.approve-question | ActivityBankController@approveQuestion | json | super_admin | Minor | F-U14-005 = C13-1: action URL in `$data` slot → dead link, action_url null |
| POST | admin/activity-bank/{id}/reject-activity | admin.activity-bank.reject-activity | ActivityBankController@rejectActivity | json | super_admin | OK | AJAX sends {reason}; matches $request->reason |
| POST | admin/activity-bank/{id}/reject-question | admin.activity-bank.reject-question | ActivityBankController@rejectQuestion | json | super_admin | Minor | F-U14-005 = C13-1: action URL in `$data` slot → dead link, action_url null |

## Findings detail

F-U14-001 | Activity image upload returns wrong public URL | Controller | Major | app/Http/Controllers/Admin/ActivityManagementController.php:185-186 | `$path = ...->store('activities/images','public'); $url = asset('storage/app/public/data/' . $path)` — public disk maps to storage/app/public, served at `/storage/{path}`; the canonical correct form (LeaderboardController:391) is `asset('storage/'.$path)`. The `storage/app/public/data/` segment yields `/storage/app/public/data/activities/images/x.jpg` which does not exist under the storage symlink. | Uploaded image_order images render then 404 (alt/onerror fallback "❌ صورة غير متاحة"); the URL is also persisted into `questions` JSON so it stays broken after save. | Needs-runtime-confirm (4 controllers share this exact prefix — MessagesController:471, LandingContentController:139, ThemeController:112 — so a non-standard storage route MAY exist; if it does this is a no-op, if not all four 404) | PROPOSE: replace with `asset('storage/'.$path)` (or `Storage::disk('public')->url($path)`) to match the working pattern; verify storage:link target. | related: C03-3 (SVG upload — svg allowed in mimes here too)

F-U14-002 | Unguarded `lesson->concept->value` chain in index/create | View | Minor | resources/views/admin/activities/index.blade.php:231,308 ; resources/views/admin/activities/create.blade.php:256 | `{{ $lesson->concept->value->icon }}` / `$activity->lesson->concept->value->icon` with no null-safe operator. `lessons.concept_id` is nullable and `concepts.value_id` likewise; show.blade.php & edit.blade.php correctly use `?->`. | If any lesson has null concept (or concept has null value), index/create throw "Attempt to read property on null" (500). | Needs-runtime-confirm (data-dependent; admin-curated lessons normally have full chain) | PROPOSE: use `$lesson->concept?->value?->icon` consistent with edit/show views. | related: cross-unit `concept->value` pattern

F-U14-003 | Back-to-lesson button passes null model when activity has no lesson | View | Minor | resources/views/admin/activities/show.blade.php:289 | `route('admin.lessons.show', $activity->lesson)` sits OUTSIDE the `@if($activity->lesson)` guard (l.213-228 guards only the breadcrumb). `activities.lesson_id` is nullable (bank activities are often lesson-less). | For a lesson-less activity the link resolves to `/admin/lessons` with empty binding → 404 / wrong page on click. | Confirmed-static (link generation), Needs-runtime-confirm (only triggers for null-lesson activity) | PROPOSE: guard the button or fall back to `route('admin.activities.index')` when `$activity->lesson` is null. | related: —

F-U14-004 | bulkApprove endpoint has no UI entry point | Wiring/orphans | Minor | app/Http/Controllers/Admin/ActivityApprovalController.php:123 ; route admin.activity-approval.bulk-approve | Method + route exist and are correct, but neither activity-approval/index nor /show renders any checkbox list or form posting `activity_ids[]` to this route. | Feature unreachable from UI (dead endpoint), not user-visible breakage. | Confirmed-static | PROPOSE: either add bulk-select UI to index, or drop the route if unused. | related: —

F-U14-005 | approveQuestion/rejectQuestion pass action-URL into the `$data` argument slot | Controller | Minor | app/Http/Controllers/Admin/ActivityBankController.php:189-195, 213-219 | `NotificationService::create($id,$type,$title,$message, route('teacher.question-bank.index'))` — signature is `create($userId,$type,$title,$message,$data=[],$actionUrl=null)`, so the route string lands in `$data` (cast to array on Notification) and `$actionUrl` stays null. Sibling methods approveActivity/rejectActivity (l.133-140,165-172) correctly pass `[]` then the url. | Teacher's question-approved/rejected notification has a dead "view" link (action_url null); the URL string gets json-stored in `data`. No fatal (array cast accepts the string). | Confirmed-static | PROPOSE: insert `[]` as the 5th arg: `...create($id,$type,$title,$message, [], route('teacher.question-bank.index'))`. | related: C13-1/2/3 (NotificationService arg-order, security audit) — same root

## Notes
- info: Image-upload mimes allow `svg` (l.182) — see C03-3 (SVG upload) cross-ref; wiring only, not re-audited here.
- info: `create.blade.php` / `edit.blade.php` type selector exposes only quiz/exercise/project, while store/update validators accept a wider enum (creative,upload,practical,discussion,image_order) and ActivityBank.storeActivity adds homework,practice. Not a bug (UI offers a valid subset); flagged as observation only.
- Columns/relations verified present: activities.{title,description,type,question_type,difficulty,coins,points,passing_score,order,status,approval_status,approved_by,approved_at,rejection_reason,is_activity_bank,created_by,classroom_id,quiz_duration,max_attempts,allowed_file_types,max_file_size}; values.name/icon; lessons.title/concept_id; question_bank.{title,question_text,question_type,status,difficulty}; Lesson→concept→value, Activity→{creator,approver,lesson,submissions}, User→school all defined. No `value->title` / `lesson->meaning` mismatches in this unit.
- All 20 routes guarded by `web,auth,Authorize:access-admin` (super_admin). No orphan controller methods (every public method is routed).
- Shared deps noted, not audited (U-GLOBAL): `layouts.admin`, `safe_html()`.
