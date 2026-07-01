<?php

namespace App\Services;

use App\Enums\StockAdjustmentReason;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockAdjustment;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockAdjustmentService
{
    public function adjust(
        Branch $branch,
        Product $product,
        Batch $batch,
        int $quantityDelta,
        StockAdjustmentReason $reason,
        ?User $adjustedBy = null,
        ?string $notes = null,
    ): StockAdjustment {
        return DB::transaction(function () use ($branch, $product, $batch, $quantityDelta, $reason, $adjustedBy, $notes): StockAdjustment {
            $stock = Stock::query()
                ->where('branch_id', $branch->id)
                ->where('batch_id', $batch->id)
                ->lockForUpdate()
                ->first();

            if ($stock === null && $quantityDelta < 0) {
                throw InsufficientStockException::forProduct($product->name, abs($quantityDelta), 0);
            }

            if ($stock !== null && ($stock->quantity + $quantityDelta) < 0) {
                throw InsufficientStockException::forProduct(
                    $product->name,
                    abs($quantityDelta),
                    $stock->quantity
                );
            }

            if ($stock === null) {
                $stock = Stock::query()->create([
                    'tenant_id' => $product->tenant_id,
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'quantity' => 0,
                ]);
            }

            $stock->increment('quantity', $quantityDelta);

            return StockAdjustment::query()->create([
                'tenant_id' => $product->tenant_id,
                'branch_id' => $branch->id,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'quantity_delta' => $quantityDelta,
                'reason' => $reason->value,
                'adjusted_by' => $adjustedBy?->id,
                'notes' => $notes,
            ]);
        });
    }
}
