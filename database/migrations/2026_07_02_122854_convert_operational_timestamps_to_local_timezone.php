<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * @var array<string, list<string>>
     */
    private array $columns = [
        'sales' => ['sold_at'],
        'register_shifts' => ['opened_at', 'closed_at'],
        'purchase_orders' => ['ordered_at', 'received_at'],
        'batches' => ['received_at'],
        'stock_transfers' => ['transferred_at'],
        'sale_payments' => ['paid_at'],
    ];

    public function up(): void
    {
        $this->convertFromUtcToLocal();
    }

    public function down(): void
    {
        $this->convertFromLocalToUtc();
    }

    private function convertFromUtcToLocal(): void
    {
        if (! $this->shouldConvert()) {
            return;
        }

        $timezone = config('app.timezone');

        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement(
                    "UPDATE {$table} SET {$column} = ({$column} AT TIME ZONE 'UTC') AT TIME ZONE ? WHERE {$column} IS NOT NULL",
                    [$timezone],
                );
            }
        }
    }

    private function convertFromLocalToUtc(): void
    {
        if (! $this->shouldConvert()) {
            return;
        }

        $timezone = config('app.timezone');

        foreach ($this->columns as $table => $columns) {
            foreach ($columns as $column) {
                DB::statement(
                    "UPDATE {$table} SET {$column} = ({$column} AT TIME ZONE ?) AT TIME ZONE 'UTC' WHERE {$column} IS NOT NULL",
                    [$timezone],
                );
            }
        }
    }

    private function shouldConvert(): bool
    {
        return DB::getDriverName() === 'pgsql' && config('app.timezone') !== 'UTC';
    }
};
