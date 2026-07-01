<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $category ? 'Edit Category' : 'Add Category' }}</h1>
            </div>
            <a href="{{ route('pharmacy.inventory.categories') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>

    <form wire:submit="save">
        <div class="card">
            <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label class="form-label">Name</label>
                    <input type="text" wire:model="name" class="form-input" placeholder="e.g. Antibiotics">
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Parent Category</label>
                    <select wire:model="parent_id" class="form-input">
                        <option value="">— None (top level) —</option>
                        @foreach($parentOptions as $parent)
                            <option value="{{ $parent->id }}">{{ $parent->name }}</option>
                        @endforeach
                    </select>
                    @error('parent_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary">Save Category</button>
            </div>
        </div>
    </form>
</div>
