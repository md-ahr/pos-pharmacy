<?php

namespace App\Livewire\Inventory;

use App\Livewire\Concerns\ListensForBranchSwitch;
use App\Models\Batch;
use App\Models\Branch;
use App\Models\Product;
use App\Services\BranchContext;
use App\Services\StockTransferService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class StockTransfer extends Component
{
    use ListensForBranchSwitch;

    public ?int $to_branch_id = null;

    public ?int $product_id = null;

    public ?int $batch_id = null;

    public int $quantity = 0;

    public string $notes = '';

    protected function refreshAfterBranchSwitch(): void
    {
        $this->reset(['to_branch_id', 'product_id', 'batch_id', 'quantity', 'notes']);
    }

    public function updatedProductId(): void
    {
        $this->batch_id = null;
    }

    public function save(StockTransferService $service, BranchContext $branchContext): void
    {
        $validated = $this->validate([
            'to_branch_id' => ['required', 'exists:branches,id'],
            'product_id' => ['required', 'exists:products,id'],
            'batch_id' => ['required', 'exists:batches,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'notes' => ['nullable', 'string'],
        ]);

        $fromBranch = $branchContext->activeBranch();

        if ($fromBranch === null) {
            $this->addError('product_id', 'No active branch selected.');

            return;
        }

        $toBranch = Branch::query()->findOrFail($validated['to_branch_id']);
        $product = Product::query()->findOrFail($validated['product_id']);
        $batch = Batch::query()->findOrFail($validated['batch_id']);

        $service->initiate(
            fromBranch: $fromBranch,
            toBranch: $toBranch,
            product: $product,
            batch: $batch,
            quantity: $validated['quantity'],
            initiatedBy: auth()->user(),
            notes: $validated['notes'] ?: null,
        );

        $this->reset(['to_branch_id', 'quantity', 'notes']);
        session()->flash('success', 'Stock transferred successfully.');
    }

    public function render(BranchContext $branchContext): View
    {
        $activeBranchId = $branchContext->activeBranchId();

        $batches = $this->product_id
            ? Batch::query()->where('product_id', $this->product_id)->orderBy('expiry_date')->get()
            : collect();

        return view('livewire.inventory.stock-transfer', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
            'branches' => Branch::query()
                ->where('is_active', true)
                ->when($activeBranchId, fn ($q) => $q->where('id', '!=', $activeBranchId))
                ->orderBy('name')
                ->get(),
            'batches' => $batches,
            'fromBranch' => $branchContext->activeBranch(),
        ])->layout('layouts.pharmacy', [
            'title' => 'Stock Transfers',
            'nav' => 'inventory',
        ]);
    }
}
