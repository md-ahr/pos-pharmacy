<?php

namespace App\Services\Reports;

use App\Data\ReportFilters;
use App\Models\Stock;
use Illuminate\Support\Collection;

class ExpiryReportService
{
    /**
     * @return Collection<int, object{
     *     product_name: string,
     *     batch_no: string,
     *     expiry_date: string,
     *     quantity: int,
     *     base_unit: string,
     *     branch_name: string,
     *     status: string
     * }>
     */
    public function rows(ReportFilters $filters): Collection
    {
        return Stock::query()
            ->with(['product', 'batch', 'branch'])
            ->when($filters->branchId !== null, fn ($query) => $query->where('branch_id', $filters->branchId))
            ->where('quantity', '>', 0)
            ->whereHas('batch', function ($query) use ($filters): void {
                $query->where('expiry_date', '<=', today()->addDays($filters->expiryDaysAhead));
            })
            ->join('batches', 'stock.batch_id', '=', 'batches.id')
            ->orderBy('batches.expiry_date')
            ->select('stock.*')
            ->get()
            ->map(function (Stock $stock): object {
                $expiryDate = $stock->batch->expiry_date;
                $status = $expiryDate->isPast() ? 'expired' : 'expiring_soon';

                return (object) [
                    'product_name' => $stock->product->name,
                    'batch_no' => $stock->batch->batch_no,
                    'expiry_date' => $expiryDate->toDateString(),
                    'quantity' => $stock->quantity,
                    'base_unit' => $stock->product->base_unit,
                    'branch_name' => $stock->branch->name,
                    'status' => $status,
                ];
            });
    }
}
