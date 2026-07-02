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

    <div class="stats-grid">
        <x-tyro-dashboard::stat label="Sales" :value="(string) $summary['sales_count']" variant="primary" />
        <x-tyro-dashboard::stat label="Taxable Sales" :value="$summary['taxable_sales']" variant="info" />
        <x-tyro-dashboard::stat label="Tax Collected" :value="$summary['tax_total']" variant="success" />
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Daily Breakdown</h2>
            <p class="page-description" style="margin: 0.25rem 0 0;">Taxable sales and tax collected grouped by day.</p>
        </div>
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
                    @forelse ($dailyBreakdown as $row)
                        <tr>
                            <td style="white-space: nowrap;">{{ $row->period }}</td>
                            <td style="font-variant-numeric: tabular-nums;">{{ $row->sales_count }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $row->taxable_sales }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums; font-weight: 600;">{{ $row->tax_total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state" style="padding: 2rem 1rem;">
                                    <div class="empty-state-title">No tax data in this period</div>
                                    <p class="empty-state-description" style="margin-bottom: 0;">Widen the date range to include days with completed sales.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
