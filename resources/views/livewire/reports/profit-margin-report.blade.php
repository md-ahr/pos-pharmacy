<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Profit Margin Report</h1>
                <p class="page-description">Revenue versus batch cost price for sold items in the selected period.</p>
            </div>
        </div>
    </div>

    @include('livewire.reports.partials.filters', ['showProduct' => true])

    <div class="stats-grid">
        <x-tyro-dashboard::stat label="Revenue" :value="$summary['revenue']" variant="primary" />
        <x-tyro-dashboard::stat label="Cost" :value="$summary['cost']" variant="warning" />
        <x-tyro-dashboard::stat label="Profit" :value="$summary['profit']" variant="success" />
        <x-tyro-dashboard::stat label="Margin %" :value="$summary['margin_percent'].'%'" variant="info" />
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Product Profitability</h2>
            <p class="page-description" style="margin: 0.25rem 0 0;">Per-product revenue, cost, and margin for sold stock.</p>
        </div>
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
                    @forelse ($rows as $row)
                        <tr>
                            <td style="font-weight: 500;">{{ $row->product_name }}</td>
                            <td>{{ number_format((int) $row->quantity_sold) }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $row->revenue }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $row->cost }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums; font-weight: 600;">{{ $row->profit }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">
                                <span class="badge {{ (float) $row->margin_percent >= 0 ? 'badge-success' : 'badge-danger' }}">
                                    {{ $row->margin_percent }}%
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state" style="padding: 2rem 1rem;">
                                    <div class="empty-state-title">No sold items in this period</div>
                                    <p class="empty-state-description" style="margin-bottom: 0;">Adjust filters to include completed sales with stock deductions.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
