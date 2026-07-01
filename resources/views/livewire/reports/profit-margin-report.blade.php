<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Profit Margin Report</h1>
                <p class="page-description">Revenue vs batch cost price for sold items.</p>
            </div>
        </div>
    </div>

    @include('livewire.reports.partials.filters', ['showProduct' => true])

    <div class="stats-grid" style="margin-bottom: 1rem;">
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Revenue</div><div class="stat-value">{{ $summary['revenue'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Cost</div><div class="stat-value">{{ $summary['cost'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Profit</div><div class="stat-value">{{ $summary['profit'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Margin %</div><div class="stat-value">{{ $summary['margin_percent'] }}%</div></div></div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty Sold (base)</th>
                        <th style="text-align: right;">Revenue</th>
                        <th style="text-align: right;">Cost</th>
                        <th style="text-align: right;">Profit</th>
                        <th style="text-align: right;">Margin %</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->quantity_sold }}</td>
                            <td style="text-align: right;">{{ $row->revenue }}</td>
                            <td style="text-align: right;">{{ $row->cost }}</td>
                            <td style="text-align: right;">{{ $row->profit }}</td>
                            <td style="text-align: right;">{{ $row->margin_percent }}%</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No sold items in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
