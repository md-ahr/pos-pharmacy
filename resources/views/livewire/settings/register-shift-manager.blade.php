<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Register & Shifts</h1>
                <p class="page-description">Open and close till shifts with manual cash reconciliation for {{ $branch->name }}.</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <div class="stats-grid" style="margin-bottom: 1rem;">
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Shift Status</div>
                <div class="stat-value">{{ $summary['shift'] ? 'Open' : 'Closed' }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Opening Float</div>
                <div class="stat-value">{{ $summary['opening_float'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Cash Sales (Shift)</div>
                <div class="stat-value">{{ $summary['cash_sales'] }}</div>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-content">
                <div class="stat-label">Expected Cash</div>
                <div class="stat-value">{{ $summary['expected_cash'] }}</div>
            </div>
        </div>
    </div>

    <div style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(320px, 1fr)); margin-bottom: 1rem;">
        @if($summary['shift'] === null)
            <form wire:submit="openShift" class="card">
                <div class="card-header"><h2 class="card-title">Open Shift</h2></div>
                <div class="card-body" style="display: grid; gap: 1rem;">
                    <div>
                        <label class="form-label">Opening Float (cash in drawer)</label>
                        <input type="number" step="0.01" min="0" wire:model="openingFloat" class="form-input">
                        @error('openingFloat') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Open Register</button>
                </div>
            </form>
        @else
            <form wire:submit="closeShift" class="card">
                <div class="card-header"><h2 class="card-title">Close Shift</h2></div>
                <div class="card-body" style="display: grid; gap: 1rem;">
                    <p class="text-muted">Opened @displayDatetime($summary['shift']->opened_at) by {{ $summary['shift']->openedBy->name }}</p>
                    <div>
                        <label class="form-label">Counted Cash</label>
                        <input type="number" step="0.01" min="0" wire:model="countedCash" class="form-input">
                        @error('countedCash') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="form-label">Notes</label>
                        <textarea wire:model="closeNotes" class="form-input" rows="3"></textarea>
                        @error('closeNotes') <span class="form-error">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Close & Reconcile</button>
                </div>
            </form>
        @endif
    </div>

    <div class="card">
        <div class="card-header"><h2 class="card-title">Recent Shifts</h2></div>
        <div class="card-body" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Opened</th>
                        <th>Closed</th>
                        <th>Opening</th>
                        <th>Expected</th>
                        <th>Counted</th>
                        <th>Variance</th>
                        <th>Sales</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentShifts as $shift)
                        <tr>
                            <td>@displayDatetime($shift->opened_at)</td>
                            <td>@displayDatetime($shift->closed_at)</td>
                            <td>{{ $shift->opening_float }}</td>
                            <td>{{ $shift->expected_cash ?? '—' }}</td>
                            <td>{{ $shift->counted_cash ?? '—' }}</td>
                            <td>{{ $shift->cash_variance ?? '—' }}</td>
                            <td>{{ $shift->sales_total ?? '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-muted">No shifts recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $recentShifts->links() }}</div>
    </div>
</div>
