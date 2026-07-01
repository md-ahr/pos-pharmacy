<?php

namespace App\Services\Reports;

use App\Enums\PaymentMethod;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Models\SalePayment;
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
}
