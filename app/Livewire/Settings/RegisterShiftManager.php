<?php

namespace App\Livewire\Settings;

use App\Livewire\Concerns\ListensForBranchSwitch;
use App\Models\Branch;
use App\Models\RegisterShift;
use App\Services\BranchContext;
use App\Services\RegisterShiftService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class RegisterShiftManager extends Component
{
    use ListensForBranchSwitch;
    use WithPagination;

    public string $openingFloat = '0.00';

    public string $countedCash = '0.00';

    public string $closeNotes = '';

    protected function refreshAfterBranchSwitch(): void
    {
        $this->resetPage();
        $this->openingFloat = '0.00';
        $this->countedCash = '0.00';
        $this->closeNotes = '';
    }

    public function openShift(RegisterShiftService $shiftService, BranchContext $branchContext): void
    {
        $branch = $this->resolveBranch($branchContext);

        $validated = $this->validate([
            'openingFloat' => ['required', 'numeric', 'min:0'],
        ]);

        $shiftService->openShift($branch, Auth::user(), (string) $validated['openingFloat']);

        $this->openingFloat = '0.00';
        session()->flash('success', 'Register shift opened.');
    }

    public function closeShift(RegisterShiftService $shiftService, BranchContext $branchContext): void
    {
        $branch = $this->resolveBranch($branchContext);
        $shift = $shiftService->openShiftForBranch($branch);

        abort_if($shift === null, 422, 'No open shift found for this branch.');

        $validated = $this->validate([
            'countedCash' => ['required', 'numeric', 'min:0'],
            'closeNotes' => ['nullable', 'string', 'max:1000'],
        ]);

        $shiftService->closeShift(
            $shift,
            Auth::user(),
            (string) $validated['countedCash'],
            $validated['closeNotes'] !== '' ? $validated['closeNotes'] : null,
        );

        $this->countedCash = '0.00';
        $this->closeNotes = '';
        session()->flash('success', 'Register shift closed and reconciled.');
    }

    public function render(RegisterShiftService $shiftService, BranchContext $branchContext): View
    {
        $branch = $this->resolveBranch($branchContext);
        $summary = $shiftService->currentShiftSummary($branch);

        if ($summary['shift'] !== null && $this->countedCash === '0.00') {
            $this->countedCash = $summary['expected_cash'];
        }

        $recentShifts = RegisterShift::query()
            ->with(['openedBy', 'closedBy'])
            ->where('branch_id', $branch->id)
            ->orderByDesc('opened_at')
            ->paginate(10);

        return view('livewire.settings.register-shift-manager', [
            'branch' => $branch,
            'summary' => $summary,
            'recentShifts' => $recentShifts,
        ])->layout('layouts.pharmacy', [
            'title' => 'Register & Shifts',
            'nav' => 'settings',
        ]);
    }

    protected function resolveBranch(BranchContext $branchContext): Branch
    {
        $branch = $branchContext->activeBranch();

        abort_if($branch === null, 422, 'Select a branch before managing the register.');

        return $branch;
    }
}
