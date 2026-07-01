<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Batch Intake</h1>
                <p class="page-description">Receive stock manually into the active branch.</p>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <form wire:submit="save">
        <div class="card">
            <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label class="form-label">Product</label>
                    <select wire:model="product_id" class="form-input">
                        <option value="">Select product...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Batch Number</label>
                    <input type="text" wire:model="batch_no" class="form-input">
                    @error('batch_no') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Expiry Date</label>
                    <input type="date" wire:model="expiry_date" class="form-input">
                    @error('expiry_date') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Cost Price</label>
                    <input type="number" step="0.01" wire:model="cost_price" class="form-input">
                    @error('cost_price') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Selling Price</label>
                    <input type="number" step="0.01" wire:model="selling_price" class="form-input">
                    @error('selling_price') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Quantity (base units)</label>
                    <input type="number" wire:model="quantity" class="form-input" min="1">
                    @error('quantity') <span class="form-error">{{ $message }}</span> @enderror
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary">Receive Stock</button>
            </div>
        </div>
    </form>
</div>
