<?php

namespace App\Livewire\Dashboard;

use App\Livewire\Concerns\ListensForBranchSwitch;
use App\Models\Product;
use App\Models\Stock;
use App\Models\User;
use App\Services\BranchContext;
use App\Services\RegisterShiftService;
use App\Services\Reports\DashboardMetricsService;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class Welcome extends Component
{
    use ListensForBranchSwitch;

    public function render(
        BranchContext $branchContext,
        DashboardMetricsService $metrics,
        RegisterShiftService $shiftService,
    ): View {
        $user = auth()->user();
        $branch = $branchContext->activeBranch();
        $branchId = $branchContext->activeBranchId();
        $shiftSummary = $branch !== null
            ? $shiftService->currentShiftSummary($branch)
            : ['shift' => null, 'expected_cash' => '0.00'];

        return view('livewire.dashboard.welcome', [
            'user' => $user,
            'summary' => $metrics->todaySummary($branchId),
            'totals' => $metrics->totalsSummary($branchId),
            'charts' => $metrics->charts($branchId),
            'branchName' => $branch?->name,
            'shiftSummary' => $shiftSummary,
            'lowStockCount' => $this->lowStockCount($branchId),
            'nearExpiryCount' => $this->nearExpiryCount($branchId),
            'quickActions' => $user instanceof User ? $this->quickActionsFor($user) : [],
        ])->layout('layouts.pharmacy', [
            'title' => 'Dashboard',
        ]);
    }

    private function lowStockCount(?int $branchId): int
    {
        if ($branchId === null) {
            return 0;
        }

        return Product::query()
            ->where('is_active', true)
            ->where('reorder_level', '>', 0)
            ->withSum(['stock as branch_stock' => fn ($query) => $query->where('branch_id', $branchId)], 'quantity')
            ->get()
            ->filter(fn (Product $product) => (int) ($product->branch_stock ?? 0) <= $product->reorder_level)
            ->count();
    }

    private function nearExpiryCount(?int $branchId, int $daysAhead = 90): int
    {
        if ($branchId === null) {
            return 0;
        }

        return Stock::query()
            ->where('branch_id', $branchId)
            ->where('quantity', '>', 0)
            ->whereHas('batch', function ($query) use ($daysAhead): void {
                $query->whereBetween('expiry_date', [today(), today()->addDays($daysAhead)]);
            })
            ->count();
    }

    /**
     * @return list<array{label: string, route: string, variant: string}>
     */
    private function quickActionsFor(User $user): array
    {
        $actions = [];

        foreach ($this->quickActionDefinitions() as $action) {
            if (! Route::has($action['route'])) {
                continue;
            }

            if (! method_exists($user, 'hasPrivilege') || ! $user->hasPrivilege($action['privilege'])) {
                continue;
            }

            $actions[] = [
                'label' => $action['label'],
                'route' => $action['route'],
                'variant' => $action['variant'],
            ];
        }

        return $actions;
    }

    /**
     * @return list<array{label: string, route: string, privilege: string, variant: string}>
     */
    private function quickActionDefinitions(): array
    {
        return [
            [
                'label' => 'Open POS',
                'route' => 'pharmacy.pos',
                'privilege' => 'pos.access',
                'variant' => 'primary',
            ],
            [
                'label' => 'Inventory',
                'route' => 'pharmacy.inventory',
                'privilege' => 'inventory.manage',
                'variant' => 'secondary',
            ],
            [
                'label' => 'Reports',
                'route' => 'pharmacy.reports',
                'privilege' => 'reports.view',
                'variant' => 'secondary',
            ],
            [
                'label' => 'Settings',
                'route' => 'pharmacy.settings.general',
                'privilege' => 'settings.manage',
                'variant' => 'secondary',
            ],
        ];
    }
}
