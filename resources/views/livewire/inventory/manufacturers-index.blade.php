<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Manufacturers</h1>
                <p class="page-description">Track who makes the products in your catalog.</p>
            </div>
            <a href="{{ route('pharmacy.inventory.manufacturers.create') }}" class="btn btn-primary">Add Manufacturer</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Search manufacturers...">
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Products</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($manufacturers as $manufacturer)
                        <tr>
                            <td>{{ $manufacturer->name }}</td>
                            <td>{{ $manufacturer->products_count }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('pharmacy.inventory.manufacturers.edit', $manufacturer) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No manufacturers found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($manufacturers->hasPages())
            <div class="card-body">{{ $manufacturers->links() }}</div>
        @endif
    </div>
</div>
