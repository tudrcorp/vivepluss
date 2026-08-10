# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel 12 + Filament 4 admin panel for ViVEplus (an insurance/health-plan affiliation and corporate-quoting platform). Auth/2FA is built on Laravel Fortify, front-end interactivity uses Livewire/Volt, and PDFs (quotes, cards, proposals) are generated with barryvdh/laravel-dompdf. Styling uses Tailwind v4 via Vite.

## Commands

- `composer run dev` — runs the full local dev stack concurrently: `php artisan serve`, `queue:listen`, `php artisan pail` (logs), and `npm run dev` (Vite).
- `npm run dev` / `npm run build` — Vite only.
- `composer run test` or `./vendor/bin/pest` — run the test suite (Pest). Clears config cache first when run via composer.
- Run a single test: `./vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php` or filter by name with `./vendor/bin/pest --filter=<name>`.
- `vendor/bin/pint` — code style fixer (Laravel Pint, no custom `pint.json`, defaults apply). CI runs this on every push/PR to `main`/`develop`.
- `php artisan migrate` — migrations run against the default DB connection (see "Two database connections" below).

CI (`.github/workflows/tests.yml`, `lint.yml`) runs Pint and the full Pest suite on push/PR to `develop` and `main`.

## Architecture

### Two database connections

This app talks to **two separate MySQL databases** via two named connections in `config/database.php`:

- `mysql` (default, env `DB_*`) — the legacy/main schema (agencies, agents, affiliations, quotes, users, etc.). Most models use this implicitly.
- `mysql_vivepluss` (env `VIVEPLUS_DB_*`) — a newer, separate schema. Models that belong to it set `protected $connection = 'mysql_vivepluss';` explicitly (e.g. `Configuration`, `Zone`, `DownloadZone`, `Plan`, `Benefit`, `BenefitPlan`, `Coverage`, `Fee`, `AgeRange`).

When adding a model or writing a query/migration, check which connection the related tables live on — mixing them in one query (e.g. a join) will not work since they're different physical databases.

### Filament resource layout (v4 style)

Admin UI lives under `app/Filament/Resources/<Name>/` using Filament 4's split-file convention rather than single-class resources:

```
Resources/<Name>/
  <Name>Resource.php          # resource definition, navigation, relations registration
  Pages/                      # List/Create/Edit page classes
  Schemas/<Name>Form.php      # form schema (create/edit)
  Tables/<Name>Table.php      # table columns/filters/actions
  RelationManagers/           # nested relation managers, when present
```

Follow this same split when adding a new resource rather than inlining `form()`/`table()` on the resource class. The single Filament panel is registered in `app/Providers/Filament/ViveadminPanelProvider.php` (id `viveadmin`, path `/viveadmin`), which auto-discovers resources/pages/widgets from their respective directories and defines panel-wide config (theme colors pulled from the `Configuration` model, navigation groups, user menu actions, etc.).

### Domain model shape

Core business entities and how they relate:
- **Agencies/Agents** (`Agency`, `Agent`, `AgencyType`, `AgentType`) — the sales org structure; agencies can be "MASTER" type. Agent/agency creation flows are exposed as public Volt routes (`/agency/c/{code?}`, `/agent/c/{code?}`, `/m/o/c/{code?}` in `routes/web.php`), separate from the Filament panel.
- **Quotes**: `IndividualQuote` and `CorporateQuote` (plus `*QuoteRequest`, `Detail*Quote`, `*QuoteObservation`, `StatusLog*Quote` for history/audit trails). Corporate quotes have plan tiers (Inicial/Ideal/Especial) — see `App\Actions\Contraportada*`/`Planes*`/`Portada*` classes used to build PDF proposal documents, and `App\Jobs\SendEmailPropuestaEconomica*` jobs that render and email those proposals per plan tier.
- **Affiliations**: `Affiliation`/`AffiliationCorporate` plus supporting `*Observation`, `*Document`, `StatusLogAffiliation*` models — the enrollment records once a quote converts.
- **Plan catalog** (on the `mysql_vivepluss` connection): `Plan`, `Benefit`, `BenefitPlan`, `Coverage`, `Fee`, `AgeRange`, `Zone`.
- **Configuration** — single-row(ish) tenant/branding config (colors, logo, currency symbol, social URLs) read directly by the panel provider and views; look here before hardcoding branding values.

### PDF/document generation

`app/Actions/*` classes build Blade views for printable documents (quote cover pages, benefit tables, pathology history, etc.), rendered via `barryvdh/laravel-dompdf` from `resources/views/documents/`. Quote PDFs are saved to `public/storage/quotes` and then emailed via `App\Mail\*` mailables or dispatched through `App\Jobs\Send*` jobs.

### Auth

Fortify-based auth with two-factor support (`app/Actions/Fortify`), plus a custom `DuplicatedSession` middleware applied to the Filament panel to prevent concurrent sessions. Logout redirects to an external URL (`config('parametros.REDIRECT_LOGOUT_EXTERNAL_URL')`) via the `/external` route, which also invalidates the Filament guard/session.

### Dashboard widgets

`app/Filament/Widgets/` holds the panel dashboard: `StatsOverview` (a plain `Widget`, not `StatsOverviewWidget`, rendering its own Blade view at `resources/views/filament/widgets/stats-overview.blade.php`) plus several `ChartWidget` subclasses (`VentasVsPlanChart`, `VentasVsPlanCorpChart`, `VentasAgenciasChart`, `VentasAgentesChart`) that query sales/quote data via `DB::` and `Configuration` for chart colors. Widgets are auto-discovered by the panel provider like resources/pages.
