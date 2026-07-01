<?php

namespace App\Enums;

enum PharmacyRole: string
{
    case Owner = 'owner';
    case Manager = 'manager';
    case Pharmacist = 'pharmacist';
    case Cashier = 'cashier';

    /**
     * @return list<string>
     */
    public static function tenantWide(): array
    {
        return [
            self::Owner->value,
            self::Manager->value,
        ];
    }

    public function hasTenantWideAccess(): bool
    {
        return in_array($this->value, self::tenantWide(), true);
    }
}
