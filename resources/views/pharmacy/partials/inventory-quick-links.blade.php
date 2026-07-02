@php
    $links = [
        [
            'route' => 'pharmacy.inventory.products',
            'title' => 'Products',
            'description' => 'Manage catalog, pricing, units, and reorder levels.',
            'variant' => 'primary',
        ],
        [
            'route' => 'pharmacy.inventory.categories',
            'title' => 'Categories',
            'description' => 'Organize products into pharmacy categories.',
            'variant' => 'info',
        ],
        [
            'route' => 'pharmacy.inventory.manufacturers',
            'title' => 'Manufacturers',
            'description' => 'Maintain manufacturer records for products.',
            'variant' => 'info',
        ],
        [
            'route' => 'pharmacy.inventory.batch-intake',
            'title' => 'Batch Intake',
            'description' => 'Receive stock with batch numbers and expiry dates.',
            'variant' => 'success',
        ],
        [
            'route' => 'pharmacy.inventory.purchase-orders',
            'title' => 'Purchase Orders',
            'description' => 'Create and receive supplier purchase orders.',
            'variant' => 'warning',
        ],
        [
            'route' => 'pharmacy.inventory.suppliers',
            'title' => 'Suppliers',
            'description' => 'Manage vendor contacts and supply relationships.',
            'variant' => 'info',
        ],
        [
            'route' => 'pharmacy.inventory.adjustments',
            'title' => 'Stock Adjustments',
            'description' => 'Correct on-hand quantities with audit trail.',
            'variant' => 'danger',
        ],
        [
            'route' => 'pharmacy.inventory.transfers',
            'title' => 'Stock Transfers',
            'description' => 'Move inventory between branches.',
            'variant' => 'primary',
        ],
    ];
@endphp

<x-tyro-dashboard::card title="Inventory Tasks" description="Jump to common stock management workflows.">
    <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));">
        @foreach ($links as $link)
            <a
                href="{{ route($link['route']) }}"
                class="card stat-card"
                style="text-decoration: none; color: inherit;"
            >
                <div class="card-body" style="display: flex; flex-direction: column; gap: 0.5rem; height: 100%;">
                    <div style="display: flex; align-items: flex-start; justify-content: space-between; gap: 0.75rem;">
                        <h3 style="margin: 0; font-size: 0.9375rem; font-weight: 600; color: var(--foreground);">{{ $link['title'] }}</h3>
                        <span class="stat-icon stat-icon-{{ $link['variant'] }}" style="width: 36px; height: 36px; margin: 0; flex-shrink: 0;">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" aria-hidden="true" style="width: 18px; height: 18px;">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25v3.75M14 11.25v3.75M5.25 7.5h13.5M9.75 7.5V5.625A2.25 2.25 0 0112 3.75c1.243 0 2.25 1.007 2.25 2.25V7.5"/>
                            </svg>
                        </span>
                    </div>
                    <p style="margin: 0; font-size: 0.8125rem; line-height: 1.5; color: var(--muted-foreground); flex: 1;">{{ $link['description'] }}</p>
                    <span class="btn btn-ghost btn-sm" style="align-self: flex-start; pointer-events: none;">Open</span>
                </div>
            </a>
        @endforeach
    </div>
</x-tyro-dashboard::card>
