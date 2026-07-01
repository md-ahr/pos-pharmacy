<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $customer->displayName() }}</h1>
                <p class="page-description">Customer purchase history</p>
            </div>
            <div style="display:flex; gap:0.5rem;">
                <a href="{{ route('pharmacy.customers.edit', $customer) }}" class="btn btn-secondary">Edit</a>
                <a href="{{ route('pharmacy.customers') }}" class="btn btn-ghost">Back</a>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body" style="display:grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap:1rem;">
            <div>
                <div class="text-muted" style="font-size:0.875rem;">Phone</div>
                <strong>{{ $customer->phone ?? '—' }}</strong>
            </div>
            <div>
                <div class="text-muted" style="font-size:0.875rem;">Email</div>
                <strong>{{ $customer->email ?? '—' }}</strong>
            </div>
            <div>
                <div class="text-muted" style="font-size:0.875rem;">Total spent (completed)</div>
                <strong>{{ $totalSpent }}</strong>
            </div>
            @if($customer->address)
                <div style="grid-column: 1 / -1;">
                    <div class="text-muted" style="font-size:0.875rem;">Address</div>
                    <strong>{{ $customer->address }}</strong>
                </div>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-header"><h3 class="card-title">Purchase History</h3></div>
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Invoice</th>
                        <th>Date</th>
                        <th>Branch</th>
                        <th>Cashier</th>
                        <th>Status</th>
                        <th style="text-align: right;">Total</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                        <tr wire:key="sale-{{ $sale->id }}">
                            <td>{{ $sale->invoice_no }}</td>
                            <td>{{ $sale->sold_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td>{{ $sale->branch->name }}</td>
                            <td>{{ $sale->cashier->name }}</td>
                            <td>
                                <span class="badge badge-secondary">{{ ucfirst(str_replace('_', ' ', $sale->status->value)) }}</span>
                            </td>
                            <td style="text-align: right;">{{ number_format((float) $sale->total, 2) }}</td>
                            <td style="text-align: right;">
                                @if($sale->status === \App\Enums\SaleStatus::Completed)
                                    <a href="{{ route('pharmacy.pos.receipt', $sale) }}" target="_blank" class="btn btn-ghost btn-sm">Receipt</a>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No sales recorded for this customer yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
            <div class="card-body">{{ $sales->links() }}</div>
        @endif
    </div>
</div>
