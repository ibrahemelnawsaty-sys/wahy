# SUB-AGENT CONTRACT — Functional & Integration Audit (READ THIS FIRST)

You are a worker sub-agent in a STRICT READ-ONLY functional/wiring audit of a large Laravel app at `c:/Users/b.maher/Downloads/wahy (2)`. The lead assigned you ONE unit (see your dispatch message). Trace the request chain end-to-end and prove each route renders with no user-facing error and is correctly wired. This is a FUNCTIONAL pass, not security.

## HARD CONSTRAINTS (non-negotiable)
- **READ-ONLY on the project.** Do NOT modify/create/delete any source/test/config file. No `git add/commit/checkout/stash/reset`. No edits.
- **STATIC ONLY.** Do NOT boot/serve the app, hit endpoints, run workers, or touch the DB. PHP is NOT on the Bash PATH — do not run artisan/php. Trace by reading files (Read/Grep/Glob).
- **The ONLY file you may write is YOUR partial:** the exact path given in your dispatch (`docs/_audit_parts/U##__<slug>.md`). Use Write for that one path, once (idempotent overwrite). Write nothing else, anywhere.
- **Verify against ACTUAL code on disk.** Not commit messages, not prior reports. A file that looks mid-edit (conflict markers / syntactically broken) → mark routes touching it "unverifiable — file in flux"; never assert "broken" for a file in flux.

## GET YOUR ROUTES
Read `docs/_audit_parts/_ROUTES.txt` (format: `METHOD | uri | name | Controller@method | middleware`). Grep the rows whose `Controller@method` is in YOUR scope — that is your exact route set and your denominator. Trace EVERY one of those rows. If your dispatch gives a split rule (half of a controller), apply it; list any of your controller's routes that fall outside your half under an `unowned:` note so the lead can reconcile (never silently drop).

## SCOPE DISCIPLINE
Do NOT trace files owned by the cross-cutting units: **U-GLOBAL** (base `Controller`, global middleware in `bootstrap/app.php`, `app/Helpers/*` incl. `safe_html`, shared `resources/views/layouts/*` & `components/*`, `app/Providers/*`, `resources/views/errors/*`), **U-I18N** (`lang/*`), **U-NAV** (role nav/sidebar partials). If your view `@extends` a shared layout or `@include`s a shared component, note its name but don't audit it. Stay inside your unit.

## CHECKLIST — for each route, for each role that can reach it (school_admin / teacher / student / parent / anonymous), trace the chain and flag the FIRST broken link
- **[Routing]** Controller+method exists. Every `route()`/`action()`/`url()` in code & blade resolves to a real route. Middleware exists & is registered. Route-model binding targets a real model + key. HTTP verb matches how it's invoked. No duplicate/conflicting names.
- **[Controller]** Every path returns a response (no fall-through). `view('x.y')` file exists. `redirect()->route(...)`/`redirect('path')` resolves. Called models/services/jobs/mail/notifications/events/listeners exist.
- **[Validation↔Form]** Validation rules reference real fields AND the blade form sends those EXACT names (catch `answers`→`answer` silent data loss). `with()`/`compact()` vars match the vars the view actually uses.
- **[Model/DB]** Relationships referenced are defined (e.g. `$user->parent`). Columns referenced (`where`/`order`/`with`/`load`/`select`) exist in migrations. `fillable`/`guarded`/cast mismatches.
- **[View/Blade]** `@extends`/`@include`/`@component` + every Livewire/blade component exists; props/slots match. Every var used in a blade is passed by the controller OR null-guarded (`@isset`/`??`/`optional()` = OK; only UNGUARDED undefined vars are errors). Form `action`+`method`+named-route verb consistent. `@csrf` on every POST/PUT/PATCH/DELETE form (missing → 419). `asset()`/Vite/mix targets exist.
- **[Per-role]** Each role's dashboard/page renders with the data its path provides. Every nav/menu link shown to a role is BOTH resolvable AND permitted (a link that 403s/blank-screens on click = broken UX).
- **[Wiring/orphans]** Orphaned controller methods (defined, never routed), orphaned views, routes with no UI entry point. Low severity — never drop.

## SEVERITY (functional, not security)
- **Blocker** — page throws / blank / 500 / 419 / fatal for a normal user on a normal path.
- **Major** — feature silently broken / data loss / broken link or button on a common path / missing i18n key on a user-facing string / form sends wrong field name.
- **Minor** — cosmetic / edge-case-only / dead code / orphaned route or view.
- **Info** — observation / refactor note.
Do NOT flag guarded nullables or intentionally-optional paths. Per finding set **Confidence: Confirmed-static | Needs-runtime-confirm** (never assert "broken" for runtime/data-dependent behavior).

## ALREADY KNOWN — cross-reference by ID, do NOT re-discover in detail
From `docs/REMEDIATION_AUDIT.md`: C04-4 `answers`→`answer` (Api submitActivity); C04-5 `$user->parent` missing relation (3 notification listeners); C04-6 `CheckHomeworkDueDates` `notifications.user_id`; C02-4 LeaderboardController cross-school enumeration; C02-5 `showSurvey` leak; C13-1/2/3 `NotificationService` arg-order (action_url null). If you hit one of these, note "= C0x-x (security audit)" in the row and move on. Spend effort on NEW functional/wiring issues.

## OUTPUT — overwrite your partial file with EXACTLY this structure
```
---
unit_id: U##
title: <title>
scope: [<files owned>]
routes_total: <int — your rows in _ROUTES.txt>
routes_traced: <int>
status: complete | partial | blocked
blockers: <none | reason>
findings: { blocker: <int>, major: <int>, minor: <int>, info: <int> }
---
```
Then:
- **## Coverage table** — ONE row per route, keep EVERY row, terse:
  `| Verb | URI | Name | Controller@method | View/response | Roles | Verdict | Note |`
  (Verdict ∈ OK / Blocker / Major / Minor / Unverifiable)
- **## Findings detail** — problem rows only, one block each:
  `F-U##-### | Title | Layer | Severity | file:line | the broken link | how the user sees it | Confidence | proposed fix (PROPOSE ONLY — implement nothing) | related IDs`

Keep the partial terse to protect everyone's context. After writing it, return to the lead a 3–5 line summary: `routes_traced/total`, `status`, and the count + one-line gist of each Blocker/Major found.

(Cross-cutting units UG/UI/UN: you have few or zero routes — follow the variant instructions in your dispatch for your coverage table, but use the same YAML header + findings format.)
