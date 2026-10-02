<?php

namespace App\Enums;

enum Role: string
{
    case Admin = 'admin';
    case Technician = 'tecnico';

    public function label(): string
    {
        return match ($this) {
            self::Admin => 'Administrador',
            self::Technician => 'Técnico',
        };
    }

    /** @return array<int, string> */
    public function permissions(): array
    {
        return match ($this) {
            self::Admin => Permission::values(),
            self::Technician => [Permission::ReportsCreate->value],
        };
    }
}
