<?php

namespace App\Services;

use App\Data\StockDeductionLine;
use App\Exceptions\ExpiredStockException;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockDeductionService
{
    /**
     * @return Collection<int, StockDeductionLine>
     */
    public function deduct(Branch $branch, Product $product, int $quantityBase, ?Batch $overrideBatch = null): Collection
    {
        if ($quantityBase <= 0) {
            return collect();
        }

        return DB::transaction(function () use ($branch, $product, $quantityBase, $overrideBatch): Collection {
            if ($overrideBatch !== null) {
                return collect([$this->deductFromBatch($branch, $product, $overrideBatch, $quantityBase)]);
            }

            $stockRows = $this->lockedCandidateStock($branch, $product);

            if ($stockRows->isEmpty()) {
                throw ExpiredStockException::forProduct($product->name);
            }

            $available = $stockRows->sum('quantity');

            if ($available < $quantityBase) {
                throw InsufficientStockException::forProduct($product->name, $quantityBase, $available);
            }

            $remaining = $quantityBase;
            $deductions = collect();

            foreach ($stockRows as $stock) {
                if ($remaining <= 0) {
                    break;
                }

                $deduct = min($remaining, $stock->quantity);
                $stock->decrement('quantity', $deduct);
                $remaining -= $deduct;

                $deductions->push(new StockDeductionLine(
                    batchId: $stock->batch_id,
                    stockId: $stock->id,
                    quantityDeducted: $deduct,
                ));
            }

            return $deductions;
        });
    }

    private function deductFromBatch(Branch $branch, Product $product, Batch $batch, int $quantityBase): StockDeductionLine
    {
        if ($batch->product_id !== $product->id) {
            throw ExpiredStockException::forBatch($batch->batch_no);
        }

        if ($batch->expiry_date->lt(today())) {
            throw ExpiredStockException::forBatch($batch->batch_no);
        }

        $stock = Stock::query()
            ->where('branch_id', $branch->id)
            ->where('batch_id', $batch->id)
            ->lockForUpdate()
            ->first();

        if ($stock === null || $stock->quantity < $quantityBase) {
            $available = $stock?->quantity ?? 0;

            throw InsufficientStockException::forProduct($product->name, $quantityBase, $available);
        }

        $stock->decrement('quantity', $quantityBase);

        return new StockDeductionLine(
            batchId: $batch->id,
            stockId: $stock->id,
            quantityDeducted: $quantityBase,
        );
    }

    /**
     * @return Collection<int, Stock>
     */
    public function restore(Branch $branch, Product $product, Batch $batch, int $quantityBase): void
    {
        if ($quantityBase <= 0) {
            return;
        }

        DB::transaction(function () use ($branch, $product, $batch, $quantityBase): void {
            if ($batch->product_id !== $product->id) {
                throw ExpiredStockException::forBatch($batch->batch_no);
            }

            $stock = Stock::query()
                ->where('branch_id', $branch->id)
                ->where('batch_id', $batch->id)
                ->lockForUpdate()
                ->first();

            if ($stock === null) {
                Stock::query()->create([
                    'tenant_id' => $product->tenant_id,
                    'branch_id' => $branch->id,
                    'product_id' => $product->id,
                    'batch_id' => $batch->id,
                    'quantity' => $quantityBase,
                ]);

                return;
            }

            $stock->increment('quantity', $quantityBase);
        });
    }

    private function lockedCandidateStock(Branch $branch, Product $product): Collection
    {
        return Stock::query()
            ->where('stock.branch_id', $branch->id)
            ->where('stock.product_id', $product->id)
            ->where('stock.quantity', '>', 0)
            ->whereHas('batch', fn ($query) => $query->where('batches.expiry_date', '>=', today()))
            ->join('batches', 'stock.batch_id', '=', 'batches.id')
            ->orderBy('batches.expiry_date')
            ->select('stock.*')
            ->lockForUpdate()
            ->get();
    }
}
