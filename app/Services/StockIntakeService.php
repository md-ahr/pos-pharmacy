<?php

namespace App\Services;

use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;

class StockIntakeService
{
    public function intake(
        Branch $branch,
        Product $product,
        string $batchNo,
        CarbonInterface $expiryDate,
        string $costPrice,
        string $sellingPrice,
        int $quantityBase,
    ): Stock {
        return DB::transaction(function () use ($branch, $product, $batchNo, $expiryDate, $costPrice, $sellingPrice, $quantityBase): Stock {
            $batch = Batch::query()->firstOrCreate(
                [
                    'tenant_id' => $product->tenant_id,
                    'product_id' => $product->id,
                    'batch_no' => $batchNo,
                ],
                [
                    'expiry_date' => $expiryDate,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                    'received_at' => now(),
                ]
            );

            if (! $batch->wasRecentlyCreated) {
                $batch->update([
                    'expiry_date' => $expiryDate,
                    'cost_price' => $costPrice,
                    'selling_price' => $sellingPrice,
                ]);
            }

            $stock = Stock::query()
                ->where('branch_id', $branch->id)
                ->where('batch_id', $batch->id)
                ->lockForUpdate()
                ->first();

            if ($stock === null) {
                return Stock::query()->create([
                    'tenant_id' => $product->tenant_id,
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'quantity' => $quantityBase,
                ]);
            }

            $stock->increment('quantity', $quantityBase);

            return $stock->fresh();
        });
    }
}
