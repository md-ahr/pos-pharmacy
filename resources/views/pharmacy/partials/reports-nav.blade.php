<nav class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <a href="{{ route('pharmacy.reports') }}" class="btn {{ request()->routeIs('pharmacy.reports') ? 'btn-primary' : 'btn-ghost' }}">Dashboard</a>
        <a href="{{ route('pharmacy.reports.sales') }}" class="btn {{ request()->routeIs('pharmacy.reports.sales') ? 'btn-primary' : 'btn-ghost' }}">Sales</a>
        <a href="{{ route('pharmacy.reports.profit-margin') }}" class="btn {{ request()->routeIs('pharmacy.reports.profit-margin') ? 'btn-primary' : 'btn-ghost' }}">Profit Margin</a>
        <a href="{{ route('pharmacy.reports.inventory-valuation') }}" class="btn {{ request()->routeIs('pharmacy.reports.inventory-valuation') ? 'btn-primary' : 'btn-ghost' }}">Inventory Valuation</a>
        <a href="{{ route('pharmacy.reports.expiry') }}" class="btn {{ request()->routeIs('pharmacy.reports.expiry') ? 'btn-primary' : 'btn-ghost' }}">Expiry</a>
        <a href="{{ route('pharmacy.reports.tax') }}" class="btn {{ request()->routeIs('pharmacy.reports.tax') ? 'btn-primary' : 'btn-ghost' }}">Tax</a>
    </div>
</nav>
