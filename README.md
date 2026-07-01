# Pharmacy POS

Multi-tenant Point-of-Sale SaaS for pharmacies. Each pharmacy signs up self-serve, runs one or more branches, and manages inventory, sales, and reporting with pharmacy-specific workflows: batch/expiry tracking (FEFO), unit conversions, and lightweight prescription metadata.

**Status:** Phase 0–1 complete — Laravel 13, PostgreSQL, Tyro dashboard/auth, Livewire, Pint, and Larastan are configured. Domain schema and tenant scoping start in Phase 2 per [`PHARMACY_POS_PLAN.md`](PHARMACY_POS_PLAN.md).

## Features (planned)

- **Multi-tenant SaaS** — single database, `tenant_id` on business tables, automatic Eloquent scoping
- **Multi-branch** — per-tenant branches with branch assignment for staff
- **Inventory** — products, batches, FEFO stock deduction, unit conversions (tablet/strip/box)
- **POS** — keyboard-first checkout, cart, payments, hold/resume, browser-print receipts
- **Purchasing** — suppliers, purchase orders, stock adjustments and transfers
- **Reporting** — sales, margins, inventory valuation, expiry, tax (PDF/Excel export)
- **Roles** — owner, manager, pharmacist, cashier (extends Tyro auth/RBAC)

### Out of scope for v1

No barcode scanner, thermal printer, ESC/POS, or cash-drawer hardware. Product lookup is search-driven; receipts use the browser print dialog.

## Tech stack

| Layer | Choice |
|---|---|
| Backend | Laravel 13, PHP 8.4 |
| Database | PostgreSQL |
| UI | Livewire + Alpine.js, Tyro dashboard (Blade) |
| Auth / RBAC | Tyro (extended with `tenant_id`, `branch_id`, pharmacy roles) |
| API tokens | Laravel Sanctum |
| Tests | Pest 4 |
| CSS | Tailwind CSS 4 |
| Hosting target | Hostinger VPS + Coolify |

## Requirements

- PHP 8.4+
- Composer 2
- Node.js 20+ and npm
- PostgreSQL 15+
- [Laravel Herd](https://herd.laravel.com/) (recommended for local macOS/Windows development)

## Local setup

### With Laravel Herd

Herd serves this app at `https://pos-pharmecy.test` when the project directory is linked.

```bash
git clone <repo-url> pos-pharmecy
cd pos-pharmecy

composer install
cp .env.example .env
php artisan key:generate
```

Configure PostgreSQL in `.env`:

```env
APP_NAME="Pharmacy POS"
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=pos_pharmecy
DB_USERNAME=your_user
DB_PASSWORD=your_password
```

Then migrate and build assets:

```bash
php artisan migrate
npm install
npm run build
```

Or use the combined setup script:

```bash
composer run setup
```

### Without Herd

Use `composer run dev` (see below) or run `php artisan serve` alongside `npm run dev`.

## Development

Start the app, queue worker, log tail, and Vite dev server together:

```bash
composer run dev
```

Other useful commands:

```bash
php artisan test              # run Pest tests
composer run format           # format PHP (Pint)
composer run analyse          # static analysis (Larastan)
npm run build                 # production frontend assets
```

## Project documentation

| File | Purpose |
|---|---|
| [`PHARMACY_POS_PLAN.md`](PHARMACY_POS_PLAN.md) | Full spec: schema, phases, business rules |
| [`docs/PHASE_0_DISCOVERY.md`](docs/PHASE_0_DISCOVERY.md) | Confirmed v1 scope and deferred items |
| [`docs/TYRO_AUTH_INVESTIGATION.md`](docs/TYRO_AUTH_INVESTIGATION.md) | Tyro auth/RBAC findings for Phase 3 |
| [`AGENTS.md`](AGENTS.md) / [`CLAUDE.md`](CLAUDE.md) | Laravel Boost + agent conventions |
| `.cursor/rules/` | Pharmacy guardrails (tenant isolation, stock, commits) |
| `**/skills/pharmacy-*` | Domain skills for AI-assisted development |

Work through plan phases in order — do not build POS UI before tenant scoping and core services exist.

## Architecture highlights

- **Tenancy:** `BelongsToTenant` trait + `TenantScope` global scope on business models
- **Stock:** all deductions via `StockDeductionService` with `DB::transaction` + `lockForUpdate()`; FEFO (`expiry_date ASC`)
- **Units:** `UnitConversionService` converts sell units to `quantity_base` before stock math
- **Money:** `decimal(12,2)` columns; use `bcmath`, not floats
- **Invoices:** `InvoiceNumberService` per tenant — no ad-hoc ID generation

Queued jobs and Artisan commands must set tenant context explicitly; `TenantScope` relies on the authenticated user.

## Testing

```bash
php artisan test
php artisan test --filter=TenantIsolation
```

Every checkout or stock-affecting change should include a feature test.

## Agentic development

This project uses [Laravel Boost](https://laravel.com/docs/ai) and pharmacy-specific skills under `.agents/skills/`, `.claude/skills/`, and `.factory/skills/`. Activate the relevant `pharmacy-*` skill when working on tenancy, stock, POS UI, or deployment.

## License

MIT
