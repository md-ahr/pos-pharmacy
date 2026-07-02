<nav class="card subnav-scroll" style="margin-bottom: 1rem;" aria-label="Settings navigation">
    <div class="subnav-inner">
        <span class="subnav-label">Settings</span>
        <a href="{{ route('pharmacy.settings.general') }}" class="btn btn-sm {{ request()->routeIs('pharmacy.settings.general') ? 'btn-primary' : 'btn-ghost' }}">General</a>
        <a href="{{ route('pharmacy.settings.branches') }}" class="btn btn-sm {{ request()->routeIs('pharmacy.settings.branches*') ? 'btn-primary' : 'btn-ghost' }}">Branches</a>
        <a href="{{ route('pharmacy.settings.staff') }}" class="btn btn-sm {{ request()->routeIs('pharmacy.settings.staff*') ? 'btn-primary' : 'btn-ghost' }}">Staff</a>
        <a href="{{ route('pharmacy.settings.register') }}" class="btn btn-sm {{ request()->routeIs('pharmacy.settings.register') ? 'btn-primary' : 'btn-ghost' }}">Register & Shifts</a>
    </div>
</nav>
