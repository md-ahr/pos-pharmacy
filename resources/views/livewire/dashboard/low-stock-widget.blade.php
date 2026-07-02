<x-tyro-dashboard::card title="Low Stock" description="Products at or below reorder level for the active branch.">
    <x-slot:actions>
        @if ($totalCount > 0)
            <span class="badge badge-warning">{{ $totalCount }} {{ str('item')->plural($totalCount) }}</span>
        @endif
    </x-slot:actions>

    @if ($items->isEmpty())
        <div class="empty-state" style="padding: 2rem 1rem;">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
            <div class="empty-state-title">Stock levels look healthy</div>
            <p class="empty-state-description" style="margin-bottom: 0;">No products are below their reorder threshold.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.875rem;">
            @foreach ($items as $product)
                @php
                    $onHand = (int) ($product->branch_stock ?? 0);
                    $reorderLevel = (int) $product->reorder_level;
                    $isOutOfStock = $onHand === 0;
                    $fillPct = $reorderLevel > 0 ? min(100, (int) round(($onHand / $reorderLevel) * 100)) : 0;
                @endphp
                <div>
                    <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem; margin-bottom: 0.5rem;">
                        <div style="min-width: 0;">
                            <div style="font-weight: 600; color: var(--foreground); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                                {{ $product->name }}
                            </div>
                            <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 0.125rem;">
                                {{ $onHand }} on hand · reorder at {{ $reorderLevel }}
                            </div>
                        </div>
                        <span class="badge {{ $isOutOfStock ? 'badge-danger' : 'badge-warning' }}" style="flex-shrink: 0;">
                            {{ $isOutOfStock ? 'Out of stock' : 'Low' }}
                        </span>
                    </div>
                    <div style="height: 8px; width: 100%; background: var(--muted); border-radius: 9999px; overflow: hidden; border: 1px solid var(--border);">
                        <div style="height: 100%; width: {{ max($fillPct, $onHand > 0 ? 6 : 0) }}%; background: {{ $isOutOfStock ? 'var(--destructive)' : 'var(--warning)' }}; border-radius: 9999px;"></div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($totalCount > $items->count())
        <x-slot:footer>
            <a href="{{ route('pharmacy.inventory.products') }}" class="btn btn-ghost btn-sm">View all {{ $totalCount }} products</a>
        </x-slot:footer>
    @elseif ($items->isNotEmpty())
        <x-slot:footer>
            <a href="{{ route('pharmacy.inventory.products') }}" class="btn btn-ghost btn-sm">Manage products</a>
        </x-slot:footer>
    @endif
</x-tyro-dashboard::card>
