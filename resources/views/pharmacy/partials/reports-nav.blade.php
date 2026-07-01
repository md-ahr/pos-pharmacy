<nav class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <a href="{{ route('pharmacy.reports') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.reports')) btn-primary @endif">Dashboard</a>
        <a href="{{ route('pharmacy.reports.sales') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.reports.sales')) btn-primary @endif">Sales</a>
        <a href="{{ route('pharmacy.reports.profit-margin') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.reports.profit-margin')) btn-primary @endif">Profit Margin</a>
        <a href="{{ route('pharmacy.reports.inventory-valuation') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.reports.inventory-valuation')) btn-primary @endif">Inventory Valuation</a>
        <a href="{{ route('pharmacy.reports.expiry') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.reports.expiry')) btn-primary @endif">Expiry</a>
        <a href="{{ route('pharmacy.reports.tax') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.reports.tax')) btn-primary @endif">Tax</a>
    </div>
</nav>
