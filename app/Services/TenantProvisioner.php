<?php

namespace App\Services;

use App\Enums\PharmacyRole;
use App\Models\Tenant;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class TenantProvisioner
{
    /**
     * @param  array{name: string, email: string, password: string, pharmacy_name: string}  $data
     */
    public function provision(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $slug = $this->uniqueSlug($data['pharmacy_name']);

            $tenant = Tenant::create([
                'name' => $data['pharmacy_name'],
                'slug' => $slug,
                'email' => $data['email'],
                'subscription_plan' => 'trial',
                'trial_ends_at' => now()->addDays(30),
                'is_active' => true,
            ]);

            $branch = $tenant->branches()->create([
                'name' => 'Main Branch',
                'code' => 'MAIN',
                'is_main' => true,
                'is_active' => true,
            ]);

            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => Hash::make($data['password']),
                'tenant_id' => $tenant->id,
                'branch_id' => null,
                'role' => PharmacyRole::Owner->value,
                'is_active' => true,
            ]);

            $this->assignOwnerRole($user);

            app(TenantSettingsService::class)->update($tenant, [
                'default_branch_id' => $branch->id,
            ]);

            app(RegisterShiftService::class)->ensureRegister($branch);

            app(BranchContext::class)->initialize($user, $branch->id);

            return $user;
        });
    }

    protected function uniqueSlug(string $pharmacyName): string
    {
        $baseSlug = Str::slug($pharmacyName);
        $slug = $baseSlug;
        $suffix = 1;

        while (Tenant::query()->where('slug', $slug)->exists()) {
            $slug = $baseSlug.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }

    protected function assignOwnerRole(User $user): void
    {
        if (! method_exists($user, 'assignRole')) {
            return;
        }

        $roleSlug = config('pharmacy.registration.owner_role_slug', 'owner');
        $role = Role::query()->where('slug', $roleSlug)->first();

        if ($role !== null) {
            $user->assignRole($role);
        }
    }
}
