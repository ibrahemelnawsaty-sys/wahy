---
unit_id: UI
title: UI — U-I18N (cross-cutting, localization key resolution)
scope: [lang/ar/*, lang/en/*, lang/ar.json, lang/vendor/backup/*, config/app.php (read-only), app/Providers/AppServiceProvider.php (locale boot, read-only), app/Http/Middleware/SetArabicLocale.php (read-only)]
routes_total: 0
routes_traced: 0
status: partial
blockers: Cross-cutting unit (no routes). Fully checked (key-by-key) the only 4 locale file groups that exist (auth, passwords, validation, pagination) for ar+en parity, plus lang/ar.json. Coverage of *usage* is COMPLETE for dotted/file keys (exhaustive repo grep — only vendor pagination uses them) but only SAMPLED for JSON-string keys (the pagination set). NOT done: no per-string sweep of hardcoded Arabic literals in blades (out of i18n scope — app is hardcoded-Arabic, not key-driven), and no audit of lang/vendor/backup/* (spatie/laravel-backup vendor strings, not user-facing in app flows).
findings: { blocker: 0, major: 0, minor: 1, info: 2 }
---

## Key facts (runtime locale model)
- `config/app.php`: `locale = ar`, `fallback_locale = ar`, `faker_locale = ar_SA`. **Default AND fallback are both Arabic.**
- Locale is FORCE-PINNED to `ar` at runtime in TWO places: `app/Providers/AppServiceProvider.php:34` (`App::setLocale('ar')`) and `app/Http/Middleware/SetArabicLocale.php:15`. No locale switcher, no path that activates `en`. There is no `en.json`.
- Consequence: the live locale is ALWAYS `ar`. A missing key in `ar` would render the raw key (no further fallback). A missing key in `en` is moot — `en` is effectively dead at runtime.
- The app does NOT use Laravel translation for its own UI: `Grep` for `__(`, `@lang(`, `trans(`, `trans_choice(`, `Lang::get`, `->trans` across **all of `app/`** = ZERO matches; across `resources/` the ONLY matches are the 8 Laravel **vendor pagination** templates. App blades use hardcoded Arabic literals, not keys — so there is no key-resolution surface to break for custom UI strings.

## Coverage table
`Group/File | ar? | en? | keys-sampled | resolved? | Verdict`
| Group/File | ar? | en? | keys-sampled | resolved? | Verdict |
|---|---|---|---|---|---|
| auth.php | yes | yes | failed, password, throttle (3/3, full) | yes | OK — full parity |
| passwords.php | yes | yes | reset, sent, throttled, token, user (5/5, full) | yes | OK — full parity |
| pagination.php | yes | yes | previous, next (2/2, full) — only keys actually used (`pagination.previous/next` in vendor templates) | yes | OK — full parity, keys in use resolve |
| validation.php | yes | yes | ALL ~70 rules + size/min/max sub-arrays + password.* + custom + attributes (full file diff vs en) | yes | OK — ar fully translated, no English framework-message leak |
| ar.json (JSON string-keys) | yes (ar only) | n/a (no en.json) | Showing, to, of, results, Pagination Navigation, Previous, Next (7/7) — covers all `__('<literal>')` string-keys used (pagination vendor templates) | yes | OK for used keys; 2 entries dead (see F-UI-001) |
| lang/vendor/backup/<31 locales>/notifications.php | yes (incl ar,en) | yes | not key-checked | n/a | Info — spatie/laravel-backup vendor strings, admin/CLI backup mails only, not user app flow |

## Findings detail
F-UI-001 | Dead/mismatched JSON keys `Previous`/`Next` in lang/ar.json | i18n (lang/ar.json:7-8) | Minor | lang/ar.json:7 (`"Previous"`), :8 (`"Next"`) | Capitalized string-keys `Previous`/`Next` are not referenced by any template — every pagination view calls the FILE key `__('pagination.previous')`/`__('pagination.next')` (resolved from lang/ar/pagination.php), never the bare-string `__('Previous')`. | No user impact — these two JSON entries are simply never read; the visible "previous/next" text comes from pagination.php and renders correctly in Arabic. | Confirmed-static | PROPOSE: remove the two unused keys from ar.json (cosmetic cleanup only) or leave as-is. | none

F-UI-002 (Info) | App is not i18n-key-driven; locale hard-pinned to ar | i18n | Info | config/app.php:81-83; AppServiceProvider.php:34; SetArabicLocale.php:15 | n/a | n/a | Confirmed-static | OBSERVATION ONLY: there is essentially no missing-translation-key attack surface for user-facing strings, because no app blade/controller uses `__()`/`@lang`/`trans` for custom keys and the locale is fixed to `ar` (which is also the fallback). The complete English locale files (auth/passwords/validation/pagination) are currently unreachable at runtime — switching APP_LOCALE/APP_FALLBACK_LOCALE to `en` would be safe for these 4 framework groups but app UI would stay Arabic (hardcoded). | none

F-UI-003 (Info) | en has no JSON catalog (no lang/en.json) | i18n | Info | lang/ (only ar.json present) | n/a | n/a | Confirmed-static | OBSERVATION: If the app were ever switched to render under `en` locale with `fallback_locale=en`, the 7 string-keys used by vendor pagination (Showing/to/of/results/Pagination Navigation/Previous/Next) would have no `en.json` and Laravel would echo the raw English-ish key text — which for these happens to read acceptably (e.g. "Showing", "Next"). Not a current break (fallback is `ar`, and `ar.json` resolves them). | F-UI-002

## Parity / validation-completeness verdict
- ar↔en parity: **PASS** for all 4 existing file groups (auth, passwords, validation, pagination). No group exists in one locale but not the other.
- `lang/ar/validation.php` completeness: **COMPLETE** — every rule present in `en/validation.php` is present and Arabic-translated in `ar/validation.php` (incl. between/gt/gte/lt/lte/max/min/size sub-arrays and password.*). `ar` additionally has a populated `custom` + `attributes` block (en's are empty stubs). => No English framework validation messages leak to Arabic users.
- Missing-key risk for live (ar) users: **NONE found.** Every translation call in the codebase (`pagination.previous/next` + the 7 ar.json string-keys) resolves in `ar`.
