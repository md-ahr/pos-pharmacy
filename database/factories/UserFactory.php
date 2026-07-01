<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\User;
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

    public function owner(Tenant $tenant, Branch $branch): static
    {
        return $this->forTenant($tenant, $branch)->state(fn (array $attributes) => [
            'role' => 'owner',
        ]);
    }
}
