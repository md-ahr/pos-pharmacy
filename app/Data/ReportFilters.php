<?php

namespace App\Data;

use Carbon\Carbon;

readonly class ReportFilters
{
    public Carbon $from;

    public Carbon $to;

    public function __construct(
        public ?int $branchId = null,
        public ?int $cashierId = null,
        public ?int $productId = null,
        ?Carbon $from = null,
        ?Carbon $to = null,
        public int $expiryDaysAhead = 90,
    ) {
        $this->from = ($from ?? today()->startOfMonth())->copy()->startOfDay();
        $this->to = ($to ?? today())->copy()->endOfDay();
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            branchId: self::nullableInt($data['branch_id'] ?? null),
            cashierId: self::nullableInt($data['cashier_id'] ?? null),
            productId: self::nullableInt($data['product_id'] ?? null),
            from: filled($data['from'] ?? null) ? Carbon::parse((string) $data['from']) : null,
            to: filled($data['to'] ?? null) ? Carbon::parse((string) $data['to']) : null,
            expiryDaysAhead: (int) ($data['expiry_days_ahead'] ?? 90),
        );
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'branch_id' => $this->branchId,
            'cashier_id' => $this->cashierId,
            'product_id' => $this->productId,
            'from' => $this->from->toDateString(),
            'to' => $this->to->toDateString(),
            'expiry_days_ahead' => $this->expiryDaysAhead,
        ];
    }

    private static function nullableInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return (int) $value;
    }
}
