<div class="grid-2" style="margin-bottom: 1rem;">
    <x-tyro-dashboard::card title="Revenue (Last 14 Days)">
        <div style="display: flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <div style="font-size: 0.875rem; color: var(--muted-foreground);">Total</div>
                <div style="font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; color: var(--foreground);">{{ $charts['revenue_total'] }}</div>
            </div>
            <div class="badge-list">
                @if ($charts['revenue_growth_pct'] >= 0)
                    <span class="badge badge-success">+{{ $charts['revenue_growth_pct'] }}%</span>
                @else
                    <span class="badge badge-warning">{{ $charts['revenue_growth_pct'] }}%</span>
                @endif
            </div>
        </div>

        <div style="border: 1px solid var(--border); border-radius: 10px; padding: 1rem; background: var(--card);">
            <div class="chart-revenue-layout">
                <div style="display: flex; flex-direction: column; justify-content: space-between; height: 180px; padding: 0.25rem 0; font-size: 0.75rem; font-weight: 600; color: var(--foreground); line-height: 1;">
                    @foreach ($charts['revenue_y_ticks'] as $tick)
                        <span>{{ $tick }}</span>
                    @endforeach
                </div>

                <div>
                    <svg viewBox="0 0 600 180" width="100%" height="180" preserveAspectRatio="none" style="display:block;">
                        <g opacity="0.45" stroke="var(--border)" stroke-width="1">
                            <path d="M0 150 H600" fill="none" />
                            <path d="M0 110 H600" fill="none" />
                            <path d="M0 70 H600" fill="none" />
                            <path d="M0 30 H600" fill="none" />
                        </g>
                        <path d="{{ $charts['revenue_area_path'] }}" fill="var(--chart-1)" opacity="0.22"></path>
                        <path d="{{ $charts['revenue_line_path'] }}" fill="none" stroke="var(--chart-1)" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"></path>
                    </svg>
                    <div style="display:flex; justify-content: space-between; margin-top: 0.5rem; font-size: 0.8125rem; font-weight: 500; color: var(--foreground);">
                        <span>{{ $charts['revenue_range_label_left'] }}</span>
                        <span>{{ $charts['revenue_range_label_right'] }}</span>
                    </div>
                </div>
            </div>
        </div>
    </x-tyro-dashboard::card>

    <x-tyro-dashboard::card title="Sales (Last 7 Days)">
        <div style="display:flex; align-items: baseline; justify-content: space-between; gap: 1rem; margin-bottom: 1rem;">
            <div>
                <div style="font-size: 0.875rem; color: var(--muted-foreground);">Total</div>
                <div style="font-size: 1.75rem; font-weight: 700; letter-spacing: -0.02em; color: var(--foreground);">{{ $charts['weekly_total'] }}</div>
            </div>
            <span class="badge badge-primary">7 days</span>
        </div>

        @if ($charts['weekly_bars'] === [])
            <p class="text-muted">No sales in the last 7 days.</p>
        @else
            <div style="border: 1px solid var(--border); border-radius: 10px; padding: 1rem; background: var(--card);">
                <div class="chart-weekly-bars">
                    @foreach ($charts['weekly_bars'] as $bar)
                        <div style="display:flex; flex-direction: column; gap: 0.375rem; align-items: stretch; min-width: 0;">
                            <div class="chart-bar-value" style="min-height: 1.125rem; text-align: center; font-size: 0.8125rem; font-weight: 700; color: var(--foreground); line-height: 1.125rem;">
                                {{ $bar['value'] > 0 ? number_format($bar['value'], 0) : '—' }}
                            </div>
                            <div title="{{ $bar['label'] }}: {{ number_format($bar['value'], 2) }}" style="height: 140px; display:flex; align-items:flex-end;">
                                <div style="width: 100%; height: {{ max($bar['pct'], $bar['value'] > 0 ? 6 : 0) }}%; background: var(--chart-1); border-radius: 8px 8px 4px 4px; min-height: {{ $bar['value'] > 0 ? '6px' : '2px' }}; opacity: {{ $bar['value'] > 0 ? '1' : '0.35' }}; box-shadow: {{ $bar['value'] > 0 ? 'inset 0 1px 0 color-mix(in srgb, white 25%, transparent)' : 'none' }};"></div>
                            </div>
                            <div class="chart-bar-label" style="font-size: 0.8125rem; font-weight: 600; color: var(--foreground); text-align:center;">{{ $bar['label'] }}</div>
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </x-tyro-dashboard::card>
</div>

<div class="grid-2" style="margin-bottom: 1rem;">
    <x-tyro-dashboard::card title="Payment Mix (Last 7 Days)">
        @if ($charts['payment_donut'] === [])
            <p class="text-muted">No payments recorded in the last 7 days.</p>
        @else
            <div class="chart-donut-layout">
                <div style="display:flex; align-items:center; justify-content:center;">
                    <svg viewBox="0 0 42 42" width="132" height="132" style="display:block;">
                        <circle cx="21" cy="21" r="15.915" fill="transparent" stroke="var(--border)" stroke-width="6"></circle>
                        @php($offset = 25)
                        @foreach ($charts['payment_donut'] as $slice)
                            <circle
                                cx="21" cy="21" r="15.915"
                                fill="transparent"
                                stroke="currentColor"
                                stroke-width="6"
                                stroke-dasharray="{{ $slice['pct'] }} {{ 100 - $slice['pct'] }}"
                                stroke-dashoffset="{{ $offset }}"
                                stroke-linecap="round"
                                style="color: {{ $slice['color'] }};"
                            ></circle>
                            @php($offset -= $slice['pct'])
                        @endforeach
                    </svg>
                </div>

                <div>
                    <div style="display:flex; flex-direction:column; gap: 0.625rem;">
                        @foreach ($charts['payment_donut'] as $slice)
                            <div style="display:flex; align-items:center; justify-content: space-between; gap: 1rem;">
                                <div style="display:flex; align-items:center; gap: 0.5rem; min-width: 0;">
                                    <span style="width: 10px; height: 10px; border-radius: 9999px; background: {{ $slice['color'] }}; display:inline-block;"></span>
                                    <span style="font-size: 0.9375rem; color: var(--foreground); white-space: nowrap; overflow:hidden; text-overflow: ellipsis;">{{ $slice['label'] }}</span>
                                </div>
                                <div style="font-size: 0.9375rem; font-weight: 600; color: var(--foreground);">{{ $slice['amount'] }} ({{ $slice['pct'] }}%)</div>
                            </div>
                        @endforeach
                    </div>
                    <div style="margin-top: 1rem; padding-top: 1rem; border-top: 1px solid var(--border); display:flex; justify-content: space-between;">
                        <span style="font-size: 0.875rem; color: var(--muted-foreground);">Total</span>
                        <strong style="font-size: 0.9375rem; color: var(--foreground);">{{ $charts['payment_total'] }}</strong>
                    </div>
                </div>
            </div>
        @endif
    </x-tyro-dashboard::card>

    <x-tyro-dashboard::card title="Top Products Today">
        @if ($charts['top_product_bars'] === [])
            <p class="text-muted">No sales recorded today.</p>
        @else
            <div style="display:flex; flex-direction: column; gap: 0.875rem;">
                @foreach ($charts['top_product_bars'] as $row)
                    <div>
                        <div style="display:flex; justify-content: space-between; gap: 1rem; margin-bottom: 0.5rem;">
                            <div style="font-weight: 600; color: var(--foreground); min-width: 0; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">{{ $row['label'] }}</div>
                            <div style="font-size: 0.875rem; font-weight: 600; color: var(--foreground); flex-shrink: 0;">{{ $row['value'] }}</div>
                        </div>
                        <div style="height: 12px; width: 100%; background: var(--muted); border-radius: 9999px; overflow:hidden; border: 1px solid var(--border);">
                            <div style="height: 100%; width: {{ $row['pct'] }}%; background: {{ $row['color'] }};"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </x-tyro-dashboard::card>
</div>
