<?php

namespace App\Enums;

enum Permission: string
{
    case ReportsCreate = 'reports.create';
    case ReportsViewAll = 'reports.view_all';
    case ReportsDelete = 'reports.delete';
    case TemplatesManage = 'templates.manage';
    case UsersManage = 'users.manage';
    case SettingsManage = 'settings.manage';

    /** @return array<int, string> */
    public static function values(): array
    {
        return array_column(self::cases(), 'value');
    }
}
