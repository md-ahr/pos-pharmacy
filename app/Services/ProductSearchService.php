<?php

namespace App\Services;

use App\Models\Product;
use Illuminate\Support\Collection;

class ProductSearchService
{
    /**
     * @return Collection<int, Product>
     */
    public function search(string $query, int $limit = 20): Collection
    {
        $query = trim($query);

        if ($query === '') {
            return collect();
        }

        return Product::query()
            ->where('is_active', true)
            ->where(function ($builder) use ($query): void {
                $builder->where('name', 'ilike', "%{$query}%")
                    ->orWhere('generic_name', 'ilike', "%{$query}%")
                    ->orWhere('sku', 'ilike', "%{$query}%")
                    ->orWhere('barcode', $query)
                    ->orWhereHas('units', fn ($unitQuery) => $unitQuery->where('barcode', $query));
            })
            ->with(['units' => fn ($unitQuery) => $unitQuery->orderByDesc('is_default')])
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
