<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Sales Report</h1>
                <p class="page-description">Completed sales filtered by date range, branch, cashier, and product.</p>
            </div>
        </div>
    </div>

    @include('livewire.reports.partials.filters', ['showProduct' => true])

    <div class="stats-grid">
        <x-tyro-dashboard::stat label="Sales" :value="(string) $summary['sales_count']" variant="primary" />
        <x-tyro-dashboard::stat label="Subtotal" :value="$summary['subtotal']" variant="info" />
        <x-tyro-dashboard::stat label="Discounts" :value="$summary['discount_total']" variant="warning" />
        <x-tyro-dashboard::stat label="Tax" :value="$summary['tax_total']" variant="success" />
        <x-tyro-dashboard::stat label="Total" :value="$summary['total']" variant="primary" />
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-header">
            <h2 class="card-title">Product Breakdown</h2>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Qty Sold (base)</th>
                        <th style="text-align: right;">Revenue</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($productBreakdown as $row)
                        <tr>
                            <td style="font-weight: 500;">{{ $row->product_name }}</td>
                            <td>{{ number_format((int) $row->quantity_sold) }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums;">{{ $row->revenue }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3">
                                <div class="empty-state" style="padding: 2rem 1rem;">
                                    <div class="empty-state-title">No sales in this period</div>
                                    <p class="empty-state-description" style="margin-bottom: 0;">Adjust the filters above to broaden your date range.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Sales</h2>
            <p class="page-description" style="margin: 0.25rem 0 0;">Invoice-level detail for the selected filters.</p>
        </div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Cashier</th>
                        <th>Customer</th>
                        <th style="text-align: right;">Total</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}">
                            <td class="font-mono">{{ $sale->invoice_no }}</td>
                            <td style="white-space: nowrap;">@displayDatetime($sale->sold_at)</td>
                            <td>{{ $sale->branch->name }}</td>
                            <td>{{ $sale->cashier->name }}</td>
                            <td>{{ $sale->customer?->displayName() ?? 'Walk-in' }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums; font-weight: 600;">{{ $sale->total }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state" style="padding: 2rem 1rem;">
                                    <div class="empty-state-title">No sales in this period</div>
                                    <p class="empty-state-description" style="margin-bottom: 0;">Try widening the date range or clearing branch and cashier filters.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($sales->hasPages())
            <div class="card-footer">{{ $sales->links() }}</div>
        @endif
    </div>
</div>
