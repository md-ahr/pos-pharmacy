<?php

namespace App\Livewire\Dashboard;

use App\Models\Product;
use App\Services\BranchContext;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class LowStockWidget extends Component
{
    public function render(BranchContext $branchContext): View
    {
        $branchId = $branchContext->activeBranchId();

        $items = collect();

        if ($branchId !== null) {
            $items = Product::query()
                ->where('is_active', true)
                ->where('reorder_level', '>', 0)
                ->withSum(['stock as branch_stock' => fn ($q) => $q->where('branch_id', $branchId)], 'quantity')
                ->get()
                ->filter(fn (Product $product) => (int) ($product->branch_stock ?? 0) <= $product->reorder_level)
                ->take(8)
                ->values();
        }

        return view('livewire.dashboard.low-stock-widget', [
            'items' => $items,
        ]);
    }
}
