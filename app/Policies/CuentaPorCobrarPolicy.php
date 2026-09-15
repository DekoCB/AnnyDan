<?php

namespace App\Policies;

use App\Models\CuentaPorCobrar;
use App\Models\User;

class CuentaPorCobrarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cuentas_cobrar.ver');
    }

    public function view(User $user, CuentaPorCobrar $cuentaPorCobrar): bool
    {
        return $user->can('cuentas_cobrar.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('cuentas_cobrar.crear');
    }

    public function pagar(User $user, CuentaPorCobrar $cuentaPorCobrar): bool
    {
        return $user->can('cuentas_cobrar.pagar');
    }

    /**
     * Solo quien puede crear cuentas por cobrar puede anularlas — no se
     * añade un permiso adicional solo para esto (ver sección 38: evitar
     * sobreingeniería).
     */
    public function anular(User $user, CuentaPorCobrar $cuentaPorCobrar): bool
    {
        return $user->can('cuentas_cobrar.crear');
    }
}
