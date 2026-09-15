<?php

namespace Database\Seeders;

use App\Enums\Rol;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    /**
     * Permisos agrupados por módulo. ADMINISTRADOR recibe acceso total
     * mediante Gate::before (ver AppServiceProvider), no por asignación.
     */
    private const PERMISOS_POR_ROL = [
        Rol::ADMINISTRATIVO->value => [
            'clientes.ver', 'clientes.crear', 'clientes.editar', 'clientes.desactivar',
            'proveedores.ver', 'proveedores.crear', 'proveedores.editar', 'proveedores.desactivar',
            'ventas.ver', 'ventas.crear', 'ventas.anular',
            'cuentas_cobrar.ver', 'cuentas_cobrar.crear', 'cuentas_cobrar.pagar',
            'cuentas_pagar.ver', 'cuentas_pagar.crear', 'cuentas_pagar.pagar',
            'caja.ver',
            'reportes.ver',
            'comprobantes.emitir',
        ],
        Rol::CAJERO->value => [
            'clientes.ver',
            'ventas.ver', 'ventas.crear',
            'cuentas_cobrar.ver', 'cuentas_cobrar.pagar',
            'caja.ver', 'caja.registrar',
            'comprobantes.emitir',
        ],
        Rol::CONTABILIDAD->value => [
            'clientes.ver', 'proveedores.ver',
            'ventas.ver',
            'cuentas_cobrar.ver', 'cuentas_pagar.ver',
            'caja.ver',
            'reportes.ver',
            'auditoria.ver',
            'comprobantes.ver',
        ],
    ];

    public function run(): void
    {
        $todosLosPermisos = collect(self::PERMISOS_POR_ROL)->flatten()->unique();

        $todosLosPermisos->each(
            fn (string $permiso) => Permission::findOrCreate($permiso)
        );

        foreach (Rol::cases() as $rol) {
            Role::findOrCreate($rol->value);
        }

        foreach (self::PERMISOS_POR_ROL as $rol => $permisos) {
            Role::findByName($rol)->syncPermissions($permisos);
        }
    }
}
