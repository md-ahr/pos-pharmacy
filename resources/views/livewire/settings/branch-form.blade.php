<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $branch ? 'Edit Branch' : 'Add Branch' }}</h1>
            </div>
            <a href="{{ route('pharmacy.settings.branches') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>

    <form wire:submit="save">
        <div class="card">
            <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label class="form-label">Name</label>
                    <input type="text" wire:model="name" class="form-input">
                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Code</label>
                    <input type="text" wire:model="code" class="form-input">
                    @error('code') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" wire:model="phone" class="form-input">
                    @error('phone') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Address</label>
                    <textarea wire:model="address" class="form-input" rows="3"></textarea>
                    @error('address') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label><input type="checkbox" wire:model="isMain"> Main branch</label>
                </div>
                <div>
                    <label><input type="checkbox" wire:model="isActive"> Active</label>
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border-color, #eee);">
                <button type="submit" class="btn btn-primary">Save Branch</button>
            </div>
        </div>
    </form>
</div>
