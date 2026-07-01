<?php

namespace App\Services;

use App\Models\Branch;
use App\Models\Tenant;
use App\Models\TenantSetting;

class TenantSettingsService
{
    public function forTenant(Tenant|int $tenant): TenantSetting
    {
        $tenantId = $tenant instanceof Tenant ? $tenant->id : $tenant;

        return TenantSetting::query()->firstOrCreate(
            ['tenant_id' => $tenantId],
            [
                'currency_code' => 'USD',
                'tax_rate' => config('pharmacy.pos.tax_rate', '0.1500'),
                'receipt_footer' => 'Thank you for your purchase.',
            ],
        );
    }

    public function taxRateFor(Tenant|int $tenant): string
    {
        $settings = $this->forTenant($tenant);

        return number_format((float) $settings->tax_rate, 4, '.', '');
    }

    public function currencyCodeFor(Tenant|int $tenant): string
    {
        return $this->forTenant($tenant)->currency_code;
    }

    /**
     * @param  array{
     *     currency_code?: string,
     *     tax_rate?: string|float,
     *     receipt_header?: string|null,
     *     receipt_footer?: string|null,
     *     default_branch_id?: int|null
     * }  $data
     */
    public function update(Tenant $tenant, array $data): TenantSetting
    {
        $settings = $this->forTenant($tenant);

        if (array_key_exists('default_branch_id', $data) && $data['default_branch_id'] !== null) {
            $branchExists = Branch::query()
                ->where('tenant_id', $tenant->id)
                ->where('id', $data['default_branch_id'])
                ->exists();

            if (! $branchExists) {
                $data['default_branch_id'] = null;
            }
        }

        $settings->update($data);

        return $settings->fresh();
    }
}
