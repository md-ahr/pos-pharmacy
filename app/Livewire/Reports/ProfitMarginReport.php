<?php

namespace App\Livewire\Reports;

use App\Livewire\Reports\Concerns\InteractsWithReportFilters;
use App\Services\Reports\ProfitMarginReportService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ProfitMarginReport extends Component
{
    use InteractsWithReportFilters;

    public function mount(): void
    {
        $this->mountReportFilters();
    }

    public function render(ProfitMarginReportService $profitMarginReport): View
    {
        $filters = $this->reportFilters();

        return view('livewire.reports.profit-margin-report', [
            'summary' => $profitMarginReport->summary($filters),
            'rows' => $profitMarginReport->rows($filters),
            'branches' => $this->branchOptions(),
            'cashiers' => $this->cashierOptions(),
            'products' => $this->productOptions(),
            'exportQuery' => http_build_query(array_merge($filters->toArray(), ['type' => 'profit-margin'])),
        ])->layout('layouts.pharmacy', $this->reportLayoutData('Profit Margin Report'));
    }
}
