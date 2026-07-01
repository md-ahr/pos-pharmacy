<?php

namespace App\Services;

use App\Models\Customer;
use Illuminate\Support\Collection;

class CustomerSearchService
{
    /**
     * @return Collection<int, Customer>
     */
    public function search(string $query, int $limit = 15): Collection
    {
        $query = trim($query);

        if ($query === '') {
            return collect();
        }

        return Customer::query()
            ->where(function ($builder) use ($query): void {
                $builder->where('name', 'ilike', "%{$query}%")
                    ->orWhere('phone', 'ilike', "%{$query}%")
                    ->orWhere('email', 'ilike', "%{$query}%");
            })
            ->orderByRaw('name nulls last')
            ->orderBy('name')
            ->limit($limit)
            ->get();
    }
}
