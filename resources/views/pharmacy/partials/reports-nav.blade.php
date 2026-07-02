@php
    $links = [
        ['route' => 'pharmacy.reports', 'label' => 'Dashboard', 'pattern' => 'pharmacy.reports'],
        ['route' => 'pharmacy.reports.sales', 'label' => 'Sales', 'pattern' => 'pharmacy.reports.sales'],
        ['route' => 'pharmacy.reports.profit-margin', 'label' => 'Profit Margin', 'pattern' => 'pharmacy.reports.profit-margin'],
        ['route' => 'pharmacy.reports.inventory-valuation', 'label' => 'Inventory Valuation', 'pattern' => 'pharmacy.reports.inventory-valuation'],
        ['route' => 'pharmacy.reports.expiry', 'label' => 'Expiry', 'pattern' => 'pharmacy.reports.expiry'],
        ['route' => 'pharmacy.reports.tax', 'label' => 'Tax', 'pattern' => 'pharmacy.reports.tax'],
    ];
@endphp

<nav class="card" style="margin-bottom: 1rem;" aria-label="Reports navigation">
    <div class="card-body" style="display: flex; flex-wrap: wrap; align-items: center; gap: 0.375rem; padding: 0.75rem 1rem;">
        <span style="font-size: 0.75rem; font-weight: 600; letter-spacing: 0.04em; text-transform: uppercase; color: var(--muted-foreground); margin-right: 0.5rem;">Reports</span>
        @foreach ($links as $link)
            <a
                href="{{ route($link['route']) }}"
                class="btn btn-sm {{ request()->routeIs($link['pattern']) ? 'btn-primary' : 'btn-ghost' }}"
            >
                {{ $link['label'] }}
            </a>
        @endforeach
    </div>
</nav>
