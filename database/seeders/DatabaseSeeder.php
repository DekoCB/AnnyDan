<?php

namespace Database\Seeders;

use App\Enums\Rol;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            RolesAndPermissionsSeeder::class,
            CategoriasMovimientoSeeder::class,
        ]);

        $admin = User::firstOrCreate(
            ['email' => 'admin@annydan.test'],
            ['name' => 'Administrador', 'password' => 'password']
        );
        $admin->assignRole(Rol::ADMINISTRADOR->value);
    }
}
