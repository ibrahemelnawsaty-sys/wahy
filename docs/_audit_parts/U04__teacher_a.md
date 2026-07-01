---
unit_id: U04
title: Teacher-A — TeacherController (first half: dashboard, classrooms, activities, exercises, leaderboards, messages)
scope: [app/Http/Controllers/TeacherController.php (first ~28 routes), resources/views/teacher/*]
routes_total: 28
routes_traced: 28
status: complete
blockers: none
findings: { blocker: 0, major: 3, minor: 4, info: 2 }
---

## Boundary
Owned rows = _ROUTES.txt lines 364–391 (28 rows, file order). **Boundary URI (last I own): `POST teacher/messages/send` (teacher.messages.send, line 391).** U05 owns 392–418 (parent-engagement, question-bank, ratings, reports, review, settings, students, surveys, teams).
Shared private helper: none. `destroyTeam()` delegates to `deleteTeam()` but both are U05.
Note: methods `createActivity`, `analytics`, `addActivityToBank`, `activityBank`, `featureActivity`, `unfeatureActivity`, `practiceExercises`, `createExercise/storeExercise/editExercise/updateExercise/deleteExercise/exerciseResults`, `teacherLeaderboard`, `studentLeaderboard` are physically in the file's lower half but are routed by MY rows (364–391), so I own them.

## Coverage table
| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | teacher/activities | teacher.activities | activities | teacher.activities | teacher | OK | $activities,$stats keys match; lesson.concept.value guarded |
| POST | teacher/activities | teacher.activities.store | storeActivity | redirect activities | teacher | Minor | **Orphan route** — no blade posts here; create-activity form posts to activity-bank.store instead (F-U04-001) |
| GET | teacher/activities/create | teacher.activities.create | createActivity | teacher.create-activity | teacher | Major | Form action = activity-bank.store → "new activity" silently becomes a pending bank item (F-U04-001) |
| PUT | teacher/activities/{id} | teacher.activities.update | updateActivity | redirect activities | teacher | Major | Form sends `duration_minutes` + `attachment`; validator has neither → silently dropped (F-U04-002, F-U04-003) |
| DELETE | teacher/activities/{id} | teacher.activities.delete | deleteActivity | JSON | teacher | OK | JS fetch DELETE w/ X-CSRF-TOKEN |
| GET | teacher/activities/{id}/edit | teacher.activities.edit | editActivity | teacher.edit-activity | teacher | OK | @csrf+@method(PUT); ownership scoped |
| POST | teacher/activities/{id}/feature | teacher.activities.feature | featureActivity | back() | teacher | Minor | No UI entry point found in teacher blades (orphan-ish) — F-U04-006 |
| GET | teacher/activities/{id}/preview | teacher.activities.preview | previewActivity | teacher.preview-activity | teacher | OK | $activity passed; edit links resolve |
| POST | teacher/activities/{id}/unfeature | teacher.activities.unfeature | unfeatureActivity | back() | teacher | Minor | No UI entry point — F-U04-006 |
| POST | teacher/activity-bank | teacher.activity-bank.store | addActivityToBank | redirect activity-bank.index | teacher | Major | Receives create-activity media inputs (image/audio/video/document) but validates/stores none → dead media section (F-U04-004) |
| GET | teacher/activity-bank | teacher.activity-bank.index | activityBank | teacher.activity-bank | teacher | OK | $activities,$stats,$questions,$questionStats all passed |
| GET | teacher/activity-bank/create | teacher.activity-bank.create | createActivity | teacher.create-activity | teacher | OK | Same view; for bank flow the activity-bank.store action is correct here |
| GET | teacher/analytics | teacher.analytics | analytics | teacher.analytics | teacher | OK | all 6 vars passed; ?? guarded |
| GET | teacher/classrooms | teacher.classrooms | classrooms | teacher.classrooms | teacher | OK | $classrooms,$stats,$school passed |
| GET | teacher/classrooms/{id} | teacher.classrooms.detail | classroomDetail | teacher.classroom-detail | teacher | OK | firstOrFail scoped to teacher_id; N+1 in student loop (Info) |
| GET | teacher/dashboard | teacher.dashboard | dashboard | teacher.dashboard | teacher | Minor | `activity:id,title` eager-restrict → blade reads $submission->activity->points/type ⇒ blank/null (no crash, default match) F-U04-005 |
| GET | teacher/exercises | teacher.exercises | practiceExercises | teacher.practice-exercises | teacher | OK | $exercises,$stats; attempts_count via withCount |
| POST | teacher/exercises | teacher.exercises.store | storeExercise | redirect exercises | teacher | OK | field names match; = C02-6 (security) wiring OK |
| GET | teacher/exercises/create | teacher.exercises.create | createExercise | teacher.create-exercise | teacher | OK | @csrf; $classrooms,$questions |
| PUT | teacher/exercises/{id} | teacher.exercises.update | updateExercise | redirect exercises | teacher | OK | @method(PUT); = C02-6 wiring OK |
| DELETE | teacher/exercises/{id} | teacher.exercises.delete | deleteExercise | JSON | teacher | OK | JS DELETE w/ CSRF |
| GET | teacher/exercises/{id}/edit | teacher.exercises.edit | editExercise | teacher.create-exercise | teacher | OK | reuses create-exercise with $exercise |
| GET | teacher/exercises/{id}/results | teacher.exercises.results | exerciseResults | teacher.exercise-results | teacher | OK | $exercise,$attempts,$stats; relations exist |
| GET | teacher/leaderboard/students | teacher.leaderboard.students | studentLeaderboard | teacher.student-leaderboard | teacher | OK | $leaders,$scope; = C02-4 family (security) |
| GET | teacher/leaderboard/teachers | teacher.leaderboard.teachers | teacherLeaderboard | teacher.leaderboard | teacher | OK | $leaders,$scope,$currentTeacher(+Rank); TeacherPoint cols exist |
| GET | teacher/messages | teacher.messages | messages | teacher.messages | teacher | OK | $parents,$conversations; html_excerpt (U-GLOBAL) |
| GET | teacher/messages/conversation | teacher.messages.conversation | getConversation | JSON | teacher | OK | reads parent_id/student_id query; marks read |
| POST | teacher/messages/send | teacher.messages.send | sendMessage | JSON | teacher | OK | fetch w/ CSRF; field names match; route('parent.messages') in actionUrl |

## Findings detail

F-U04-001 | "New activity" create flow silently routes to the approval-gated bank | Routing/Validation↔Form | Major | resources/views/teacher/create-activity.blade.php:174 ; routes/web.php:492,505 ; TeacherController.php:639(storeActivity, unused) & 1577(addActivityToBank) | `teacher.activities.create` renders create-activity.blade whose `<form action="{{ route('teacher.activity-bank.store') }}">` → addActivityToBank (sets approval_status='pending', is_activity_bank=true). | Teacher clicks "➕ نشاط جديد" on dashboard/activities list expecting to publish an activity to their class; instead it is created as a pending bank submission needing admin approval and never appears as a live classroom activity. storeActivity (the direct-publish path) is never reachable from any blade. | Confirmed-static | Point `teacher.activities.create` at a dedicated create view that posts to `teacher.activities.store`, OR repurpose/remove storeActivity and rename the button to "إضافة لبنك الأنشطة". | related: none (functional)

F-U04-002 | Edit-activity duration field name mismatch → silent data loss | Validation↔Form | Major | resources/views/teacher/edit-activity.blade.php:116 ; TeacherController.php:767 (updateActivity validator) | Form input `name="duration_minutes"`; updateActivity validator only allows `quiz_duration` (both are real columns per migrations 2025_12_17 & 2026_01_09). `$validated` strips `duration_minutes`. | Teacher edits the quiz duration, saves, value is silently discarded — duration never updates. | Confirmed-static | Either rename the form field to `quiz_duration`, or add `duration_minutes` to the validator + persist it (decide which column is canonical; both exist). | related: none

F-U04-003 | Edit-activity attachment upload silently ignored | Validation↔Form/Controller | Major | resources/views/teacher/edit-activity.blade.php:199 ; TeacherController.php:747-804 | Form `name="attachment"` (file); updateActivity has no `attachment` rule and no `$request->hasFile('attachment')` handling (unlike updateSettings which does handle avatar). | UI says "رفع ملف جديد سيستبدل المرفق الحالي" but uploading a new attachment does nothing — current attachment unchanged. | Confirmed-static | Add `attachment => nullable|file|...` to validator and store/replace the file in updateActivity (mirror the avatar logic in updateSettings). | related: none

F-U04-004 | create-activity media-upload section is dead (image/audio/video/document never stored) | Validation↔Form/Controller | Major | resources/views/teacher/create-activity.blade.php:298-342 ; TeacherController.php:1577 (addActivityToBank) | The form's entire "📎 الوسائط المتعددة" block sends file inputs `image`,`audio`,`video`,`document`; addActivityToBank validator lists none of them and performs no file handling. | Teacher attaches media to a new activity; all uploads are silently dropped on submit. | Confirmed-static | Add file rules + storage handling in addActivityToBank for each media input (or remove the media UI if unsupported). | related: F-U04-001

F-U04-005 | Dashboard pending list: restricted eager-load drops activity->points/type | Model/DB/View | Minor | TeacherController.php:85 (`'activity:id,title'`) ; resources/views/teacher/dashboard.blade.php:248,288 | Eager load selects only id+title; blade reads `$submission->activity->type` (match has default → no crash) and `->points` (renders blank). `->lesson->title` lazy-loads (works, N+1). | "النقاط المحتملة:" shows empty; activity icon always falls to default 📝. No error/500. | Confirmed-static | Add `points,type,lesson_id` to the eager-load column list: `'activity:id,title,type,points,lesson_id'`. | related: none

F-U04-006 | feature/unfeature activity routes have no teacher UI entry | Wiring/orphans | Minor | TeacherController.php:1855,1881 ; routes/web.php:521-522 | POST teacher.activities.feature / .unfeature exist + work, but no teacher blade renders a form/button posting to them (grep of teacher/* found none). | Feature-an-activity capability is unreachable from the teacher portal (dead endpoints from the UI's perspective). | Needs-runtime-confirm | Add a "تمييز" button on activities/activity-bank rows, or drop the routes if superseded. | related: none

## Info
- I-U04-A | N+1 queries in classroom-detail.blade.php:99 (per-student ActivitySubmission count inside @forelse) and classrooms()/dashboard() per-classroom loops. Functional-OK, perf only.
- I-U04-B | C02-7 updateStreakSettings (streak-settings.blade @csrf+@method(PUT), fields enabled/min_days/max_days(hidden)/bonus_points match validator) — WIRING/render verified OK; security logic out of scope. Economy/award wiring in submitReview & gradeTeamActivity (AwardService::award idempotent, try/catch wrapped) renders/returns JSON correctly — out-of-scope security per contract.
