<?php

namespace App\Livewire\Dashboard;

use App\Models\Branch;
use App\Services\BranchContext;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class BranchSwitcher extends Component
{
    public ?int $selectedBranchId = null;

    public function mount(BranchContext $branchContext): void
    {
        $user = auth()->user();

        if ($user === null || ! $branchContext->canSwitchBranches($user)) {
            return;
        }

        $this->selectedBranchId = $branchContext->activeBranchId();
    }

    public function updatedSelectedBranchId(?int $value, BranchContext $branchContext): void
    {
        $user = auth()->user();

        if ($user === null || $value === null) {
            return;
        }

        $branchContext->switchBranch($user, $value);
        $this->dispatch('branch-switched');
    }

    public function render(BranchContext $branchContext): View
    {
        $user = auth()->user();

        if ($user === null || ! $branchContext->canSwitchBranches($user)) {
            return view('livewire.dashboard.branch-switcher', [
                'canSwitch' => false,
                'branches' => collect(),
                'activeBranch' => null,
            ]);
        }

        $branches = Branch::query()
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        return view('livewire.dashboard.branch-switcher', [
            'canSwitch' => true,
            'branches' => $branches,
            'activeBranch' => $branchContext->activeBranch(),
        ]);
    }
}
