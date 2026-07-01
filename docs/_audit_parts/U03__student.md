---
unit_id: U03
title: Student portal
scope: [App\Http\Controllers\StudentController]
routes_total: 31
routes_traced: 31
status: complete
blockers: none
findings: { blocker: 0, major: 0, minor: 2, info: 1 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | student/dashboard | student.dashboard | dashboard | student.dashboard | student | OK | all 10 vars passed; relations streak/badges/crowns/classrooms verified |
| GET | student/path | student.path | learningPath | student.path | student | OK | 6 vars passed; sequential_lesson_lock setting guarded |
| GET | student/learn | student.learn | learn | student.learn | student | OK | currentLesson/stats/streak |
| GET | student/lesson/{id} | student.lesson | lesson | student.lesson-view | student | OK | findOrFail + BOLA visibleForSchool guard; lessonStreak guarded |
| GET | student/values-tree | student.values-tree | valuesTree | student.values-tree | student | OK | syncCrowns + visible values |
| GET | student/crowns | student.crowns | crowns | student.crowns | student | OK | crowns/availableCrowns; value rel exists |
| GET | student/badges | student.badges | badges | student.badges | student | OK | orderByPivot earned_at; masteredValues from crowns |
| GET | student/leaderboard | student.leaderboard | leaderboard | student.leaderboard | student | OK | withSum points; school-scoped (cf C02-4) |
| GET | student/analytics | student.analytics | analytics | student.analytics | student | Minor | renders OK; NO nav/UI entry point (F-U03-002) |
| GET | student/profile | student.profile | profile | student.profile-view | student | OK | try/catch safe defaults; logout form @csrf ok |
| POST | student/profile/update | student.profile.update | updateProfile | JSON | student | OK | name/email/avatar/password validated |
| GET | student/shop | student.shop | shop | student.shop-view | student | OK | ShopItem active+available |
| POST | student/shop/purchase | student.shop.purchase | purchaseItem | JSON | student | OK | item_id; SpendService atomic |
| POST | student/shop/redeem | student.shop.redeem | redeemReward | JSON | student | OK | reward_id; cost derived server-side (Batch 2) |
| GET | student/coins/history | student.coins.history | coinsHistory | JSON | student | OK | fetched from shared student layout |
| GET | student/gifts | student.gifts | gifts | student.gifts | student | OK | praisesReceived/giftsReceived→parent rel ok |
| GET | student/teams | student.teams | teams | student.teams | student | OK | teams.members/creator rels ok |
| GET | student/activity/{id} | student.activity | activity | student.activity-view | student | OK | findOrFail + isActivityAccessibleByStudent guard |
| POST | student/activity/{id}/submit | student.activity.submit | submitActivity | JSON | student | OK | field `answer` matches (NOT C04-4 web-side); X-CSRF header |
| GET | student/practice | student.practice | practice | student.practice-view | student | OK | ensureDefaultPvpChallenge; Schema::hasTable guards |
| GET | student/practice/{id}/start | student.practice.start | startExercise | student.practice-start | student | OK | BOLA exerciseBelongsToStudent; max_attempts gate |
| POST | student/practice/{id}/submit | student.practice.submit | submitExercise | redirect→result | student | OK | `answers`/`time_taken` match form names |
| GET | student/practice/result/{attemptId} | student.practice.result | exerciseResult | student.practice-result | student | OK | scoped where student_id |
| GET | student/pvp | student.pvp.lobby | pvpLobby | student.pvp-lobby | student | OK | hasTable fallback view; availableForSchool |
| POST | student/pvp/{challengeId}/join | student.pvp.join | joinPvpMatch | JSON | student | OK | BOLA availableForSchool guard |
| GET | student/pvp/{matchId}/play | student.pvp.play | pvpPlay | student.pvp-play | student | OK | participant-only (403 otherwise) |
| GET | student/pvp/{matchId}/status | student.pvp.status | pvpMatchStatus | JSON | student | OK | IDOR-guarded; polled from pvp-play |
| GET | student/pvp/{matchId}/result | student.pvp.result | pvpResult | student.pvp-result | student | OK | participant-only; match+student passed |
| POST | student/pvp/{matchId}/submit | student.pvp.submit | submitPvpAnswers | JSON | student | OK | participant-only; AwardService idempotent |
| GET | student/rate-teachers | student.rate.teachers | rateTeachers | student.rate-teachers | student | OK | teachingClassrooms.students; ratings rel ok |
| POST | student/rate-teacher | student.rate.submit | submitRating | JSON | student | OK | teacher_id/rating/comment match JS payload |

## Findings detail

F-U03-001 | Dead private method `calculateScore()` | Controller | Minor | app/Http/Controllers/StudentController.php:1078 | method defined, never called | not user-visible — grading goes through `ActivityGradingService::grade()` in submitActivity; calculateScore is orphaned legacy code | Confirmed-static | remove `calculateScore()` (PROPOSE ONLY) | —

F-U03-002 | `student.analytics` has no UI entry point | Wiring/orphan | Minor | route student/analytics (StudentController@analytics) | no `route('student.analytics')` in any student blade, layout, or nav partial | page renders correctly but is reachable only by typing the URL — no link/button leads to it | Confirmed-static | add an analytics link to the student nav or dashboard (PROPOSE ONLY) | —

F-U03-003 | All POST/PUT submit forms are JS-driven (no native action) | View/Wiring | Info | rate-teachers/activity-view/pvp-play forms | forms `#ratingForm`,`#activityForm`,`#challengeForm` have no `action`/`method`; submit via `fetch()` | not broken — each fetch sends `X-CSRF-TOKEN` header and correct field names (`teacher_id`/`rating`/`comment`; `answer`/`answer_file`; `answers`/`time_taken`); native forms (practice-start, logout) carry `@csrf` | Confirmed-static | none — documenting the CSRF-via-header pattern | —

## Notes
- All 25 student `view(...)` targets exist on disk; all 31 public methods map 1:1 to the 31 routes (no unrouted public method).
- All `route()`/`action()` names referenced in student blades resolve to real routes.
- Every var consumed by the 20 audited blades is either passed by the controller or null-guarded (subagent blade-var sweep: all OK).
- All model relations in `with()/load()` verified: User(streak,badges,crowns,classrooms,teams,parents,points,purchases,praisesReceived,giftsReceived,teachingClassrooms,ratings); PvpMatch(player1/2,winner,challenge); PvpChallenge(value,matches,scopeAvailableForSchool); PracticeExercise(teacher,attempts); Lesson(concept,activities,hasStreakEnabled); Value(scopeVisibleForSchool,crowns); Classroom(teacher,students); Team(members,creator); ParentPraise/ParentGift(parent).
- Cross-ref security items already covered elsewhere (NOT re-audited): BOLA/IDOR guards present on lesson, activity, exercise start/submit, pvp join/play/status/result/submit; leaderboard school-scoped (cf C02-4); shop redeem server-side cost (Batch 2). C04-4 (`answers`→`answer`) is the **Api** submitActivity — the **web** submitActivity here correctly validates `answer` and the blade sends `answer`, so no data loss on this route.
