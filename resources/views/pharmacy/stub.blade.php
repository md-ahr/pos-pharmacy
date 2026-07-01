@extends('tyro-dashboard::layouts.app')

@section('title', $title ?? 'Pharmacy')

@section('content')
    <div class="card">
        <div class="card-header">
            <h1 class="card-title">{{ $title ?? 'Pharmacy' }}</h1>
        </div>
        <div class="card-body">
            <p class="text-muted">This screen will be implemented in a later phase.</p>
        </div>
    </div>
@endsection
