<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Products</h1>
                <p class="page-description">Manage pharmacy products, units, and reorder levels.</p>
            </div>
            <a href="{{ route('pharmacy.inventory.products.create') }}" class="btn btn-primary">Add Product</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <input type="text" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Search by name, SKU, generic name, or barcode...">
        </div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>SKU</th>
                        <th>Category</th>
                        <th>Cost</th>
                        <th>Sell Price</th>
                        <th>Base Unit</th>
                        <th>Reorder</th>
                        <th>Status</th>
                        <th style="text-align: right;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($products as $product)
                        @php
                            $defaultUnit = $product->units->firstWhere('is_default', true) ?? $product->units->first();
                            $latestBatch = $product->batches->sortByDesc(fn ($batch) => $batch->received_at?->getTimestamp() ?? 0)->first();
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $product->name }}</strong>
                                @if($product->generic_name)
                                    <div class="text-muted" style="font-size: 0.875rem;">{{ $product->generic_name }}</div>
                                @endif
                            </td>
                            <td>{{ $product->sku ?? '—' }}</td>
                            <td>{{ $product->category?->name ?? '—' }}</td>
                            <td>{{ $latestBatch ? number_format((float) $latestBatch->cost_price, 2) : '—' }}</td>
                            <td>{{ $defaultUnit?->selling_price !== null ? number_format((float) $defaultUnit->selling_price, 2) : '—' }}</td>
                            <td>{{ $product->base_unit }}</td>
                            <td>{{ $product->reorder_level }}</td>
                            <td>
                                @if($product->is_active)
                                    <span class="badge badge-success">Active</span>
                                @else
                                    <span class="badge badge-secondary">Inactive</span>
                                @endif
                            </td>
                            <td style="text-align: right;">
                                <a href="{{ route('pharmacy.inventory.products.edit', $product) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-muted">No products found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($products->hasPages())
            <div class="card-body">{{ $products->links() }}</div>
        @endif
    </div>
</div>
