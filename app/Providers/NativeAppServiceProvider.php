<?php

namespace App\Providers;

use Database\Seeders\CategoriasMovimientoSeeder;
use Database\Seeders\RolesAndPermissionsSeeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;
use Native\Desktop\Contracts\ProvidesPhpIni;
use Native\Desktop\Facades\Window;
use Spatie\Permission\Models\Role;
use Throwable;

class NativeAppServiceProvider implements ProvidesPhpIni
{
    /**
     * Executed once the native application has been booted.
     * Use this method to open windows, register global shortcuts, etc.
     */
    public function boot(): void
    {
        $this->seedOnFirstRun();

        Window::open()
            ->title('AnnyDan')
            ->width(1280)
            ->height(800)
            ->minWidth(1024)
            ->minHeight(700);
    }

    /**
     * Return an array of php.ini directives to be set.
     */
    public function phpIni(): array
    {
        return [
        ];
    }

    /**
     * La base SQLite del usuario nace vacía: se siembran roles y categorías
     * solo la primera vez. El administrador lo crea el usuario en /register.
     */
    private function seedOnFirstRun(): void
    {
        try {
            if (! Schema::hasTable('roles') || Role::query()->exists()) {
                return;
            }

            foreach ([RolesAndPermissionsSeeder::class, CategoriasMovimientoSeeder::class] as $seeder) {
                Artisan::call('db:seed', ['--class' => $seeder, '--force' => true]);
            }
        } catch (Throwable $e) {
            report($e);
        }
    }
}
