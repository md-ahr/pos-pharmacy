<?php

namespace App\Services\Reports\Concerns;

use App\Data\ReportFilters;
use App\Enums\SaleStatus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

trait AppliesSaleReportFilters
{
    /**
     * @param  Builder<Model>  $query
     * @return Builder<Model>
     */
    protected function applySaleReportFilters(Builder $query, ReportFilters $filters): Builder
    {
        return $query
            ->whereIn('status', [
                SaleStatus::Completed,
                SaleStatus::PartiallyRefunded,
            ])
            ->when($filters->branchId !== null, fn (Builder $builder): Builder => $builder->where('branch_id', $filters->branchId))
            ->when($filters->cashierId !== null, fn (Builder $builder): Builder => $builder->where('user_id', $filters->cashierId))
            ->whereBetween('sold_at', [$filters->from, $filters->to]);
    }
}
