<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Stock Transfers</h1>
                <p class="page-description">
                    Move stock from {{ $fromBranch?->name ?? 'active branch' }} to another branch.
                </p>
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
                    <label class="form-label">To Branch</label>
                    <select wire:model="to_branch_id" class="form-select">
                        <option value="">Select branch...</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('to_branch_id') <span class="form-error">{{ $message }}</span> @enderror
                </div>
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
                    <label class="form-label">Quantity (base units)</label>
                    <input type="number" wire:model="quantity" class="form-input" min="1">
                    @error('quantity') <span class="form-error">{{ $message }}</span> @enderror
                </div>
                <div style="grid-column: 1 / -1;">
                    <label class="form-label">Notes</label>
                    <textarea wire:model="notes" class="form-input" rows="2"></textarea>
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border);">
                <button type="submit" class="btn btn-primary">Transfer Stock</button>
            </div>
        </div>
    </form>
</div>
