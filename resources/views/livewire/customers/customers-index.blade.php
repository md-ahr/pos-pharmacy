<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Customers</h1>
                <p class="page-description">Manage customer records and view purchase history.</p>
            </div>
            <a href="{{ route('pharmacy.customers.create') }}" class="btn btn-primary">Add Customer</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Search by name, phone, or email...">
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Sales</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                        <tr wire:key="customer-{{ $customer->id }}">
                            <td>{{ $customer->displayName() }}</td>
                            <td>{{ $customer->phone ?? '—' }}</td>
                            <td>{{ $customer->email ?? '—' }}</td>
                            <td>{{ $customer->sales_count }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('pharmacy.customers.show', $customer) }}" class="btn btn-ghost btn-sm">History</a>
                                <a href="{{ route('pharmacy.customers.edit', $customer) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5" class="text-muted">No customers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($customers->hasPages())
            <div class="card-body">{{ $customers->links() }}</div>
        @endif
    </div>
</div>
