<?php

namespace App\Services;

use App\Models\Product;
use App\Models\ProductUnit;
use InvalidArgumentException;

class UnitConversionService
{
    public function toBaseUnits(Product $product, ?ProductUnit $unit, int $quantity): int
    {
        if ($quantity < 0) {
            throw new InvalidArgumentException('Quantity cannot be negative.');
        }

        if ($quantity === 0) {
            return 0;
        }

        if ($unit === null) {
            return $quantity;
        }

        if ($unit->product_id !== $product->id) {
            throw new InvalidArgumentException('The selected unit does not belong to this product.');
        }

        return $quantity * $unit->conversion_factor;
    }

    public function fromBaseUnits(Product $product, ProductUnit $unit, int $baseQuantity): int
    {
        if ($unit->product_id !== $product->id) {
            throw new InvalidArgumentException('The selected unit does not belong to this product.');
        }

        if ($unit->conversion_factor <= 0) {
            throw new InvalidArgumentException('Conversion factor must be positive.');
        }

        if ($baseQuantity % $unit->conversion_factor !== 0) {
            throw new InvalidArgumentException('Base quantity is not divisible by the unit conversion factor.');
        }

        return intdiv($baseQuantity, $unit->conversion_factor);
    }
}
