<div>
    @if ($canSwitch && $branches->isNotEmpty())
        <div class="branch-switcher">
            <label for="branch-switcher" class="form-label branch-switcher-label">
                Branch
            </label>
            <select
                id="branch-switcher"
                wire:model.live="selectedBranchId"
                class="form-select form-select-compact branch-switcher-select"
            >
                @foreach ($branches as $branch)
                    <option value="{{ $branch->id }}">
                        {{ $branch->name }}{{ $branch->is_main ? ' (Main)' : '' }}
                    </option>
                @endforeach
            </select>
        </div>
    @elseif ($activeBranch)
        <div class="branch-switcher branch-switcher-readonly">
            {{ $activeBranch->name }}
        </div>
    @endif
</div>
