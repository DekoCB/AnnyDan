<?php

namespace App\Policies;

use App\Models\Cliente;
use App\Models\User;

class ClientePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('clientes.ver');
    }

    public function view(User $user, Cliente $cliente): bool
    {
        return $user->can('clientes.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('clientes.crear');
    }

    public function update(User $user, Cliente $cliente): bool
    {
        return $user->can('clientes.editar');
    }

    /**
     * "Eliminar" un cliente nunca borra el registro: solo permite
     * activar/desactivar (ver ClienteController::destroy/activate).
     */
    public function delete(User $user, Cliente $cliente): bool
    {
        return $user->can('clientes.desactivar');
    }

    public function restore(User $user, Cliente $cliente): bool
    {
        return $user->can('clientes.desactivar');
    }

    public function forceDelete(User $user, Cliente $cliente): bool
    {
        return false;
    }
}
