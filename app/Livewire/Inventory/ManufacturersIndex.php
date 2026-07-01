<?php

namespace App\Livewire\Inventory;

use App\Models\Manufacturer;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ManufacturersIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $manufacturers = Manufacturer::query()
            ->withCount('products')
            ->when($this->search !== '', function ($query): void {
                $term = '%'.strtolower($this->search).'%';
                $query->whereRaw('LOWER(name) LIKE ?', [$term]);
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.inventory.manufacturers-index', [
            'manufacturers' => $manufacturers,
        ])->layout('layouts.pharmacy', [
            'title' => 'Manufacturers',
            'nav' => 'inventory',
        ]);
    }
}
