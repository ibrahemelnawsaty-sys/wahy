---
unit_id: U02
title: Public & Landing-front
scope: [App\Http\Controllers\PagesController, App\Http\Controllers\PublicRegistrationController, App\Http\Controllers\ContactController, App\Http\Controllers\EditorUploadController]
routes_total: 15
routes_traced: 15
status: complete
blockers: none
findings: { blocker: 0, major: 0, minor: 2, info: 2 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | / | landing | PagesController@landing | view `pages.show` (if PageBuilder slug=home active) else `landing` | anon+all | OK | Both views exist. `landing` standalone; `pages.show` extends `layouts.auth` (U-GLOBAL). Contact form AJAX-wired via `public/js/landing.js` (csrf via FormData `_token`). |
| GET | register | register | PagesController@register | view `register` | anon+all | OK | `@extends layouts.auth`; gated by `setting('enable_registration',true)` (helper exists). Form posts `register.post`→AuthController (U-AUTH, route exists). |
| GET | survey/{survey} | survey.show | PagesController@showSurvey | view `survey.show` | anon+all | OK | `findOrFail` + `with('questions')`; relation+casts exist. Form posts `survey.submit` (exists). = C02-5 (security audit: public access/leak). |
| GET | pages/{slug} | pages.show | PagesController@showPage | view `pages.show` / 404 | anon+all | OK | active-only; abort(404) on miss. |
| GET | page/{slug} | page.show | PagesController@showPageAlt | view `pages.show` / 404 | anon+all | OK | `PageBuilder::getBySlug` (active-only); abort(404) on miss. |
| GET | home | home.custom | PagesController@home | view `pages.show` / 404 | anon+all | OK | abort(404) if no active slug=home page. |
| GET | refresh-csrf | refresh.csrf | PagesController@refreshCsrf | JSON `{token}` | auth | OK | auth-gated; JSON only (AJAX). |
| POST | api/landing/content/snapshot | — | PagesController@landingSnapshot | JSON | super_admin | OK | auth+CheckRole:super_admin; `LandingContent::createSnapshot` exists; try/catch→500 JSON. |
| POST | contact | contact.store | ContactController@store | JSON (200/422/500) | anon | OK | Throttle 5,1. Validator fields = landing form fields (`full_name,email,user_type,message`); honeypot `website`. AJAX-consumed JSON. |
| POST | editor/upload-image | editor.upload-image | EditorUploadController@uploadImage | JSON | auth | OK | auth-gated; mime/size guarded; returns `{success,url,filename,size}`. |
| GET | register/teacher/{token} | public.register.teacher | PublicRegistrationController@showTeacherForm | view `public.register.teacher` | anon | OK | `School::where(teacher_token)->where(enable_teacher_registration)->firstOrFail` (cols exist). |
| POST | register/teacher/{token} | public.register.teacher.submit | PublicRegistrationController@registerTeacher | redirect back +flash | anon | OK | Throttle 6,1. `@csrf` present; form field names = validator. RegistrationRequest fillable OK; Mail+Notification classes exist. |
| GET | register/student/{token} | public.register.student | PublicRegistrationController@showStudentForm | view `public.register.student` | anon | OK | token+flag firstOrFail (cols exist). |
| POST | register/student/{token} | public.register.student.submit | PublicRegistrationController@registerStudent | redirect back +flash | anon | OK | `@csrf`; all form fields = validator (name,email,birth_date,grade_level,password,parent_*). |
| GET | register/parent/{token} | public.register.parent | PublicRegistrationController@showParentForm | view `public.register.parent` | anon | OK | token+flag firstOrFail (cols exist). |
| POST | register/parent/{token} | public.register.parent.submit | PublicRegistrationController@registerParent | redirect back +flash | anon | OK | `@csrf`; fields = validator (name,email,phone,password,relationship,children_names,address). |

(Note: 3 GET + 3 POST register rows = 6; total 15 traced. All PagesController/Contact/Editor/PublicRegistration rows owned — none unowned.)

## Findings detail

F-U02-001 | EditorUpload error message lists SVG as allowed but SVG is blocked | View/Controller | Minor | app/Http/Controllers/EditorUploadController.php:34 | `'image.mimetypes' => 'الأنواع المسموحة: JPG, PNG, GIF, WEBP, SVG'` — but ALLOWED_MIME and validation rule both exclude SVG (intentionally, anti-XSS) | On an SVG upload user is rejected yet the message implies SVG is accepted — confusing, not breaking | Confidence: Confirmed-static | PROPOSE: drop "SVG" from the message string. Implement nothing. | —

F-U02-002 | survey.submit has a duplicate auth-gated route name pointing to same handler | Routing | Minor | routes (_ROUTES.txt 361-362) | `survey/{survey}` (survey.submit, throttle, public) and `survey/{survey}/submit` (survey.ajax-submit, auth) both → SurveyController@submit; `survey.show` blade posts to public `survey.submit` | No user-facing break; the second route is orphaned from this view (SurveyController = not my unit) | Confidence: Needs-runtime-confirm | PROPOSE: lead reconcile with SurveyController unit whether ajax-submit is wired/needed. Implement nothing. | —

F-U02-003 | landingSnapshot/refreshCsrf are JSON-only endpoints with no obvious UI caller in this unit's views | Wiring/orphans | Info | app/Http/Controllers/PagesController.php:100,108 | refreshCsrf/landingSnapshot return JSON; no `<form>`/fetch to them found in landing/register/pages views | No user impact; likely called by admin landing-editor JS (out of unit) | Confidence: Needs-runtime-confirm | PROPOSE: none; observation only. | —

F-U02-004 | Public register GET forms render full standalone HTML (no shared layout) — duplicated CDN Bootstrap/FA across teacher/student/parent | View/Blade | Info | resources/views/public/register/{teacher,student,parent}.blade.php | Each blade is a self-contained `<!DOCTYPE html>` loading Bootstrap+FontAwesome from CDN | Renders fine; pure maintenance/dedup debt | Confidence: Confirmed-static | PROPOSE: none required for function. | —
