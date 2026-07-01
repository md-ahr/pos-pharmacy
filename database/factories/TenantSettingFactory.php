<?php

namespace Database\Factories;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantSetting;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<TenantSetting>
 */
class TenantSettingFactory extends Factory
{
    protected $model = TenantSetting::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'currency_code' => 'USD',
            'tax_rate' => '0.1500',
            'receipt_header' => null,
            'receipt_footer' => 'Thank you for your purchase.',
            'default_branch_id' => null,
        ];
    }

    public function forTenant(Tenant $tenant, ?Branch $defaultBranch = null): static
    {
        return $this->state(fn (array $attributes) => [
            'tenant_id' => $tenant->id,
            'default_branch_id' => $defaultBranch?->id,
        ]);
    }
}
