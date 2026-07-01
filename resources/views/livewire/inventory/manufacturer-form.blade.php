<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $manufacturer ? 'Edit Manufacturer' : 'Add Manufacturer' }}</h1>
            </div>
            <a href="{{ route('pharmacy.inventory.manufacturers') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>

    <form wire:submit="save">
        <div class="card">
            <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label class="form-label">Name</label>
                    <input type="text" wire:model="name" class="form-input" placeholder="e.g. Acme Pharma">
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary">Save Manufacturer</button>
            </div>
        </div>
    </form>
</div>
