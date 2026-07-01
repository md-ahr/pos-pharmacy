<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Suppliers</h1>
            </div>
            <a href="{{ route('pharmacy.inventory.suppliers.create') }}" class="btn btn-primary">Add Supplier</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Search suppliers...">
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Contact</th>
                        <th>Phone</th>
                        <th>Email</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                        <tr>
                            <td>{{ $supplier->name }}</td>
                            <td>{{ $supplier->contact_name ?? '—' }}</td>
                            <td>{{ $supplier->phone ?? '—' }}</td>
                            <td>{{ $supplier->email ?? '—' }}</td>
                            <td>
                                @if($supplier->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('pharmacy.inventory.suppliers.edit', $supplier) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="text-muted">No suppliers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="card-body">{{ $suppliers->links() }}</div>
        @endif
    </div>
</div>
