<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Categories</h1>
                <p class="page-description">Organize products into categories for inventory and reporting.</p>
            </div>
            <a href="{{ route('pharmacy.inventory.categories.create') }}" class="btn btn-primary">Add Category</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Search categories...">
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Parent</th>
                        <th>Products</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->parent?->name ?? '—' }}</td>
                            <td>{{ $category->products_count }}</td>
                            <td style="text-align: right;">
                                <a href="{{ route('pharmacy.inventory.categories.edit', $category) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No categories found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($categories->hasPages())
            <div class="card-body">{{ $categories->links() }}</div>
        @endif
    </div>
</div>
