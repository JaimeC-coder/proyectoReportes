<?php

namespace App\Enums;

enum ReportStatus: string
{
    case Draft = 'draft';
    case Final = 'final';

    public function label(): string
    {
        return match ($this) {
            self::Draft => 'Borrador',
            self::Final => 'Finalizado',
        };
    }
}
