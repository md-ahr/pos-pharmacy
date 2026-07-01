<?php

namespace App\Livewire\Dashboard;

use App\Models\Stock;
use App\Services\BranchContext;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class NearExpiryWidget extends Component
{
    public int $daysAhead = 90;

    public function render(BranchContext $branchContext): View
    {
        $branchId = $branchContext->activeBranchId();

        $items = collect();

        if ($branchId !== null) {
            $items = Stock::query()
                ->with(['product', 'batch'])
                ->where('branch_id', $branchId)
                ->where('quantity', '>', 0)
                ->whereHas('batch', function ($query): void {
                    $query->whereBetween('expiry_date', [today(), today()->addDays($this->daysAhead)]);
                })
                ->join('batches', 'stock.batch_id', '=', 'batches.id')
                ->orderBy('batches.expiry_date')
                ->select('stock.*')
                ->limit(8)
                ->get();
        }

        return view('livewire.dashboard.near-expiry-widget', [
            'items' => $items,
            'daysAhead' => $this->daysAhead,
        ]);
    }
}
