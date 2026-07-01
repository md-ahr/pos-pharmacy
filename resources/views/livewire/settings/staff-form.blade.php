<div>
    <div class="page-header">
        <div class="page-header-row">
            <div>
                <h1 class="page-title">{{ $staff ? 'Edit Staff' : 'Add Staff' }}</h1>
            </div>
            <a href="{{ route('pharmacy.settings.staff') }}" class="btn btn-ghost">Back</a>
        </div>
    </div>

    @if (session('success'))
        <div class="alert alert-success" style="margin-bottom: 1rem;">{{ session('success') }}</div>
    @endif

    <form wire:submit="save">
        <div class="card">
            <div class="card-body" style="display: grid; gap: 1rem; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));">
                <div>
                    <label class="form-label">Name</label>
                    <input type="text" wire:model="name" class="form-input">
                    @error('name') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Email</label>
                    <input type="email" wire:model="email" class="form-input">
                    @error('email') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Role</label>
                    <select wire:model.live="role" class="form-input">
                        @foreach($roles as $roleOption)
                            <option value="{{ $roleOption->value }}">{{ ucfirst($roleOption->value) }}</option>
                        @endforeach
                    </select>
                    @error('role') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Branch</label>
                    <select wire:model="branchId" class="form-input" @if(in_array($role, ['owner', 'manager'], true)) disabled @endif>
                        <option value="">All branches</option>
                        @foreach($branches as $branch)
                            <option value="{{ $branch->id }}">{{ $branch->name }}</option>
                        @endforeach
                    </select>
                    @error('branchId') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Password @if($staff)<span class="text-muted">(optional)</span>@endif</label>
                    <input type="password" wire:model="password" class="form-input">
                    @error('password') <div class="text-danger">{{ $message }}</div> @enderror
                </div>
                <div>
                    <label class="form-label">Confirm Password</label>
                    <input type="password" wire:model="password_confirmation" class="form-input">
                </div>
                <div>
                    <label><input type="checkbox" wire:model="isActive"> Active</label>
                </div>
            </div>
            <div class="card-body" style="border-top: 1px solid var(--border-color, #eee);">
                <button type="submit" class="btn btn-primary">Save Staff Member</button>
            </div>
        </div>
    </form>
</div>
