<div>
    @if ($canSwitch && $branches->isNotEmpty())
        <div class="branch-switcher" style="display: inline-flex; align-items: center; gap: 0.5rem; margin-right: 0.75rem;">
            <label for="branch-switcher" class="form-label" style="margin: 0; font-size: 0.8125rem; color: var(--muted-foreground); white-space: nowrap;">
                Branch
            </label>
            <select
                id="branch-switcher"
                wire:model.live="selectedBranchId"
                class="form-input"
                style="min-width: 10rem; padding: 0.375rem 0.625rem; font-size: 0.875rem;"
            >
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">
                        {{ $branch->name }}{{ $branch->is_main ? ' (Main)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    @elseif ($activeBranch)
        <div class="branch-switcher" style="display: inline-flex; align-items: center; margin-right: 0.75rem; font-size: 0.875rem; color: var(--muted-foreground);">
            {{ $activeBranch->name }}
        </div>
    @endif
</div>
