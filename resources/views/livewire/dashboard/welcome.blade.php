<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Welcome back, {{ $user->name }}!</h1>
                <p class="page-description">
                    Today's snapshot for {{ $branchName ?? 'your branch' }}.
                    @if ($shiftSummary['shift'])
                        Register shift open since @displayTime($shiftSummary['shift']->opened_at) — expected cash {{ $shiftSummary['expected_cash'] }}.
                    @else
                        No register shift is open for the active branch.
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
            label="Today's Sales"
            :value="(string) $summary['sales_count']"
            variant="primary"
        />
        <x-tyro-dashboard::stat
            label="Today's Revenue"
            :value="$summary['sales_total']"
            variant="success"
        />
        <x-tyro-dashboard::stat
            label="Today's Tax"
            :value="$summary['tax_total']"
            variant="info"
        />
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
    </div>

    @include('livewire.dashboard.partials.charts')

    <div class="grid-2" style="margin-bottom: 1rem;">
        <livewire:dashboard.low-stock-widget />
        <livewire:dashboard.near-expiry-widget />
    </div>

    @if ($quickActions !== [])
        <x-tyro-dashboard::card title="Quick Actions">
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                @foreach ($quickActions as $action)
                    <a href="{{ route($action['route']) }}" class="btn btn-{{ $action['variant'] }}">
                        {{ $action['label'] }}
                    </a>
                @endforeach
            </div>
        </x-tyro-dashboard::card>
    @endif
</div>
