---
unit_id: U09
title: Messaging (1:1 chat + Bulk messages)
scope: [app/Http/Controllers/MessagesController.php, app/Http/Controllers/BulkMessageController.php]
routes_total: 21
routes_traced: 21
status: complete
blockers: none
findings: { blocker: 0, major: 0, minor: 3, info: 3 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | messages | messages.index | MessagesController@index | match→admin/school-admin/teacher/`messages.index` | all auth | OK | role-routed view; all vars passed (`conversations`,`availableUsers`,+student `stats`/`streak`) |
| GET | messages/conversation/{userId} | messages.conversation | @getConversation | JSON | all auth | OK | 403 on !canMessage; JSON shape consumed by admin/school-admin JS |
| POST | messages/send | messages.send | @send | JSON | super/school_admin/student/parent | OK | dup name across prefixes — see F-U09-002 |
| GET | messages/unread/count | messages.unread.count | @unreadCount | JSON `{count}` | all auth | OK | no direct UI poll found (orphan-ish, F-U09-003) |
| GET | messages/check-new/{userId} | messages.check.new | @checkNewMessages | JSON `{messages,hasNew}` | all auth | OK | polled by admin/index 5s interval; shape matches |
| GET | messages/check-all/new | messages.check.all | @checkAllNewMessages | JSON `{hasNew,total,notifications}` | all auth | OK | polled by public/js/messages-realtime.js; shape matches |
| POST | messages/upload | messages.upload | @chatUpload | JSON `{success,url}` | all auth | Minor | URL build convention, F-U09-001 (shared) |
| GET | messages/{userId} | messages.show | @show | `messages.show` | all auth | OK | abort(403) on !canMessage; wildcard last (after bulk + verbs) — OK |
| GET | messages/bulk | messages.bulk.index | BulkMessageController@index | `messages.bulk.index` | super/school_admin | OK | authorizeBulkSender 403s others; `sentMessages`,`stats` passed |
| GET | messages/bulk/create | messages.bulk.create | @create | `messages.bulk.create` | super/school_admin | OK | `schools`,`recipientCounts` passed; form fields match validator |
| GET | messages/bulk/inbox | messages.bulk.inbox | @inbox | `messages.bulk.inbox` | all auth | OK | `messages`,`unreadCount` passed; sender eager-loaded |
| GET | messages/bulk/recipient-count | messages.bulk.recipient-count | @getRecipientCount | JSON `{count}` | super/school_admin | OK | fetched by create.blade preview JS |
| POST | messages/bulk/send | messages.bulk.send | @send | redirect/back | super/school_admin | OK | `@csrf` present; fields subject/message/recipient_type/school_id match |
| POST | messages/bulk/{id}/read | messages.bulk.read | @markAsRead | back() | all auth | Minor | called via fetch POST w/ CSRF header — F-U09-004 (no form, ok) |
| GET | school-admin/messages | school-admin.messages.index | @index | `messages.school-admin.index` | school_admin | OK | CheckRole+CheckSchoolAccess; same data |
| GET | school-admin/messages/check-all/new | school-admin.messages.check.all | @checkAllNewMessages | JSON | school_admin | OK | baseUrl `/school-admin` branch in realtime.js |
| GET | school-admin/messages/check-new/{userId} | school-admin.messages.check.new | @checkNewMessages | JSON | school_admin | OK | — |
| GET | school-admin/messages/conversation/{userId} | school-admin.messages.conversation | @getConversation | JSON | school_admin | OK | school-admin/index JS fetches this |
| POST | school-admin/messages/send | school-admin.messages.send | @send | JSON | school_admin | OK | — |
| GET | school-admin/messages/unread/count | school-admin.messages.unread.count | @unreadCount | JSON | school_admin | OK | — |
| GET | school-admin/messages/{userId} | school-admin.messages.show | @show | `messages.show` | school_admin | OK | wildcard registered before unread/check routes but 2-seg routes don't collide |

Models/columns verified: `conversations(user1_id,user2_id,last_message_at)`, `messages(conversation_id,sender_id,receiver_id,message,is_read,read_at,timestamps)`, `bulk_messages(sender_id,recipient_type[VARCHAR50],recipient_id,school_id,subject,message,sent_at)`, `bulk_message_recipients(bulk_message_id,user_id,read_at)` — all present (migrations 2026_01_09_*, 2026_02_12). User relations used (`children`,`classrooms`,`teachingClassrooms`,`school`,`streak`,`badges`,`avatar_url`) all defined. School relations (`branches`,`users`,`students`,`teachers`) defined. Routes referenced in blades (`admin.theme.upload`,`dashboard`,`school-admin.dashboard`,`messages.bulk.*`,`messages.show/index/send/upload`) all resolve.

Security cross-refs (noted, not re-audited): narrowed `sender:id,name,avatar,role` select (C05-1) — blades only read `sender.name`/`avatar`/`role` and `sender_id`; **no blade reads a column outside the narrow select**, so no break. `safe_html()` render sink (C03) used in `messages.show` (line 593) and `bulk.inbox` (line 369) — sink only, safe_html itself is U-GLOBAL. Poll N+1 in `checkAllNewMessages` (per-conversation re-query inside loop, MessagesController:377-384) = C09-2 (security/perf) — functionally correct, not re-flagged.

## Findings detail

F-U09-001 | chatUpload returns non-standard storage URL | Controller/Storage | Minor | app/Http/Controllers/MessagesController.php:471 | `asset('storage/app/public/data/'.$path)` for a file stored on the `public` disk (root=`storage/app/public/data`, so `$path`=`chat-images/x`) | inserted chat `<img src>` may 404 if the deployed symlink/alias doesn't materialize the `/storage/app/public/data/` URL path | Needs-runtime-confirm | none required for U09 — this exact URL convention is shared verbatim by ThemeController:112, ActivityManagementController:186, LandingContentController:139 (a platform-wide `public`-disk URL convention, owned by U-GLOBAL/config). If those render correctly in prod, chat images do too. | related: config/filesystems.php public disk

F-U09-002 | Route name `messages.send` (and `messages.index`/`.show`/`.conversation`...) duplicated across prefix groups | Routing | Info | routes/web.php:145 (global `messages.`) vs 535 (`teacher.messages.send` → TeacherController) vs 616 (`parent.messages.send` → ParentController) | three different `messages.send` names exist, but each lives in a distinct name-prefix group (`messages.`, `teacher.`, `parent.`), so they are fully-qualified-distinct, not colliding | no user impact — `route('messages.send')` unambiguously resolves to the global MessagesController one used by the chat blades | Confirmed-static | none — naming is namespaced correctly. Listed only so lead can confirm no unprefixed collision elsewhere. | related: none

F-U09-003 | `unreadCount` endpoint has no traced consumer | Wiring/orphan | Minor | app/Http/Controllers/MessagesController.php:315; routes web.php:146 & 469 | `messages.unread.count` / `school-admin.messages.unread.count` route + method defined; grep found no fetch of `/messages/unread/count` in views or JS (badge updates instead ride `check-all/new`'s `total`) | none (dead-but-harmless endpoint) | Confirmed-static | optionally remove route+method or wire a badge poll to it; safe to leave | related: none

F-U09-004 | `messages.bulk.read` POST invoked via fetch, depends on `<meta name=csrf-token>` | View/CSRF | Minor | resources/views/messages/bulk/inbox.blade.php:418-423 | `markAsRead()` reads `document.querySelector('meta[name="csrf-token"]').content`; no `@csrf` form (it's a JS POST) | if the active layout for a non-admin role (parent/student/teacher viewing bulk inbox) omits the `<meta name=csrf-token>` tag, `markAsRead` throws (null.content) and the read-state silently never persists | Needs-runtime-confirm | confirm every layout that `bulk.inbox` @extends (it role-switches: admin/school-admin/teacher/parent/student-app) emits `<meta name="csrf-token">` — that tag lives in shared layouts (U-GLOBAL). No change in U09 if all layouts include it. | related: U-GLOBAL layouts

F-U09-005 | `messages.index` modal reads `$availableUsers->...->email` | Validation↔Form/View | Info | resources/views/messages/index.blade.php:483, partials/user-select-modal.blade.php:131,166 | blade renders `$user->email` for each available user | OK — `getAvailableUsers()` returns FULL User models (`->get()`), so `email` is present; not a narrowed select | Confirmed-static | none — flagged only to confirm C05-1 narrow-select does NOT apply here (that select is on `messages.sender`, a different query) | related: C05-1
