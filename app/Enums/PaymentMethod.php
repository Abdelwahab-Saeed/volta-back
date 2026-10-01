<?php

namespace App\Enums;

// Only cash on delivery for now; checkout rejects anything else. Add a case here when an online payment is ready.
enum PaymentMethod: string
{
    case CASH = 'cash';

    /**
     * Get all enum values as an array
     */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
