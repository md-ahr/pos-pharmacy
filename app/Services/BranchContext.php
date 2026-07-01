<?php

namespace App\Services;

use App\Enums\PharmacyRole;
use App\Models\Branch;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Support\Facades\Session;

class BranchContext
{
    public function tenantSessionKey(): string
    {
        return config('pharmacy.session.tenant_id', 'pharmacy.tenant_id');
    }

    public function branchSessionKey(): string
    {
        return config('pharmacy.session.branch_id', 'pharmacy.branch_id');
    }

    public function initialize(User $user, ?int $preferredBranchId = null): void
    {
        if ($user->tenant_id === null) {
            $this->clear();

            return;
        }

        Session::put($this->tenantSessionKey(), $user->tenant_id);

        $branchId = $this->resolveActiveBranchId($user, $preferredBranchId);

        if ($branchId !== null) {
            Session::put($this->branchSessionKey(), $branchId);
        }
    }

    public function activeTenantId(): ?int
    {
        $tenantId = Session::get($this->tenantSessionKey());

        return is_numeric($tenantId) ? (int) $tenantId : null;
    }

    public function activeBranchId(): ?int
    {
        $branchId = Session::get($this->branchSessionKey());

        return is_numeric($branchId) ? (int) $branchId : null;
    }

    public function activeBranch(): ?Branch
    {
        $branchId = $this->activeBranchId();

        if ($branchId === null) {
            return null;
        }

        return Branch::query()->find($branchId);
    }

    public function canSwitchBranches(User $user): bool
    {
        if ($user->tenant_id === null) {
            return false;
        }

        $role = PharmacyRole::tryFrom((string) $user->role);

        return $role?->hasTenantWideAccess() ?? false;
    }

    /**
     * @throws AuthorizationException
     */
    public function switchBranch(User $user, int $branchId): void
    {
        if (! $this->canSwitchBranches($user)) {
            throw new AuthorizationException('You are not allowed to switch branches.');
        }

        $branch = Branch::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('id', $branchId)
            ->where('is_active', true)
            ->first();

        if ($branch === null) {
            throw new AuthorizationException('The selected branch is not available.');
        }

        Session::put($this->tenantSessionKey(), $user->tenant_id);
        Session::put($this->branchSessionKey(), $branch->id);
    }

    public function clear(): void
    {
        Session::forget([
            $this->tenantSessionKey(),
            $this->branchSessionKey(),
        ]);
    }

    protected function resolveActiveBranchId(User $user, ?int $preferredBranchId = null): ?int
    {
        if ($preferredBranchId !== null && $this->branchBelongsToTenant($preferredBranchId, $user->tenant_id)) {
            if ($user->branch_id === null || $user->branch_id === $preferredBranchId) {
                return $preferredBranchId;
            }
        }

        if ($user->branch_id !== null) {
            return $user->branch_id;
        }

        return Branch::query()
            ->where('tenant_id', $user->tenant_id)
            ->where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('id')
            ->value('id');
    }

    protected function branchBelongsToTenant(int $branchId, int $tenantId): bool
    {
        return Branch::query()
            ->where('id', $branchId)
            ->where('tenant_id', $tenantId)
            ->exists();
    }
}
