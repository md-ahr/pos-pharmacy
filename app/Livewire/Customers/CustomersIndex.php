<?php

namespace App\Livewire\Customers;

use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CustomersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $customers = Customer::query()
            ->when($this->search !== '', function ($query): void {
                $term = '%'.strtolower($this->search).'%';
                $query->where(function ($builder) use ($term): void {
                    $builder->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(phone) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                });
            })
            ->withCount('sales')
            ->orderByRaw('name nulls last')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.customers.customers-index', [
            'customers' => $customers,
        ])->layout('layouts.pharmacy', [
            'title' => 'Customers',
            'nav' => 'customers',
        ]);
    }
}
