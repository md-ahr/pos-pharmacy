<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $supplier ? 'Edit Supplier' : 'Add Supplier' }}</h1>
            </div>
            <a href="{{ route('pharmacy.inventory.suppliers') }}" class="btn btn-ghost">Back</a>
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
                    <label class="form-label">Contact Name</label>
                    <input type="text" wire:model="contact_name" class="form-input">
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" wire:model="phone" class="form-input">
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" wire:model="email" class="form-input">
                    @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Address</label>
                    <textarea wire:model="address" class="form-input" rows="3"></textarea>
                </div>
                <div>
                    <label><input type="checkbox" wire:model="is_active"> Active</label>
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border-color, #eee);">
                <button type="submit" class="btn btn-primary">Save Supplier</button>
            </div>
        </div>
    </form>
</div>
