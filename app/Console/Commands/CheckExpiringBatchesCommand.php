<?php

namespace App\Console\Commands;

use App\Models\Batch;
use App\Models\Scopes\TenantScope;
use App\Models\Tenant;
use Illuminate\Console\Command;

class CheckExpiringBatchesCommand extends Command
{
    protected $signature = 'pharmacy:check-expiring-batches {--days=30 : Days ahead to check for expiry}';

    protected $description = 'Log batches expiring within the configured window for each tenant';

    public function handle(): int
    {
        $days = max(1, (int) $this->option('days'));
        $cutoff = now()->addDays($days)->toDateString();
        $total = 0;

        Tenant::query()->where('is_active', true)->each(function (Tenant $tenant) use ($cutoff, &$total): void {
            $count = Batch::query()
                ->withoutGlobalScope(TenantScope::class)
                ->where('tenant_id', $tenant->id)
                ->whereDate('expiry_date', '<=', $cutoff)
                ->whereDate('expiry_date', '>=', today())
                ->count();

            if ($count > 0) {
                $total += $count;
                $this->line("Tenant {$tenant->name} ({$tenant->id}): {$count} batch(es) expiring on or before {$cutoff}");
            }
        });

        $this->info("Checked expiring batches. {$total} batch(es) flagged.");

        return self::SUCCESS;
    }
}
