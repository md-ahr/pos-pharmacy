<?php

namespace App\Livewire\Inventory;

use App\Models\Product;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class ProductsIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $products = Product::query()
            ->with(['category', 'manufacturer', 'units'])
            ->when($this->search !== '', function ($query): void {
                $term = '%'.strtolower($this->search).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(generic_name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(sku) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(barcode) LIKE ?', [$term]);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.inventory.products-index', [
            'products' => $products,
        ])->layout('layouts.pharmacy', [
            'title' => 'Products',
            'nav' => 'inventory',
        ]);
    }
}
