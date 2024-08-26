<?php

namespace App;

enum JobBudgetTypeEnum: string
{
    case Hourly = 'hourly';
    case Fixed  = 'fixed';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabelText(): string
    {
        return match ($this) {
            self::Hourly => 'Hourly Rate',
            self::Fixed  => 'Fixed Price',
        };
    }
}
