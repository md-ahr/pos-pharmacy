<?php

namespace App\Services;

use App\Enums\PurchaseOrderStatus;
use App\Models\Branch;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use App\Models\Supplier;
use App\Models\User;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PurchaseOrderService
{
    public function __construct(
        private StockIntakeService $stockIntake,
    ) {}

    /**
     * @param  array<int, array{product_id: int, quantity: int, unit_cost: string}>  $lines
     */
    public function createDraft(
        Branch $branch,
        Supplier $supplier,
        array $lines,
        ?User $createdBy = null,
        ?string $notes = null,
    ): PurchaseOrder {
        return DB::transaction(function () use ($branch, $supplier, $lines, $createdBy, $notes): PurchaseOrder {
            $referenceNo = $this->nextReferenceNo($branch->tenant_id);

            $purchaseOrder = PurchaseOrder::query()->create([
                'tenant_id' => $branch->tenant_id,
                'branch_id' => $branch->id,
                'supplier_id' => $supplier->id,
                'created_by' => $createdBy?->id,
                'reference_no' => $referenceNo,
                'status' => PurchaseOrderStatus::Draft,
                'total_amount' => '0.00',
                'notes' => $notes,
            ]);

            $this->syncItems($purchaseOrder, $lines);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'branch']);
        });
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, unit_cost: string}>  $lines
     */
    public function updateDraft(PurchaseOrder $purchaseOrder, array $lines, ?string $notes = null): PurchaseOrder
    {
        $this->assertStatus($purchaseOrder, PurchaseOrderStatus::Draft);

        return DB::transaction(function () use ($purchaseOrder, $lines, $notes): PurchaseOrder {
            if ($notes !== null) {
                $purchaseOrder->update(['notes' => $notes]);
            }

            $purchaseOrder->items()->delete();
            $this->syncItems($purchaseOrder, $lines);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'branch']);
        });
    }

    public function markOrdered(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        $this->assertStatus($purchaseOrder, PurchaseOrderStatus::Draft);

        $purchaseOrder->update([
            'status' => PurchaseOrderStatus::Ordered,
            'ordered_at' => now(),
        ]);

        return $purchaseOrder->fresh();
    }

    /**
     * @param  array<int, array{purchase_order_item_id: int, batch_no: string, expiry_date: CarbonInterface|string, selling_price: string}>  $receiptLines
     */
    public function receive(PurchaseOrder $purchaseOrder, array $receiptLines): PurchaseOrder
    {
        $this->assertStatus($purchaseOrder, PurchaseOrderStatus::Ordered);

        return DB::transaction(function () use ($purchaseOrder, $receiptLines): PurchaseOrder {
            $itemsById = $purchaseOrder->items()->with('product')->get()->keyBy('id');

            foreach ($receiptLines as $line) {
                $item = $itemsById->get($line['purchase_order_item_id']);

                if ($item === null) {
                    throw new InvalidArgumentException('Invalid purchase order line.');
                }

                /** @var Product $product */
                $product = $item->product;

                $expiryDate = $line['expiry_date'] instanceof CarbonInterface
                    ? $line['expiry_date']
                    : now()->parse($line['expiry_date']);

                $this->stockIntake->intake(
                    branch: $purchaseOrder->branch,
                    product: $product,
                    batchNo: $line['batch_no'],
                    expiryDate: $expiryDate,
                    costPrice: (string) $item->unit_cost,
                    sellingPrice: $line['selling_price'],
                    quantityBase: $item->quantity,
                );
            }

            $purchaseOrder->update([
                'status' => PurchaseOrderStatus::Received,
                'received_at' => now(),
            ]);

            return $purchaseOrder->fresh(['items.product', 'supplier', 'branch']);
        });
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, unit_cost: string}>  $lines
     */
    private function syncItems(PurchaseOrder $purchaseOrder, array $lines): void
    {
        $total = '0.00';

        foreach ($lines as $line) {
            $lineTotal = bcmul((string) $line['unit_cost'], (string) $line['quantity'], 2);

            PurchaseOrderItem::query()->create([
                'purchase_order_id' => $purchaseOrder->id,
                'product_id' => $line['product_id'],
                'quantity' => $line['quantity'],
                'unit_cost' => $line['unit_cost'],
                'line_total' => $lineTotal,
            ]);

            $total = bcadd($total, $lineTotal, 2);
        }

        $purchaseOrder->update(['total_amount' => $total]);
    }

    private function nextReferenceNo(int $tenantId): string
    {
        $count = PurchaseOrder::query()
            ->where('tenant_id', $tenantId)
            ->count() + 1;

        return sprintf('PO-%s-%04d', now()->format('Y'), $count);
    }

    private function assertStatus(PurchaseOrder $purchaseOrder, PurchaseOrderStatus $expected): void
    {
        if ($purchaseOrder->status !== $expected) {
            throw new InvalidArgumentException(
                "Purchase order must be in {$expected->value} status."
            );
        }
    }
}
