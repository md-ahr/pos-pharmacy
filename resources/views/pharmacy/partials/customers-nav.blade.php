<nav style="display:flex; gap:0.5rem; flex-wrap:wrap; margin-bottom:1rem;">
    <a href="{{ route('pharmacy.customers') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.customers') && !request()->routeIs('pharmacy.customers.create')) btn-primary @endif">All Customers</a>
    <a href="{{ route('pharmacy.customers.create') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.customers.create')) btn-primary @endif">Add Customer</a>
    <a href="{{ route('pharmacy.pos') }}" class="btn btn-ghost">Back to POS</a>
</nav>
