@php
    $links = [
        ['route' => 'pharmacy.inventory', 'label' => 'Overview', 'pattern' => 'pharmacy.inventory'],
        ['route' => 'pharmacy.inventory.products', 'label' => 'Products', 'pattern' => 'pharmacy.inventory.products*'],
        ['route' => 'pharmacy.inventory.categories', 'label' => 'Categories', 'pattern' => 'pharmacy.inventory.categories*'],
        ['route' => 'pharmacy.inventory.manufacturers', 'label' => 'Manufacturers', 'pattern' => 'pharmacy.inventory.manufacturers*'],
        ['route' => 'pharmacy.inventory.batch-intake', 'label' => 'Batch Intake', 'pattern' => 'pharmacy.inventory.batch-intake'],
        ['route' => 'pharmacy.inventory.suppliers', 'label' => 'Suppliers', 'pattern' => 'pharmacy.inventory.suppliers*'],
        ['route' => 'pharmacy.inventory.purchase-orders', 'label' => 'Purchase Orders', 'pattern' => 'pharmacy.inventory.purchase-orders*'],
        ['route' => 'pharmacy.inventory.adjustments', 'label' => 'Adjustments', 'pattern' => 'pharmacy.inventory.adjustments'],
        ['route' => 'pharmacy.inventory.transfers', 'label' => 'Transfers', 'pattern' => 'pharmacy.inventory.transfers'],
    ];
@endphp

<nav class="card subnav-scroll" style="margin-bottom: 1rem;" aria-label="Inventory navigation">
    <div class="subnav-inner">
        <span class="subnav-label">Inventory</span>
        @foreach ($links as $link)
            <a href="{{ route($link['route']) }}" class="btn btn-sm {{ request()->routeIs($link['pattern']) ? 'btn-primary' : 'btn-ghost' }}">{{ $link['label'] }}</a>
        @endforeach
    </div>
</nav>
