<div class="card">
    <div class="card-header">
        <h2 class="card-title">Low Stock</h2>
    </div>
    <div class="card-body">
        @if($items->isEmpty())
            <p class="text-muted">No products below reorder level.</p>
        @else
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($items as $product)
                    <li>
                        <strong>{{ $product->name }}</strong>
                        — {{ (int) ($product->branch_stock ?? 0) }} on hand
                        (reorder at {{ $product->reorder_level }})
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
