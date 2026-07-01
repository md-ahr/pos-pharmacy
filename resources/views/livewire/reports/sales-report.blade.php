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

    <div class="stats-grid" style="margin-bottom: 1rem;">
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Sales</div><div class="stat-value">{{ $summary['sales_count'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Subtotal</div><div class="stat-value">{{ $summary['subtotal'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Discounts</div><div class="stat-value">{{ $summary['discount_total'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Tax</div><div class="stat-value">{{ $summary['tax_total'] }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Total</div><div class="stat-value">{{ $summary['total'] }}</div></div></div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-header"><h2 class="card-title">Product Breakdown</h2></div>
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
                    @forelse($productBreakdown as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->quantity_sold }}</td>
                            <td style="text-align: right;">{{ $row->revenue }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Sales</h2></div>
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
                    @forelse($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}">
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->sold_at?->format('Y-m-d H:i') }}</td>
                            <td>{{ $sale->branch->name }}</td>
                            <td>{{ $sale->cashier->name }}</td>
                            <td>{{ $sale->customer?->displayName() ?? 'Walk-in' }}</td>
                            <td style="text-align: right;">{{ $sale->total }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No sales in this period.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
            <div class="card-body">{{ $sales->links() }}</div>
        @endif
    </div>
</div>
