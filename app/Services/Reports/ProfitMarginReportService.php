<?php

namespace App\Services\Reports;

use App\Data\ReportFilters;
use App\Enums\SaleStatus;
use App\Models\SaleItem;
use Illuminate\Support\Collection;

class ProfitMarginReportService
{
    /**
     * @return array{
     *     revenue: string,
     *     cost: string,
     *     profit: string,
     *     margin_percent: string
     * }
     */
    public function summary(ReportFilters $filters): array
    {
        $rows = $this->rows($filters);

        $revenue = '0.00';
        $cost = '0.00';

        foreach ($rows as $row) {
            $revenue = bcadd($revenue, $row->revenue, 2);
            $cost = bcadd($cost, $row->cost, 2);
        }

        $profit = bcsub($revenue, $cost, 2);
        $marginPercent = bccomp($revenue, '0', 2) === 1
            ? bcmul(bcdiv($profit, $revenue, 4), '100', 2)
            : '0.00';

        return [
            'revenue' => $revenue,
            'cost' => $cost,
            'profit' => $profit,
            'margin_percent' => $marginPercent,
        ];
    }

    /**
     * @return Collection<int, object{
     *     product_id: int,
     *     product_name: string,
     *     quantity_sold: int,
     *     revenue: string,
     *     cost: string,
     *     profit: string,
     *     margin_percent: string
     * }>
     */
    public function rows(ReportFilters $filters): Collection
    {
        return SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->join('batches', 'sale_items.batch_id', '=', 'batches.id')
            ->whereIn('sales.status', [SaleStatus::Completed, SaleStatus::PartiallyRefunded])
            ->when($filters->branchId !== null, fn ($query) => $query->where('sales.branch_id', $filters->branchId))
            ->when($filters->cashierId !== null, fn ($query) => $query->where('sales.user_id', $filters->cashierId))
            ->when($filters->productId !== null, fn ($query) => $query->where('sale_items.product_id', $filters->productId))
            ->whereBetween('sales.sold_at', [$filters->from, $filters->to])
            ->groupBy('sale_items.product_id', 'products.name')
            ->orderBy('products.name')
            ->selectRaw('sale_items.product_id')
            ->selectRaw('products.name as product_name')
            ->selectRaw('SUM(sale_items.quantity_base) as quantity_sold')
            ->selectRaw('COALESCE(SUM(sale_items.line_total), 0) as revenue')
            ->selectRaw('COALESCE(SUM(batches.cost_price * sale_items.quantity_base), 0) as cost')
            ->get()
            ->map(function ($row): object {
                $revenue = number_format((float) $row->revenue, 2, '.', '');
                $cost = number_format((float) $row->cost, 2, '.', '');
                $profit = bcsub($revenue, $cost, 2);
                $marginPercent = bccomp($revenue, '0', 2) === 1
                    ? bcmul(bcdiv($profit, $revenue, 4), '100', 2)
                    : '0.00';

                return (object) [
                    'product_id' => (int) $row->product_id,
                    'product_name' => (string) $row->product_name,
                    'quantity_sold' => (int) $row->quantity_sold,
                    'revenue' => $revenue,
                    'cost' => $cost,
                    'profit' => $profit,
                    'margin_percent' => $marginPercent,
                ];
            });
    }
}
