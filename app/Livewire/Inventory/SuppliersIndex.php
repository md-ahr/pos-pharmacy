<?php

namespace App\Livewire\Inventory;

use App\Models\Supplier;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SuppliersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $suppliers = Supplier::query()
            ->when($this->search !== '', function ($query): void {
                $term = '%'.strtolower($this->search).'%';
                $query->whereRaw('LOWER(name) LIKE ?', [$term]);
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.inventory.suppliers-index', [
            'suppliers' => $suppliers,
        ])->layout('layouts.pharmacy', [
            'title' => 'Suppliers',
            'nav' => 'inventory',
        ]);
    }
}
