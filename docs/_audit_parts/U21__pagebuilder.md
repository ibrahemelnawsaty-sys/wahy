---
unit_id: U21
title: PageBuilder / Landing CMS
scope: [app/Http/Controllers/Admin/PageBuilderController.php, app/Http/Controllers/Admin/LandingPageController.php, app/Http/Controllers/Api/LandingContentController.php]
routes_total: 16
routes_traced: 16
status: complete
blockers: none
findings: { blocker: 0, major: 2, minor: 4, info: 3 }
---

## Coverage table

Roles: only super_admin reaches admin/* and the protected api/landing/* (gate `access-admin` / `role:super_admin`); `api/landing/content` GET is anonymous-public.

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/pages | admin.pages.index | PageBuilderController@index | admin.pages.index | super_admin | OK | `$pages` passed; `pages.show` link resolves |
| GET | admin/pages/create | admin.pages.create | PageBuilderController@create | admin.pages.create-pro | super_admin | OK | form→store, fields match validator |
| POST | admin/pages | admin.pages.store | PageBuilderController@store | redirect index | super_admin | Major | `is_active` always-true bug (F-U21-001) |
| GET | admin/pages/{id}/edit | admin.pages.edit | PageBuilderController@edit | admin.pages.edit-pro | super_admin | OK | findOrFail; loads `json_data.sections` |
| PUT | admin/pages/{id} | admin.pages.update | PageBuilderController@update | redirect index | super_admin | Major | `is_active` always-true bug (F-U21-001) |
| DELETE | admin/pages/{id} | admin.pages.destroy | PageBuilderController@destroy | redirect index | super_admin | OK | @csrf+@method DELETE present |
| POST | admin/pages/preview | admin.pages.preview | PageBuilderController@preview | JSON | super_admin | Minor | orphaned — no view calls it (F-U21-004) |
| GET | admin/pages/preview/show | admin.pages.preview.show | PageBuilderController@showPreview | pages.show | super_admin | Minor | orphaned — only reached by preview() (F-U21-004) |
| GET | admin/landing-page | admin.landing.index | LandingPageController@index | admin.landing-page | super_admin | Major | URI shadowed by SuperAdminController (F-U21-002) |
| POST | admin/landing-page/theme | admin.landing.theme | LandingPageController@updateTheme | JSON | super_admin | Major | shadowed; runtime hits SuperAdmin (F-U21-002) |
| POST | admin/landing-page/content | admin.landing.content | LandingPageController@updateContent | JSON | super_admin | Major | shadowed + dead, no caller (F-U21-002/003) |
| GET | api/landing/content | (unnamed) | LandingContentController@index | JSON | anonymous | OK | consumed by landing.blade.php:1204 |
| POST | api/landing/content/update | (unnamed) | LandingContentController@update | JSON | super_admin | Minor | orphaned — no JS/blade caller (F-U21-005) |
| POST | api/landing/content/bulk-update | (unnamed) | LandingContentController@bulkUpdate | JSON | super_admin | OK | landing-editor.js:145; body matches validator |
| POST | api/landing/content/upload-image | (unnamed) | LandingContentController@uploadImage | JSON | super_admin | Info | landing-editor.js:218; storage URL convention (F-U21-006) |
| POST | api/landing/content/restore/{versionId} | (unnamed) | LandingContentController@restoreVersion | JSON | super_admin | Minor | orphaned + non-tx truncate = C13-5 (F-U21-007) |

unowned (note for lead): `admin/landing-page/*` block-editor + theme/content/import routes (`admin.landing-page.*`) and `api/landing/content/snapshot` map to `SuperAdminController` / `PagesController` (other units), not my three classes. The `super-admin/landing-page` redirect → `admin.landing-page` resolves OK.

## Findings detail

F-U21-001 | `is_active` select always saves "active" (disabled pages stay live) | Validation↔Form | Major | PageBuilderController.php:61,119 ; create-pro.blade.php:659-662 / edit-pro.blade.php:660-662 | controller uses `$request->has('is_active')`, but the form is a `<select name="is_active">` that ALWAYS submits a value (`1` or `0`); `has()` is true even for `0` | admin picks "✕ معطل" and saves → page stays `is_active=true` and remains publicly visible; cannot disable a page from the builder | Confidence: Confirmed-static | PROPOSE: use `$request->boolean('is_active')` (or `filter_var(...FILTER_VALIDATE_BOOL)`) instead of `->has(...)`. | —

F-U21-002 | Entire `LandingPageController` is route-shadowed by `SuperAdminController` (duplicate URIs) | Routing | Major | routes/web.php:206-208 vs 354-356 (same `admin` prefix group) | both blocks register GET `admin/landing-page`, POST `.../theme`, POST `.../content`; the later SuperAdminController block (354-356) wins each URI, so `LandingPageController@index/updateTheme/updateContent` never execute. `admin.landing-page.blade.php:356` `route('admin.landing.theme')` resolves to the shared URI but dispatches to `SuperAdminController@updateLandingTheme`. | No visible breakage today (the two implementations are near-identical), but the three methods in my unit are dead code and any future divergence silently won't run; also a latent maintenance trap | Confidence: Confirmed-static | PROPOSE: delete the duplicate `LandingPageController` + its routes 206-208 (consolidate on SuperAdminController), OR give it distinct URIs. Lead to reconcile with the unit owning SuperAdminController. | related: F-U21-003

F-U21-003 | `LandingPageController@updateContent` validation diverges from the live SuperAdmin version | Validation | Minor | LandingPageController.php:61-63 vs SuperAdminController.php:997-998 | dead method validates `json_data` as `required|json` (string→json_decode); the winning live method validates `required|array`. If 206-208 were ever re-prioritized, the editor (which posts a JS object/array) would 422 | only matters if F-U21-002 is "fixed" by reordering rather than deleting | Confidence: Confirmed-static | PROPOSE: resolve as part of F-U21-002 (delete the dead controller). | related: F-U21-002

F-U21-004 | `admin.pages.preview` + `preview.show` are orphaned (builder uses direct slug open) | Wiring/orphans | Minor | PageBuilderController.php:155,190 ; create-pro.blade.php:2265-2268 / edit-pro.blade.php:~2245 | the AJAX preview endpoint + its session-backed `showPreview` are never called; both builder views' `preview()` instead does `window.open('/pages/'+slug)` | for an unsaved page, the public slug route returns nothing (getBySlug requires `is_active=true` + saved record) → preview button opens a blank/404 page until after save | Confidence: Confirmed-static | PROPOSE: either wire `preview()` to POST `admin.pages.preview` then open `preview.show`, or drop the unused preview controller methods+routes. | —

F-U21-005 | `api/landing/content/update` (single-key) has no caller | Wiring/orphans | Minor | LandingContentController.php:49 ; routes/web.php:57 | grep across views + public/js finds no consumer; landing-editor.js only uses bulk-update/upload-image/snapshot | dead endpoint, no user impact | Confidence: Confirmed-static | PROPOSE: remove or document as API-only. | —

F-U21-006 | uploadImage returns a non-standard storage URL | Controller | Info | LandingContentController.php:129,139 | stores to `landing-images` on `public` disk then returns `asset('storage/app/public/data/'.$path)` rather than `Storage::url()` / `asset('storage/'.$path)` | image may 404 if the deploy's `public/storage` symlink doesn't expose `app/public/data/`; BUT the same string convention is used by ThemeController, MessagesController, ActivityManagementController AND read back identically in pages/show.blade.php:541,552 — so it is self-consistent within the project's storage layout | Confidence: Needs-runtime-confirm | PROPOSE: none for this unit alone; verify the `storage/app/public/data` mapping at U-GLOBAL/storage-config level. | —

F-U21-007 | `restoreVersion` truncates then re-inserts with no transaction; also orphaned | Controller | Minor | LandingContentController.php:166-175 | snapshot→`LandingContent::truncate()`→loop `create()` outside any DB transaction; a mid-loop failure leaves the table empty (landing content wiped). No UI caller found. = C13-5 (security/data audit) | if invoked and it fails mid-restore, the public landing content is lost with no rollback | Confidence: Confirmed-static | PROPOSE: wrap in `DB::transaction(...)` (and `delete()` over `truncate()` so it participates in the tx). | related: C13-5

## Cross-refs noted (not re-audited)
- C03-4: `pages/show.blade.php:672` renders `html` block via `{!! safe_html($component['content']['code']) !!}` — unescaped landing-block sink (render side = U02 / helper = U-GLOBAL). Builder writes this block; schema otherwise matches.
- Public renderer `pages/show.blade.php` new-format schema (`json_data.sections[].columns` + `.grid[][].{type,content}`) is CONSISTENT with what the builder JS serializes (create-pro/edit-pro) and what `LandingPageController::createDefaultLandingPage()` seeds. No schema mismatch. The old-format fallback branch (line 691 `@switch($block['type'])`) is unguarded but only triggers for legacy flat-array pages — render side owned by U02.
