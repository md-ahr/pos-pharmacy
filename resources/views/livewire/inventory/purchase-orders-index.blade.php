<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Purchase Orders</h1>
            </div>
            <a href="{{ route('pharmacy.inventory.purchase-orders.create') }}" class="btn btn-primary">New Purchase Order</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <select wire:model.live="statusFilter" class="form-input" style="max-width: 220px;">
                <option value="">All statuses</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}">{{ ucfirst($status->value) }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Reference</th>
                        <th>Supplier</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th>Total</th>
                        <th>Ordered</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                        <tr>
                            <td>{{ $order->reference_no }}</td>
                            <td>{{ $order->supplier->name }}</td>
                            <td>{{ $order->branch->name }}</td>
                            <td><span class="badge badge-secondary">{{ ucfirst($order->status->value) }}</span></td>
                            <td>{{ number_format($order->total_amount, 2) }}</td>
                            <td>{{ $order->ordered_at?->format('Y-m-d') ?? '—' }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('pharmacy.inventory.purchase-orders.edit', $order) }}" class="btn btn-ghost btn-sm">Open</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-muted">No purchase orders found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
            <div class="card-body">{{ $orders->links() }}</div>
        @endif
    </div>
</div>
