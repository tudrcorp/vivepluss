# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project

Laravel 12 + Filament 4 admin panel for ViVEplus (an insurance/health-plan affiliation and corporate-quoting platform). Auth/2FA is built on Laravel Fortify, front-end interactivity uses Livewire/Volt, and PDFs (quotes, cards, proposals) are generated with barryvdh/laravel-dompdf. Styling uses Tailwind v4 via Vite.

## Commands

- `composer run setup` — first-time bootstrap: `composer install`, copies `.env.example` to `.env`, `key:generate`, `migrate --force`, `npm install`, `npm run build`.
- `composer run dev` — runs the full local dev stack concurrently: `php artisan serve`, `queue:listen`, `php artisan pail` (logs), and `npm run dev` (Vite).
- `npm run dev` / `npm run build` — Vite only.
- `composer run test` or `./vendor/bin/pest` — run the test suite (Pest). Clears config cache first when run via composer.
- Run a single test: `./vendor/bin/pest tests/Feature/Auth/AuthenticationTest.php` or filter by name with `./vendor/bin/pest --filter=<name>`.
- `vendor/bin/pint` — code style fixer (Laravel Pint, no custom `pint.json`, defaults apply). CI runs this on every push/PR to `main`/`develop`.
- `php artisan migrate` — migrations run against the default DB connection (see "Two database connections" below).

CI (`.github/workflows/tests.yml`, `lint.yml`) runs Pint and the full Pest suite on push/PR to `develop` and `main`.

`livewire/flux` (used in the Fortify auth/settings views under `resources/views/flux/` and `components/auth-*`/`settings/layout.blade.php`) is a paid package hosted on a private Packagist repo — `composer install` will fail without Flux credentials configured (`composer config http-basic.composer.fluxui.dev <username> <license-key>`, as CI does from secrets).

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

Resources can register extra pages beyond the default index/create/edit trio in `getPages()` — e.g. `AffiliationResource`'s `affiliates` route (`Pages/ManageAffiliates.php`, a `ManageRelatedRecords` page) manages an affiliation's family members, and `AffiliationCorporateResource`'s analogous `Pages/ManageAffiliateCorporates.php` manages a corporate affiliation's employees. Standalone pages that aren't tied to a resource (e.g. `App\Filament\Pages\EstructuraViveplus`, an org-hierarchy viewer) set `$shouldRegisterNavigation = false` and are instead linked from `userMenuItems()` in the panel provider, gated by `Auth::user()->is_whiteCompanyAdmin` / `agency_type === 'MASTER'`.

List pages override `getHeader(): ?View` to return a `resources/views/filament/resources/<resource>/list-header.blade.php` partial (icon + title + description) in place of Filament's default header — follow this convention for new list pages rather than relying on `$title` alone.

### Domain model shape

Core business entities and how they relate:
- **Agencies/Agents** (`Agency`, `Agent`, `AgencyType`, `AgentType`) — the sales org structure; agencies can be "MASTER" type. Agent/agency creation flows are exposed as public Volt routes (`/agency/c/{code?}`, `/agent/c/{code?}`, `/m/o/c/{code?}` in `routes/web.php`), separate from the Filament panel.
- **Quotes**: `IndividualQuote` and `CorporateQuote` (plus `*QuoteRequest`, `Detail*Quote`, `*QuoteObservation`, `StatusLog*Quote` for history/audit trails). Corporate quotes have plan tiers (Inicial/Ideal/Especial) — see `App\Actions\Contraportada*`/`Planes*`/`Portada*` classes used to build PDF proposal documents, and `App\Jobs\SendEmailPropuestaEconomica*` jobs that render and email those proposals per plan tier.
- **Affiliations**: `Affiliation`/`AffiliationCorporate` plus supporting `*Observation`, `*Document`, `StatusLogAffiliation*` models — the enrollment records once a quote converts.
- **Plan catalog** (on the `mysql_vivepluss` connection): `Plan`, `Benefit`, `BenefitPlan`, `Coverage`, `Fee`, `AgeRange`, `Zone`.
- **Configuration** — single-row(ish) tenant/branding config (colors, logo, currency symbol, social URLs) read directly by the panel provider and views; look here before hardcoding branding values.

### White-label scoping (`white_company_id`)

Several `mysql`-connection tables (`sales`, `collections`, `paid_memberships`, `agencies`, `agents`, `individual_quotes`, `corporate_quotes`, plus the `affiliations`/`affiliation_corporates` queried by the dashboard widgets) carry a `white_company_id` column that ties a row to a tenant/brand row in the `mysql_vivepluss` `configurations` table. `Configuration::currentWhiteCompanyId()` resolves the active scope from the authenticated user's `white_company_id`, falling back to the first configured tenant. Use this helper (rather than hardcoding a tenant id) whenever a query, widget, or PDF needs to filter or brand by the current white-label company — it's the pattern already used by `VentasChart`/`VentasPorPlanChart`/`StatsOverview` and by the quote PDF builders below. `App\Models\WhiteCompany` (no explicit `$connection`, so it lives on `mysql` — a *different* connection than `Configuration` despite sharing the same `white_company_id` value) holds per-tenant billing info such as `assigned_credit`, read by `StatsOverview::getAssignedCredit()`.

### PDF/document generation

Controllers (`IndividualQuoteController`, `CorporateQuoteController`, `AffiliationController`, `PdfController`) build PDFs directly with `Barryvdh\DomPDF\Facade\Pdf::loadView(...)` against Blade views in `resources/views/documents/` (e.g. `propuesta-economica.blade.php`, `propuesta-economica-cor.blade.php`, `propuesta-economica-multiple.blade.php`, `certificate.blade.php`). Small `App\Livewire\{Portada,Contraportada,PlanesCotizacion}*` components render the cover-page/plan-tier partials embedded inside those documents. Generated quote PDFs are saved under `public/storage/quotes` and then emailed via `App\Mail\SendMail*` mailables or dispatched through `App\Jobs\SendEmailPropuestaEconomica{Inicial,Ideal,Especial}Cor` (one per corporate plan tier) / `SendEmailPropuestaEconomicaMultiple` jobs.

Individual quotes go through `App\Services\IndividualQuotePdfBuilder` instead of a direct `Pdf::loadView()` call: if the current white-label `Configuration` has `quote_cover_individual`/`quote_back_cover_individual` template files set, it merges those single-page PDFs with a dompdf-rendered calculations page via `setasign/fpdi`, matching the generated page size (converted mm→pt) to the uploaded templates. It falls back to the full `documents.propuesta-economica` dompdf render if no templates are configured or the FPDI merge throws. Apply this same builder-with-fallback shape if corporate quotes need per-tenant cover templates later.

### Affiliation payment/activation flow

`AffiliationController::uploadPayment` (and its bulk counterpart `uploadPaymentMultipleAffiliations`) is the entry point when an analyst uploads a payment proof for an affiliation; it creates a `PaidMembership` row per method/frequency combination but only auto-approves/activates for `payment_method === 'CREDITO'` — every other method leaves the proof `PENDIENTE` with the affiliation untouched, since validating the proof, approving it, generating the next due dates, and activating the affiliation is done by Integracorp's own analysts outside ViVEplus, not by the ViVEplus analyst who uploaded it. `approveAndActivate()` (called only for `CREDITO`) marks the proof `APROBADO` and activates the affiliation immediately (`status = 'ACTIVA'`, `activated_at`/`effective_date` set), then calls `createUpcomingCollections()` to pre-generate the rest of the annual cycle's pending `collections` rows (per `payment_frequency`: MENSUAL → 11 more, TRIMESTRAL → 3, SEMESTRAL/ANUAL → 1) and emails `AffiliationAutoActivatedMail` to `config('parametros.ACTIVATION_NOTIFICATION_EMAILS[_DEV]')`. A failed notification email is logged but never rolls back the activation. `App\Http\Controllers\PaidMembershipController::approvePayment`/`PaidMembershipCorporateController::approvePayment` implement an older manual-approval flow (status update, `Sale`/`Collection` creation, `SendAvisoDePago`/`CreateAvisoDeCobro` jobs) but aren't wired to any route or Filament action — dead code from before the CREDITO auto-approval was added, not the mechanism Integracorp uses today. Payment-proof file fields (`document_usd`, `document_ves`, `Configuration::brandLogo`) are inconsistently stored across the `local`/`public` disks depending on how each `FileUpload` component was declared — when resolving one of these to an absolute path, check both disks (see `resolveLogoPath`/`resolvePaymentDocumentPaths`) rather than assuming one.

### White-label credit ledger (`CreditReconciliation`)

The `CREDITO` payment method on `AffiliationController::uploadPayment` lets an affiliation be paid against the white-label company's line of credit instead of a real proof of payment: it generates a credit-note PDF (`documents/nota-credito.blade.php`, via `generateCreditNote()`), stores it as the `PaidMembership`'s `document_ves` with `status = 'APROBADO'` immediately, and records the movement with `recordCreditMovement()` as a row in `credit_reconciliations` (`mysql` connection, model `App\Models\CreditReconciliation`). `CreditReconciliation::remainingCredit($whiteCompanyId)` computes `WhiteCompany::assigned_credit` minus the sum of `total_to_pay` across that tenant's ledger rows — this is the single source of truth for "how much credit does this white-label company have left," checked before allowing a `CREDITO` payment (`AffiliationsTable.php`) and surfaced on `StatsOverview`. There's no FK between `paid_memberships`/`collections` and `credit_reconciliations`; rows link back via `collection_invoice_number`/`paid_membership_id`.

### Sale/commission registration on Integracorp's shared tables

ViVEplus's default `mysql` connection and Integracorp's own app point at the *same physical database* (`operaciones`) — `sales`, `commissions`, `white_companies`, `white_company_fees`, and Integracorp's own plan/coverage/fee/age-range catalog all already live there alongside `affiliations`/`paid_memberships`. On the first `CREDITO` payment of an affiliation (inside `AffiliationController::approveAndActivate()`, gated the same way as `createUpcomingCollections()` so it only runs once per affiliation, not on every subsequent installment), `registerSaleAndCommission()` writes a `Sale` and a `Commission` row exactly like Integracorp's own `PaidMembershipController::approvePayment()` does for an allied/white-label company — ViVEplus is always the allied party from Integracorp's point of view, so only that code path is ported (not the agent/sub-agent/agency commission tree, which is Integracorp's own direct-sales channel).

The amount used for `Sale.total_amount` and for the commission is **not** the analyst-entered `total_amount` (that still drives `PaidMembership`/`Collection`/`CreditReconciliation` unchanged) — it's the negotiated *neta* resolved by `App\Support\WhiteCompanies\WhiteCompanyNegotiatedRateResolver::settlementForAffiliation()`, which sums per-person (titular and/or each `Affiliate`) `sale_price`/`neta` from `WhiteCompanyFee` (keyed by `white_company_id` + `fee_id`) after resolving the matching `fee_id` by plan+coverage+age against **Integracorp's own fee catalog** (`App\Models\IntegracorpFee`/`IntegracorpAgeRange`, table names `fees`/`age_ranges` — deliberately separate model classes from ViVEplus's own `mysql_vivepluss`-connection `Fee`/`AgeRange`, since `white_company_fees.fee_id` references Integracorp's catalog, not ViVEplus's, even though the two catalogs happen to mirror IDs for the plans ViVEplus sells). The resolved settlement is snapshotted onto `Affiliation.white_company_sale_price`/`white_company_neta`/`white_company_fee_id` the first time (these columns already exist on the shared `affiliations` table from Integracorp's side) so later installments reuse the same figures instead of recomputing. `WhiteCompanyPaymentSettlement::installmentNeta()`/`installmentMasterCommission()` divide the annual neta/margin (`sale_price - neta`) by the payment-frequency period count for the per-installment `Sale`/`Commission` amounts — all of the commission goes to `commission_agency_master_usd` (Integracorp's cut), with the agent/sub-agent/agency-general fields left at zero.

If no `WhiteCompanyFee` row matches a person's resolved fee, `WhiteCompanyNegotiatedRateResolver` throws `App\Exceptions\WhiteCompanyNegotiatedRateMissingException` **before any write happens** (it runs at the very top of the `CREDITO` branch in `uploadPayment()`, ahead of the credit note/`PaidMembership`/`CreditReconciliation`), so a missing rate blocks the whole approval cleanly instead of leaving it half-done. That exception (like any other now) propagates out of `uploadPayment()` — its catch block used to `dd()` on `\Throwable` before logging/notifying, which silently swallowed every exception behind a debug dump; that `dd()` was removed so failures reach the existing `Log::error()` + `Notification::make()->danger()` and actually show the analyst a clear message.

### Integracorp document webhook (certificates/carnets)

ViVEplus no longer generates the affiliation certificate (`certificado`) or member card (`carnet`) itself — Integracorp generates them externally when an operator runs "Regenerar documentos" and pushes the resulting PDF to `POST /api/documents/webhook` (`routes/api.php`, registered via `withRouting(api: ...)` in `bootstrap/app.php` — Laravel 12 has no `api.php` by default, so this is the only API route file in the app; `App\Http\Controllers\Api\AffiliationDocumentWebhookController`). The route is protected by `App\Http\Middleware\VerifyIntegracorpDocumentWebhook`: a fixed Bearer token (`parametros.INTEGRACORP_WEBHOOK_TOKEN`) plus an HMAC-SHA256 signature (`parametros.INTEGRACORP_WEBHOOK_SECRET`) over a canonical string of the payload fields (not the raw multipart body). The controller is idempotent on `idempotency_key` and rejects stale deliveries by comparing `generated_at`, writes the file atomically (temp file + `Storage::move`) under `documentos-integracorp/{certificados,carnets}` on the `public` disk, and upserts an `App\Models\AffiliationDocument` row (table `affiliation_integracorp_documents`) keyed on `affiliation_code` + `document_type` + `affiliate_identification` (blank for `certificado`, since it's one per affiliation; populated for `carnet`, since it's one per family member/employee). `AffiliationDocument::latestFor()` is how the panel resolves the current file to offer for download. The `documents:check-missing` Artisan command (scheduled hourly in `routes/console.php`) logs affiliations/employees whose certificate or carnet hasn't arrived within `parametros.DOCUMENT_SYNC_ALERT_HOURS` (default 48h) of the affiliation being created.

After storing each document, `notifyAnalysts()` fires one notification per document received (it doesn't wait for the certificate and all carnets of an affiliation to arrive) — gated per white-label by `Configuration::document_notifications_enabled`, fanning out to `document_notification_emails` (via `AffiliationDocumentAvailableMail`) and `document_notification_phones` (via the `SendAffiliationDocumentWhatsApp` job). That job posts to the ultramsg.com API using the same curl pattern as `MiddlewareController::notificacionSesionDuplicada` — the only WhatsApp send in this codebase that actually works; other flows reference a job class from `NotificationController` that doesn't exist. Notification failures are caught and logged inside `notifyAnalysts()` so they never affect the 201 already returned to Integracorp.

### Auth

Fortify-based auth with two-factor support (`app/Actions/Fortify`), plus a custom `DuplicatedSession` middleware applied to the Filament panel to prevent concurrent sessions. Logout redirects to an external URL (`config('parametros.REDIRECT_LOGOUT_EXTERNAL_URL')`) via the `/external` route, which also invalidates the Filament guard/session.

### Dashboard widgets

`app/Filament/Widgets/` holds the panel dashboard: `StatsOverview` (a plain `Widget`, not `StatsOverviewWidget`, rendering its own Blade view at `resources/views/filament/widgets/stats-overview.blade.php`) plus two `ChartWidget` subclasses — `VentasChart` (monthly individual-vs-corporate sales totals) and `VentasPorPlanChart` (monthly sales broken out per plan) — that query `affiliations`/`affiliation_corporates` via `DB::` raw queries, scoped by `Configuration::currentWhiteCompanyId()`, with chart currency formatting from `Configuration::currencySymbol()`. `StatsOverview` builds its own per-metric sparklines (`buildSparkline()`, hand-rolled SVG path + per-point hover coordinates, no charting library) and month-over-month deltas (`monthOverMonth()`/`changeSummary()`) for agencies/agents/quotes counts, all scoped by `white_company_id`. Widgets are auto-discovered by the panel provider like resources/pages.
