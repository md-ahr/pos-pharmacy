<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Inventory</h1>
                <p class="page-description">
                    Stock overview and alerts for {{ $branchName ?? 'your branch' }}.
                    @if ($lowStockCount > 0 || $nearExpiryCount > 0)
                        Review items needing attention below.
                    @else
                        No urgent stock issues detected right now.
                    @endif
                </p>
            </div>
            <div>
                <livewire:dashboard.branch-switcher />
            </div>
        </div>
    </div>

    <div class="stats-grid" style="margin-bottom: 1rem;">
        <x-tyro-dashboard::stat
            label="Low Stock Items"
            :value="(string) $lowStockCount"
            variant="warning"
            :change="$lowStockCount > 0 ? 'At or below reorder level' : 'Stock levels healthy'"
        />
        <x-tyro-dashboard::stat
            label="Expiring Batches"
            :value="(string) $nearExpiryCount"
            variant="danger"
            change="Within 90 days"
        />
        <x-tyro-dashboard::stat
            label="Active Products"
            :value="(string) $activeProductCount"
            variant="primary"
            change="In catalog"
        />
    </div>

    <div class="grid-2" style="margin-bottom: 1rem;">
        <livewire:dashboard.low-stock-widget />
        <livewire:dashboard.near-expiry-widget />
    </div>

    @include('pharmacy.partials.inventory-quick-links')
</div>
