<?php

namespace App\Livewire\Reports;

use App\Livewire\Reports\Concerns\InteractsWithReportFilters;
use App\Services\Reports\TaxReportService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class TaxReport extends Component
{
    use InteractsWithReportFilters;

    public function mount(): void
    {
        $this->mountReportFilters();
    }

    public function render(TaxReportService $taxReport): View
    {
        $filters = $this->reportFilters();

        return view('livewire.reports.tax-report', [
            'summary' => $taxReport->summary($filters),
            'dailyBreakdown' => $taxReport->dailyBreakdown($filters),
            'branches' => $this->branchOptions(),
            'cashiers' => $this->cashierOptions(),
            'exportQuery' => http_build_query(array_merge($filters->toArray(), ['type' => 'tax'])),
        ])->layout('layouts.pharmacy', $this->reportLayoutData('Tax Report'));
    }
}
