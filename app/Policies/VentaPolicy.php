<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Venta;

/**
 * Una venta nunca se edita ni se borra una vez registrada — solo se
 * consulta o se anula (ver sección 16 del requerimiento). Por eso este
 * Policy no define update/delete/restore, solo las abilities reales que
 * expone VentaController.
 */
class VentaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ventas.ver');
    }

    public function view(User $user, Venta $venta): bool
    {
        return $user->can('ventas.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('ventas.crear');
    }

    public function anular(User $user, Venta $venta): bool
    {
        return $user->can('ventas.anular');
    }
}
