<?php

namespace App\Console\Commands;

use App\Models\Tenant;
use App\Services\Reports\DashboardMetricsService;
use Illuminate\Console\Command;

class GenerateDailyReportCommand extends Command
{
    protected $signature = 'pharmacy:generate-daily-report';

    protected $description = 'Generate a daily sales summary per tenant for logging or downstream notifications';

    public function handle(DashboardMetricsService $metrics): int
    {
        Tenant::query()->where('is_active', true)->each(function (Tenant $tenant) use ($metrics): void {
            $branchIds = $tenant->branches()->where('is_active', true)->pluck('id');

            foreach ($branchIds as $branchId) {
                $summary = $metrics->todaySummary($branchId);

                $this->line(sprintf(
                    'Tenant %s branch %d — sales: %s, revenue: %s, tax: %s',
                    $tenant->name,
                    $branchId,
                    $summary['sales_count'],
                    $summary['sales_total'],
                    $summary['tax_total'],
                ));
            }
        });

        $this->info('Daily report generation complete.');

        return self::SUCCESS;
    }
}
