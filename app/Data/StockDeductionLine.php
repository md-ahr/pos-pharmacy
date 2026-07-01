<?php

namespace App\Data;

readonly class StockDeductionLine
{
    public function __construct(
        public int $batchId,
        public int $stockId,
        public int $quantityDeducted,
    ) {}
}
