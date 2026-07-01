<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class CategoriesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $categories = Category::query()
            ->with('parent')
            ->withCount('products')
            ->when($this->search !== '', function ($query): void {
                $term = '%'.strtolower($this->search).'%';
                $query->whereRaw('LOWER(name) LIKE ?', [$term]);
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.inventory.categories-index', [
            'categories' => $categories,
        ])->layout('layouts.pharmacy', [
            'title' => 'Categories',
            'nav' => 'inventory',
        ]);
    }
}
