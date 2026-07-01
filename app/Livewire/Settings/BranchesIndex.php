<?php

namespace App\Livewire\Settings;

use App\Models\Branch;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class BranchesIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $branches = Branch::query()
            ->when($this->search !== '', function ($query): void {
                $term = '%'.strtolower($this->search).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(code) LIKE ?', [$term]);
                });
            })
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.settings.branches-index', [
            'branches' => $branches,
        ])->layout('layouts.pharmacy', [
            'title' => 'Branches',
            'nav' => 'settings',
        ]);
    }
}
