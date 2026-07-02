<nav class="card subnav-scroll" style="margin-bottom: 1rem;" aria-label="Customers navigation">
    <div class="subnav-inner">
        <span class="subnav-label">Customers</span>
        <a href="{{ route('pharmacy.customers') }}" class="btn btn-sm {{ request()->routeIs('pharmacy.customers') && ! request()->routeIs('pharmacy.customers.create') ? 'btn-primary' : 'btn-ghost' }}">All Customers</a>
        <a href="{{ route('pharmacy.customers.create') }}" class="btn btn-sm {{ request()->routeIs('pharmacy.customers.create') ? 'btn-primary' : 'btn-ghost' }}">Add Customer</a>
        <a href="{{ route('pharmacy.pos') }}" class="btn btn-sm btn-ghost">Back to POS</a>
    </div>
</nav>
