<nav class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <a href="{{ route('pharmacy.settings.general') }}" class="btn {{ request()->routeIs('pharmacy.settings.general') ? 'btn-primary' : 'btn-ghost' }}">General</a>
        <a href="{{ route('pharmacy.settings.branches') }}" class="btn {{ request()->routeIs('pharmacy.settings.branches*') ? 'btn-primary' : 'btn-ghost' }}">Branches</a>
        <a href="{{ route('pharmacy.settings.staff') }}" class="btn {{ request()->routeIs('pharmacy.settings.staff*') ? 'btn-primary' : 'btn-ghost' }}">Staff</a>
        <a href="{{ route('pharmacy.settings.register') }}" class="btn {{ request()->routeIs('pharmacy.settings.register') ? 'btn-primary' : 'btn-ghost' }}">Register & Shifts</a>
    </div>
</nav>
