<?php

namespace App;

enum JobDurationEnum: string
{
    case LessThanAMonth    = 'less_than_1_month';
    case OneToThreeMonths  = '1_to_3_months';
    case ThreeToSixMonths  = '3_to_6_months';
    case SixToTwelveMonths = '6_to_12_months';
    case MoreThanAYear     = 'more_than_12_months';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabelText(): string
    {
        return match ($this) {
            self::LessThanAMonth    => 'Less than a month',
            self::OneToThreeMonths  => '1 to 3 months',
            self::ThreeToSixMonths  => '3 to 6 months',
            self::SixToTwelveMonths => '6 to 12 months',
            self::MoreThanAYear     => 'More than a year',
        };
    }
}
