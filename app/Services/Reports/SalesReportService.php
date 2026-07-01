<?php

namespace App\Services\Reports;

use App\Data\ReportFilters;
use App\Enums\SaleStatus;
use App\Models\Sale;
use App\Models\SaleItem;
use App\Services\Reports\Concerns\AppliesSaleReportFilters;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class SalesReportService
{
    use AppliesSaleReportFilters;

    /**
     * @return array{
     *     sales_count: int,
     *     subtotal: string,
     *     discount_total: string,
     *     tax_total: string,
     *     total: string
     * }
     */
    public function summary(ReportFilters $filters): array
    {
        $query = $this->applySaleReportFilters(Sale::query(), $filters);

        if ($filters->productId !== null) {
            $query->whereHas('items', fn ($builder) => $builder->where('product_id', $filters->productId));
        }

        $totals = $query
            ->selectRaw('COUNT(*) as sales_count')
            ->selectRaw('COALESCE(SUM(subtotal), 0) as subtotal')
            ->selectRaw('COALESCE(SUM(discount_amount), 0) as discount_total')
            ->selectRaw('COALESCE(SUM(tax_amount), 0) as tax_total')
            ->selectRaw('COALESCE(SUM(total), 0) as total')
            ->first();

        return [
            'sales_count' => (int) ($totals->sales_count ?? 0),
            'subtotal' => number_format((float) ($totals->subtotal ?? 0), 2, '.', ''),
            'discount_total' => number_format((float) ($totals->discount_total ?? 0), 2, '.', ''),
            'tax_total' => number_format((float) ($totals->tax_total ?? 0), 2, '.', ''),
            'total' => number_format((float) ($totals->total ?? 0), 2, '.', ''),
        ];
    }

    /**
     * @return LengthAwarePaginator<int, Sale>
     */
    public function paginatedSales(ReportFilters $filters, int $perPage = 20): LengthAwarePaginator
    {
        $query = $this->applySaleReportFilters(
            Sale::query()->with(['cashier', 'branch', 'customer']),
            $filters,
        );

        if ($filters->productId !== null) {
            $query->whereHas('items', fn ($builder) => $builder->where('product_id', $filters->productId));
        }

        return $query
            ->orderByDesc('sold_at')
            ->paginate($perPage);
    }

    /**
     * @return Collection<int, object{product_id: int, product_name: string, quantity_sold: int, revenue: string}>
     */
    public function productBreakdown(ReportFilters $filters): Collection
    {
        return SaleItem::query()
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
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
            ->get()
            ->map(fn ($row) => (object) [
                'product_id' => (int) $row->product_id,
                'product_name' => (string) $row->product_name,
                'quantity_sold' => (int) $row->quantity_sold,
                'revenue' => number_format((float) $row->revenue, 2, '.', ''),
            ]);
    }
}
