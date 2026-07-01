<?php

namespace App\Livewire\Customers;

use App\Enums\SaleStatus;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CustomerShow extends Component
{
    use WithPagination;

    public Customer $customer;

    public function mount(Customer $customer): void
    {
        $this->customer = $customer;
    }

    public function render(): View
    {
        $sales = $this->customer->sales()
            ->with(['branch', 'cashier'])
            ->whereIn('status', [
                SaleStatus::Completed,
                SaleStatus::Refunded,
                SaleStatus::PartiallyRefunded,
                SaleStatus::Held,
            ])
            ->latest('sold_at')
            ->latest('id')
            ->paginate(15);

        $totalSpent = $this->customer->sales()
            ->where('status', SaleStatus::Completed)
            ->sum('total');

        return view('livewire.customers.customer-show', [
            'sales' => $sales,
            'totalSpent' => number_format((float) $totalSpent, 2, '.', ''),
        ])->layout('layouts.pharmacy', [
            'title' => $this->customer->displayName(),
            'nav' => 'customers',
        ]);
    }
}
