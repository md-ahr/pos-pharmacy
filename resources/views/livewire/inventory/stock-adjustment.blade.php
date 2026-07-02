<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Stock Adjustments</h1>
                <p class="page-description">Record damages, expiry write-offs, or physical count corrections.</p>
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
                    <select wire:model.live="product_id" class="form-select">
                        <option value="">Select product...</option>
                        @foreach($products as $product)
                            <option value="{{ $product->id }}">{{ $product->name }}</option>
                        @endforeach
                    </select>
                    @error('product_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Batch</label>
                    <select wire:model="batch_id" class="form-select" @disabled(!$product_id)>
                        <option value="">Select batch...</option>
                        @foreach($batches as $batch)
                            <option value="{{ $batch->id }}">{{ $batch->batch_no }} (exp {{ $batch->expiry_date->format('Y-m-d') }})</option>
                        @endforeach
                    </select>
                    @error('batch_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Quantity Delta</label>
                    <input type="number" wire:model="quantity_delta" class="form-input" placeholder="Use negative to reduce">
                    @error('quantity_delta') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div>
                    <label class="form-label">Reason</label>
                    <select wire:model="reason" class="form-select">
                        @foreach($reasons as $reasonOption)
                            <option value="{{ $reasonOption->value }}">{{ $reasonOption->label() }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" class="form-input" rows="2"></textarea>
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary">Record Adjustment</button>
            </div>
        </div>
    </form>
</div>
