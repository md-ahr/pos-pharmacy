@once
    @include('pharmacy.partials.sidebar-logo-icon-auth-styles')
@endonce

<div class="logo-container">
    @if($branding['logo'] ?? false)
        <img src="{{ $branding['logo'] }}" alt="{{ $branding['app_name'] ?? config('app.name') }}" @class(['logo-light' => $branding['logo_dark'] ?? false])>
        @if($branding['logo_dark'] ?? false)
            <img src="{{ $branding['logo_dark'] }}" alt="{{ $branding['app_name'] ?? config('app.name') }}" class="logo-dark">
        @endif
    @else
        @include('pharmacy.partials.sidebar-logo-icon')
    @endif
</div>
