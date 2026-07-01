# FEFO & Concurrency Patterns

## FEFO query pattern

```php
Stock::query()
    ->where('branch_id', $branchId)
    ->where('product_id', $productId)
    ->where('quantity', '>', 0)
    ->whereHas('batch', fn ($q) => $q->where('expiry_date', '>=', today()))
    ->join('batches', 'stock.batch_id', '=', 'batches.id')
    ->orderBy('batches.expiry_date')
    ->select('stock.*')
    ->lockForUpdate()
    ->get();
```

Prefer relationship-based queries matching project conventions once models exist.

## Multi-batch deduction

If first batch quantity < `quantityBase`, deduct full first batch, continue to next FEFO batch until satisfied or throw.

## Race condition test sketch

```php
it('prevents overselling under concurrent checkout', function () {
    // Seed stock quantity = 5
    // Dispatch two concurrent deduction attempts for qty 3 each
    // Assert one succeeds, one fails; stock.quantity >= 0
});
```

Use Pest concurrency or parallel HTTP requests per project capability.

## Invoice number locking

```php
DB::transaction(function () {
    $counter = InvoiceCounter::where('tenant_id', $tenantId)->lockForUpdate()->first();
    $number = $counter->incrementAndGetNext();
});
```

Alternative: `SELECT ... FOR UPDATE` on last sale row for tenant — less ideal but acceptable if documented.

## PostgreSQL safety net

Migration must include:

```php
$table->check('quantity >= 0');
```

On `stock.quantity`.
