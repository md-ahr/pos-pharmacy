<?php

namespace App\Livewire\Reports\Concerns;

use App\Data\ReportFilters;
use App\Models\Branch;
use App\Models\Product;
use App\Models\User;
use App\Services\BranchContext;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;

trait InteractsWithReportFilters
{
    public string $from = '';

    public string $to = '';

    public string $branchId = '';

    public string $cashierId = '';

    public string $productId = '';

    public int $expiryDaysAhead = 90;

    public function mountReportFilters(): void
    {
        $this->from = today()->startOfMonth()->toDateString();
        $this->to = today()->toDateString();
        $this->syncActiveBranchFilter();
    }

    #[On('branch-switched')]
    public function syncActiveBranchFilter(): void
    {
        $activeBranchId = app(BranchContext::class)->activeBranchId();
        $this->branchId = $activeBranchId !== null ? (string) $activeBranchId : '';

        if (method_exists($this, 'resetPage')) {
            $this->resetPage();
        }
    }

    public function resetReportFilters(): void
    {
        $this->mountReportFilters();
        $this->cashierId = '';
        $this->productId = '';
        $this->expiryDaysAhead = 90;
    }

    protected function reportFilters(): ReportFilters
    {
        return ReportFilters::fromArray([
            'branch_id' => $this->branchId,
            'cashier_id' => $this->cashierId,
            'product_id' => $this->productId,
            'from' => $this->from,
            'to' => $this->to,
            'expiry_days_ahead' => $this->expiryDaysAhead,
        ]);
    }

    /**
     * @return Collection<int, Branch>
     */
    protected function branchOptions(): Collection
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

    /**
     * @return Collection<int, User>
     */
    protected function cashierOptions(): Collection
    {
        $tenantId = auth()->user()?->tenant_id;

        if ($tenantId === null) {
            return collect();
        }

        return User::query()
            ->where('tenant_id', $tenantId)
            ->where('is_active', true)
            ->orderBy('name')
            ->get();
    }

    /**
     * @return Collection<int, Product>
     */
    protected function productOptions(): Collection
    {
        return Product::query()
            ->where('is_active', true)
            ->orderBy('name')
            ->get(['id', 'name']);
    }

    /**
     * @return array<string, mixed>
     */
    protected function reportLayoutData(string $title): array
    {
        return [
            'title' => $title,
            'nav' => 'reports',
        ];
    }
}
