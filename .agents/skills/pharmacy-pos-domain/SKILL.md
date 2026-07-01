---
name: pharmacy-pos-domain
description: "Guides implementation of the multi-tenant Pharmacy POS SaaS per PHARMACY_POS_PLAN.md. Use when adding features, models, migrations, routes, or planning work for inventory, sales, branches, products, batches, customers, reports, or pharmacy-specific business logic. Covers schema, roles (owner/manager/pharmacist/cashier), FEFO, unit conversions, lightweight prescriptions, phase order, and v1 scope limits. Activate alongside laravel-best-practices for any domain code."
---

# Pharmacy POS Domain

## Before coding

1. Read relevant sections of `PHARMACY_POS_PLAN.md`.
2. Confirm current **phase** — do not skip data layer before UI.
3. Activate sibling skills: `pharmacy-multi-tenancy`, `pharmacy-checkout-stock`, `pharmacy-pos-ui`, or `pharmacy-deployment-coolify` as needed.

## Architecture (fixed)

| Decision | Choice |
|----------|--------|
| Multi-tenancy | Single DB, `tenant_id` + `BelongsToTenant` |
| Branches | Multi-branch per tenant from day one |
| Stock strategy | FEFO, stored in base_unit |
| Prescriptions | Lightweight fields on sales/sale_items only |
| Auth | Extend Tyro — no parallel auth system |
| Hardware v1 | None — browser-only POS |

## Roles

| Role | Access |
|------|--------|
| Owner | All branches, settings, reports |
| Manager | Inventory, purchasing, staff at assigned branch(es) |
| Pharmacist | POS + prescription flags |
| Cashier | POS only, assigned branch |

## Phase gate checklist

Before POS UI (Phase 6):
- [ ] Migrations + models for core tables
- [ ] `TenantScope` + `BelongsToTenant`
- [ ] Tyro auth extended with tenant/branch/roles
- [ ] `UnitConversionService`, `StockDeductionService`, `InvoiceNumberService`
- [ ] `TenantIsolationTest` passing

## Schema quick reference

See [rules/schema.md](rules/schema.md) for table/column rules.

## Business logic

See [rules/business-logic.md](rules/business-logic.md) for non-negotiable rules.

## Out of scope without approval

- Full prescription/doctor modules
- Barcode scanner / thermal printer / cash drawer
- Database-per-tenant
- Replacing Tyro with Breeze/Fortify
- New Composer packages
