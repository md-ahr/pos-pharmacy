@php
    $reports = [
        [
            'route' => 'pharmacy.reports.sales',
            'title' => 'Sales Report',
            'description' => 'Completed sales with product breakdown and invoice detail.',
            'variant' => 'primary',
        ],
        [
            'route' => 'pharmacy.reports.profit-margin',
            'title' => 'Profit Margin',
            'description' => 'Revenue versus batch cost for sold items.',
            'variant' => 'success',
        ],
        [
            'route' => 'pharmacy.reports.inventory-valuation',
            'title' => 'Inventory Valuation',
            'description' => 'On-hand stock valued at batch cost price.',
            'variant' => 'info',
        ],
        [
            'route' => 'pharmacy.reports.expiry',
            'title' => 'Expiry Report',
            'description' => 'Batches expiring soon or already expired.',
            'variant' => 'warning',
        ],
        [
            'route' => 'pharmacy.reports.tax',
            'title' => 'Tax Report',
            'description' => 'Tax collected on completed sales by day.',
            'variant' => 'danger',
        ],
    ];
@endphp

<x-tyro-dashboard::card title="Detailed Reports" description="Open a report for deeper analysis and exports.">
    <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        @foreach ($reports as $report)
            <a
                href="{{ route($report['route']) }}"
                class="card stat-card"
                style="text-decoration: none; color: inherit;"
            >
                <div class="card-body" style="display: flex; flex-direction: column; gap: 0.5rem; height: 100%;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem;">
                        <h3 style="margin: 0; font-size: 0.9375rem; font-weight: 600; color: var(--foreground);">{{ $report['title'] }}</h3>
                        <span class="stat-icon stat-icon-{{ $report['variant'] }}" style="width: 36px; height: 36px; margin: 0; flex-shrink: 0;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width: 18px; height: 18px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-6m3 6V7m3 10v-4M5 21h14a2 2 0 002-2V7l-4-4H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </span>
                    </div>
                    <p style="margin: 0; font-size: 0.8125rem; line-height: 1.5; color: var(--muted-foreground); flex: 1;">{{ $report['description'] }}</p>
                    <span class="btn btn-ghost btn-sm" style="align-self: flex-start; pointer-events: none;">View report</span>
                </div>
            </a>
        @endforeach
    </div>
</x-tyro-dashboard::card>
