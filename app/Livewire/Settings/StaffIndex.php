<?php

namespace App\Livewire\Settings;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class StaffIndex extends Component
{
    use WithPagination;

    public string $search = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $staff = User::query()
            ->where('tenant_id', Auth::user()?->tenant_id)
            ->when($this->search !== '', function ($query): void {
                $term = '%'.strtolower($this->search).'%';
                $query->where(function ($inner) use ($term): void {
                    $inner->whereRaw('LOWER(name) LIKE ?', [$term])
                        ->orWhereRaw('LOWER(email) LIKE ?', [$term]);
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.settings.staff-index', [
            'staff' => $staff,
        ])->layout('layouts.pharmacy', [
            'title' => 'Staff',
            'nav' => 'settings',
        ]);
    }
}
