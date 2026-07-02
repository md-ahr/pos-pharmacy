@php
    $colors = ['var(--chart-1)', 'var(--chart-2)', 'var(--chart-3)', 'var(--chart-4)', 'var(--chart-5)'];
    $maxRevenue = (float) $topProducts->max(fn (object $product): float => (float) $product->revenue) ?: 1.0;
@endphp

<x-tyro-dashboard::card title="Top Products Today">
    @if ($topProducts->isEmpty())
        <p class="text-muted" style="margin: 0;">No sales recorded today.</p>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.875rem;">
            @foreach ($topProducts as $index => $product)
                @php($pct = (int) round(((float) $product->revenue / $maxRevenue) * 100))
                <div>
                    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.5rem;">
                        <div style="font-weight: 600; color: var(--foreground); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $product->product_name }}
                        </div>
                        <div style="font-size: 0.875rem; font-weight: 600; color: var(--foreground); flex-shrink: 0; text-align: right;">
                            <div>{{ $product->revenue }}</div>
                            <div style="font-size: 0.75rem; font-weight: 500; color: var(--muted-foreground);">{{ $product->quantity_sold }} sold</div>
                        </div>
                    </div>
                    <div style="height: 10px; width: 100%; background: var(--muted); border-radius: 9999px; overflow: hidden; border: 1px solid var(--border);">
                        <div style="height: 100%; width: {{ max($pct, 4) }}%; background: {{ $colors[$index % count($colors)] }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</x-tyro-dashboard::card>
