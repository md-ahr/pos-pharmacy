<?php

namespace App\Services\Reports;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
use App\Support\SvgChartBuilder;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class DashboardMetricsService
{
    /**
     * @return array{
     *     sales_count: int,
     *     sales_total: string,
     *     tax_total: string,
     *     discount_total: string,
     *     payment_totals: array<string, string>
     * }
     */
    public function todaySummary(?int $branchId): array
    {
        $salesQuery = Sale::query()
            ->whereIn('status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->whereDate('sold_at', today())
            ->when($branchId !== null, fn ($query) => $query->where('branch_id', $branchId));

        $totals = (clone $salesQuery)
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(total), 0) as sales_total')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as tax_total')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_total')
            ->first();

        $paymentTotals = SalePayment::query()
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereIn('sales.status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->whereDate('sales.sold_at', today())
            ->when($branchId !== null, fn ($query) => $query->where('sales.branch_id', $branchId))
            ->groupBy('sale_payments.method')
            ->selectRaw('sale_payments.method, COALESCE(SUM(sale_payments.amount), 0) as total')
            ->pluck('total', 'method');

        $paymentBreakdown = [];

        foreach (PaymentMethod::cases() as $method) {
            $paymentBreakdown[$method->value] = number_format((float) ($paymentTotals[$method->value] ?? 0), 2, '.', '');
        }

        return [
            'sales_count' => (int) ($totals->sales_count ?? 0),
            'sales_total' => number_format((float) ($totals->sales_total ?? 0), 2, '.', ''),
            'tax_total' => number_format((float) ($totals->tax_total ?? 0), 2, '.', ''),
            'discount_total' => number_format((float) ($totals->discount_total ?? 0), 2, '.', ''),
            'payment_totals' => $paymentBreakdown,
        ];
    }

    /**
     * @return Collection<int, object{product_id: int, product_name: string, quantity_sold: int, revenue: string}>
     */
    public function topProducts(?int $branchId, int $limit = 5): Collection
    {
        return SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereIn('sales.status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->whereDate('sales.sold_at', today())
            ->when($branchId !== null, fn ($query) => $query->where('sales.branch_id', $branchId))
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderByDesc(DB::raw('SUM(sale_items.quantity_base)'))
            ->limit($limit)
            ->selectRaw('sale_items.product_id')
            ->selectRaw('products.name as product_name')
            ->selectRaw('SUM(sale_items.quantity_base) as quantity_sold')
            ->selectRaw('COALESCE(SUM(sale_items.line_total), 0) as revenue')
            ->get()
            ->map(fn ($row) => (object) [
                'product_id' => (int) $row->product_id,
                'product_name' => (string) $row->product_name,
                'quantity_sold' => (int) $row->quantity_sold,
                'revenue' => number_format((float) $row->revenue, 2, '.', ''),
            ]);
    }

    /**
     * @return array{
     *     revenue_total: string,
     *     revenue_growth_pct: float,
     *     revenue_line_path: string,
     *     revenue_area_path: string,
     *     revenue_range_label_left: string,
     *     revenue_range_label_right: string,
     *     revenue_y_ticks: list<string>,
     *     weekly_bars: list<array{label: string, value: float, pct: int}>,
     *     weekly_total: string,
     *     payment_donut: list<array{label: string, amount: string, pct: int, color: string}>,
     *     payment_total: string,
     *     top_product_bars: list<array{label: string, value: string, pct: int, color: string}>
     * }
     */
    public function charts(?int $branchId): array
    {
        $revenueSeries = $this->dailyRevenue($branchId, 14);
        $lineChart = SvgChartBuilder::lineChart(
            $revenueSeries->pluck('value')->map(fn ($value): float => (float) $value)->all()
        );

        $previousWeekTotal = (float) $revenueSeries->slice(0, 7)->sum('value');
        $currentWeekTotal = (float) $revenueSeries->slice(7)->sum('value');
        $growthPct = $previousWeekTotal > 0
            ? round((($currentWeekTotal - $previousWeekTotal) / $previousWeekTotal) * 100, 1)
            : ($currentWeekTotal > 0 ? 100.0 : 0.0);

        $weeklySeries = $this->dailyRevenue($branchId, 7);
        $weeklyBars = SvgChartBuilder::barHeights(
            $weeklySeries
                ->map(fn (array $day): array => [
                    'label' => $day['label'],
                    'value' => $day['value'],
                ])
                ->all()
        );

        $paymentDonut = $this->paymentDonut($branchId, 7);
        $topProductBars = $this->topProductBars($branchId);

        $firstDay = $revenueSeries->first();

        return [
            'revenue_total' => number_format($lineChart['total'], 2, '.', ''),
            'revenue_growth_pct' => $growthPct,
            'revenue_line_path' => $lineChart['line_path'],
            'revenue_area_path' => $lineChart['area_path'],
            'revenue_range_label_left' => $firstDay !== null
                ? Carbon::parse($firstDay['date'])->format('M j')
                : today()->subDays(13)->format('M j'),
            'revenue_range_label_right' => 'Today',
            'revenue_y_ticks' => $lineChart['y_ticks'],
            'weekly_bars' => $weeklyBars,
            'weekly_total' => number_format((float) $weeklySeries->sum('value'), 2, '.', ''),
            'payment_donut' => $paymentDonut['slices'],
            'payment_total' => $paymentDonut['total'],
            'top_product_bars' => $topProductBars,
        ];
    }

    /**
     * @return Collection<int, array{label: string, date: string, value: float}>
     */
    public function dailyRevenue(?int $branchId, int $days): Collection
    {
        $start = today()->subDays($days - 1);

        $rows = Sale::query()
            ->whereIn('status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->whereDate('sold_at', '>=', $start)
            ->whereDate('sold_at', '<=', today())
            ->when($branchId !== null, fn ($query) => $query->where('branch_id', $branchId))
            ->groupByRaw('DATE(sold_at)')
            ->orderByRaw('DATE(sold_at)')
            ->selectRaw('DATE(sold_at) as sale_date')
            ->selectRaw('COALESCE(SUM(total), 0) as revenue')
            ->pluck('revenue', 'sale_date');

        $series = collect();

        for ($offset = 0; $offset < $days; $offset++) {
            $date = $start->copy()->addDays($offset);
            $key = $date->toDateString();

            $series->push([
                'label' => $days <= 7 ? $date->format('D') : $date->format('M j'),
                'date' => $key,
                'value' => (float) ($rows[$key] ?? 0),
            ]);
        }

        return $series;
    }

    /**
     * @return array{total: string, slices: list<array{label: string, amount: string, pct: int, color: string}>}
     */
    public function paymentDonut(?int $branchId, int $days): array
    {
        $start = today()->subDays($days - 1);

        $paymentTotals = SalePayment::query()
            ->join('sales', 'sale_payments.sale_id', '=', 'sales.id')
            ->whereIn('sales.status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->whereDate('sales.sold_at', '>=', $start)
            ->whereDate('sales.sold_at', '<=', today())
            ->when($branchId !== null, fn ($query) => $query->where('sales.branch_id', $branchId))
            ->groupBy('sale_payments.method')
            ->selectRaw('sale_payments.method, COALESCE(SUM(sale_payments.amount), 0) as total')
            ->pluck('total', 'method');

        $colors = [
            PaymentMethod::Cash->value => 'var(--success)',
            PaymentMethod::Card->value => 'var(--primary)',
            PaymentMethod::Mobile->value => 'var(--info)',
            PaymentMethod::Other->value => 'var(--muted-foreground)',
        ];

        $amounts = [];

        foreach (PaymentMethod::cases() as $method) {
            $amount = (float) ($paymentTotals[$method->value] ?? 0);

            if ($amount <= 0) {
                continue;
            }

            $amounts[] = [
                'label' => ucfirst($method->value),
                'value' => $amount,
                'color' => $colors[$method->value],
            ];
        }

        $grandTotal = array_sum(array_column($amounts, 'value'));

        if ($grandTotal <= 0) {
            return [
                'total' => '0.00',
                'slices' => [],
            ];
        }

        $slices = [];
        $remainingPct = 100;

        foreach ($amounts as $index => $amount) {
            $isLast = $index === count($amounts) - 1;
            $pct = $isLast
                ? $remainingPct
                : (int) round(($amount['value'] / $grandTotal) * 100);
            $remainingPct -= $pct;

            $slices[] = [
                'label' => $amount['label'],
                'amount' => number_format($amount['value'], 2, '.', ''),
                'pct' => max(1, $pct),
                'color' => $amount['color'],
            ];
        }

        return [
            'total' => number_format($grandTotal, 2, '.', ''),
            'slices' => $slices,
        ];
    }

    /**
     * @return list<array{label: string, value: string, pct: int, color: string}>
     */
    public function topProductBars(?int $branchId, int $limit = 5): array
    {
        $products = $this->topProducts($branchId, $limit);

        if ($products->isEmpty()) {
            return [];
        }

        $maxRevenue = (float) $products->max(fn (object $product): float => (float) $product->revenue) ?: 1.0;
        $colors = [
            'var(--chart-1)',
            'var(--chart-2)',
            'var(--chart-3)',
            'var(--chart-4)',
            'var(--chart-5)',
        ];

        return $products
            ->values()
            ->map(fn (object $product, int $index): array => [
                'label' => $product->product_name,
                'value' => $product->revenue,
                'pct' => (int) round(((float) $product->revenue / $maxRevenue) * 100),
                'color' => $colors[$index % count($colors)],
            ])
            ->all();
    }
}
