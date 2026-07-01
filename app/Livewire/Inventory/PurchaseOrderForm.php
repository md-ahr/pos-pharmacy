<?php

namespace App\Livewire\Inventory;

use App\Enums\PurchaseOrderStatus;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Supplier;
use App\Services\BranchContext;
use App\Services\PurchaseOrderService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class PurchaseOrderForm extends Component
{
    public ?PurchaseOrder $purchaseOrder = null;

    public ?int $supplier_id = null;

    public string $notes = '';

    /** @var array<int, array{product_id: ?int, quantity: int, unit_cost: string}> */
    public array $lines = [];

    /** @var array<int, array{purchase_order_item_id: int, batch_no: string, expiry_date: string, selling_price: string}> */
    public array $receiptLines = [];

    public function mount(?PurchaseOrder $purchaseOrder = null): void
    {
        $this->purchaseOrder = $purchaseOrder;

        if ($purchaseOrder === null) {
            $this->lines = [
                ['product_id' => null, 'quantity' => 1, 'unit_cost' => '0.00'],
            ];

            return;
        }

        $this->supplier_id = $purchaseOrder->supplier_id;
        $this->notes = $purchaseOrder->notes ?? '';
        $this->lines = $purchaseOrder->items->map(fn ($item) => [
            'product_id' => $item->product_id,
            'quantity' => $item->quantity,
            'unit_cost' => (string) $item->unit_cost,
        ])->all();

        if ($purchaseOrder->status === PurchaseOrderStatus::Ordered) {
            $this->receiptLines = $purchaseOrder->items->map(fn ($item) => [
                'purchase_order_item_id' => $item->id,
                'batch_no' => '',
                'expiry_date' => '',
                'selling_price' => '',
            ])->all();
        }
    }

    public function addLine(): void
    {
        $this->lines[] = ['product_id' => null, 'quantity' => 1, 'unit_cost' => '0.00'];
    }

    public function removeLine(int $index): void
    {
        if (count($this->lines) <= 1) {
            return;
        }

        unset($this->lines[$index]);
        $this->lines = array_values($this->lines);
    }

    public function saveDraft(PurchaseOrderService $service, BranchContext $branchContext): void
    {
        $this->assertEditable();

        $validated = $this->validatePurchaseOrder();
        $branch = $branchContext->activeBranch();

        if ($branch === null) {
            $this->addError('supplier_id', 'No active branch selected.');

            return;
        }

        $supplier = Supplier::query()->findOrFail($validated['supplier_id']);
        $lines = $this->normalizedLines($validated['lines']);

        if ($this->purchaseOrder === null) {
            $this->purchaseOrder = $service->createDraft(
                branch: $branch,
                supplier: $supplier,
                lines: $lines,
                createdBy: auth()->user(),
                notes: $validated['notes'] ?: null,
            );
        } else {
            $service->updateDraft($this->purchaseOrder, $lines, $validated['notes'] ?: null);
        }

        session()->flash('success', 'Purchase order saved as draft.');
        $this->redirectRoute('pharmacy.inventory.purchase-orders.edit', $this->purchaseOrder, navigate: true);
    }

    public function markOrdered(PurchaseOrderService $service): void
    {
        $this->assertEditable();

        if ($this->purchaseOrder === null) {
            $this->saveDraft($service, app(BranchContext::class));
        }

        $service->markOrdered($this->purchaseOrder->fresh());
        session()->flash('success', 'Purchase order marked as ordered.');
        $this->redirectRoute('pharmacy.inventory.purchase-orders.edit', $this->purchaseOrder, navigate: true);
    }

    public function receive(PurchaseOrderService $service): void
    {
        if ($this->purchaseOrder === null || $this->purchaseOrder->status !== PurchaseOrderStatus::Ordered) {
            return;
        }

        $validated = $this->validate([
            'receiptLines' => ['required', 'array', 'min:1'],
            'receiptLines.*.purchase_order_item_id' => ['required', 'integer'],
            'receiptLines.*.batch_no' => ['required', 'string', 'max:100'],
            'receiptLines.*.expiry_date' => ['required', 'date', 'after:today'],
            'receiptLines.*.selling_price' => ['required', 'numeric', 'min:0'],
        ]);

        $service->receive($this->purchaseOrder, $validated['receiptLines']);

        session()->flash('success', 'Purchase order received and stock updated.');
        $this->redirectRoute('pharmacy.inventory.purchase-orders', navigate: true);
    }

    public function render(): View
    {
        return view('livewire.inventory.purchase-order-form', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'suppliers' => Supplier::query()->where('is_active', true)->orderBy('name')->get(),
            'isEditable' => $this->purchaseOrder === null || $this->purchaseOrder->status === PurchaseOrderStatus::Draft,
            'canReceive' => $this->purchaseOrder?->status === PurchaseOrderStatus::Ordered,
        ])->layout('layouts.pharmacy', [
            'title' => $this->purchaseOrder ? 'Purchase Order '.$this->purchaseOrder->reference_no : 'New Purchase Order',
            'nav' => 'inventory',
        ]);
    }

    private function assertEditable(): void
    {
        if ($this->purchaseOrder !== null && $this->purchaseOrder->status !== PurchaseOrderStatus::Draft) {
            abort(403, 'This purchase order can no longer be edited.');
        }
    }

    /**
     * @return array{supplier_id: int, notes: string, lines: array<int, array{product_id: int, quantity: int, unit_cost: string}>}
     */
    private function validatePurchaseOrder(): array
    {
        return $this->validate([
            'supplier_id' => ['required', 'exists:suppliers,id'],
            'notes' => ['nullable', 'string'],
            'lines' => ['required', 'array', 'min:1'],
            'lines.*.product_id' => ['required', 'exists:products,id'],
            'lines.*.quantity' => ['required', 'integer', 'min:1'],
            'lines.*.unit_cost' => ['required', 'numeric', 'min:0'],
        ]);
    }

    /**
     * @param  array<int, array{product_id: int, quantity: int, unit_cost: string}>  $lines
     * @return array<int, array{product_id: int, quantity: int, unit_cost: string}>
     */
    private function normalizedLines(array $lines): array
    {
        return collect($lines)->map(fn (array $line) => [
            'product_id' => (int) $line['product_id'],
            'quantity' => (int) $line['quantity'],
            'unit_cost' => number_format((float) $line['unit_cost'], 2, '.', ''),
        ])->all();
    }
}
