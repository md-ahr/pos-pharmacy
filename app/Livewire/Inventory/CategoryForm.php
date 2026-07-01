<?php

namespace App\Livewire\Inventory;

use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class CategoryForm extends Component
{
    public ?Category $category = null;

    public string $name = '';

    public ?int $parent_id = null;

    public function mount(?Category $category = null): void
    {
        $this->category = $category;

        if ($category === null) {
            return;
        }

        $this->fill([
            'name' => $category->name,
            'parent_id' => $category->parent_id,
        ]);
    }

    public function save(): void
    {
        if ($this->parent_id === 0) {
            $this->parent_id = null;
        }

        $tenantId = auth()->user()?->tenant_id;

        $validated = $this->validate([
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('categories', 'name')->where('tenant_id', $tenantId)->ignore($this->category?->id),
            ],
            'parent_id' => [
                'nullable',
                'integer',
                Rule::exists('categories', 'id'),
                Rule::notIn($this->category ? [$this->category->id] : []),
            ],
        ]);

        if ($this->category === null) {
            Category::query()->create($validated);
        } else {
            $this->category->update($validated);
        }

        session()->flash('success', 'Category saved successfully.');
        $this->redirectRoute('pharmacy.inventory.categories', navigate: true);
    }

    public function render(): View
    {
        $parentOptions = Category::query()
            ->when($this->category !== null, fn ($query) => $query->where('id', '!=', $this->category->id))
            ->orderBy('name')
            ->get();

        return view('livewire.inventory.category-form', [
            'parentOptions' => $parentOptions,
        ])->layout('layouts.pharmacy', [
            'title' => $this->category ? 'Edit Category' : 'Add Category',
            'nav' => 'inventory',
        ]);
    }
}
