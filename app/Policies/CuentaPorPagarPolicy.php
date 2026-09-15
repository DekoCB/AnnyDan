<?php

namespace App\Policies;

use App\Models\CuentaPorPagar;
use App\Models\User;

class CuentaPorPagarPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('cuentas_pagar.ver');
    }

    public function view(User $user, CuentaPorPagar $cuentaPorPagar): bool
    {
        return $user->can('cuentas_pagar.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('cuentas_pagar.crear');
    }

    public function pagar(User $user, CuentaPorPagar $cuentaPorPagar): bool
    {
        return $user->can('cuentas_pagar.pagar');
    }

    public function anular(User $user, CuentaPorPagar $cuentaPorPagar): bool
    {
        return $user->can('cuentas_pagar.crear');
    }
}
