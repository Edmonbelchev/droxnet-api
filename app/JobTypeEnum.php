<?php

namespace App;

enum JobTypeEnum: string
{
    case FullTime   = 'full_time';
    case PartTime   = 'part_time';
    case Contract   = 'contract';
    case Temporary  = 'temporary';
    case Internship = 'internship';
    case Volunteer  = 'volunteer';
    case Remote     = 'remote';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabelText(): string
    {
        return match ($this) {
            self::FullTime   => 'Full Time',
            self::PartTime   => 'Part Time',
            self::Contract   => 'Contract',
            self::Temporary  => 'Temporary',
            self::Internship => 'Internship',
            self::Volunteer  => 'Volunteer',
            self::Remote     => 'Remote',
        };
    }
}
