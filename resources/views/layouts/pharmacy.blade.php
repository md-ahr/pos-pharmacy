@extends('tyro-dashboard::layouts.app')

@section('title', $title ?? 'Pharmacy')

@section('breadcrumb')
<a href="{{ route('tyro-dashboard.index') }}">Dashboard</a>
<span class="breadcrumb-separator">/</span>
@if(isset($breadcrumb))
    {!! $breadcrumb !!}
@else
    <span>{{ $title ?? 'Pharmacy' }}</span>
@endif
@endsection

@section('content')
@if(isset($nav) && $nav === 'inventory')
    @include('pharmacy.partials.inventory-nav')
@endif

@if(isset($nav) && $nav === 'customers')
    @include('pharmacy.partials.customers-nav')
@endif

@if(isset($nav) && $nav === 'reports')
    @include('pharmacy.partials.reports-nav')
@endif

@if(isset($nav) && $nav === 'settings')
    @include('pharmacy.partials.settings-nav')
@endif

{{ $slot ?? '' }}
@hasSection('pharmacy-content')
    @yield('pharmacy-content')
@endif
@endsection
