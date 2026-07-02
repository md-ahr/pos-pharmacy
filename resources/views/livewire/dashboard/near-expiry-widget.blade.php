<x-tyro-dashboard::card title="Expiring Within {{ $daysAhead }} Days" description="Batches nearing expiry at the active branch.">
    <x-slot:actions>
        @if ($totalCount > 0)
            <span class="badge badge-danger">{{ $totalCount }} {{ str('batch')->plural($totalCount) }}</span>
        @endif
    </x-slot:actions>

    @if ($items->isEmpty())
        <div class="empty-state" style="padding: 2rem 1rem;">
            <svg class="empty-state-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" aria-hidden="true">
                <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5"/>
            </svg>
            <div class="empty-state-title">No batches nearing expiry</div>
            <p class="empty-state-description" style="margin-bottom: 0;">Nothing is scheduled to expire in the next {{ $daysAhead }} days.</p>
        </div>
    @else
        <div style="display: flex; flex-direction: column; gap: 0.875rem;">
            @foreach ($items as $stock)
                @php
                    $daysLeft = today()->diffInDays($stock->batch->expiry_date, false);
                    $isUrgent = $daysLeft <= 30;
                @endphp
                <div style="display: flex; justify-content: space-between; align-items: flex-start; gap: 0.75rem;">
                    <div style="min-width: 0;">
                        <div style="font-weight: 600; color: var(--foreground); overflow: hidden; text-overflow: ellipsis; white-space: nowrap;">
                            {{ $stock->product->name }}
                        </div>
                        <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 0.125rem;">
                            Batch <span class="font-mono">{{ $stock->batch->batch_no }}</span>
                            · {{ (int) $stock->quantity }} {{ $stock->product->base_unit }}
                        </div>
                    </div>
                    <div style="text-align: right; flex-shrink: 0;">
                        <span class="badge {{ $isUrgent ? 'badge-danger' : 'badge-warning' }}">
                            {{ $daysLeft === 0 ? 'Today' : $daysLeft.'d left' }}
                        </span>
                        <div style="font-size: 0.75rem; color: var(--muted-foreground); margin-top: 0.25rem;">
                            {{ $stock->batch->expiry_date->format('M j, Y') }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if ($totalCount > $items->count())
        <x-slot:footer>
            <a href="{{ route('pharmacy.reports.expiry') }}" class="btn btn-ghost btn-sm">View all {{ $totalCount }} batches</a>
        </x-slot:footer>
    @elseif ($items->isNotEmpty())
        <x-slot:footer>
            <a href="{{ route('pharmacy.reports.expiry') }}" class="btn btn-ghost btn-sm">Open expiry report</a>
        </x-slot:footer>
    @endif
</x-tyro-dashboard::card>
