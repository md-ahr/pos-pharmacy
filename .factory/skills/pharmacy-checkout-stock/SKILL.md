---
name: pharmacy-checkout-stock
description: "Implements checkout, stock deduction, FEFO batch selection, unit conversion, invoice numbering, refunds, and concurrency-safe sales for the Pharmacy POS. Use when working on StockDeductionService, UnitConversionService, InvoiceNumberService, POS checkout, sale_items, sale_payments, held sales, refunds, or stock-related Feature tests including race conditions."
---

# Pharmacy Checkout & Stock

## Service responsibilities

### UnitConversionService

```php
public function toBaseUnits(Product $product, ?ProductUnit $unit, int $quantity): int
```

- Null unit → treat as base_unit (factor 1).
- Return integer base quantity for `sale_items.quantity_base`.

### StockDeductionService

```php
public function deduct(Branch $branch, Product $product, int $quantityBase, ?Batch $overrideBatch = null): Collection
```

Inside `DB::transaction`:

1. Resolve candidate batches: product + branch stock, `expiry_date >= today()`, FEFO order.
2. If `$overrideBatch`, validate it is eligible.
3. `lockForUpdate()` on stock rows.
4. Deduct across batches if one batch insufficient.
5. Throw domain exception if insufficient or only expired stock.

Never call from Livewire directly without going through checkout action/service wrapper.

### InvoiceNumberService

- Per-tenant sequential numbers (e.g. `INV-2026-000001`).
- Use row lock or dedicated counter table to prevent duplicate invoice_no under concurrency.

## Checkout flow

Single transaction:

1. Validate cart (units, prices, prescription flags).
2. For each line: `quantity_base = UnitConversionService::toBaseUnits(...)`.
3. `StockDeductionService::deduct(...)` per line (or batch optimized API).
4. Create `Sale`, `SaleItem`, `SalePayment` records.
5. Set `sold_at`, status `completed`.

## Refunds

- Reverse stock via dedicated service method (restock to original or current batch policy).
- Support `refunded` / `partially_refunded` statuses.

## Tests (required)

| Test | Asserts |
|------|---------|
| FEFO selection | Soonest expiry batch used first |
| Expired batch | Sale blocked |
| Insufficient stock | Exception, no partial sale |
| Concurrent checkout | Two simultaneous sales — no negative stock |
| Unit conversion | Strip/box quantities deduct correct base units |

Use Pest; activate `pest-testing` skill for syntax.

See [rules/fefo-concurrency.md](rules/fefo-concurrency.md) for locking patterns.
