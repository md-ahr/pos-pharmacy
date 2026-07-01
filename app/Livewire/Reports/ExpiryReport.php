<?php

namespace App\Livewire\Reports;

use App\Livewire\Reports\Concerns\InteractsWithReportFilters;
use App\Services\Reports\ExpiryReportService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class ExpiryReport extends Component
{
    use InteractsWithReportFilters;

    public function mount(): void
    {
        $this->mountReportFilters();
    }

    public function render(ExpiryReportService $expiryReport): View
    {
        $filters = $this->reportFilters();

        return view('livewire.reports.expiry-report', [
            'rows' => $expiryReport->rows($filters),
            'branches' => $this->branchOptions(),
            'exportQuery' => http_build_query(array_merge($filters->toArray(), ['type' => 'expiry'])),
        ])->layout('layouts.pharmacy', $this->reportLayoutData('Expiry Report'));
    }
}
