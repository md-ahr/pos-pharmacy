<?php

namespace App\Services;

use App\Enums\PharmacyRole;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class StaffManagementService
{
    /**
     * @param  array{
     *     name: string,
     *     email: string,
     *     password?: string|null,
     *     role: string,
     *     branch_id?: int|null,
     *     is_active?: bool
     * }  $data
     */
    public function create(Tenant $tenant, array $data): User
    {
        $this->assertRoleIsValid($data['role']);
        $this->assertBranchForRole($tenant, $data['role'], $data['branch_id'] ?? null);

        return DB::transaction(function () use ($tenant, $data): User {
            $user = User::query()->create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password'] ?? 'password'),
                'tenant_id' => $tenant->id,
                'branch_id' => $this->resolvedBranchId($data['role'], $data['branch_id'] ?? null),
                'role' => $data['role'],
                'is_active' => $data['is_active'] ?? true,
            ]);

            $this->syncTyroRole($user, $data['role']);

            return $user;
        });
    }

    /**
     * @param  array{
     *     name?: string,
     *     email?: string,
     *     password?: string|null,
     *     role?: string,
     *     branch_id?: int|null,
     *     is_active?: bool
     * }  $data
     */
    public function update(User $staff, Tenant $tenant, array $data): User
    {
        $this->assertStaffBelongsToTenant($staff, $tenant);

        if (isset($data['role'])) {
            $this->assertRoleIsValid($data['role']);
            $this->assertBranchForRole($tenant, $data['role'], $data['branch_id'] ?? $staff->branch_id);
        }

        return DB::transaction(function () use ($staff, $data): User {
            $payload = collect($data)->only(['name', 'email', 'is_active'])->filter()->all();

            if (isset($data['role'])) {
                $payload['role'] = $data['role'];
                $payload['branch_id'] = $this->resolvedBranchId($data['role'], $data['branch_id'] ?? null);
            } elseif (array_key_exists('branch_id', $data)) {
                $payload['branch_id'] = $this->resolvedBranchId((string) $staff->role, $data['branch_id']);
            }

            if (! empty($data['password'])) {
                $payload['password'] = Hash::make($data['password']);
            }

            $staff->update($payload);

            if (isset($data['role'])) {
                $this->syncTyroRole($staff, $data['role']);
            }

            return $staff->fresh();
        });
    }

    public function assertStaffBelongsToTenant(User $staff, Tenant $tenant): void
    {
        if ($staff->tenant_id !== $tenant->id) {
            throw new AuthorizationException('Staff member does not belong to your pharmacy.');
        }
    }

    protected function assertRoleIsValid(string $role): void
    {
        if (PharmacyRole::tryFrom($role) === null) {
            throw ValidationException::withMessages([
                'role' => 'Invalid pharmacy role selected.',
            ]);
        }
    }

    protected function assertBranchForRole(Tenant $tenant, string $role, ?int $branchId): void
    {
        $pharmacyRole = PharmacyRole::from($role);

        if ($pharmacyRole->hasTenantWideAccess()) {
            return;
        }

        if ($branchId === null) {
            throw ValidationException::withMessages([
                'branch_id' => 'Assign a branch for cashier and pharmacist roles.',
            ]);
        }

        $exists = Branch::query()
            ->where('tenant_id', $tenant->id)
            ->where('id', $branchId)
            ->exists();

        if (! $exists) {
            throw ValidationException::withMessages([
                'branch_id' => 'Selected branch is not available.',
            ]);
        }
    }

    protected function resolvedBranchId(string $role, ?int $branchId): ?int
    {
        $pharmacyRole = PharmacyRole::from($role);

        if ($pharmacyRole->hasTenantWideAccess()) {
            return null;
        }

        return $branchId;
    }

    protected function syncTyroRole(User $user, string $roleSlug): void
    {
        if (! method_exists($user, 'syncRoles')) {
            return;
        }

        $role = Role::query()->where('slug', $roleSlug)->first();

        if ($role !== null) {
            $user->syncRoles([$role]);
        }
    }
}
