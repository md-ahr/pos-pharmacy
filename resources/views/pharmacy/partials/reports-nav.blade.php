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

<nav class="card subnav-scroll" style="margin-bottom: 1rem;" aria-label="Reports navigation">
    <div class="subnav-inner">
        <span class="subnav-label">Reports</span>
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}" class="btn btn-sm {{ request()->routeIs($link['pattern']) ? 'btn-primary' : 'btn-ghost' }}">{{ $link['label'] }}</a>
        @endforeach
    </div>
</nav>
