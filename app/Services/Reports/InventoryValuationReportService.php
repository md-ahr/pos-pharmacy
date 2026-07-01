<?php

namespace App\Services\Reports;

use App\Models\Stock;
use Illuminate\Support\Collection;

class InventoryValuationReportService
{
    /**
     * @return array{total_quantity: int, total_value: string}
     */
    public function summary(?int $branchId): array
    {
        $rows = $this->rows($branchId);

        $totalQuantity = 0;
        $totalValue = '0.00';

        foreach ($rows as $row) {
            $totalQuantity += $row->quantity;
            $totalValue = bcadd($totalValue, $row->value, 2);
        }

        return [
            'total_quantity' => $totalQuantity,
            'total_value' => $totalValue,
        ];
    }

    /**
     * @return Collection<int, object{
     *     product_id: int,
     *     product_name: string,
     *     base_unit: string,
     *     quantity: int,
     *     value: string
     * }>
     */
    public function rows(?int $branchId): Collection
    {
        return Stock::query()
            ->join('products', 'stock.product_id', '=', 'products.id')
            ->join('batches', 'stock.batch_id', '=', 'batches.id')
            ->when($branchId !== null, fn ($query) => $query->where('stock.branch_id', $branchId))
            ->where('stock.quantity', '>', 0)
            ->groupBy('stock.product_id', 'products.name', 'products.base_unit')
            ->orderBy('products.name')
            ->selectRaw('stock.product_id')
            ->selectRaw('products.name as product_name')
            ->selectRaw('products.base_unit')
            ->selectRaw('SUM(stock.quantity) as quantity')
            ->selectRaw('COALESCE(SUM(stock.quantity * batches.cost_price), 0) as value')
            ->get()
            ->map(fn ($row) => (object) [
                'product_id' => (int) $row->product_id,
                'product_name' => (string) $row->product_name,
                'base_unit' => (string) $row->base_unit,
                'quantity' => (int) $row->quantity,
                'value' => number_format((float) $row->value, 2, '.', ''),
            ]);
    }
}
