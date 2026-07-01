<div class="card">
    <div class="card-header">
        <h2 class="card-title">Expiring Within {{ $daysAhead }} Days</h2>
    </div>
    <div class="card-body">
        @if($items->isEmpty())
            <p class="text-muted">No batches nearing expiry.</p>
        @else
            <ul style="margin: 0; padding-left: 1.25rem;">
                @foreach($items as $stock)
                    <li>
                        <strong>{{ $stock->product->name }}</strong>
                        — Batch {{ $stock->batch->batch_no }}
                        ({{ $stock->quantity }} {{ $stock->product->base_unit }}, exp {{ $stock->batch->expiry_date->format('Y-m-d') }})
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
</div>
