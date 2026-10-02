<?php

namespace Database\Seeders;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::updateOrCreate(['email' => 'admin@informes.test'], [
            'name' => 'Administrador',
            'password' => 'password',
            'position' => 'Jefe de Mantenimiento',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $admin->syncRoles([Role::Admin->value]);

        $tech = User::updateOrCreate(['email' => 'tecnico@informes.test'], [
            'name' => 'Técnico Electricista',
            'password' => 'password',
            'position' => 'Técnico electricista',
            'is_active' => true,
            'email_verified_at' => now(),
        ]);
        $tech->syncRoles([Role::Technician->value]);
    }
}
