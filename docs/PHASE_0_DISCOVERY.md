# Phase 0 — Discovery & Requirements

Confirmed v1 scope for Pharmacy POS. These decisions are fixed for the initial release unless the pharmacy owner explicitly revisits them.

## v1 modules (in scope)

| Module | v1 scope |
|--------|----------|
| **POS** | Keyboard-first checkout, cart, payments, hold/resume, browser-print receipts |
| **Inventory** | Products, batches, FEFO stock, unit conversions, adjustments, transfers |
| **Purchasing** | Suppliers, purchase orders, goods receipt → batch/stock creation |
| **Reporting** | Sales, margins, inventory valuation, expiry, tax (PDF/Excel export) |
| **Multi-tenant SaaS** | Self-serve signup, single DB with `tenant_id`, multi-branch per tenant |
| **Auth & roles** | Owner, manager, pharmacist, cashier (extends Tyro RBAC) |

## Deferred to later phases

| Item | Notes |
|------|-------|
| Full prescription / doctor module | Lightweight prescriber fields on `sales` / `sale_items` only |
| Barcode scanner hardware | Optional `barcode` columns for manual entry; search-driven POS |
| Thermal receipt printer / ESC/POS | Browser `window.print()` on A4/letter Blade receipt |
| Cash drawer integration | Manual cash count at register open/close |
| Database-per-tenant | Single-database `tenant_id` strategy unless compliance demands isolation |

## Software-only POS constraints (confirmed)

- Staff look up products by **name, SKU, or generic name** (typed search).
- Barcode fields may be filled manually or pasted; no scanner wedge listeners in v1.
- Receipts use **browser print** (`@media print` CSS) — no thermal-width layouts.
- No USB/Bluetooth/serial device integrations.

## Compliance baseline (v1)

| Area | Approach |
|------|----------|
| Batch / expiry | FEFO deduction; block sale of expired batches |
| Prescription traceability | `prescription_required`, prescriber name/reg on sale; `is_prescription_item` on lines |
| Receipt fields | Tenant-configurable header/footer; invoice number, line items, batch/expiry where required |
| Audit trail | `audit_logs` table (Phase 2); Tyro audit for auth/RBAC changes |
| Controlled substances | Flag via `requires_prescription` on products; full controlled-substance workflow deferred |

## Hosting target

- **Hostinger VPS** managed via **Coolify** (Phase 11).
- **PostgreSQL** as primary database.

## Sign-off

Phase 0 is satisfied by adopting the architecture and scope defined in [`PHARMACY_POS_PLAN.md`](../PHARMACY_POS_PLAN.md). Phase 3+ implements tenant registration and pharmacy-specific roles on top of Tyro.
