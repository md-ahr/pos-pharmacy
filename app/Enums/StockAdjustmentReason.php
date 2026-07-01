<?php

namespace App\Enums;

enum StockAdjustmentReason: string
{
    case Damage = 'damage';
    case ExpiryWriteOff = 'expiry_write_off';
    case PhysicalCount = 'physical_count';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Damage => 'Damage',
            self::ExpiryWriteOff => 'Expiry write-off',
            self::PhysicalCount => 'Physical count reconciliation',
            self::Other => 'Other',
        };
    }
}
