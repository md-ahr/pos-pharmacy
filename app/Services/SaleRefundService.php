<?php

namespace App\Services;

use App\Enums\SaleStatus;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Models\Sale;
use App\Models\SaleItem;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class SaleRefundService
{
    public function __construct(
        private StockDeductionService $stockDeduction,
    ) {}

    /**
     * @param  list<int>|null  $saleItemIds  Null refunds all refundable items.
     */
    public function refund(Sale $sale, Branch $branch, ?array $saleItemIds = null): Sale
    {
        if (! in_array($sale->status, [SaleStatus::Completed, SaleStatus::PartiallyRefunded], true)) {
            throw new InvalidArgumentException('Only completed sales can be refunded.');
        }

        if ($sale->branch_id !== $branch->id) {
            throw new InvalidArgumentException('Sale belongs to a different branch.');
        }

        return DB::transaction(function () use ($sale, $branch, $saleItemIds): Sale {
            $items = $sale->items()
                ->when($saleItemIds !== null, fn ($query) => $query->whereIn('id', $saleItemIds))
                ->get();

            if ($items->isEmpty()) {
                throw new InvalidArgumentException('No sale items selected for refund.');
            }

            $refundedAny = false;

            foreach ($items as $item) {
                $refundableQuantity = $item->quantity - $item->refunded_quantity;

                if ($refundableQuantity <= 0) {
                    continue;
                }

                $this->restoreItemStock($branch, $item, $refundableQuantity);
                $item->update([
                    'refunded_quantity' => $item->quantity,
                ]);
                $refundedAny = true;
            }

            if (! $refundedAny) {
                throw new InvalidArgumentException('Selected items are already refunded.');
            }

            $allRefunded = $sale->items()
                ->whereColumn('refunded_quantity', '<', 'quantity')
                ->doesntExist();

            $sale->update([
                'status' => $allRefunded ? SaleStatus::Refunded : SaleStatus::PartiallyRefunded,
            ]);

            return $sale->fresh(['items']);
        });
    }

    private function restoreItemStock(Branch $branch, SaleItem $item, int $quantityUnits): void
    {
        $product = Product::query()->findOrFail($item->product_id);
        $batch = Batch::query()->findOrFail($item->batch_id);

        $quantityBase = $item->quantity_base;

        if ($quantityUnits < $item->quantity) {
            $quantityBase = (int) round($item->quantity_base * ($quantityUnits / $item->quantity));
        }

        $this->stockDeduction->restore($branch, $product, $batch, $quantityBase);
    }
}
