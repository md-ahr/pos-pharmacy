<div>
    @php
        $latestBatchCost = $latestBatchCostPrice !== null ? (float) $latestBatchCostPrice : null;
    @endphp

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
                    @error('name') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Generic Name</label>
                    <input type="text" wire:model="generic_name" class="form-input">
                </div>
                <div>
                    <label class="form-label">SKU</label>
                    <input type="text" wire:model="sku" class="form-input">
                    @error('sku') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Barcode</label>
                    <input type="text" wire:model="barcode" class="form-input">
                    @error('barcode') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Base Unit</label>
                    <input type="text" wire:model="base_unit" class="form-input" placeholder="tablet, ml, capsule">
                    @error('base_unit') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.375rem;">
                        <label class="form-label" style="margin: 0;">Category</label>
                        <a href="{{ route('pharmacy.inventory.categories.create') }}" class="btn btn-ghost btn-sm">+ Add</a>
                    </div>
                    <select wire:model="category_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}">{{ $category->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('pharmacy.inventory.categories') }}" class="btn btn-ghost btn-sm" style="margin-top: 0.375rem;">Manage categories</a>
                </div>
                <div>
                    <div style="display: flex; align-items: center; justify-content: space-between; gap: 0.5rem; margin-bottom: 0.375rem;">
                        <label class="form-label" style="margin: 0;">Manufacturer</label>
                        <a href="{{ route('pharmacy.inventory.manufacturers.create') }}" class="btn btn-ghost btn-sm">+ Add</a>
                    </div>
                    <select wire:model="manufacturer_id" class="form-select">
                        <option value="">— None —</option>
                        @foreach($manufacturers as $manufacturer)
                            <option value="{{ $manufacturer->id }}">{{ $manufacturer->name }}</option>
                        @endforeach
                    </select>
                    <a href="{{ route('pharmacy.inventory.manufacturers') }}" class="btn btn-ghost btn-sm" style="margin-top: 0.375rem;">Manage manufacturers</a>
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
            <div class="card-header">
                <h2 class="card-title">Pricing Guide</h2>
            </div>
            <div class="card-body" style="display: grid; gap: 0.75rem;">
                <p class="page-description" style="margin: 0;">
                    Set the <strong>selling price</strong> here for each unit customers buy. The
                    <strong>cost price</strong> is recorded during <a href="{{ route('pharmacy.inventory.batch-intake') }}">batch intake</a>,
                    because cost can change every time you receive stock.
                </p>
                @if ($latestBatchCostPrice !== null)
                    <div class="alert alert-info" style="margin: 0;">
                        Latest batch cost for 1 {{ $base_unit ?: 'base unit' }}:
                        <strong>{{ $latestBatchCostPrice }}</strong>
                    </div>
                @else
                    <div class="alert alert-warning" style="margin: 0;">
                        No batch cost recorded yet. Receive stock first if you want staff to compare sell price against cost.
                    </div>
                @endif
            </div>
        </div>

        <div class="card" style="margin-bottom: 1rem;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center;">
                <h2 class="card-title">Sellable Units</h2>
                <button type="button" wire:click="addUnit" class="btn btn-secondary btn-sm">Add Unit</button>
            </div>
            <div class="card-body">
                @error('units') <span class="form-error" style="margin-bottom: 1rem;">{{ $message }}</span> @enderror
                @foreach($units as $index => $unit)
                    @php
                        $conversionFactor = max((int) ($unit['conversion_factor'] ?: 1), 1);
                        $costForUnit = $latestBatchCost !== null ? $latestBatchCost * $conversionFactor : null;
                        $sellingPrice = $unit['selling_price'] !== '' ? (float) $unit['selling_price'] : null;
                        $profitForUnit = ($costForUnit !== null && $sellingPrice !== null) ? $sellingPrice - $costForUnit : null;
                        $marginPercent = ($costForUnit !== null && $costForUnit > 0 && $sellingPrice !== null)
                            ? (($sellingPrice - $costForUnit) / $costForUnit) * 100
                            : null;
                    @endphp
                    <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fit, minmax(140px, 1fr)); margin-bottom: 1rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border);">
                        <div>
                            <label class="form-label">Unit Name</label>
                            <input type="text" wire:model="units.{{ $index }}.unit_name" class="form-input">
                            @error("units.{$index}.unit_name") <span class="form-error">{{ $message }}</span> @enderror
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
                            <label class="form-label">Selling Price (per {{ $unit['unit_name'] !== '' ? $unit['unit_name'] : 'unit' }})</label>
                            <input type="number" step="0.01" wire:model="units.{{ $index }}.selling_price" class="form-input">
                            @if ($costForUnit !== null)
                                <p class="form-hint" style="margin-bottom: 0;">
                                    Cost for this unit: {{ number_format($costForUnit, 2, '.', '') }}
                                    @if ($sellingPrice !== null)
                                        | Profit:
                                        <span style="color: {{ $profitForUnit !== null && $profitForUnit >= 0 ? 'var(--success)' : 'var(--destructive)' }}; font-weight: 600;">
                                            {{ number_format((float) $profitForUnit, 2, '.', '') }}
                                        </span>
                                        @if ($marginPercent !== null)
                                            ({{ number_format($marginPercent, 1, '.', '') }}% margin)
                                        @endif
                                    @endif
                                </p>
                            @else
                                <p class="form-hint" style="margin-bottom: 0;">Sell price is customer-facing. Cost price is captured later during batch intake.</p>
                            @endif
                        </div>
                        <div style="display: flex; align-items: end; gap: 0.5rem;">
                            <label><input type="checkbox" wire:model="units.{{ $index }}.is_default"> Default</label>
                            @if(count($units) > 1)
                                <button type="button" wire:click="removeUnit({{ $index }})" class="btn btn-ghost btn-sm text-destructive">Remove</button>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <button type="submit" class="btn btn-primary">Save Product</button>
    </form>
</div>
