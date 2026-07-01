# Business Logic Rules

Non-negotiable — violating these creates data integrity or compliance risk.

## 1. Tenant scoping

- Scoped models auto-filter via `BelongsToTenant`.
- Do **not** add manual `where('tenant_id')` on scoped models.
- Jobs/commands without auth must explicitly scope by tenant.

## 2. Stock deduction

- **Only** through `StockDeductionService`.
- Inside `DB::transaction` with `lockForUpdate()`.
- Never `Stock::decrement()` in controllers or Livewire.

## 3. FEFO

- Select batches `ORDER BY expiry_date ASC`.
- Exclude `expiry_date < today()`.
- Allow manual batch override at POS when staff chooses.

## 4. Unit conversion

- Always convert to base units before touching `stock`.
- Persist both `quantity` and `quantity_base` on `sale_items`.

## 5. Money

- DB: `decimal(12,2)`.
- PHP: `bcmath` or integer cents — never float arithmetic.

## 6. Invoice numbers

- `InvoiceNumberService` only — sequential, unique per tenant.
- Never `uniqid()`, random strings, or max(id)+1 without locking.

## 7. Expired stock

- Cannot sell expired batches — clear error when no valid batch remains.

## 8. Registration

- Atomic: Tenant + main Branch + owner User in one transaction.

## 9. Auth

- Extend Tyro roles/permissions — do not scaffold a second system.

## 10. Testing

- Feature test required for every checkout or stock-affecting path.
