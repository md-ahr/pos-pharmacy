<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">General Settings</h1>
                <p class="page-description">Currency, tax rate, receipt text, and default branch.</p>
            </div>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <form wire:submit="save">
        <div class="card">
            <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label class="form-label">Currency Code</label>
                    <input type="text" wire:model="currencyCode" class="form-input" maxlength="3">
                    @error('currencyCode') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Tax Rate (decimal)</label>
                    <input type="number" step="0.0001" min="0" max="1" wire:model="taxRate" class="form-input">
                    <small class="text-muted">Example: 0.1500 = 15%</small>
                    @error('taxRate') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Default Branch</label>
                    <select wire:model="defaultBranchId" class="form-input">
                        <option value="">No default</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('defaultBranchId') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Receipt Header</label>
                    <textarea wire:model="receiptHeader" class="form-input" rows="3" placeholder="Optional text above invoice details"></textarea>
                    @error('receiptHeader') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Receipt Footer</label>
                    <textarea wire:model="receiptFooter" class="form-input" rows="3" placeholder="Thank you message, return policy, etc."></textarea>
                    @error('receiptFooter') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary">Save Settings</button>
            </div>
        </div>
    </form>
</div>
