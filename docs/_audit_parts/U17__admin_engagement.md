---
unit_id: U17
title: Admin Engagement (Shop items, Admin Survey builder/responses/exports, Survey management)
scope: [App\Http\Controllers\Admin\ShopManagementController, App\Http\Controllers\Admin\SurveyController, App\Http\Controllers\Admin\SurveyManagementController]
routes_total: 18
routes_traced: 18
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 5, info: 2 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/shop | admin.shop.index | ShopManagementController@index | admin.shop.index | super_admin | OK | `$items`,`$stats` passed; cols exist; `user_purchases` table exists |
| GET | admin/shop/create | admin.shop.create | ShopManagementController@create | admin.shop.create | super_admin | OK | form sends all validator fields + `@csrf` + enctype |
| POST | admin/shop | admin.shop.store | ShopManagementController@store | redirect shop.index | super_admin | OK | validated keys ⊆ fillable; image stored to `shop/` disk public |
| GET | admin/shop/{id}/edit | admin.shop.edit | ShopManagementController@edit | admin.shop.edit | super_admin | OK | findOrFail + `$item`; `@method('PUT')` |
| PUT | admin/shop/{id} | admin.shop.update | ShopManagementController@update | redirect shop.index | super_admin | OK | adds `sold_out` to status enum (matches migration) |
| DELETE | admin/shop/{id} | admin.shop.destroy | ShopManagementController@destroy | JSON | super_admin | Major | F-U17-001 hard-delete cascades `user_purchases` (= C12-1) |
| GET | admin/surveys | admin.surveys.index | SurveyController@index | admin.surveys.index | super_admin | OK | `$surveys` passed; view doesn't read `$stats` |
| GET | admin/surveys/create | admin.surveys.create | SurveyController@create | admin.surveys.create | super_admin | OK | `$lessons` passed; concept/value null-guarded |
| POST | admin/surveys | admin.surveys.store | SurveyController@store | redirect surveys.index | super_admin | OK | `questions[*][...]`, `target_type[]`, `option_scores` all match; `@csrf` |
| GET | admin/surveys/{survey} | admin.surveys.show | SurveyController@show | admin.surveys.show | super_admin | OK | `$stats`,`$surveyUrl`,`$qrCode`,`$qrCodeType` passed; `survey.show` route exists |
| GET | admin/surveys/{survey}/edit | admin.surveys.edit | SurveyController@edit | admin.surveys.edit | super_admin | OK | `$survey` w/ questions; `@method('PUT')` |
| PUT\|PATCH | admin/surveys/{survey} | admin.surveys.update | SurveyController@update | redirect surveys.index | super_admin | OK | delete+recreate questions in txn; field names match |
| DELETE | admin/surveys/{survey} | admin.surveys.destroy | SurveyController@destroy | redirect surveys.index | super_admin | OK | guards on existing responses; unlinks pre/post |
| GET | admin/surveys/{survey}/comparison | admin.surveys.comparison | SurveyController@comparisonReport | admin.surveys.comparison | super_admin | OK | `getComparisonData()` returns all keys view reads |
| GET | admin/surveys/{survey}/export | admin.surveys.export | SurveyController@export | streamed CSV | super_admin | Minor | F-U17-002 CSV formula injection (= C03-5); else wiring OK |
| GET | admin/surveys/{survey}/export-responses | admin.surveys.export-responses | SurveyManagementController@exportResponses | streamed CSV | super_admin | Minor | F-U17-002 same; `completed_at`/`role` cols exist; not linked in any UI (F-U17-005) |
| GET | admin/surveys/{survey}/responses | admin.surveys.responses | SurveyController@responses | admin.surveys.responses | super_admin | OK | grouped responses; try/catch redirect on failure |
| DELETE | admin/surveys/{survey}/responses/{userId} | admin.surveys.responses.delete | SurveyController@deleteResponse | JSON / back | super_admin | OK | guest_/user handling; expectsJson branch |
| POST | admin/surveys/{survey}/toggle-status | admin.surveys.toggle-status | SurveyManagementController@toggleStatus | back | super_admin | Minor | F-U17-004 toggles active↔closed but no UI button calls this route |

unowned: none. All 18 of my rows traced. (Rows 193/361/362 in _ROUTES.txt are `App\Http\Controllers\SurveyController` — different namespace, not my scope.)

## Findings detail

F-U17-001 | Shop item hard-delete cascades purchase history | Model/DB | Major | app/Http/Controllers/Admin/ShopManagementController.php:103 ; migration 2025_12_17_160229_create_user_purchases_table.php:17 | `destroy()` calls `$item->delete()` and `user_purchases.shop_item_id` is `onDelete('cascade')` | every student who bought the item silently loses it from inventory and the `total_purchases` stat drops; no confirmation/soft-delete | Confirmed-static | PROPOSE: block delete when `purchasers()->exists()` (mirror Survey-with-responses guard) or soft-delete / set status=inactive instead. | related: C12-1

F-U17-002 | CSV/formula injection in survey exports | Controller | Minor | app/Http/Controllers/Admin/SurveyController.php:450,476 ; app/Http/Controllers/Admin/SurveyManagementController.php:297,299 | `fputcsv` writes user-supplied answer/question text without neutralizing leading `= + - @` | a crafted answer like `=cmd()` executes as a formula when the CSV is opened in Excel | Confirmed-static | PROPOSE: prefix any cell starting with `=+-@` with a single quote (or wrap in `"\t"`). Export wiring otherwise correct — real streamed responses, columns exist. | related: C03-5

F-U17-003 | SurveyManagementController CRUD methods are orphaned (route::resource binds Admin\SurveyController) | Wiring/orphans | Minor | routes/web.php:259 ; app/Http/Controllers/Admin/SurveyManagementController.php:19-247 | `Route::resource('surveys', SurveyController::class)` owns index/create/store/show/edit/update/destroy; SurveyManagementController's same-named methods are never routed | dead code; its `create()`/`edit()` pass `$schools`/`$roles` and `show()` passes `$responseStats` that the shared views do NOT read — would have broken the views had they been wired, but they aren't reachable | Confirmed-static | PROPOSE: delete the orphaned methods (keep only `toggleStatus` + `exportResponses`) or fold into one controller to avoid future mis-wiring. | related: none

F-U17-004 | toggle-status route has no UI entry point | Wiring/orphans | Minor | routes/web.php:264 ; resources/views/admin/surveys/* | `admin.surveys.toggle-status` (POST) is registered but no blade references `route('admin.surveys.toggle-status')` | feature unreachable from the UI; admins toggle status only via the edit form's status select | Needs-runtime-confirm | PROPOSE: add a toggle button on index/show, or remove the route if status is managed solely through edit. | related: none

F-U17-005 | export-responses route has no UI entry point | Wiring/orphans | Minor | routes/web.php:265 ; resources/views/admin/surveys/responses.blade.php:277 | responses view's "تصدير Excel" button links to `admin.surveys.export` (SurveyController), not `admin.surveys.export-responses`; no blade references the latter | the second export endpoint is dead UX (only `export` is reachable) | Confirmed-static | PROPOSE: pick one export path; remove the unused route/method or wire a button to it. | related: none

F-U17-006 | Shop image URL path likely wrong | View/Blade | Minor | resources/views/admin/shop/index.blade.php:95 ; resources/views/admin/shop/edit.blade.php:127 | image saved via `->store('shop','public')` (path `shop/x.jpg`) but rendered as `asset('storage/app/public/data/'.$item->image)` | uploaded product images 404 / show broken-image icon (falls back to icon only when `$item->image` is null, so uploaded images break) | Needs-runtime-confirm | PROPOSE: use `asset('storage/'.$item->image)` to match the public-disk symlink. Image is optional so not a blocker. | related: none

F-U17-007 | create form provides no is_mandatory / is_popup inputs | Validation↔Form | Info | resources/views/admin/surveys/create.blade.php (whole form) ; app/Http/Controllers/Admin/SurveyController.php:133-134,184-185 | store reads `$validated['is_mandatory'] ?? true` / `is_popup ?? true` but the create blade has no checkbox for either (edit blade does) | new surveys always default mandatory+popup = true with no way to opt out at creation; not an error, just a UX gap | Confirmed-static | PROPOSE: add the two checkboxes to create.blade (edit already has them) if opt-out at creation is desired. | related: none

F-U17-008 | Two controllers render the same admin.surveys.* views with divergent var contracts | Wiring/orphans | Info | app/Http/Controllers/Admin/SurveyController.php vs SurveyManagementController.php | both `view('admin.surveys.index'/'create'/'edit'/'show')`; views are built for SurveyController's contract (`$lessons`, `option_scores`, `survey_type`, `$stats[total_questions]`, `$qrCode`). Active routes use SurveyController, so no break. | latent foot-gun: re-pointing any resource route to SurveyManagementController would blank-screen the views | Confirmed-static | PROPOSE: consolidate to a single survey admin controller. | related: F-U17-003
