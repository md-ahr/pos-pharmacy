<div
    x-data="{
        highlight: 0,
        results: @js($searchResults->map(fn ($p) => ['id' => $p->id, 'name' => $p->name, 'sku' => $p->sku, 'generic' => $p->generic_name])->values()),
        selectHighlighted() {
            const item = this.results[this.highlight];
            if (item) {
                $wire.addProduct(item.id);
                this.highlight = 0;
            }
        }
    }"
    @keydown.arrow-down.prevent="if (results.length) highlight = Math.min(highlight + 1, results.length - 1)"
    @keydown.arrow-up.prevent="if (results.length) highlight = Math.max(highlight - 1, 0)"
    @keydown.enter.prevent="selectHighlighted()"
    @keydown.f2.window.prevent="$refs.searchInput?.focus()"
    @keydown.f4.window.prevent="if ($wire.cart.length) $wire.openPayment()"
>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Point of Sale</h1>
                <p class="page-description">
                    @if($branch)
                        Branch: {{ $branch->name }}
                    @else
                        No active branch selected.
                    @endif
                </p>
            </div>
            <div style="display:flex; gap:0.5rem; flex-wrap:wrap;">
                <button type="button" class="btn btn-secondary btn-sm" wire:click="clearCart">Clear (Esc)</button>
                <button type="button" class="btn btn-secondary btn-sm" wire:click="holdSale" @disabled(!$branch || count($cart) === 0)>Hold Sale</button>
                <button type="button" class="btn btn-primary btn-sm" wire:click="openPayment" @disabled(!$branch || count($cart) === 0)>Checkout (F4)</button>
            </div>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    @error('search')
        <div class="alert alert-danger" style="margin-bottom: 1rem;">{{ $message }}</div>
    @enderror

    @if($completedSaleId)
        <div class="alert alert-success" style="margin-bottom: 1rem; display:flex; justify-content:space-between; align-items:center; gap:1rem;">
            <span>Sale completed successfully.</span>
            <a href="{{ route('pharmacy.pos.receipt', $completedSaleId) }}" target="_blank" class="btn btn-primary btn-sm">Print Receipt</a>
        </div>
    @endif

    <div style="display:grid; grid-template-columns: 1fr 1.2fr; gap: 1rem; align-items:start;">
        <div style="display:grid; gap:1rem;">
            <div class="card">
                <div class="card-body">
                    <label class="form-label" for="pos-search">Search products (F2)</label>
                    <input
                        id="pos-search"
                        x-ref="searchInput"
                        type="search"
                        wire:model.live.debounce.250ms="search"
                        class="form-input"
                        placeholder="Name, SKU, generic name, or barcode..."
                        autocomplete="off"
                        autofocus
                    >
                    <p class="text-muted" style="margin-top:0.5rem; font-size:0.875rem;">Use ↑↓ to navigate, Enter to add. F4 opens checkout.</p>
                </div>
            </div>

            @if($search !== '' && $searchResults->isNotEmpty())
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Results</h3></div>
                    <div class="card-body" style="padding:0;">
                        <table class="table" style="margin:0;">
                            <tbody>
                                @foreach($searchResults as $index => $product)
                                    <tr
                                        wire:key="search-{{ $product->id }}"
                                        wire:click="addProduct({{ $product->id }})"
                                        style="cursor:pointer;"
                                        :style="highlight === {{ $index }} ? 'background: var(--primary-light, #eef2ff)' : ''"
                                    >
                                        <td>
                                            <strong>{{ $product->name }}</strong>
                                            @if($product->generic_name)
                                                <div class="text-muted" style="font-size:0.875rem;">{{ $product->generic_name }}</div>
                                            @endif
                                        </td>
                                        <td class="text-muted">{{ $product->sku }}</td>
                                        <td>
                                            @if($product->requires_prescription)
                                                <span class="badge badge-warning">Rx</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @elseif($search !== '')
                <div class="card"><div class="card-body text-muted">No products found.</div></div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Held Sales</h3></div>
                <div class="card-body" style="padding:0;">
                    @forelse($heldSales as $held)
                        <div wire:key="held-{{ $held->id }}" style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; border-bottom:1px solid var(--border-color,#eee);">
                            <div>
                                <strong>{{ $held->invoice_no }}</strong>
                                <div class="text-muted" style="font-size:0.875rem;">{{ number_format((float) $held->total, 2) }}</div>
                            </div>
                            <button type="button" class="btn btn-secondary btn-sm" wire:click="resumeHeld({{ $held->id }})">Resume</button>
                        </div>
                    @empty
                        <div class="card-body text-muted">No held sales.</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-header"><h3 class="card-title">Recent Sales</h3></div>
                <div class="card-body" style="padding:0;">
                    @forelse($recentSales as $recent)
                        <div wire:key="recent-{{ $recent->id }}" style="display:flex; justify-content:space-between; align-items:center; padding:0.75rem 1rem; border-bottom:1px solid var(--border-color,#eee);">
                            <div>
                                <strong>{{ $recent->invoice_no }}</strong>
                                <div class="text-muted" style="font-size:0.875rem;">
                                    {{ $recent->sold_at?->format('Y-m-d H:i') }} · {{ $recent->status->value }}
                                </div>
                            </div>
                            <div style="display:flex; gap:0.5rem;">
                                @if($recent->status === \App\Enums\SaleStatus::Completed)
                                    <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('refundSaleId', {{ $recent->id }})">Refund</button>
                                @endif
                                <a href="{{ route('pharmacy.pos.receipt', $recent) }}" target="_blank" class="btn btn-secondary btn-sm">Receipt</a>
                            </div>
                        </div>
                    @empty
                        <div class="card-body text-muted">No recent sales.</div>
                    @endforelse
                </div>
            </div>
        </div>

        <div style="display:grid; gap:1rem;">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Cart</h3></div>
                <div class="card-body" style="padding:0;">
                    @forelse($cart as $line)
                        <div wire:key="cart-{{ $line['key'] }}" style="padding:1rem; border-bottom:1px solid var(--border-color,#eee);">
                            <div style="display:flex; justify-content:space-between; gap:1rem;">
                                <div>
                                    <strong>{{ $line['product_name'] }}</strong>
                                    <div class="text-muted" style="font-size:0.875rem;">Batch {{ $line['batch_no'] }}</div>
                                </div>
                                <button type="button" class="btn btn-secondary btn-sm" wire:click="removeLine('{{ $line['key'] }}')">Remove</button>
                            </div>
                            <div style="display:grid; grid-template-columns: repeat(auto-fit, minmax(120px, 1fr)); gap:0.75rem; margin-top:0.75rem;">
                                <div>
                                    <label class="form-label">Qty</label>
                                    <input type="number" min="1" class="form-input" value="{{ $line['quantity'] }}"
                                        wire:change="updateQuantity('{{ $line['key'] }}', $event.target.value)">
                                </div>
                                <div>
                                    <label class="form-label">Unit</label>
                                    <select class="form-input" wire:change="updateUnit('{{ $line['key'] }}', $event.target.value || null)">
                                        <option value="">{{ $line['unit_name'] ?? 'Base unit' }}</option>
                                        @foreach($line['units'] as $unit)
                                            <option value="{{ $unit['id'] }}" @selected($line['product_unit_id'] === $unit['id'])>{{ $unit['unit_name'] }}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Batch</label>
                                    <select class="form-input" wire:change="updateBatch('{{ $line['key'] }}', $event.target.value)">
                                        @foreach($line['available_batches'] as $batch)
                                            <option value="{{ $batch['id'] }}" @selected($line['batch_id'] === $batch['id'])>
                                                {{ $batch['batch_no'] }} ({{ $batch['expiry_date'] }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div>
                                    <label class="form-label">Unit price</label>
                                    <input type="text" class="form-input" value="{{ $line['unit_price'] }}" readonly>
                                </div>
                                <div>
                                    <label class="form-label">Line total</label>
                                    <input type="text" class="form-input" value="{{ $line['line_subtotal'] }}" readonly>
                                </div>
                            </div>
                            <label style="display:flex; align-items:center; gap:0.5rem; margin-top:0.75rem;">
                                <input type="checkbox" @checked($line['is_prescription_item']) wire:click="togglePrescriptionItem('{{ $line['key'] }}')">
                                <span>Prescription item</span>
                            </label>
                        </div>
                    @empty
                        <div class="card-body text-muted">Cart is empty. Search and add products to begin.</div>
                    @endforelse
                </div>
            </div>

            <div class="card">
                <div class="card-body" style="display:grid; gap:0.75rem;">
                    <div style="display:flex; justify-content:space-between;"><span>Subtotal</span><strong>{{ $totals['subtotal'] }}</strong></div>
                    <div>
                        <label class="form-label">Sale discount</label>
                        <input type="number" step="0.01" min="0" wire:model.live="saleDiscount" class="form-input">
                    </div>
                    <div style="display:flex; justify-content:space-between;"><span>Tax</span><strong>{{ $totals['tax'] }}</strong></div>
                    <div style="display:flex; justify-content:space-between; font-size:1.125rem;"><span>Total</span><strong>{{ $totals['total'] }}</strong></div>

                    @if($prescriptionRequired)
                        <div style="display:grid; gap:0.75rem; border-top:1px solid var(--border-color,#eee); padding-top:0.75rem;">
                            <div class="alert alert-warning" style="margin:0;">This sale includes prescription items.</div>
                            <div>
                                <label class="form-label">Prescriber name</label>
                                <input type="text" wire:model="prescriberName" class="form-input">
                            </div>
                            <div>
                                <label class="form-label">Prescriber registration no.</label>
                                <input type="text" wire:model="prescriberRegNo" class="form-input">
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if($showPayment)
        <div style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:1000;">
            <div class="card" style="width:min(520px, 92vw);">
                <div class="card-header" style="display:flex; justify-content:space-between; align-items:center;">
                    <h3 class="card-title">Payment</h3>
                    <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('showPayment', false)">Close</button>
                </div>
                <div class="card-body" style="display:grid; gap:1rem;">
                    <div style="display:flex; justify-content:space-between; font-size:1.125rem;">
                        <span>Amount due</span><strong>{{ $totals['total'] }}</strong>
                    </div>

                    <div style="display:grid; grid-template-columns: repeat(3, 1fr); gap:0.5rem;">
                        <div>
                            <label class="form-label">Method</label>
                            <select wire:model="paymentMethod" class="form-input">
                                @foreach($paymentMethods as $method)
                                    <option value="{{ $method->value }}">{{ ucfirst($method->value) }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="0.01" wire:model="paymentAmount" class="form-input">
                        </div>
                        <div>
                            <label class="form-label">Reference</label>
                            <input type="text" wire:model="paymentReference" class="form-input" placeholder="Optional">
                        </div>
                    </div>

                    <button type="button" class="btn btn-secondary btn-sm" wire:click="addPaymentLine">Add payment line</button>

                    @foreach($payments as $index => $payment)
                        <div wire:key="payment-{{ $index }}" style="display:flex; justify-content:space-between; align-items:center;">
                            <span>{{ ucfirst($payment['method']) }} — {{ $payment['amount'] }}</span>
                            <button type="button" class="btn btn-secondary btn-sm" wire:click="removePayment({{ $index }})">Remove</button>
                        </div>
                    @endforeach

                    <div style="display:flex; justify-content:space-between;">
                        <span>Remaining</span><strong>{{ $this->remainingDue() }}</strong>
                    </div>

                    <button type="button" class="btn btn-primary" wire:click="completeSale">Complete sale</button>
                </div>
            </div>
        </div>
    @endif

    @if($refundSaleId)
        <div style="position:fixed; inset:0; background:rgba(0,0,0,0.45); display:flex; align-items:center; justify-content:center; z-index:1000;">
            <div class="card" style="width:min(420px, 92vw);">
                <div class="card-header"><h3 class="card-title">Confirm refund</h3></div>
                <div class="card-body" style="display:grid; gap:1rem;">
                    <p>Refund this sale and restore stock to the original batches?</p>
                    <div style="display:flex; gap:0.5rem; justify-content:flex-end;">
                        <button type="button" class="btn btn-secondary btn-sm" wire:click="$set('refundSaleId', null)">Cancel</button>
                        <button type="button" class="btn btn-primary btn-sm" wire:click="refundSale">Refund sale</button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
