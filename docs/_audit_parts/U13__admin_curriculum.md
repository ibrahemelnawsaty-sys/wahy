---
unit_id: U13
title: Admin Curriculum (Values / Concepts / Lessons CMS)
scope: [App\Http\Controllers\Admin\ValueManagementController, App\Http\Controllers\Admin\ConceptManagementController, App\Http\Controllers\Admin\LessonManagementController]
routes_total: 23
routes_traced: 23
status: complete
blockers: none
findings: { blocker: 0, major: 1, minor: 2, info: 3 }
---

## Coverage table

| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |
|------|-----|------|-------------------|---------------|-------|---------|------|
| GET | admin/values | admin.values.index | ValueManagementController@index | admin.values.index | super_admin | OK | `with('creator','concepts')`; cols name/description/status/order exist; image URL = app-wide convention (Info-1) |
| POST | admin/values | admin.values.store | ValueManagementController@store | redirect values.index | super_admin | OK | form fields name/description/icon/image/order/status match rules; @csrf+enctype present |
| GET | admin/values/create | admin.values.create | ValueManagementController@create | admin.values.create | super_admin | OK | no vars needed; image upload wired |
| GET | admin/values/{value} | admin.values.show | ValueManagementController@show | admin.values.show | super_admin | OK | conceptsCount/lessonsCount passed; `load(concepts.lessons,creator)`; no image render |
| PUT/PATCH | admin/values/{value} | admin.values.update | ValueManagementController@update | redirect values.index | super_admin | OK | @method PUT + @csrf; old-image delete guarded |
| DELETE | admin/values/{value} | admin.values.destroy | ValueManagementController@destroy | redirect/back | super_admin | OK | blocks delete if concepts attached |
| GET | admin/values/{value}/edit | admin.values.edit | ValueManagementController@edit | admin.values.edit | super_admin | OK | image URL = app-wide convention (Info-1) |
| POST | admin/values/{value}/toggle-status | admin.values.toggle-status | ValueManagementController@toggleStatus | back() | super_admin | Minor | orphan: no UI entry point invokes it (F-U13-003) |
| GET | admin/concepts | admin.concepts.index | ConceptManagementController@index | admin.concepts.index | super_admin | OK | `with('value','lessons')`; `$values` passed; value_id/search cols exist |
| POST | admin/concepts | admin.concepts.store | ConceptManagementController@store | redirect concepts.index | super_admin | OK | value_id/name/description/order match; @csrf present |
| GET | admin/concepts/create | admin.concepts.create | ConceptManagementController@create | admin.concepts.create | super_admin | OK | `$values`+`$selectedValue` passed |
| GET | admin/concepts/{concept} | admin.concepts.show | ConceptManagementController@show | admin.concepts.show | super_admin | OK | lessonsCount passed; links to lessons.create/values.show resolve |
| PUT/PATCH | admin/concepts/{concept} | admin.concepts.update | ConceptManagementController@update | redirect concepts.index | super_admin | OK | @method PUT + @csrf |
| DELETE | admin/concepts/{concept} | admin.concepts.destroy | ConceptManagementController@destroy | redirect/back | super_admin | OK | blocks delete if lessons attached |
| GET | admin/concepts/{concept}/edit | admin.concepts.edit | ConceptManagementController@edit | admin.concepts.edit | super_admin | OK | `$concept`+`$values` passed |
| GET | admin/lessons | admin.lessons.index | LessonManagementController@index | admin.lessons.index | super_admin | OK | `with('concept.value')`; `$concepts` passed; type/status filters omit mixed/archived (F-U13-002) |
| POST | admin/lessons | admin.lessons.store | LessonManagementController@store | redirect lessons.index | super_admin | OK | all 18 fields incl images[]/streak_* match rules; @csrf+enctype; uploads wired |
| GET | admin/lessons/create | admin.lessons.create | LessonManagementController@create | admin.lessons.create | super_admin | OK | `$concepts`+`$selectedConcept`; route('editor.upload-image') resolves |
| GET | admin/lessons/{lesson} | admin.lessons.show | LessonManagementController@show | admin.lessons.show | super_admin | Major | media `src` missing `/data` segment → video/audio 404 (F-U13-001) |
| PUT/PATCH | admin/lessons/{lesson} | admin.lessons.update | LessonManagementController@update | redirect lessons.index | super_admin | OK | @method PUT + @csrf; existing media path correct (`storage/data/`) |
| DELETE | admin/lessons/{lesson} | admin.lessons.destroy | LessonManagementController@destroy | redirect/back | super_admin | OK | blocks delete if activities attached; cleans up files |
| GET | admin/lessons/{lesson}/edit | admin.lessons.edit | LessonManagementController@edit | admin.lessons.edit | super_admin | OK | `$lesson`+`$concepts`; existing-media URLs correct |
| POST | admin/lessons/{lesson}/toggle-status | admin.lessons.toggle-status | LessonManagementController@toggleStatus | back() | super_admin | OK | wired in lessons.index 🔄 button |

## Findings detail

F-U13-001 | Lesson media (video/audio) src omits `/data` disk-root segment → 404 | View/Blade | Major | resources/views/admin/lessons/show.blade.php:294,312 | `<source src="{{ asset('storage/' . ltrim($lesson->video_file,'/')) }}">` (also audio l.312) | Admin opens a lesson show page; uploaded video/audio player shows but file never loads (broken/empty media). | Confirmed-static | The `public` disk root is `storage_path('app/public/data')` and the symlink is `public/storage → storage/app/public` (config/filesystems.php:43,100), so the browser path must be `/storage/data/<stored>`. The sibling EDIT view already does this correctly (`asset('storage/data/' . $lesson->video_file)`, edit.blade.php:445,483,417). Change show.blade.php lines 294 & 312 to `asset('storage/data/' . ltrim($lesson->video_file,'/'))` / `...audio_file...`. NOTE: the same wrong prefix exists in student/lesson-view.blade.php:492,521 (out of unit — flag for U-student owner). | related: Info-1
F-U13-002 | Lesson `status` select + index filter omit `archived` (and `mixed`) options | View/Blade | Minor | resources/views/admin/lessons/create.blade.php:502-505, edit.blade.php (status select), index.blade.php:239-253 | status `<select>` offers only active/draft though validation allows `in:active,draft,archived`; index type filter offers text/video/audio but not `mixed` | An archived lesson can never be created/cleared via the form, and `mixed`-type lessons can't be filtered in the index dropdown. No error — just an unreachable valid state. | Confirmed-static | Add `<option value="archived">` to the status selects and `<option value="mixed">` to the index type filter to match the validators' allowed sets. | related: none
F-U13-003 | `admin.values.toggle-status` route has no UI entry point | Wiring/orphans | Minor | app/Http/Controllers/Admin/ValueManagementController.php:155 (route web.php; views resources/views/admin/values/*) | Route+controller method exist and work, but no blade in the values views posts to `admin.values.toggle-status` (lessons index has the equivalent 🔄 button; values index/show do not) | Super admin can toggle a value's status only by editing it; the dedicated toggle endpoint is dead from the UI. | Confirmed-static | Either add a toggle button to admin.values.index (mirror the lessons.index 🔄 form) or drop the orphan route. | related: none

---

### Info / observations (no action required by U13)

- **Info-1 (storage path convention):** `asset('storage/app/public/data/' . $value->image)` in values index.blade.php:211 and edit.blade.php:214 looks doubly-prefixed but is CORRECT for this app: the `public` disk root = `storage_path('app/public/data')` with url `APP_URL.'/storage/app/public/data'` (config/filesystems.php:41-44) and symlink `public/storage → storage/app/public` (l.100). This exact prefix is used app-wide (shop, theme, avatars, landing, emails). Not a U13 bug. The inconsistency is only that lesson media uses a DIFFERENT (shorter) prefix variant — see F-U13-001.
- **Info-2 (C03-3 cross-ref — security):** ValueManagement store/update accept `svg` in the image mime allow-list (`mimes:...,svg,...`, controller l.56,109); lessons accept svg in `images.*` too. SVG upload = stored-XSS vector = C03-3 (security audit). Noted, not re-discovered. Functional path is fine.
- **Info-3 (shared helpers/layouts — out of scope):** all three view trees `@extends('layouts.admin')` (U-GLOBAL) and call `safe_html()` / `html_excerpt()` helpers (U-GLOBAL). Existence assumed per scope discipline; not audited here.
- Eager-loaded belongsTo chains (`$concept->value->name`, `$lesson->concept->value->icon`) are mostly unguarded in concepts.index/show but FK columns are `required`/cascade-constrained, so an orphan can't reach the view (edge-case only). lessons.index/show already null-guard via `?->` / `@if`.
