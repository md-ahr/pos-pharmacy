<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Reports Dashboard</h1>
                <p class="page-description">
                    Today's performance for {{ $branchName ?? 'all branches' }}.
                    @if ($shiftSummary['shift'])
                        Register shift open since @displayTime($shiftSummary['shift']->opened_at) — expected cash {{ $shiftSummary['expected_cash'] }}.
                    @else
                        No register shift is open for the active branch.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="stats-grid">
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
            label="Today's Discounts"
            :value="$summary['discount_total']"
            variant="warning"
        />
    </div>

    <div class="grid-2" style="margin-bottom: 1rem;">
        @include('livewire.reports.partials.today-payment-mix', ['paymentTotals' => $summary['payment_totals']])
        @include('livewire.reports.partials.today-top-products', ['topProducts' => $topProducts])
    </div>

    @include('livewire.dashboard.partials.charts')

    <div class="grid-2" style="margin-bottom: 1rem;">
        <livewire:dashboard.low-stock-widget />
        <livewire:dashboard.near-expiry-widget />
    </div>

    @include('livewire.reports.partials.report-links')
</div>
