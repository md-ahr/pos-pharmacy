<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">
                    @if($purchaseOrder)
                        Purchase Order {{ $purchaseOrder->reference_no }}
                        <span class="badge badge-secondary">{{ ucfirst($purchaseOrder->status->value) }}</span>
                    @else
                        New Purchase Order
                    @endif
                </h1>
            </div>
            <a href="{{ route('pharmacy.inventory.purchase-orders') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    @if($isEditable)
        <form wire:submit.prevent="saveDraft">
            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                    <div>
                        <label class="form-label">Supplier</label>
                        <select wire:model="supplier_id" class="form-input">
                            <option value="">Select supplier...</option>
                            @foreach($suppliers as $supplier)
                                <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <div class="text-danger">{{ $message }}</div> @enderror
                    </div>
                    <div style="grid-column: 1 / -1;">
                        <label class="form-label">Notes</label>
                        <textarea wire:model="notes" class="form-input" rows="2"></textarea>
                    </div>
                </div>
            </div>

            <div class="card" style="margin-bottom: 1rem;">
                <div class="card-header" style="display: flex; justify-content: space-between;">
                    <h2 class="card-title">Line Items</h2>
                    <button type="button" wire:click="addLine" class="btn btn-secondary btn-sm">Add Line</button>
                </div>
                <div class="card-body">
                    @foreach($lines as $index => $line)
                        <div style="display: grid; gap: 0.75rem; grid-template-columns: 2fr 1fr 1fr auto; margin-bottom: 1rem;">
                            <div>
                                <label class="form-label">Product</label>
                                <select wire:model="lines.{{ $index }}.product_id" class="form-input">
                                    <option value="">Select...</option>
                                    @foreach($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->name }}</option>
                                    @endforeach
                                </select>
                                @error("lines.{$index}.product_id") <div class="text-danger">{{ $message }}</div> @enderror
                            </div>
                            <div>
                                <label class="form-label">Quantity</label>
                                <input type="number" wire:model="lines.{{ $index }}.quantity" class="form-input" min="1">
                            </div>
                            <div>
                                <label class="form-label">Unit Cost</label>
                                <input type="number" step="0.01" wire:model="lines.{{ $index }}.unit_cost" class="form-input">
                            </div>
                            <div style="display: flex; align-items: end;">
                                @if(count($lines) > 1)
                                    <button type="button" wire:click="removeLine({{ $index }})" class="btn btn-ghost btn-sm text-danger">Remove</button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>

            <div style="display: flex; gap: 0.5rem;">
                <button type="submit" class="btn btn-primary">Save Draft</button>
                @if($purchaseOrder)
                    <button type="button" wire:click="markOrdered" class="btn btn-secondary">Mark Ordered</button>
                @endif
            </div>
        </form>
    @endif

    @if($canReceive)
        <form wire:submit.prevent="receive">
            <div class="card" style="margin-top: 1rem;">
                <div class="card-header"><h2 class="card-title">Receive Goods</h2></div>
                <div class="card-body">
                    @foreach($receiptLines as $index => $line)
                        @php $item = $purchaseOrder->items->firstWhere('id', $line['purchase_order_item_id']); @endphp
                        <div style="margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 1px solid var(--border-color, #eee);">
                            <strong>{{ $item?->product?->name }}</strong> — Qty: {{ $item?->quantity }}
                            <div style="display: grid; gap: 0.75rem; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); margin-top: 0.75rem;">
                                <div>
                                    <label class="form-label">Batch No</label>
                                    <input type="text" wire:model="receiptLines.{{ $index }}.batch_no" class="form-input">
                                    @error("receiptLines.{$index}.batch_no") <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="form-label">Expiry Date</label>
                                    <input type="date" wire:model="receiptLines.{{ $index }}.expiry_date" class="form-input">
                                    @error("receiptLines.{$index}.expiry_date") <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                                <div>
                                    <label class="form-label">Selling Price</label>
                                    <input type="number" step="0.01" wire:model="receiptLines.{{ $index }}.selling_price" class="form-input">
                                    @error("receiptLines.{$index}.selling_price") <div class="text-danger">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="card-body" style="border-top: 1px solid var(--border-color, #eee);">
                    <button type="submit" class="btn btn-primary">Receive &amp; Update Stock</button>
                </div>
            </div>
        </form>
    @endif

    @if($purchaseOrder && $purchaseOrder->status === \App\Enums\PurchaseOrderStatus::Received)
        <div class="card" style="margin-top: 1rem;">
            <div class="card-body text-muted">
                Received on {{ $purchaseOrder->received_at?->format('Y-m-d H:i') }}.
            </div>
        </div>
    @endif
</div>
