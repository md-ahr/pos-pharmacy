<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Tax Report</h1>
                <p class="page-description">Tax collected on completed sales for the selected period.</p>
            </div>
        </div>
    </div>

    @include('livewire.reports.partials.filters', ['showProduct' => false])

    <div class="stats-grid" style="margin-bottom: 1rem;">
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Sales</div><div class="stat-value">{{ $summary['sales_count'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Taxable Sales</div><div class="stat-value">{{ $summary['taxable_sales'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Tax Collected</div><div class="stat-value">{{ $summary['tax_total'] }}</div></div></div>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Daily Breakdown</h2></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Sales</th>
                        <th style="text-align: right;">Taxable</th>
                        <th style="text-align: right;">Tax</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($dailyBreakdown as $row)
                        <tr>
                            <td>{{ $row->period }}</td>
                            <td>{{ $row->sales_count }}</td>
                            <td style="text-align: right;">{{ $row->taxable_sales }}</td>
                            <td style="text-align: right;">{{ $row->tax_total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No tax data in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
