<?php

namespace App;

enum JobLevelEnum: string
{
    case Entry        = 'entry';
    case Intermediate = 'intermediate';
    case Expert       = 'expert';

    public static function toArray(): array
    {
        return array_column(self::cases(), 'value');
    }

    public function getLabelText(): string
    {
        return match ($this) {
            self::Entry        => 'Entry Level',
            self::Intermediate => 'Intermediate',
            self::Expert       => 'Expert',
        };
    }
}