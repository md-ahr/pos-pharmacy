<?php

namespace App\Services\Reports;

use App\Data\ReportFilters;
use App\Models\Sale;
use App\Services\Reports\Concerns\AppliesSaleReportFilters;
use Illuminate\Support\Collection;

class TaxReportService
{
    use AppliesSaleReportFilters;

    /**
     * @return array{tax_total: string, taxable_sales: string, sales_count: int}
     */
    public function summary(ReportFilters $filters): array
    {
        $totals = $this->applySaleReportFilters(Sale::query(), $filters)
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as tax_total')
            ->selectRaw('COALESCE(SUM(subtotal - discount_amount), 0) as taxable_sales')
            ->first();

        return [
            'sales_count' => (int) ($totals->sales_count ?? 0),
            'tax_total' => number_format((float) ($totals->tax_total ?? 0), 2, '.', ''),
            'taxable_sales' => number_format((float) ($totals->taxable_sales ?? 0), 2, '.', ''),
        ];
    }

    /**
     * @return Collection<int, object{period: string, sales_count: int, taxable_sales: string, tax_total: string}>
     */
    public function dailyBreakdown(ReportFilters $filters): Collection
    {
        return $this->applySaleReportFilters(Sale::query(), $filters)
            ->selectRaw('DATE(sold_at) as period')
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(subtotal - discount_amount), 0) as taxable_sales')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as tax_total')
            ->groupByRaw('DATE(sold_at)')
            ->orderBy('period')
            ->get()
            ->map(fn ($row) => (object) [
                'period' => (string) $row->period,
                'sales_count' => (int) $row->sales_count,
                'taxable_sales' => number_format((float) $row->taxable_sales, 2, '.', ''),
                'tax_total' => number_format((float) $row->tax_total, 2, '.', ''),
            ]);
    }
}
