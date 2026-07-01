<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Inventory Valuation</h1>
                <p class="page-description">On-hand stock valued at batch cost price.</p>
            </div>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));">
            <div>
                <label class="form-label" for="valuation-branch">Branch</label>
                <select id="valuation-branch" wire:model.live="branchId" class="form-select">
                    <option value="">All branches</option>
                    @foreach($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>
        <div class="card-body" style="border-top: 1px solid var(--border); display: flex; flex-wrap: wrap; gap: 0.5rem;">
            <a href="{{ route('pharmacy.reports.export.pdf') }}?{{ $exportQuery }}" class="btn btn-secondary">Download PDF</a>
            <a href="{{ route('pharmacy.reports.export.excel') }}?{{ $exportQuery }}" class="btn btn-secondary">Download Excel</a>
        </div>
    </div>

    <div class="stats-grid" style="margin-bottom: 1rem;">
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Total Quantity (base)</div><div class="stat-value">{{ number_format($summary['total_quantity']) }}</div></div></div>
        <div class="stat-card"><div class="stat-content"><div class="stat-label">Total Value</div><div class="stat-value">{{ $summary['total_value'] }}</div></div></div>
    </div>

    <div class="card">
        <div class="table-container">
            <table class="table">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Quantity</th>
                        <th>Unit</th>
                        <th style="text-align: right;">Value</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr>
                            <td>{{ $row->product_name }}</td>
                            <td>{{ $row->quantity }}</td>
                            <td>{{ $row->base_unit }}</td>
                            <td style="text-align: right;">{{ $row->value }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No stock on hand.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
