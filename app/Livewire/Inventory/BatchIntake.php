<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use App\Services\BranchContext;
use App\Services\StockIntakeService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BatchIntake extends Component
{
    public ?int $product_id = null;

    public string $batch_no = '';

    public string $expiry_date = '';

    public string $cost_price = '';

    public string $selling_price = '';

    public int $quantity = 0;

    public function save(StockIntakeService $stockIntake, BranchContext $branchContext): void
    {
        $validated = $this->validate([
            'product_id' => ['required', 'exists:products,id'],
            'batch_no' => ['required', 'string', 'max:100'],
            'expiry_date' => ['required', 'date', 'after:today'],
            'cost_price' => ['required', 'numeric', 'min:0'],
            'selling_price' => ['required', 'numeric', 'min:0'],
            'quantity' => ['required', 'integer', 'min:1'],
        ]);

        $branch = $branchContext->activeBranch();

        if ($branch === null) {
            $this->addError('product_id', 'No active branch selected.');

            return;
        }

        $product = Product::query()->findOrFail($validated['product_id']);

        $stockIntake->intake(
            branch: $branch,
            product: $product,
            batchNo: $validated['batch_no'],
            expiryDate: now()->parse($validated['expiry_date']),
            costPrice: $validated['cost_price'],
            sellingPrice: $validated['selling_price'],
            quantityBase: $validated['quantity'],
        );

        $this->reset(['batch_no', 'expiry_date', 'cost_price', 'selling_price', 'quantity']);
        session()->flash('success', 'Stock received successfully.');
    }

    public function render(): View
    {
        return view('livewire.inventory.batch-intake', [
            'products' => Product::query()->where('is_active', true)->orderBy('name')->get(),
        ])->layout('layouts.pharmacy', [
            'title' => 'Batch Intake',
            'nav' => 'inventory',
        ]);
    }
}
