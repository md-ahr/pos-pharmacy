@php
    $colors = [
        'cash' => 'var(--success)',
        'card' => 'var(--primary)',
        'mobile' => 'var(--info)',
        'other' => 'var(--muted-foreground)',
    ];

    $rows = collect($paymentTotals)
        ->map(fn (string $total, string $method): array => [
            'label' => ucfirst($method),
            'amount' => (float) $total,
            'color' => $colors[$method] ?? 'var(--chart-1)',
        ])
        ->filter(fn (array $row): bool => $row['amount'] > 0)
        ->values();

    $grandTotal = (float) $rows->sum('amount');
@endphp

<x-tyro-dashboard::card title="Today's Payment Totals">
    @if ($rows->isEmpty())
        <p class="text-muted" style="margin: 0;">No payments recorded today.</p>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.875rem;">
            @foreach ($rows as $row)
                @php($pct = $grandTotal > 0 ? (int) round(($row['amount'] / $grandTotal) * 100) : 0)
                <div>
                    <div style="display: flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.5rem;">
                        <div style="display: flex; align-items: center; gap: 0.5rem; min-width: 0;">
                            <span style="width: 10px; height: 10px; border-radius: 9999px; background: {{ $row['color'] }}; display: inline-block; flex-shrink: 0;"></span>
                            <span style="font-weight: 600; color: var(--foreground);">{{ $row['label'] }}</span>
                        </div>
                        <span style="font-size: 0.875rem; font-weight: 600; color: var(--foreground); flex-shrink: 0;">
                            {{ number_format($row['amount'], 2, '.', '') }} ({{ $pct }}%)
                        </span>
                    </div>
                    <div style="height: 10px; width: 100%; background: var(--muted); border-radius: 9999px; overflow: hidden; border: 1px solid var(--border);">
                        <div style="height: 100%; width: {{ max($pct, 4) }}%; background: {{ $row['color'] }};"></div>
                    </div>
                </div>
            @endforeach
        </div>
        <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); display: flex; justify-content: space-between;">
            <span style="font-size: 0.875rem; color: var(--muted-foreground);">Total</span>
            <strong style="font-size: 0.9375rem; color: var(--foreground);">{{ number_format($grandTotal, 2, '.', '') }}</strong>
        </div>
    @endif
</x-tyro-dashboard::card>
