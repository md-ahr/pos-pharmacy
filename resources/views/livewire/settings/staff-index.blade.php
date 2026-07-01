<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">Staff</h1>
                <p class="page-description">Invite and manage staff roles and branch assignments.</p>
            </div>
            <a href="{{ route('pharmacy.settings.staff.create') }}" class="btn btn-primary">Add Staff</a>
        </div>
    </div>

    <div class="card" style="margin-bottom: 1rem;">
        <div class="card-body">
            <input type="search" wire:model.live.debounce.300ms="search" class="form-input" placeholder="Search staff...">
        </div>
    </div>

    <div class="card">
        <div class="card-body" style="overflow-x: auto;">
            <table class="table">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Branch</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($staff as $member)
                        <tr>
                            <td>{{ $member->name }}</td>
                            <td>{{ $member->email }}</td>
                            <td>{{ ucfirst((string) $member->role) }}</td>
                            <td>{{ $member->branch?->name ?? 'All branches' }}</td>
                            <td>{{ $member->is_active ? 'Active' : 'Inactive' }}</td>
                            <td>
                                <a href="{{ route('pharmacy.settings.staff.edit', $member) }}" class="btn btn-ghost btn-sm">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-muted">No staff members found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-body">{{ $staff->links() }}</div>
    </div>
</div>
