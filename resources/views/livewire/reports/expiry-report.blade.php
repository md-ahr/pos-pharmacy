<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Expiry Report</h1>
                <p class="page-description">Batches expiring soon or already expired with remaining stock.</p>
            </div>
        </div>
    </div>

    @include('livewire.reports.partials.filters', ['showCashier' => false, 'showExpiryDays' => true])

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Batch</th>
                        <th>Branch</th>
                        <th>Expiry</th>
                        <th>Quantity</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->batch_no }}</td>
                            <td>{{ $row->branch_name }}</td>
                            <td>{{ $row->expiry_date }}</td>
                            <td>{{ $row->quantity }} {{ $row->base_unit }}</td>
                            <td>
                                @if($row->status === 'expired')
                                    <span class="badge badge-danger">Expired</span>
                                @else
                                    <span class="badge badge-warning">Expiring Soon</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No batches match this criteria.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
