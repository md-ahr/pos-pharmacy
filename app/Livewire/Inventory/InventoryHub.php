<?php

namespace App\Livewire\Inventory;

use App\Livewire\Concerns\ListensForBranchSwitch;
use App\Models\Product;
use App\Models\Stock;
use App\Services\BranchContext;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class InventoryHub extends Component
{
    use ListensForBranchSwitch;

    public function render(BranchContext $branchContext): View
    {
        $branch = $branchContext->activeBranch();
        $branchId = $branchContext->activeBranchId();

        return view('livewire.inventory.inventory-hub', [
            'branchName' => $branch?->name,
            'lowStockCount' => $this->lowStockCount($branchId),
            'nearExpiryCount' => $this->nearExpiryCount($branchId),
            'activeProductCount' => Product::query()->where('is_active', true)->count(),
        ])->layout('layouts.pharmacy', [
            'title' => 'Inventory',
            'nav' => 'inventory',
        ]);
    }

    private function lowStockCount(?int $branchId): int
    {
        if ($branchId === null) {
            return 0;
        }

        return Product::query()
            ->where('is_active', true)
            ->where('reorder_level', '>', 0)
            ->withSum(['stock as branch_stock' => fn ($query) => $query->where('branch_id', $branchId)], 'quantity')
            ->get()
            ->filter(fn (Product $product) => (int) ($product->branch_stock ?? 0) <= $product->reorder_level)
            ->count();
    }

    private function nearExpiryCount(?int $branchId, int $daysAhead = 90): int
    {
        if ($branchId === null) {
            return 0;
        }

        return Stock::query()
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0)
            ->whereHas('batch', function ($query) use ($daysAhead): void {
                $query->whereBetween('expiry_date', [today(), today()->addDays($daysAhead)]);
            })
            ->count();
    }
}
