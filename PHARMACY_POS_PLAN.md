# Pharmacy POS System — Full Project Plan

> **Purpose of this document:** This is the complete spec for building a multi-tenant Pharmacy
> POS SaaS, from a blank repo to deployment. Feed this whole file to Cursor as project context
> (e.g. drop it in the repo root as `PROJECT_PLAN.md` or paste into `.cursor/rules`) so it
> understands the architecture, schema, and conventions before generating any code. Work through
> the phases in order — don't skip ahead to UI work before the data layer and tenant-scoping
> foundation are solid.

---

## 1. Project Overview

A multi-tenant Point-of-Sale system for pharmacies. Each tenant (pharmacy business) signs up
self-serve, can operate one or more branches, and gets full inventory, sales, and reporting
tools tailored to pharmacy needs (batch/expiry tracking, unit conversions, optional prescription
tracking).

**Primary users per tenant:**
- **Owner** — full access, manages branches, users, settings, sees all reports
- **Manager** — manages inventory, purchasing, staff at their branch(es)
- **Pharmacist** — POS access + can flag/verify prescription items
- **Cashier** — POS access only, restricted to their assigned branch

---

## 2. Tech Stack

| Layer | Choice |
|---|---|
| Backend framework | Laravel 13 |
| Database | PostgreSQL |
| Frontend reactivity | Livewire + Alpine.js (fits Blade-based Tyro dashboard template) |
| Admin UI kit | Tyro dashboard (Blade/Bootstrap-based) |
| Auth | **Tyro dashboard's built-in auth scaffolding** (login/register/password-reset views and controllers that ship with the template) — extended with `tenant_id`, `branch_id`, `role` rather than replaced with Breeze/Fortify |
| Permissions | **Tyro dashboard's built-in role/permission system** if it ships with one (most Laravel admin templates like Tyro are built on `spatie/laravel-permission` under the hood — check the template's docs/vendor files first). If Tyro does not include one, install `spatie/laravel-permission` and wire it into the same role structure Tyro's UI expects, rather than inventing a separate custom system |
| PDF/Excel export | barryvdh/laravel-dompdf, maatwebsite/excel |
| Queue | Laravel queues (database or Redis driver) for notifications/reports |
| Hosting target | **Hostinger VPS** managed via **Coolify** (self-hosted PaaS), PostgreSQL on the same VPS or a managed add-on |
| Hardware (v1) | **None required** — no barcode scanner, receipt printer, or cash drawer integration; browser-based POS only |

> **Note:** exact Tyro internals (which auth package it wraps, whether it ships Spatie
> permissions or a custom roles table) should be confirmed by inspecting the template's own
> files/docs once installed — Phase 1 includes a task to do exactly that before building on top
> of it, so assumptions here don't get baked into code incorrectly.

---

## 3. Architecture Decisions

These decisions shape the schema and code structure throughout the whole plan — treat them as
fixed unless there's a specific reason to revisit one.

1. **Multi-tenancy strategy: single database, `tenant_id` column on every business table**,
   enforced automatically via a global Eloquent scope (`App\Models\Scopes\TenantScope` +
   `App\Models\Concerns\BelongsToTenant`). Not database-per-tenant. Chosen for build speed and
   simpler hosting; revisit only if compliance requirements demand hard data isolation later.
2. **Tenant provisioning: self-serve signup.** Registration must atomically create a `Tenant`, a
   default main `Branch`, and an owner `User` in a single DB transaction — built on top of
   whatever registration flow Tyro's auth scaffolding provides.
3. **Multi-branch supported per tenant** from day one (schema supports it even if a given tenant
   only uses one branch).
4. **Prescriptions: lightweight, not a full module.** No `prescriptions` or `doctors` tables.
   Instead, `sales` has `prescription_required`, `prescriber_name`, `prescriber_reg_no`, and
   `sale_items` has `is_prescription_item` — enough for compliance/traceability without the
   overhead of a full prescription workflow.
5. **Stock deduction strategy: FEFO (First-Expire-First-Out)**, not FIFO. Batches are always
   consumed soonest-expiry-first at checkout, overridable by staff if needed.
6. **Unit conversions**: every product has a `base_unit` (e.g. "tablet"). Optional `product_units`
   rows define sellable packagings (e.g. "strip" = 10 tablets, "box" = 100 tablets) each with their
   own optional barcode (manual entry only in v1) and price override. All stock is stored internally
   in base_unit quantities.
7. **Concurrency safety**: all stock deduction at checkout MUST use `lockForUpdate()` inside a DB
   transaction to prevent overselling from simultaneous sales. The `stock.quantity` column also has
   a PostgreSQL `CHECK (quantity >= 0)` constraint as a last-resort safety net.
8. **Roles/permissions live in Tyro's system, not a bespoke one.** Whatever role/permission
   mechanism Tyro dashboard ships with (Spatie or custom) is extended with pharmacy-specific roles
   (owner/manager/pharmacist/cashier) rather than building a second, parallel permissions system.
9. **No dedicated POS hardware in v1.** There is no barcode scanner, thermal receipt printer, or
   cash drawer. Product lookup is keyboard/search-driven; receipts use the browser's print dialog
   (`window.print()` on a print-friendly Blade view). Optional `barcode` fields on products/units
   remain as **data identifiers** (typed, pasted, or scanned later if hardware is added) — not as
   a hardware integration requirement.

---

## 4. Database Schema

### tenants
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| name | string | Pharmacy business name |
| slug | string, unique | |
| email | string, unique | |
| phone | string, nullable | |
| address | text, nullable | |
| subscription_plan | string, default 'trial' | |
| trial_ends_at | timestamp, nullable | |
| is_active | boolean, default true | |

### branches
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK → tenants, cascade delete | |
| name | string | |
| code | string | unique per tenant |
| address | text, nullable | |
| phone | string, nullable | |
| is_main | boolean, default false | |
| is_active | boolean, default true | |

### users (extended from Tyro's default users table)
Adds: `tenant_id` (FK, nullable at DB level), `branch_id` (FK, nullable — null means tenant-wide
access), `role` (string or FK into Tyro's roles table — see Phase 1 investigation task),
`is_active` (boolean).

### categories
`id, tenant_id, parent_id (self-ref, nullable), name, timestamps` — unique on (tenant_id, name).

### manufacturers
`id, tenant_id, name, timestamps` — unique on (tenant_id, name).

### products
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| tenant_id | FK | |
| category_id | FK, nullable | |
| manufacturer_id | FK, nullable | |
| name | string | |
| generic_name | string, nullable | |
| barcode | string, nullable | unique per tenant (partial index, only when not null); optional identifier — typed/pasted at POS, not scanned in v1 |
| sku | string, nullable | |
| base_unit | string, default 'unit' | e.g. "tablet", "ml" |
| reorder_level | unsigned int, default 0 | triggers low-stock alert |
| requires_prescription | boolean, default false | |
| is_active | boolean, default true | |

### product_units
`id, product_id (FK), unit_name, conversion_factor (uint, = how many base_units), barcode
(nullable), selling_price (nullable override), is_default (bool), timestamps`

### batches
`id, tenant_id, product_id (FK), batch_no, expiry_date, cost_price, selling_price, received_at,
timestamps` — indexed on (product_id, expiry_date) for fast FEFO lookups.

### stock
`id, tenant_id, branch_id (FK), product_id (FK), batch_id (FK), quantity (int, default 0, stored
in base_unit), timestamps` — unique on (branch_id, batch_id), CHECK (quantity >= 0).

### customers
`id, tenant_id, name (nullable — allows anonymous walk-in), phone, email, address, timestamps`

### sales
| Column | Type | Notes |
|---|---|---|
| id | bigint PK | |
| tenant_id, branch_id, user_id (cashier), customer_id (nullable) | FKs | |
| invoice_no | string | unique per tenant |
| subtotal, discount_amount, tax_amount, total, paid_amount, change_amount | decimal(12,2) | |
| status | string | completed / held / refunded / partially_refunded |
| prescription_required | boolean | |
| prescriber_name, prescriber_reg_no | string, nullable | |
| sold_at | timestamp, nullable | |

### sale_items
`id, sale_id (FK), product_id (FK), batch_id (FK), product_unit_id (nullable FK), quantity (in
unit sold), quantity_base (converted to base_unit — used for stock deduction), unit_price,
discount_amount, tax_amount, line_total, is_prescription_item, timestamps`

### sale_payments
`id, sale_id (FK), method (cash/card/mobile/other), amount, reference (nullable), paid_at,
timestamps` — supports split payments.

### Additional tables to design when reaching those phases
- `suppliers`, `purchase_orders`, `purchase_order_items`, `goods_received_notes`
- `stock_adjustments` (damages, expiry write-offs, physical count corrections)
- `stock_transfers` (inter-branch transfers)
- `taxes`, `discounts` (if going beyond flat fields on sales)
- `expenses`
- `audit_logs` (who changed what, when — important for pharmacy compliance)
- `registers`/`shifts` (till open/close, cash reconciliation)

---

## 5. Folder Structure Conventions

Follow the **standard Laravel 13 application layout** (same slim structure introduced in Laravel 11).
Do not add legacy folders (`app/Http/Kernel.php`, `app/Console/Kernel.php`, `routes/api.php` unless
an API is needed). Register middleware in `bootstrap/app.php`; schedule tasks in `routes/console.php`.

```
app/
  Actions/                    # Optional: single-purpose invokable classes for discrete operations
    Sales/
      CompleteSaleAction.php
  Enums/                      # Backed enums for status/method fields (e.g. SaleStatus, PaymentMethod)
  Http/
    Controllers/              # Thin controllers; delegate to Services/Actions
      Auth/
        RegisterController.php  # extends Tyro's auth controller; atomic Tenant+Branch+User creation
    Middleware/               # Custom middleware (tenant context, branch context, role gates)
    Requests/                 # Form Request validation, one per major write action
  Livewire/                   # Livewire 3 default namespace (NOT app/Http/Livewire/)
    Pos/
      SaleScreen.php
      ProductSearch.php
    Inventory/
      ProductForm.php
      BatchIntake.php
  Models/
    Concerns/
      BelongsToTenant.php     # Trait for tenant-scoped models
    Scopes/
      TenantScope.php         # Global scope applied by BelongsToTenant
    Product.php
    Sale.php
    ...
  Policies/                   # Authorization policies, registered in AppServiceProvider or auto-discovered
  Providers/
    AppServiceProvider.php
  Services/                   # Multi-step domain logic shared across controllers/Livewire/jobs
    StockDeductionService.php
    InvoiceNumberService.php
    UnitConversionService.php
bootstrap/
  app.php                     # Middleware, exception handling, routing bootstrap
  providers.php
config/
database/
  factories/
  migrations/
  seeders/
resources/
  css/
  js/
  views/
    components/               # Blade components (including Tyro integration)
    layouts/
    livewire/                 # Livewire component views (mirrors app/Livewire/ structure)
    sales/
      receipt.blade.php       # Print-friendly receipt (browser print, no thermal CSS)
routes/
  web.php
  console.php                 # Scheduled commands
tests/
  Feature/
    CheckoutTest.php
    StockDeductionTest.php
    TenantIsolationTest.php   # critical: verify tenant A can never see tenant B's data
  Unit/
```

**Naming & placement rules:**
- Tenant-scoped Eloquent models live in `app/Models/` and use `BelongsToTenant` from `app/Models/Concerns/`.
- Livewire classes in `app/Livewire/{Domain}/`; matching Blade views in `resources/views/livewire/{domain}/`.
- Validation in `app/Http/Requests/`; authorization in `app/Policies/` or Tyro's permission gates.
- No business logic in controllers or Livewire components beyond wiring — use `app/Services/` or `app/Actions/`.
- Jobs (when added) go in `app/Jobs/`; queued listeners in `app/Listeners/`.

---

## 6. Development Phases & Tasks

### Phase 0 — Discovery & Requirements
- [x] Confirm scope with the pharmacy owner: which modules are needed for v1 (POS, inventory,
      purchasing, reporting) vs later — see [`docs/PHASE_0_DISCOVERY.md`](docs/PHASE_0_DISCOVERY.md)
- [x] Confirm **software-only POS constraints**: no barcode scanner, receipt printer, or cash drawer
      in v1 — staff will search products by name/SKU/generic name and print receipts via the browser
- [x] Confirm local legal/compliance requirements: controlled substance tracking, required
      receipt fields, batch/expiry disclosure rules — baseline documented in Phase 0 discovery
- [x] Defer hardware integration (USB/Bluetooth scanners, ESC/POS thermal printers) to a later
      phase if needed; keep optional `barcode` columns in the schema for manual entry only

### Phase 1 — Environment & Project Setup
- [x] Install Laravel 13, configure PostgreSQL connection in `.env`
- [x] Install the Tyro dashboard template and wire up its Blade layout
- [x] **Investigate Tyro's bundled auth/role system** — see [`docs/TYRO_AUTH_INVESTIGATION.md`](docs/TYRO_AUTH_INVESTIGATION.md)
- [x] Install Livewire + Alpine.js alongside Tyro's Blade components
- [x] Configure Pint for formatting, Larastan for static analysis
- [x] Set up Git repo, `.gitignore`, README

### Phase 2 — Database Foundation
- [x] Write migrations: `tenants`, `branches`, extend `users` with `tenant_id`/`branch_id`/`role`
- [x] Write migrations: `categories`, `manufacturers`, `products`, `product_units`, `batches`, `stock`
- [x] Write migrations: `customers`, `sales`, `sale_items`, `sale_payments`
- [x] Build `TenantScope` (`app/Models/Scopes/`) + `BelongsToTenant` trait
      (`app/Models/Concerns/`) for automatic tenant filtering
- [x] Build models for every table above, using `HasFactory, BelongsToTenant` on tenant-scoped ones
- [x] Write migrations: `suppliers`, `purchase_orders`, `purchase_order_items`
- [x] Write migrations: `stock_adjustments`, `stock_transfers`
- [x] Write migration: `audit_logs`
- [x] Factories + seeders for dev data (sample tenant, products, batches, stock)

### Phase 3 — Auth, Roles & Registration (built on Tyro)
- [x] Extend Tyro's existing login/register controllers and views rather than replacing them —
      add `tenant_id`/`branch_id` capture and assignment
- [x] Build self-serve registration flow: on signup, atomically create Tenant → main Branch →
      owner User inside a DB transaction; roll back fully on any failure
- [x] Wire pharmacy roles (owner/manager/pharmacist/cashier) into Tyro's role/permission system
      (extend it if it uses Spatie; adapt equivalently if it uses a custom mechanism)
- [x] Extend login to set tenant/branch context in session after auth
- [x] Role-based middleware/gates restricting POS, inventory, reports, and settings screens
- [x] "Switch branch" UI for tenant-wide users (owner/manager), using Tyro's UI components
- [x] Write `TenantIsolationTest` — verify no query ever leaks cross-tenant data

### Phase 4 — Core Services (build before UI)
- [ ] `UnitConversionService` — convert between product_unit and base_unit quantities
- [ ] `StockDeductionService` — FEFO batch selection + `lockForUpdate()` deduction inside a
      transaction; throws a clear exception on insufficient stock
- [ ] `InvoiceNumberService` — generates unique, sequential invoice numbers per tenant
- [ ] Feature tests for each service, including concurrency/race-condition tests for stock deduction

### Phase 5 — Inventory & Purchasing
- [ ] Product CRUD (Livewire, styled with Tyro components) with category/manufacturer, unit conversions
- [ ] Batch intake screen (manual stock-in and via Purchase Orders)
- [ ] Supplier CRUD
- [ ] Purchase Order workflow: draft → ordered → received (received triggers batch + stock creation)
- [ ] Stock adjustment screen (damages, expiry write-off, physical count reconciliation)
- [ ] Low-stock dashboard widget (based on `reorder_level`)
- [ ] Near-expiry dashboard widget/report
- [ ] Stock transfer between branches

### Phase 6 — POS Sales Screen (the core feature)
- [ ] Product search/lookup Livewire component — primary input is **typed search** (name, SKU,
      generic name); optional barcode field match when a barcode was entered on the product record
      (paste or type — no scanner hardware)
- [ ] Fast keyboard UX: search box focused by default, arrow keys + Enter to add to cart, shortcuts
      for common actions (quantity, checkout) — optimized for keyboard/mouse, not scanner wedge
- [ ] Cart component: add/remove line items, quantity, unit selection (tablet/strip/box)
- [ ] FEFO batch auto-selection with manual override option
- [ ] Discount + tax calculation
- [ ] Payment component: cash/card/mobile, split payments
- [ ] Hold/resume sale (park a transaction)
- [ ] Prescription flag UI: mark sale/line items as prescription-related, capture prescriber info
- [ ] Checkout: wraps stock deduction + sale/sale_items/sale_payments creation in one DB transaction
- [ ] Receipt: print-friendly Blade view + **browser print** (`window.print()` / `@media print` CSS
      for A4 or letter — no ESC/POS or thermal-width layouts in v1); optional PDF download via dompdf
- [ ] Sale return/refund flow with stock reversal

### Phase 7 — Customers
- [ ] Customer CRUD
- [ ] Attach customer to sale (optional — support anonymous walk-in checkout)
- [ ] Customer purchase history view

### Phase 8 — Reporting & Dashboard
- [ ] Dashboard: today's sales, top products, low stock, expiring soon, register/shift totals
- [ ] Sales report (by date range, cashier, branch, product)
- [ ] Profit margin report (uses batch cost_price vs selling_price)
- [ ] Inventory valuation report
- [ ] Expiry report
- [ ] Tax report
- [ ] PDF/Excel export for all reports

### Phase 9 — Settings & Register Management
- [ ] Tenant settings: currency, tax rate, receipt header/footer, branch defaults
- [ ] User management screen (invite/manage staff, assign roles/branches via Tyro's role UI)
- [ ] Register/shift open-close with cash count reconciliation (manual count entry — no cash drawer pulse)

### Phase 10 — Non-Functional / Hardening
- [ ] Full test suite: checkout flow, stock deduction edge cases (overselling, expired batch
      sale prevention), tenant isolation, unit conversion accuracy
- [ ] Set up Laravel Telescope (dev) and error monitoring (Sentry or similar) for prod
- [ ] Automated PostgreSQL backups
- [ ] Rate limiting / abuse protection on public registration endpoint
- [ ] Queue worker + scheduler config (expiry check jobs, daily report jobs) — remember: scoped
      jobs/commands must manually resolve tenant context, since `TenantScope` relies on `Auth::user()`

### Phase 11 — Deployment (Hostinger VPS + Coolify)
- [ ] Provision **Hostinger VPS** (Ubuntu LTS, adequate RAM for PostgreSQL + app + queue worker)
- [ ] Install **Coolify** on the VPS (self-hosted PaaS — handles Docker, reverse proxy, SSL)
- [ ] In Coolify: create PostgreSQL service (or attach managed Postgres if Hostinger offers it)
- [ ] In Coolify: create Laravel application resource — connect GitHub repo, set build pack to
      **Dockerfile** or **Nixpacks** (PHP 8.3+, `composer install --no-dev`, `npm run build`)
- [ ] Configure environment variables in Coolify (`.env` equivalents): `APP_KEY`, `APP_URL`,
      `DB_*`, `QUEUE_CONNECTION`, mail credentials, etc. — never commit secrets to the repo
- [ ] Coolify deploy hooks / post-deploy commands: `php artisan migrate --force`,
      `php artisan config:cache`, `php artisan route:cache`, `php artisan view:cache`
- [ ] Run **queue worker** as a separate Coolify process/service (`php artisan queue:work --tries=3`)
- [ ] Run **scheduler** via Coolify cron or a dedicated `schedule:work` / system cron hitting
      `php artisan schedule:run` every minute
- [ ] Enable **automatic SSL** (Let's Encrypt) through Coolify's Traefik/Caddy proxy
- [ ] CI: GitHub Actions runs tests on push; Coolify auto-deploys on merge to `main` (or manual
      deploy trigger) — no Laravel Forge required
- [ ] Backups: Coolify volume snapshots and/or scheduled `pg_dump` to off-site storage
- [ ] Staff training + parallel run alongside any existing system before full cutover

---

## 7. Critical Business Logic Rules (Cursor must respect these in all generated code)

1. **Never write a raw `Product::all()`-style query and manually add `where('tenant_id', ...)`.**
   Tenant-scoped models already auto-filter via `BelongsToTenant`. Adding manual tenant_id filters
   is redundant; the risk is the opposite — code that runs *without* an authenticated user (jobs,
   console commands) will NOT be scoped automatically and needs explicit handling.
2. **All stock deduction must go through `StockDeductionService`**, never inline `Stock::decrement()`
   calls in controllers/Livewire components. It must use `lockForUpdate()` inside a transaction.
3. **FEFO always**: when deducting stock for a sale, always order candidate batches by
   `expiry_date ASC` unless the cashier explicitly overrides the batch.
4. **Quantities sold in non-base units must be converted via `UnitConversionService`** before
   touching the `stock` table — `sale_items.quantity_base` is what stock math uses, never
   `sale_items.quantity` directly.
5. **Money fields are `decimal(12,2)`, never floats.** Do all money math with PHP's `bcmath` or
   integer-cents internally if precision issues arise.
6. **Invoice numbers must be unique per tenant** and generated via `InvoiceNumberService`, not
   `uniqid()` or manual string building, to avoid collisions under concurrent checkouts.
7. **Expired batches must never be sold** — `StockDeductionService` should exclude any batch where
   `expiry_date < today()` regardless of stock quantity remaining, and expose a clear error if no
   valid batch is available.
8. **Auth/roles/permissions build on Tyro's existing system.** Don't scaffold a second, parallel
   auth or roles system alongside Tyro's — extend what it ships with.

---

## 8. Coding Conventions

- Follow Laravel 13 conventions: Form Requests for validation, Resource classes if any API is
  exposed, Policies for authorization.
- Use Tyro's existing Blade components/layout for all new screens instead of introducing a second
  UI kit or ad-hoc styling.
- Livewire components: keep business logic in Services, keep components focused on state/UI wiring.
- Use PHP 8.3+ features where they improve clarity (`app/Enums/` for `SaleStatus`, `PaymentMethod`,
  etc., instead of raw strings, once the MVP stabilizes).
- Place Livewire components in `app/Livewire/`, not under `app/Http/Livewire/`.
- Every tenant-scoped model uses `use HasFactory, BelongsToTenant;`.
- Run `php artisan pint` before committing; keep Larastan passing at the configured level.
- Write a Feature test alongside every new checkout-path or stock-affecting feature.
