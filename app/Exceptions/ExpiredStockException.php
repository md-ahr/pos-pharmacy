<?php

namespace App\Exceptions;

use Exception;

class ExpiredStockException extends Exception
{
    public static function forProduct(string $productName): self
    {
        return new self(
            "No non-expired stock available for {$productName}. Expired batches cannot be sold."
        );
    }

    public static function forBatch(string $batchNo): self
    {
        return new self(
            "Batch {$batchNo} has expired and cannot be used for this transaction."
        );
    }
}
