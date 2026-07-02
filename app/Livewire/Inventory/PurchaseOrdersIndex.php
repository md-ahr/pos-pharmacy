<?php

namespace App\Livewire\Inventory;

use App\Enums\PurchaseOrderStatus;
use App\Livewire\Concerns\ListensForBranchSwitch;
use App\Models\PurchaseOrder;
use App\Services\BranchContext;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrdersIndex extends Component
{
    use ListensForBranchSwitch;
    use WithPagination;

    public string $statusFilter = '';

    protected function refreshAfterBranchSwitch(): void
    {
        $this->resetPage();
    }

    public function render(BranchContext $branchContext): View
    {
        $branchId = $branchContext->activeBranchId();

        $orders = PurchaseOrder::query()
            ->with(['supplier', 'branch'])
            ->when($branchId !== null, fn ($query) => $query->where('branch_id', $branchId))
            ->when($this->statusFilter !== '', fn ($query) => $query->where('status', $this->statusFilter))
            ->latest()
            ->paginate(15);

        return view('livewire.inventory.purchase-orders-index', [
            'orders' => $orders,
            'statuses' => PurchaseOrderStatus::cases(),
        ])->layout('layouts.pharmacy', [
            'title' => 'Purchase Orders',
            'nav' => 'inventory',
        ]);
    }
}
