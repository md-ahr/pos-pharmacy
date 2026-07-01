# Schema Rules

## Tenant-scoped tables

All business data tables include `tenant_id` FK → `tenants` (cascade delete where appropriate):

`branches`, `categories`, `manufacturers`, `products`, `batches`, `stock`, `customers`, `sales`, plus purchasing/adjustment tables when added.

## Key constraints

| Table | Constraint |
|-------|------------|
| `products.barcode` | Unique per tenant (partial index when not null) |
| `branches.code` | Unique per tenant |
| `stock` | Unique `(branch_id, batch_id)`; `CHECK (quantity >= 0)` |
| `batches` | Index `(product_id, expiry_date)` for FEFO |
| `sales.invoice_no` | Unique per tenant |

## Units & stock

- `products.base_unit` — internal stock unit (tablet, ml, etc.)
- `product_units.conversion_factor` — how many base_units per sellable unit
- `stock.quantity` — always base_unit count
- `sale_items.quantity` — sold unit count; `quantity_base` — used for deduction

## Money columns

Use `decimal(12,2)` for: subtotal, discount, tax, total, paid, change, prices, costs.

## Users extension

Add to Tyro users: `tenant_id`, `branch_id` (nullable = tenant-wide), `role`, `is_active`.

## Migrations

- Use `php artisan make:migration` — follow existing migration style in repo.
- Add factories/seeders for new tenant-scoped models used in tests.
