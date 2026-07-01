<?php

namespace App\Exceptions;

use Exception;

class InsufficientStockException extends Exception
{
    public static function forProduct(string $productName, int $requested, int $available): self
    {
        return new self(
            "Insufficient stock for {$productName}. Requested {$requested} base units, only {$available} available."
        );
    }
}
