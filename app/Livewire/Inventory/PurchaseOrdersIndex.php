<?php

namespace App\Livewire\Inventory;

use App\Enums\PurchaseOrderStatus;
use App\Models\PurchaseOrder;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class PurchaseOrdersIndex extends Component
{
    use WithPagination;

    public string $statusFilter = '';

    public function render(): View
    {
        $orders = PurchaseOrder::query()
            ->with(['supplier', 'branch'])
            ->when($this->statusFilter !== '', fn ($q) => $q->where('status', $this->statusFilter))
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
