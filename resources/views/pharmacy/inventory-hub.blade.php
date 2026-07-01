@extends('layouts.pharmacy')

@section('title', 'Inventory')

@section('pharmacy-content')
    @include('pharmacy.partials.inventory-nav')

    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Inventory</h1>
                <p class="page-description">Stock overview, alerts, and quick links.</p>
            </div>
        </div>
    </div>

    <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1rem;">
        <livewire:dashboard.low-stock-widget />
        <livewire:dashboard.near-expiry-widget />
    </div>

    <div class="card">
        <div class="card-body" style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('pharmacy.inventory.products') }}" class="btn btn-primary">Manage Products</a>
            <a href="{{ route('pharmacy.inventory.categories') }}" class="btn btn-secondary">Categories</a>
            <a href="{{ route('pharmacy.inventory.manufacturers') }}" class="btn btn-secondary">Manufacturers</a>
            <a href="{{ route('pharmacy.inventory.batch-intake') }}" class="btn btn-secondary">Batch Intake</a>
            <a href="{{ route('pharmacy.inventory.purchase-orders') }}" class="btn btn-secondary">Purchase Orders</a>
        </div>
    </div>
@endsection
