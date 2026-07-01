<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Reports Dashboard</h1>
                <p class="page-description">
                    Today's performance for {{ $branchName ?? 'all branches' }}.
                    @if($shiftSummary['shift'])
                        Register shift open since {{ $shiftSummary['shift']->opened_at->format('H:i') }} — expected cash {{ $shiftSummary['expected_cash'] }}.
                    @else
                        No register shift is open for the active branch.
                    @endif
                </p>
            </div>
        </div>
    </div>

    <div class="stats-grid" style="margin-bottom: 1rem;">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Today's Sales</div>
                <div class="stat-value">{{ $summary['sales_count'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Today's Revenue</div>
                <div class="stat-value">{{ $summary['sales_total'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Today's Tax</div>
                <div class="stat-value">{{ $summary['tax_total'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Today's Discounts</div>
                <div class="stat-value">{{ $summary['discount_total'] }}</div>
            </div>
        </div>
    </div>

    <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1rem;">
        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Today's Payment Totals</h2>
            </div>
            <div class="card-body">
                <ul style="margin: 0; padding-left: 1.25rem;">
                    @foreach($summary['payment_totals'] as $method => $total)
                        <li><strong>{{ ucfirst($method) }}</strong> — {{ $total }}</li>
                    @endforeach
                </ul>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Top Products Today</h2>
            </div>
            <div class="card-body">
                @if($topProducts->isEmpty())
                    <p class="text-muted">No sales recorded today.</p>
                @else
                    <ul style="margin: 0; padding-left: 1.25rem;">
                        @foreach($topProducts as $product)
                            <li>
                                <strong>{{ $product->product_name }}</strong>
                                — {{ $product->quantity_sold }} sold ({{ $product->revenue }})
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>

    <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1rem;">
        <livewire:dashboard.low-stock-widget />
        <livewire:dashboard.near-expiry-widget />
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Detailed Reports</h2>
        </div>
        <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('pharmacy.reports.sales') }}" class="btn btn-primary">Sales Report</a>
            <a href="{{ route('pharmacy.reports.profit-margin') }}" class="btn btn-secondary">Profit Margin</a>
            <a href="{{ route('pharmacy.reports.inventory-valuation') }}" class="btn btn-secondary">Inventory Valuation</a>
            <a href="{{ route('pharmacy.reports.expiry') }}" class="btn btn-secondary">Expiry Report</a>
            <a href="{{ route('pharmacy.reports.tax') }}" class="btn btn-secondary">Tax Report</a>
        </div>
    </div>
</div>
