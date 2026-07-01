<?php

namespace App\Livewire\Reports;

use App\Models\Branch;
use App\Services\BranchContext;
use App\Services\Reports\InventoryValuationReportService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Livewire\Component;

class InventoryValuationReport extends Component
{
    public string $branchId = '';

    public function mount(): void
    {
        $activeBranchId = app(BranchContext::class)->activeBranchId();
        $this->branchId = $activeBranchId !== null ? (string) $activeBranchId : '';
    }

    public function render(
        BranchContext $branchContext,
        InventoryValuationReportService $valuationReport,
    ): View {
        $branchId = $this->branchId !== '' ? (int) $this->branchId : null;

        return view('livewire.reports.inventory-valuation-report', [
            'summary' => $valuationReport->summary($branchId),
            'rows' => $valuationReport->rows($branchId),
            'branches' => $this->branchOptions($branchContext),
            'exportQuery' => http_build_query([
                'type' => 'inventory-valuation',
                'branch_id' => $branchId,
            ]),
        ])->layout('layouts.pharmacy', [
            'title' => 'Inventory Valuation',
            'nav' => 'reports',
        ]);
    }

    /**
     * @return Collection<int, Branch>
     */
    protected function branchOptions(BranchContext $branchContext): Collection
    {
        $tenantId = auth()->user()?->tenant_id;

        if ($tenantId === null) {
            return collect();
        }

        return Branch::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();
    }
}
