<?php

namespace App\View\Composers;

use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

/**
 * Alimenta la campana de notificaciones del topbar con datos reales
 * (cuentas vencidas), no un contador decorativo. Es un View Composer
 * porque el topbar se incluye en cada página, no solo en el dashboard.
 */
class TopbarComposer
{
    public function compose(View $view): void
    {
        $usuario = Auth::user();
        $vencidas = 0;

        if ($usuario?->can('viewAny', CuentaPorCobrar::class)) {
            $vencidas += CuentaPorCobrar::query()->vencidas()->count();
        }

        if ($usuario?->can('viewAny', CuentaPorPagar::class)) {
            $vencidas += CuentaPorPagar::query()->vencidas()->count();
        }

        $view->with('cuentasVencidasCount', $vencidas);
    }
}
