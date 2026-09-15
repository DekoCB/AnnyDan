<?php

namespace App\Policies;

use App\Models\Comprobante;
use App\Models\User;

class ComprobantePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('comprobantes.ver') || $user->can('comprobantes.emitir');
    }

    public function view(User $user, Comprobante $comprobante): bool
    {
        return $user->can('comprobantes.ver') || $user->can('comprobantes.emitir');
    }

    public function create(User $user): bool
    {
        return $user->can('comprobantes.emitir');
    }

    public function anular(User $user, Comprobante $comprobante): bool
    {
        return $user->can('comprobantes.emitir');
    }
}
