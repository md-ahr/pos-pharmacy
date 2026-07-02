<?php

namespace App\Livewire\Inventory;

use App\Enums\StockAdjustmentReason;
use App\Livewire\Concerns\ListensForBranchSwitch;
use App\Models\Batch;
use App\Models\Product;
use App\Services\BranchContext;
use App\Services\StockAdjustmentService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class StockAdjustment extends Component
{
    use ListensForBranchSwitch;

    public ?int $product_id = null;

    public ?int $batch_id = null;

    public int $quantity_delta = 0;

    public string $reason = 'physical_count';

    public string $notes = '';

    protected function refreshAfterBranchSwitch(): void
    {
        $this->reset(['product_id', 'batch_id', 'quantity_delta', 'notes']);
    }

    public function updatedProductId(): void
    {
        $this->batch_id = null;
    }

    public function save(StockAdjustmentService $service, BranchContext $branchContext): void
    {
        $validated = $this->validate([
            'product_id' => ['required', 'exists:products,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'quantity_delta' => ['required', 'integer', 'not_in:0'],
            'reason' => ['required', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $branch = $branchContext->activeBranch();

        if ($branch === null) {
            $this->addError('product_id', 'No active branch selected.');

            return;
        }

        $product = Product::query()->findOrFail($validated['product_id']);
        $batch = Batch::query()->findOrFail($validated['batch_id']);

        $service->adjust(
            branch: $branch,
            product: $product,
            batch: $batch,
            quantityDelta: $validated['quantity_delta'],
            reason: StockAdjustmentReason::from($validated['reason']),
            adjustedBy: auth()->user(),
            notes: $validated['notes'] ?: null,
        );

        $this->reset(['quantity_delta', 'notes']);
        session()->flash('success', 'Stock adjustment recorded.');
    }

    public function render(): View
    {
        $batches = $this->product_id
            ? Batch::query()->where('product_id', $this->product_id)->orderBy('expiry_date')->get()
            : collect();

        return view('livewire.inventory.stock-adjustment', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'batches' => $batches,
            'reasons' => StockAdjustmentReason::cases(),
        ])->layout('layouts.pharmacy', [
            'title' => 'Stock Adjustments',
            'nav' => 'inventory',
        ]);
    }
}
