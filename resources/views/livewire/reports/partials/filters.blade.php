@php
    $inputId = fn (string $name): string => 'report-filter-' . $name . '-' . uniqid();
@endphp

<div class="card" style="margin-bottom: 1rem;">
    <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
        <div>
            <label class="form-label" for="{{ $inputId('from') }}">From</label>
            <input type="date" id="{{ $inputId('from') }}" wire:model.live="from" class="form-input">
        </div>
        <div>
            <label class="form-label" for="{{ $inputId('to') }}">To</label>
            <input type="date" id="{{ $inputId('to') }}" wire:model.live="to" class="form-input">
        </div>
        <div>
            <label class="form-label" for="{{ $inputId('branch') }}">Branch</label>
            <select id="{{ $inputId('branch') }}" wire:model.live="branchId" class="form-select">
                <option value="">All branches</option>
                @foreach($branches as $branch)
                    <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                @endforeach
            </select>
        </div>
        @if($showCashier ?? true)
            <div>
                <label class="form-label" for="{{ $inputId('cashier') }}">Cashier</label>
                <select id="{{ $inputId('cashier') }}" wire:model.live="cashierId" class="form-select">
                    <option value="">All cashiers</option>
                    @foreach($cashiers as $cashier)
                        <option value="{{ $cashier->id }}">{{ $cashier->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if($showProduct ?? false)
            <div>
                <label class="form-label" for="{{ $inputId('product') }}">Product</label>
                <select id="{{ $inputId('product') }}" wire:model.live="productId" class="form-select">
                    <option value="">All products</option>
                    @foreach($products as $product)
                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                    @endforeach
                </select>
            </div>
        @endif
        @if($showExpiryDays ?? false)
            <div>
                <label class="form-label" for="{{ $inputId('expiry') }}">Days ahead</label>
                <input type="number" id="{{ $inputId('expiry') }}" wire:model.live="expiryDaysAhead" min="1" max="365" class="form-input">
            </div>
        @endif
    </div>
    <div class="card-body" style="border-top: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 0.5rem;">
        <button type="button" wire:click="resetReportFilters" class="btn btn-ghost">Reset Filters</button>
        @isset($exportQuery)
            <a href="{{ route('pharmacy.reports.export.pdf') }}?{{ $exportQuery }}" class="btn btn-secondary">Download PDF</a>
            <a href="{{ route('pharmacy.reports.export.excel') }}?{{ $exportQuery }}" class="btn btn-secondary">Download Excel</a>
        @endisset
    </div>
</div>
