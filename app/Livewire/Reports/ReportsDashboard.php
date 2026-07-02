<?php

namespace App\Livewire\Reports;

use App\Livewire\Concerns\ListensForBranchSwitch;
use App\Services\BranchContext;
use App\Services\RegisterShiftService;
use App\Services\Reports\DashboardMetricsService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ReportsDashboard extends Component
{
    use ListensForBranchSwitch;

    public function render(
        BranchContext $branchContext,
        DashboardMetricsService $metrics,
        RegisterShiftService $shiftService,
    ): View {
        $branch = $branchContext->activeBranch();
        $branchId = $branchContext->activeBranchId();
        $shiftSummary = $branch !== null
            ? $shiftService->currentShiftSummary($branch)
            : ['shift' => null, 'expected_cash' => '0.00'];

        return view('livewire.reports.reports-dashboard', [
            'summary' => $metrics->todaySummary($branchId),
            'topProducts' => $metrics->topProducts($branchId),
            'charts' => $metrics->charts($branchId),
            'branchName' => $branch?->name,
            'shiftSummary' => $shiftSummary,
        ])->layout('layouts.pharmacy', [
            'title' => 'Reports Dashboard',
            'nav' => 'reports',
        ]);
    }
}
