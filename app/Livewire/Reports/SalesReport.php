<?php

namespace App\Livewire\Reports;

use App\Livewire\Reports\Concerns\InteractsWithReportFilters;
use App\Services\Reports\SalesReportService;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class SalesReport extends Component
{
    use InteractsWithReportFilters;
    use WithPagination;

    public function mount(): void
    {
        $this->mountReportFilters();
    }

    public function updatedFrom(): void
    {
        $this->resetPage();
    }

    public function updatedTo(): void
    {
        $this->resetPage();
    }

    public function updatedBranchId(): void
    {
        $this->resetPage();
    }

    public function updatedCashierId(): void
    {
        $this->resetPage();
    }

    public function updatedProductId(): void
    {
        $this->resetPage();
    }

    public function render(SalesReportService $salesReport): View
    {
        $filters = $this->reportFilters();

        return view('livewire.reports.sales-report', [
            'summary' => $salesReport->summary($filters),
            'sales' => $salesReport->paginatedSales($filters),
            'productBreakdown' => $salesReport->productBreakdown($filters),
            'branches' => $this->branchOptions(),
            'cashiers' => $this->cashierOptions(),
            'products' => $this->productOptions(),
            'exportQuery' => http_build_query(array_merge($filters->toArray(), ['type' => 'sales'])),
        ])->layout('layouts.pharmacy', $this->reportLayoutData('Sales Report'));
    }
}
