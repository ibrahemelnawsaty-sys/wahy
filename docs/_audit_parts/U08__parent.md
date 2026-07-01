---
unit_id: U08
title: Parent portal
scope: [app/Http/Controllers/ParentController.php, app/Http/Controllers/ParentDashboardController.php]
routes_total: 11
routes_traced: 11
status: complete
blockers: none
findings: { blocker: 0, major: 0, minor: 3, info: 1 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | parent/child/{id} | parent.child.details | ParentDashboardController@childDetails | view parent.child-detail | parent | OK | aborts 403 if not own child; all 16 compact vars used by blade; chart/praise modal wired |
| POST | parent/children/{id}/gift | parent.child.gift | ParentController@sendGift | back()->with(...) | parent | Minor | route works + validation/field-names OK, but NO form/UI anywhere posts to it (orphan route) — F-U08-001 |
| POST | parent/children/{id}/praise | parent.child.praise | ParentController@praiseChild | JSON | parent | OK | child-detail modal posts praise_message+praise_type JSON; matches input() reads; ownership-checked |
| GET | parent/dashboard | parent.dashboard | ParentDashboardController@index | view parent.dashboard | parent | OK | passes childrenData+schoolComparison; exactly the vars blade uses; per-child try/catch fallback |
| GET | parent/family-activities/pending | parent.family-activities.pending | ParentController@pendingFamilyActivities | view parent.family-activities.pending | parent | OK | $submissions paginated; student/activity/submission_data/photos all exist |
| POST | parent/family-activities/{id}/approve | parent.family-activities.approve | ParentController@approveFamilyActivity | back()->with(...) | parent | OK | both modals @csrf; fields praise/custom_praise/reject/rejection_reason match validator; ownership-locked |
| GET | parent/messages | parent.messages | ParentController@messages | view parent.messages | parent | OK | $teachers+$conversations passed; html_excerpt (U-GLOBAL) |
| GET | parent/messages/conversation | parent.messages.conversation | ParentController@getConversation | JSON | parent | OK | reads teacher_id/student_id query; marks read |
| POST | parent/messages/send | parent.messages.send | ParentController@sendMessage | JSON | parent | OK | fetch posts teacher_id/student_id/message JSON; matches validate(); X-CSRF-TOKEN header sent |
| GET | parent/surveys/comparisons | parent.surveys.comparisons | ParentDashboardController@surveyComparisonsList | view parent.surveys.comparisons-list | parent | OK | $surveys passed; links to comparison |
| GET | parent/surveys/{surveyId}/comparison | parent.surveys.comparison | ParentDashboardController@surveyComparison | view parent.surveys.comparison | parent | OK | isAssessment()/getComparisonData(null,$childIds) exist; @include partials.survey-comparison (shared) |

## Findings detail

F-U08-001 | sendGift route has no UI entry point | Wiring/orphan | Minor | routes/web.php (parent.child.gift) → ParentController@sendGift:413 | POST parent/children/{id}/gift is registered, validates gift_type/gift_message, and back()->with() works, but no blade in resources/views posts to route('parent.child.gift') — grep across all views finds only read-only gift displays in student/gifts.blade.php | A parent can never send a gift from the UI; the daily-gift / +10-points feature is dead from the parent side | Confirmed-static | Either add a gift form to parent.child-detail (alongside the praise modal) or drop the route+method if intentionally retired | none

F-U08-002 | Orphan controller methods never routed | Wiring/orphan | Minor | ParentController@dashboard:18, ParentController@childDetail:73 | Both methods exist and render parent.dashboard / parent.child-detail, but the routed dashboard+child-detail use ParentDashboardController@index/@childDetails instead; ParentController's pair is unreferenced | No user impact (unreachable); but ParentController@dashboard passes compact('user','school','children','stats') to parent.dashboard which now consumes $childrenData/$schoolComparison — if ever wired it would throw undefined-var | Confirmed-static | Delete the two dead methods (+ private getChildProgressChartData helper they use) to avoid confusion/divergence | none

F-U08-003 | Orphan blade views | Wiring/orphan | Minor | resources/views/parent/child-details.blade.php, resources/views/parent/children-reports.blade.php | No controller in app/ references view('parent.child-details') or view('parent.children-reports') (routed detail view is singular parent.child-detail) | No user impact (unrendered dead templates) | Confirmed-static | Remove the two unused blades or wire them if intended | none

F-U08-004 | gift/praise/family-activity points wiring verified clean | Wiring | Info | ParentController.php (praiseChild:331, sendGift:431, approveFamilyActivity:503) | All three use AwardService::award(userId, sourceType, sourceId, points, coins, desc) with correct positional args (idempotency key = praise/gift/submission id) and NotificationService::create(userId,type,title,message,data,actionUrl) correct arg order — NOT the C13 action_url bug; all parent_praises/parent_gifts/parent_points/award columns exist in migrations | n/a | none | related: C13 (avoided), C04-5 ($user->parent — not used here; parent uses children()/parents() belongsToMany which exist) |
