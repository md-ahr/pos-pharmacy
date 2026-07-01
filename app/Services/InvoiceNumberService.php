<?php

namespace App\Services;

use App\Models\InvoiceCounter;
use App\Models\Tenant;
use Illuminate\Support\Facades\DB;

class InvoiceNumberService
{
    public function next(Tenant $tenant, ?int $year = null): string
    {
        $year ??= (int) now()->format('Y');

        return DB::transaction(function () use ($tenant, $year): string {
            $counter = InvoiceCounter::query()
                ->where('tenant_id', $tenant->id)
                ->where('year', $year)
                ->lockForUpdate()
                ->first();

            if ($counter === null) {
                $counter = InvoiceCounter::query()->create([
                    'tenant_id' => $tenant->id,
                    'year' => $year,
                    'last_number' => 0,
                ]);

                $counter = InvoiceCounter::query()
                    ->whereKey($counter->id)
                    ->lockForUpdate()
                    ->firstOrFail();
            }

            $nextNumber = $counter->last_number + 1;
            $counter->update(['last_number' => $nextNumber]);

            return sprintf('INV-%d-%06d', $year, $nextNumber);
        });
    }
}
