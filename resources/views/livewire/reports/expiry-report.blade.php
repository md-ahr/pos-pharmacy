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
        <div class="card-header">
            <h2 class="card-title">Batch Expiry Status</h2>
            <p class="page-description" style="margin: 0.25rem 0 0;">Review batches that need attention before they expire or are sold past expiry.</p>
        </div>
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
                    @forelse ($rows as $row)
                        <tr>
                            <td style="font-weight: 500;">{{ $row->product_name }}</td>
                            <td class="font-mono">{{ $row->batch_no }}</td>
                            <td>{{ $row->branch_name }}</td>
                            <td style="white-space: nowrap;">{{ $row->expiry_date }}</td>
                            <td style="font-variant-numeric: tabular-nums;">{{ $row->quantity }} {{ $row->base_unit }}</td>
                            <td>
                                @if ($row->status === 'expired')
                                    <span class="badge badge-danger">Expired</span>
                                @else
                                    <span class="badge badge-warning">Expiring Soon</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <div class="empty-state" style="padding: 2rem 1rem;">
                                    <div class="empty-state-title">No batches match this criteria</div>
                                    <p class="empty-state-description" style="margin-bottom: 0;">Increase the days-ahead window or clear branch filters to see more results.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
