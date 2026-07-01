---
unit_id: U05
title: Teacher-B — TeacherController (second half: grading/teams/exercises/reports/settings)
scope: [app/Http/Controllers/TeacherController.php (methods from parentEngagement onward — URI rows teacher/parent-engagement .. teacher/teams/{id}/edit)]
routes_total: 27
routes_traced: 27
status: complete
blockers: none
findings: { blocker: 0, major: 3, minor: 3, info: 1 }
---

## Boundary with U04
- _ROUTES.txt has 55 TeacherController rows (lines 364–418). Split at midpoint (28/55).
- **U04 owns lines 364–391** (last = `POST teacher/messages/send` → sendMessage).
- **U05 (this unit) owns lines 392–418, 27 routes.** First URI I own (boundary): **`GET teacher/parent-engagement`** (line 392). Zero gap / zero overlap with U04.
- Shared/out-of-scope noted, not audited: `layouts/teacher.blade.php` (U-NAV sidebar provides most nav entry points), `partials/survey-comparison.blade.php` (shared partial — exists, OK), `errors/*`.

## Coverage table
| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | teacher/parent-engagement | teacher.parent-engagement | parentEngagement | teacher.parent-engagement | teacher | OK | `$rows`,`$totals` passed; aggregate queries on parent_praises/parent_gifts/parent_teacher_messages/parent_student — all tables exist |
| POST | teacher/question-bank | teacher.question-bank.store | addQuestionToBank | redirect question-bank.index | teacher | Major | F-U05-003: form sends image/audio/video — no columns/handling → media silently discarded |
| GET | teacher/question-bank | teacher.question-bank.index | questionBank | teacher.question-bank | teacher | OK | `$questions`,`$stats` match |
| GET | teacher/question-bank/create | teacher.question-bank.create | createQuestion | teacher.create-question | teacher | OK | `$lessons` passed; text fields match validator |
| GET | teacher/ratings | teacher.ratings | ratings | teacher.ratings | teacher | OK | `$ratings`,`$averageRating`,`$ratingDistribution` match; TeacherRating model+table OK |
| GET | teacher/reports/classroom/{classroomId} | teacher.reports.classroom | exportClassroomReport | teacher.classroom-report | teacher | OK | linked from classrooms + classroom-detail; vars OK |
| GET | teacher/reports/student/{studentId} | teacher.reports.student | exportStudentReport | PDF download (reports.student-progress) | teacher | Minor | F-U05-004: PDF view+`\PDF` facade OK, but NO UI entry point anywhere (orphan route) |
| GET | teacher/review | teacher.review | reviewSubmissions | teacher.review-submissions | teacher | OK | `$submissions` paginated; uses value->name (correct) |
| GET | teacher/review/{id} | teacher.review.single | reviewSubmission | teacher.review-single | teacher | Major | F-U05-001: view shows blank Value/Concept (uses ->title not ->name); F-U05-005: `submitted_at->format()` unguarded (line 45) |
| POST | teacher/review/{id} | teacher.review.submit | submitReview | JSON | teacher | OK | AJAX POST, `@csrf` token via FormData; fields score/feedback/xp_awarded/coins_awarded match validator; award via AwardService |
| GET | teacher/settings | teacher.settings | settings | teacher.settings | teacher | OK | `$user`,`$school` passed; bio/notifications null-guarded in view |
| POST | teacher/settings/update | teacher.settings.update | updateSettings | redirect teacher.settings | teacher | Major | F-U05-002: `bio` + `notifications_enabled` not in User $fillable → silently dropped (data loss). name/email/phone/avatar save OK |
| GET | teacher/streak-settings | teacher.streak.settings | streakSettings | teacher.streak-settings | teacher | OK | `$streakSettings`,`$streakBonusCount`,`$activeStreakCount` match; Setting/ActivityUserStreak OK |
| PUT | teacher/streak-settings | teacher.streak.update | updateStreakSettings | redirect teacher.streak.settings | teacher | OK | form `@method('PUT')`+`@csrf`; enabled/min_days/max_days(hidden)/bonus_points match validator. = C02-7 (security audit) |
| GET | teacher/students | teacher.students | studentReports | teacher.student-reports | teacher | OK | `$students`,`$classrooms`; aggregated points/coins/streaks queries OK |
| GET | teacher/students/{id} | teacher.students.detail | studentDetail | teacher.student-detail | teacher | OK | `$student`,`$stats`,`$recentActivities`,`$xpProgress`; access-guarded |
| GET | teacher/surveys/comparisons | teacher.surveys.comparisons | surveyComparisonsList | teacher.surveys.comparisons-list | teacher | OK | `$surveys`; survey_type/assessment_phase cols exist; value->name (correct) |
| GET | teacher/surveys/{surveyId}/comparison | teacher.surveys.comparison | surveyComparison | teacher.surveys.comparison (includes partials.survey-comparison) | teacher | OK | `$survey`,`$comparisonData`; isAssessment()/getComparisonData(null,$ids) signatures match; partial exists |
| GET | teacher/teams | teacher.teams | teams | teacher.teams | teacher | OK | `$teams` paginated withCount('members'); delete via fetch DELETE + X-CSRF-TOKEN |
| POST | teacher/teams | teacher.teams.store | storeTeam | redirect teacher.teams | teacher | OK | create-team form `@csrf`; name/classroom_id/leader_id/member_ids[]/description match validator. = C02-2 (security audit) |
| POST | teacher/teams/activities/{id}/grade | teacher.teams.grade | gradeTeamActivity | JSON | teacher | Minor | F-U05-004: validator/columns OK (total_score/teacher_feedback exist via later migration), but NO UI entry point (orphan route) |
| POST | teacher/teams/assign-activity | teacher.teams.assign | assignTeamActivity | JSON | teacher | Minor | F-U05-004: wiring OK, but NO UI entry point (orphan route) |
| GET | teacher/teams/create | teacher.teams.create | createTeam | teacher.create-team | teacher | OK | `$classrooms`,`$students` passed |
| GET | teacher/teams/{id} | teacher.teams.show | showTeam | teacher.show-team | teacher | OK | `$team`,`$leader`,`$members`; no edit/grade/assign UI on page |
| POST | teacher/teams/{id} | teacher.teams.update | updateTeam | redirect teacher.teams.show | teacher | OK | edit-team form posts (POST, not PUT — matches route) `@csrf`; fields match validator |
| DELETE | teacher/teams/{id} | teacher.teams.destroy | destroyTeam→deleteTeam | JSON | teacher | OK | fetch DELETE from teams list w/ X-CSRF-TOKEN; returns {success,message} matching JS |
| GET | teacher/teams/{id}/edit | teacher.teams.edit | editTeam | teacher.edit-team | teacher | Minor | F-U05-004: page renders, but NO link to it from teams list or show-team (orphan UI) |

## Findings detail

F-U05-001 | Grading page shows BLANK Value & Concept labels | View/Blade | Major | resources/views/teacher/review-single.blade.php:55,59 | `{{ $submission->activity->lesson->concept->value->title }}` and `...->concept->title` reference a `title` attribute that does not exist — `values` table column is `name` (migration 2025_11_18_140433), `concepts` column is `name` (migration ...140440); neither model defines a `title` accessor. | On the single-submission grading page the "القيمة" and "المفهوم" info rows render empty (Eloquent returns null for the missing attribute). Lesson row (`lesson->title`) is correct since `lessons` does have `title`. Every other teacher view (activities, review-submissions:88, student-detail:112, comparisons-list:20) correctly uses `->name`. | Confirmed-static | Change line 55 to `->value->name` and line 59 to `->concept->name`. | —

F-U05-002 | Teacher settings: bio & notifications toggle silently not saved | Controller/Model | Major | app/Http/Controllers/TeacherController.php:485-508 (updateSettings); app/Models/User.php:83-100 ($fillable) | Validator accepts `bio` and `notifications_enabled`; `$user->update($validated)` is called, but neither `bio` nor `notifications_enabled` is in User `$fillable` (and no such columns exist in any migration). Laravel mass-assignment silently drops non-fillable keys. | Teacher edits bio / flips the notifications switch, sees "تم تحديث الإعدادات بنجاح", but the values are never persisted (data loss, no error). name/email/phone/avatar persist fine (all fillable). | Confirmed-static | Either add `bio`/`notifications_enabled` columns + add to `$fillable`, or drop these two fields from the settings form + validator to match reality. | —

F-U05-003 | Question-bank: media uploads (image/audio/video) silently discarded | Validation↔Form / Model | Major | resources/views/teacher/create-question.blade.php:315,324,333 ; app/Http/Controllers/TeacherController.php:1628-1663 (addQuestionToBank) ; database/migrations/2026_01_07_225049_create_question_bank_table.php | The form prominently offers image/audio/video file inputs ("الوسائط المتعددة"), but `addQuestionToBank` neither validates nor stores them, and the `question_bank` table has no media columns. Files are uploaded and dropped on the floor. | Teacher attaches media to a question, submits, gets success — media is gone; saved question has no attachment. | Confirmed-static | Either wire media: add columns + `Storage::store()` + persist paths; or remove the media-upload section from the blade to avoid the false promise. | —

F-U05-004 | Orphan routes with no UI entry point | Wiring/orphans | Minor | routes/_ROUTES.txt lines 412 (teams.grade), 413 (teams.assign), 418 (teams.edit)+416 (teams.update), 398 (reports.student) | No blade anywhere links to `teacher.teams.grade`, `teacher.teams.assign`, or `teacher.reports.student`. `teacher.teams.edit`/`teacher.teams.update` are referenced ONLY inside edit-team.blade itself (the form) — neither the teams list nor show-team links to the edit page, so it is reachable only by typing the URL. The team grade/assign engines and the student-PDF export are effectively dead from the UI. | Teacher cannot grade/assign team activities or edit a team or export a student PDF through any button — features exist server-side but are unreachable. | Confirmed-static | Add the missing buttons/links (edit-team link on team card/show-team; grade+assign UI on show-team; student-PDF button on student-detail). PROPOSE ONLY. | C02 (team IDOR cluster) |

F-U05-005 | review-single: unguarded submitted_at->format() | View/Blade | Minor | resources/views/teacher/review-single.blade.php:45 | `{{ $submission->submitted_at->format('Y-m-d H:i') }}` is unguarded; the sibling list view (review-submissions:94) guards the same field with `?->` + `created_at` fallback. If a reachable pending submission has null `submitted_at`, this throws "format() on null". | Edge case: opening a pending submission whose `submitted_at` is null → 500 on the grading page. Pending submissions normally have submitted_at set, so low likelihood. | Needs-runtime-confirm | Use `$submission->submitted_at?->format(...) ?? $submission->created_at->format(...)` to match review-submissions. | —

## Info
- I-U05-A | submitReview returns JSON and is consumed by AJAX in review-single (no full-page redirect); CSRF satisfied via `_token` in FormData. Award path uses idempotent `AwardService::award(... 'activity_submission', submissionId ...)` and is fully try/catch-wrapped so secondary failures don't break the response — well wired. Team grade/store/update all eager-load real relations (Team::leader/members/activities/classroom all defined) and write real columns (team_activities total_score/teacher_feedback/submitted_at added by migration 2026_06_03_140000). Note storeTeam/updateTeam = C02-2 and updateStreakSettings = C02-7 (security audit) — wiring/render here is correct.
