---
unit_id: U01
title: Auth & Account
scope: [App\Http\Controllers\AuthController, App\Http\Controllers\Api\AuthApiController, App\Http\Controllers\ProfileController, App\Http\Controllers\RoleSwitchController]
routes_total: 21
routes_traced: 21
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 2, info: 2 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| POST | api/v1/login | — | Api\AuthApiController@login | JSON | anon | OK | validator email/password; JSON only |
| POST | api/v1/two-factor/verify | — | Api\AuthApiController@verifyTwoFactor | JSON | anon | OK | validator user_id/code; TwoFactorCodeMail OK |
| POST | api/v1/logout | — | Api\AuthApiController@logout | JSON | auth(sanctum) | OK | currentAccessToken()->delete() |
| GET | api/v1/profile | — | Api\AuthApiController@profile | JSON | auth(sanctum) | OK | load school+classrooms rels exist |
| PUT | api/v1/profile | — | Api\AuthApiController@updateProfile | JSON | auth(sanctum) | OK | sometimes name/phone/avatar |
| POST | api/v1/change-password | — | Api\AuthApiController@changePassword | JSON | auth(sanctum) | OK | new_password confirmed; S7 token revoke |
| GET | login | login | AuthController@showLogin | auth.login | anon | OK | route(login/password.request/register) resolve |
| POST | login | — | AuthController@login | redirect/back | anon | OK | fields email/password/remember match form |
| POST | logout | logout | AuthController@logout | redirect(login) | auth | OK | — |
| GET | dashboard | dashboard | AuthController@dashboard | redirect by role | auth | OK | all 5 dashboard route names resolve |
| GET | forgot-password | password.request | AuthController@showForgotPassword | auth.forgot-password | anon | OK | form→password.email |
| POST | forgot-password | password.email | AuthController@sendResetLink | back(status) | anon | OK | ResetPasswordMail exists |
| GET | reset-password/{token} | password.reset | AuthController@showResetPassword | auth.reset-password | anon | OK | passes token+email; view null-guards email |
| POST | reset-password | password.update | AuthController@resetPassword | redirect(login)/back | anon | OK | token/email/password+confirmation match form |
| GET | two-factor/verify | two-factor.verify | AuthController@showTwoFactorVerify | auth.two-factor-verify | anon(session) | OK | session-gated; regenerates token |
| POST | two-factor/verify | two-factor.verify.post | AuthController@verifyTwoFactor | redirect/back | anon(session) | OK | form action route(two-factor.verify)→POST URI; hidden `code` field |
| POST | two-factor/resend | two-factor.resend | AuthController@resendTwoFactorCode | back | anon(session) | OK | form in 2fa view; @csrf present |
| POST | register | register.post | AuthController@register | redirect(register) | anon | Major | form offers role=school_admin not in validator in:teacher,student,parent (F-U01-001) |
| GET | password/change | password.change | AuthController@showPasswordChange | auth.change-password | auth | OK | standalone HTML; logout form @csrf |
| POST | password/change | password.change.update | AuthController@updatePassword | redirect(dashboard)/back | auth | OK | current_password/password+confirmation match |
| POST | profile/update-avatar | profile.update-avatar | ProfileController@updateAvatar | JSON | auth | OK | fetch() in admin/teacher/school-admin/super-admin layouts; avatar_url accessor exists |
| POST | switch-role/{role} | switch.role | RoleSwitchController@switch | redirect(dashboard) | auth | OK | role-switcher component; all paths return/abort; getAllRoles/switchRole/getRoleDashboardRoute exist |

(Note: GET `register` (name `register`) and the role dashboard targets are owned by PagesController / role controllers — out of unit; only verified their route NAMES resolve.)

## Findings detail

F-U01-001 | Register role dropdown offers `school_admin` but validator rejects it | Validation↔Form | Major | resources/views/register.blade.php:154 (option) vs app/Http/Controllers/AuthController.php:330 (`role => required|in:teacher,student,parent`) | the broken link: blade `<option value="school_admin">مدير مدرسة</option>` is sent but `register()` validates `in:teacher,student,parent` | how user sees it: selecting "مدير مدرسة" and submitting bounces back with `role.in` error "نوع الحساب غير صحيح" — a dead, un-selectable option that looks valid | Confidence: Confirmed-static | proposed fix (PROPOSE ONLY): either remove the `school_admin` option from the form, OR add `school_admin` to the validator's `in:` list if self-registration of school admins is intended | related IDs: none

F-U01-002 | Orphan view `auth/two-factor-verify-new.blade.php` | Wiring/orphans | Minor | resources/views/auth/two-factor-verify-new.blade.php | the broken link: no controller renders `auth.two-factor-verify-new` (showTwoFactorVerify returns `auth.two-factor-verify`) | how user sees it: never reached; dead file | Confidence: Confirmed-static | proposed fix (PROPOSE ONLY): delete the orphan or confirm it is an intentional draft | related IDs: none

F-U01-003 | Avatar-upload UI absent from student/parent layouts | Per-role/Wiring | Minor | resources/views/layouts/{student,parent}.blade.php (no `profile.update-avatar` fetch) | the broken link: `profile.update-avatar` is a web route reachable by any authenticated role, but only admin/teacher/school-admin/super-admin layouts expose the upload UI | how user sees it: students/parents have no way to change avatar from the UI (endpoint works if hit directly) | Confidence: Needs-runtime-confirm | proposed fix (PROPOSE ONLY): add the avatar-upload control to student/parent layouts if avatar change is intended for those roles | related IDs: none

F-U01-004 | API verifyTwoFactor success path skips the `status==active` re-check | Per-role/Controller | Info | app/Http/Controllers/Api/AuthApiController.php:204-227 | observation: web/api `login` checks account status before 2FA, but on the API 2FA-verify success branch a token is issued without re-reading status (status is already gated at login, so practically fine) | how user sees it: no user-facing break on normal path | Confidence: Needs-runtime-confirm | proposed fix (PROPOSE ONLY): optionally re-assert `status==='active'` before `createToken` on the verify path for defense-in-depth | related IDs: none

F-U01-005 | `register()` allows self-registration; created account is `status=inactive` | Wiring | Info | app/Http/Controllers/AuthController.php:354 | observation: new users land inactive and cannot log in (web `status==='inactive'` block, API `status!=='active'` 403) until admin activation — intended gate, noted for completeness | how user sees it: after register, login is blocked with "الحساب غير نشط" — expected | Confidence: Confirmed-static | proposed fix (PROPOSE ONLY): none — working as designed | related IDs: none
