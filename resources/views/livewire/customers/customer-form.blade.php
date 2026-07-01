<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $customer ? 'Edit Customer' : 'Add Customer' }}</h1>
            </div>
            <a href="{{ route('pharmacy.customers') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>

    <form wire:submit="save">
        <div class="card">
            <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label class="form-label">Name</label>
                    <input type="text" wire:model="name" class="form-input" placeholder="Optional for walk-in style records">
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Phone</label>
                    <input type="text" wire:model="phone" class="form-input">
                    @error('phone') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" wire:model="email" class="form-input">
                    @error('email') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Address</label>
                    <textarea wire:model="address" class="form-input" rows="3"></textarea>
                    @error('address') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border);">
                <p class="text-muted" style="margin-bottom: 0.75rem;">At least one of name, phone, or email is required.</p>
                <button type="submit" class="btn btn-primary">Save Customer</button>
            </div>
        </div>
    </form>
</div>
