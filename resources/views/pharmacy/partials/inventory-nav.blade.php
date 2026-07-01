<nav class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <a href="{{ route('pharmacy.inventory') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.inventory')) btn-primary @endif">Overview</a>
        <a href="{{ route('pharmacy.inventory.products') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.inventory.products*')) btn-primary @endif">Products</a>
        <a href="{{ route('pharmacy.inventory.batch-intake') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.inventory.batch-intake')) btn-primary @endif">Batch Intake</a>
        <a href="{{ route('pharmacy.inventory.suppliers') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.inventory.suppliers*')) btn-primary @endif">Suppliers</a>
        <a href="{{ route('pharmacy.inventory.purchase-orders') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.inventory.purchase-orders*')) btn-primary @endif">Purchase Orders</a>
        <a href="{{ route('pharmacy.inventory.adjustments') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.inventory.adjustments')) btn-primary @endif">Adjustments</a>
        <a href="{{ route('pharmacy.inventory.transfers') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.inventory.transfers')) btn-primary @endif">Transfers</a>
    </div>
</nav>
