<?php

namespace App\Policies;

use App\Models\MovimientoCaja;
use App\Models\User;

class MovimientoCajaPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('caja.ver');
    }

    public function view(User $user, MovimientoCaja $movimientoCaja): bool
    {
        return $user->can('caja.ver');
    }

    public function create(User $user): bool
    {
        return $user->can('caja.registrar');
    }
}
