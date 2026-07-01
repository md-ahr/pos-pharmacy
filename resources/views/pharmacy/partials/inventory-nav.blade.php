<nav class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <a href="{{ route('pharmacy.inventory') }}" class="btn {{ request()->routeIs('pharmacy.inventory') ? 'btn-primary' : 'btn-ghost' }}">Overview</a>
        <a href="{{ route('pharmacy.inventory.products') }}" class="btn {{ request()->routeIs('pharmacy.inventory.products*') ? 'btn-primary' : 'btn-ghost' }}">Products</a>
        <a href="{{ route('pharmacy.inventory.categories') }}" class="btn {{ request()->routeIs('pharmacy.inventory.categories*') ? 'btn-primary' : 'btn-ghost' }}">Categories</a>
        <a href="{{ route('pharmacy.inventory.manufacturers') }}" class="btn {{ request()->routeIs('pharmacy.inventory.manufacturers*') ? 'btn-primary' : 'btn-ghost' }}">Manufacturers</a>
        <a href="{{ route('pharmacy.inventory.batch-intake') }}" class="btn {{ request()->routeIs('pharmacy.inventory.batch-intake') ? 'btn-primary' : 'btn-ghost' }}">Batch Intake</a>
        <a href="{{ route('pharmacy.inventory.suppliers') }}" class="btn {{ request()->routeIs('pharmacy.inventory.suppliers*') ? 'btn-primary' : 'btn-ghost' }}">Suppliers</a>
        <a href="{{ route('pharmacy.inventory.purchase-orders') }}" class="btn {{ request()->routeIs('pharmacy.inventory.purchase-orders*') ? 'btn-primary' : 'btn-ghost' }}">Purchase Orders</a>
        <a href="{{ route('pharmacy.inventory.adjustments') }}" class="btn {{ request()->routeIs('pharmacy.inventory.adjustments') ? 'btn-primary' : 'btn-ghost' }}">Adjustments</a>
        <a href="{{ route('pharmacy.inventory.transfers') }}" class="btn {{ request()->routeIs('pharmacy.inventory.transfers') ? 'btn-primary' : 'btn-ghost' }}">Transfers</a>
    </div>
</nav>
