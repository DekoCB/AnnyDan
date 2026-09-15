<?php

namespace App\Policies;

use App\Models\Proveedor;
use App\Models\User;

class ProveedorPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('proveedores.ver');
    }

    public function view(User $user, Proveedor $proveedor): bool
    {
        return $user->can('proveedores.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('proveedores.crear');
    }

    public function update(User $user, Proveedor $proveedor): bool
    {
        return $user->can('proveedores.editar');
    }

    /**
     * "Eliminar" un proveedor nunca borra el registro: solo permite
     * activar/desactivar (ver ProveedorController::destroy/activate).
     */
    public function delete(User $user, Proveedor $proveedor): bool
    {
        return $user->can('proveedores.desactivar');
    }

    public function restore(User $user, Proveedor $proveedor): bool
    {
        return $user->can('proveedores.desactivar');
    }

    public function forceDelete(User $user, Proveedor $proveedor): bool
    {
        return false;
    }
}
