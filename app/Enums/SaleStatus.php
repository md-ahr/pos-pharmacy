<?php

namespace App\Enums;

enum SaleStatus: string
{
    case Completed = 'completed';
    case Held = 'held';
    case Refunded = 'refunded';
    case PartiallyRefunded = 'partially_refunded';
}
