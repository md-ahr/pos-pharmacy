<?php

namespace App\Services;

use App\Enums\StockTransferStatus;
use App\Exceptions\InsufficientStockException;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\StockTransfer;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class StockTransferService
{
    public function __construct(
        private StockIntakeService $stockIntake,
    ) {}

    public function initiate(
        Branch $fromBranch,
        Branch $toBranch,
        Product $product,
        Batch $batch,
        int $quantity,
        ?User $initiatedBy = null,
        ?string $notes = null,
    ): StockTransfer {
        if ($fromBranch->id === $toBranch->id) {
            throw new \InvalidArgumentException('Source and destination branches must differ.');
        }

        return DB::transaction(function () use ($fromBranch, $toBranch, $product, $batch, $quantity, $initiatedBy, $notes): StockTransfer {
            $sourceStock = Stock::query()
                ->where('branch_id', $fromBranch->id)
                ->where('batch_id', $batch->id)
                ->lockForUpdate()
                ->first();

            if ($sourceStock === null || $sourceStock->quantity < $quantity) {
                throw InsufficientStockException::forProduct(
                    $product->name,
                    $quantity,
                    $sourceStock?->quantity ?? 0
                );
            }

            $sourceStock->decrement('quantity', $quantity);

            $this->stockIntake->intake(
                branch: $toBranch,
                product: $product,
                batchNo: $batch->batch_no,
                expiryDate: $batch->expiry_date,
                costPrice: (string) $batch->cost_price,
                sellingPrice: (string) $batch->selling_price,
                quantityBase: $quantity,
            );

            return StockTransfer::query()->create([
                'tenant_id' => $product->tenant_id,
                'from_branch_id' => $fromBranch->id,
                'to_branch_id' => $toBranch->id,
                'product_id' => $product->id,
                'batch_id' => $batch->id,
                'quantity' => $quantity,
                'status' => StockTransferStatus::Completed,
                'initiated_by' => $initiatedBy?->id,
                'transferred_at' => now(),
                'notes' => $notes,
            ]);
        });
    }
}
