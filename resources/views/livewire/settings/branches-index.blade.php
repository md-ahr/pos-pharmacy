<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Branches</h1>
                <p class="page-description">Manage pharmacy locations and branch defaults.</p>
            </div>
            <a href="{{ route('pharmacy.settings.branches.create') }}" class="btn btn-primary">Add Branch</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <input type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Search branches...">
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Code</th>
                        <th>Phone</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($branches as $branch)
                        <tr>
                            <td>
                                {{ $branch->name }}
                                @if($branch->is_main)
                                    <span class="badge">Main</span>
                                @endif
                            </td>
                            <td>{{ $branch->code }}</td>
                            <td>{{ $branch->phone ?? '—' }}</td>
                            <td>{{ $branch->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <a href="{{ route('pharmacy.settings.branches.edit', $branch) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="text-muted">No branches found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $branches->links() }}</div>
    </div>
</div>
