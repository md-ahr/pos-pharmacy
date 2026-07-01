<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\ProductUnit;
use App\Models\Stock;
use Illuminate\Support\Collection;

class PosPricingService
{
    public function suggestBatch(Branch $branch, Product $product): ?Batch
    {
        $stock = Stock::query()
            ->where('stock.branch_id', $branch->id)
            ->where('stock.product_id', $product->id)
            ->where('stock.quantity', '>', 0)
            ->whereHas('batch', fn ($query) => $query->where('batches.expiry_date', '>=', today()))
            ->join('batches', 'stock.batch_id', '=', 'batches.id')
            ->orderBy('batches.expiry_date')
            ->select('stock.*')
            ->with('batch')
            ->first();

        return $stock?->batch;
    }

    /**
     * @return Collection<int, Batch>
     */
    public function availableBatches(Branch $branch, Product $product): Collection
    {
        return Stock::query()
            ->where('stock.branch_id', $branch->id)
            ->where('stock.product_id', $product->id)
            ->where('stock.quantity', '>', 0)
            ->whereHas('batch', fn ($query) => $query->where('batches.expiry_date', '>=', today()))
            ->join('batches', 'stock.batch_id', '=', 'batches.id')
            ->orderBy('batches.expiry_date')
            ->select('stock.*')
            ->with('batch')
            ->get()
            ->map(fn (Stock $stock) => $stock->batch)
            ->filter()
            ->values();
    }

    public function resolveUnitPrice(Product $product, ?ProductUnit $unit, ?Batch $batch): string
    {
        if ($unit?->selling_price !== null) {
            return number_format((float) $unit->selling_price, 2, '.', '');
        }

        if ($batch === null) {
            return '0.00';
        }

        $basePrice = number_format((float) $batch->selling_price, 2, '.', '');

        if ($unit === null || $unit->conversion_factor <= 1) {
            return $basePrice;
        }

        return bcmul($basePrice, (string) $unit->conversion_factor, 2);
    }
}
