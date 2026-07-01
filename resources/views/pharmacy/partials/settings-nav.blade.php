<nav class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <a href="{{ route('pharmacy.settings.general') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.settings.general')) btn-primary @endif">General</a>
        <a href="{{ route('pharmacy.settings.branches') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.settings.branches*')) btn-primary @endif">Branches</a>
        <a href="{{ route('pharmacy.settings.staff') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.settings.staff*')) btn-primary @endif">Staff</a>
        <a href="{{ route('pharmacy.settings.register') }}" class="btn btn-ghost @if(request()->routeIs('pharmacy.settings.register')) btn-primary @endif">Register & Shifts</a>
    </div>
</nav>
