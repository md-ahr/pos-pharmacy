<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $product ? 'Edit Product' : 'Add Product' }}</h1>
            </div>
            <a href="{{ route('pharmacy.inventory.products') }}" class="btn btn-ghost">Back to Products</a>
        </div>
    </div>

    <form wire:submit="save">
        <div class="card" style="margin-bottom: 1rem;">
            <div class="card-header"><h2 class="card-title">Product Details</h2></div>
            <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label class="form-label">Name</label>
                    <input type="text" wire:model="name" class="form-input">
                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Generic Name</label>
                    <input type="text" wire:model="generic_name" class="form-input">
                </div>
                <div>
                    <label class="form-label">SKU</label>
                    <input type="text" wire:model="sku" class="form-input">
                    @error('sku') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Barcode</label>
                    <input type="text" wire:model="barcode" class="form-input">
                    @error('barcode') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Base Unit</label>
                    <input type="text" wire:model="base_unit" class="form-input" placeholder="tablet, ml, capsule">
                    @error('base_unit') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Category</label>
                    <select wire:model="category_id" class="form-input">
                        <option value="">— None —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Manufacturer</label>
                    <select wire:model="manufacturer_id" class="form-input">
                        <option value="">— None —</option>
                        @foreach($manufacturers as $manufacturer)
                            <option value="{{ $manufacturer->id }}">{{ $manufacturer->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="form-label">Reorder Level</label>
                    <input type="number" wire:model="reorder_level" class="form-input" min="0">
                </div>
                <div style="display: flex; flex-direction: column; gap: 0.5rem; justify-content: end;">
                    <label><input type="checkbox" wire:model="requires_prescription"> Requires prescription</label>
                    <label><input type="checkbox" wire:model="is_active"> Active</label>
                </div>
            </div>
        </div>

        <div class="card" style="margin-bottom: 1rem;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">Sellable Units</h2>
                <button type="button" wire:click="addUnit" class="btn btn-secondary btn-sm">Add Unit</button>
            </div>
            <div class="card-body">
                @error('units') <div class="text-danger" style="margin-bottom: 1rem;">{{ $message }}</div> @enderror
                @foreach($units as $index => $unit)
                    <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color, #eee);">
                        <div>
                            <label class="form-label">Unit Name</label>
                            <input type="text" wire:model="units.{{ $index }}.unit_name" class="form-input">
                            @error("units.{$index}.unit_name") <div class="text-danger">{{ $message }}</div> @enderror
                        </div>
                        <div>
                            <label class="form-label">Conversion Factor</label>
                            <input type="number" wire:model="units.{{ $index }}.conversion_factor" class="form-input" min="1">
                        </div>
                        <div>
                            <label class="form-label">Barcode</label>
                            <input type="text" wire:model="units.{{ $index }}.barcode" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Selling Price</label>
                            <input type="number" step="0.01" wire:model="units.{{ $index }}.selling_price" class="form-input">
                        </div>
                        <div style="display: flex; align-items: end; gap: 0.5rem;">
                            <label><input type="checkbox" wire:model="units.{{ $index }}.is_default"> Default</label>
                            @if(count($units) > 1)
                                <button type="button" wire:click="removeUnit({{ $index }})" class="btn btn-ghost btn-sm text-danger">Remove</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Product</button>
    </form>
</div>
