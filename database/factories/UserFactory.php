<?php

namespace Database\Factories;

use App\Enums\PharmacyRole;
use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
use HasinHayder\Tyro\Models\Role;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= Hash::make('password'),
            'remember_token' => Str::random(10),
            'tenant_id' => null,
            'branch_id' => null,
            'role' => null,
            'is_active' => true,
        ];
    }

    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    public function forTenant(Tenant $tenant, ?Branch $branch = null): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
            'branch_id' => $branch?->id,
        ]);
    }

    public function owner(Tenant $tenant): static
    {
        return $this->forTenant($tenant)->state(fn (array $attributes) => [
            'role' => PharmacyRole::Owner->value,
            'branch_id' => null,
        ])->afterCreating(function (User $user): void {
            $this->assignTyroRole($user, PharmacyRole::Owner->value);
        });
    }

    public function manager(Tenant $tenant, ?Branch $branch = null): static
    {
        return $this->forTenant($tenant, $branch)->state(fn (array $attributes) => [
            'role' => PharmacyRole::Manager->value,
            'branch_id' => $branch?->id,
        ])->afterCreating(function (User $user): void {
            $this->assignTyroRole($user, PharmacyRole::Manager->value);
        });
    }

    public function pharmacist(Tenant $tenant, Branch $branch): static
    {
        return $this->forTenant($tenant, $branch)->state(fn (array $attributes) => [
            'role' => PharmacyRole::Pharmacist->value,
        ])->afterCreating(function (User $user): void {
            $this->assignTyroRole($user, PharmacyRole::Pharmacist->value);
        });
    }

    public function cashier(Tenant $tenant, Branch $branch): static
    {
        return $this->forTenant($tenant, $branch)->state(fn (array $attributes) => [
            'role' => PharmacyRole::Cashier->value,
        ])->afterCreating(function (User $user): void {
            $this->assignTyroRole($user, PharmacyRole::Cashier->value);
        });
    }

    protected function assignTyroRole(User $user, string $slug): void
    {
        if (! method_exists($user, 'assignRole')) {
            return;
        }

        $role = Role::query()->where('slug', $slug)->first();

        if ($role !== null) {
            $user->assignRole($role);
        }
    }
}
