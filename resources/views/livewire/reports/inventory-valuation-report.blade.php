<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Inventory Valuation</h1>
                <p class="page-description">On-hand stock valued at batch cost price across branches.</p>
            </div>
        </div>
    </div>

    <x-tyro-dashboard::card title="Scope" description="Choose a branch to value stock on hand." style="margin-bottom: 1rem;">
        <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); max-width: 360px;">
            <div>
                <label class="form-label" for="valuation-branch">Branch</label>
                <select id="valuation-branch" wire:model.live="branchId" class="form-select">
                    <option value="">All branches</option>
                    @foreach ($branches as $branch)
                        <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <x-slot:footer>
            <div style="display: flex; flex-wrap: wrap; gap: 0.5rem;">
                <a href="{{ route('pharmacy.reports.export.pdf') }}?{{ $exportQuery }}" class="btn btn-secondary btn-sm">Download PDF</a>
                <a href="{{ route('pharmacy.reports.export.excel') }}?{{ $exportQuery }}" class="btn btn-secondary btn-sm">Download Excel</a>
            </div>
        </x-slot:footer>
    </x-tyro-dashboard::card>

    <div class="stats-grid">
        <x-tyro-dashboard::stat
            label="Total Quantity (base)"
            :value="number_format($summary['total_quantity'])"
            variant="primary"
        />
        <x-tyro-dashboard::stat
            label="Total Value"
            :value="$summary['total_value']"
            variant="success"
        />
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Stock Valuation</h2>
            <p class="page-description" style="margin: 0.25rem 0 0;">Current on-hand quantities and cost-based value by product.</p>
        </div>
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
                    @forelse ($rows as $row)
                        <tr>
                            <td style="font-weight: 500;">{{ $row->product_name }}</td>
                            <td style="font-variant-numeric: tabular-nums;">{{ $row->quantity }}</td>
                            <td>{{ $row->base_unit }}</td>
                            <td style="text-align: right; font-variant-numeric: tabular-nums; font-weight: 600;">{{ $row->value }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">
                                <div class="empty-state" style="padding: 2rem 1rem;">
                                    <div class="empty-state-title">No stock on hand</div>
                                    <p class="empty-state-description" style="margin-bottom: 0;">Receive inventory through batch intake to populate this report.</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
