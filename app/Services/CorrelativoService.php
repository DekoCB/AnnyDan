<?php

namespace App\Services;

use App\Models\Correlativo;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

/**
 * Genera números correlativos únicos (numero_operacion de ventas, y más
 * adelante series de comprobantes) sin duplicados bajo concurrencia.
 * Usa un lock de fila (SELECT ... FOR UPDATE) dentro de una transacción —
 * ver sección 12 del requerimiento.
 */
class CorrelativoService
{
    public function siguiente(string $clave): int
    {
        return DB::transaction(function () use ($clave) {
            $correlativo = Correlativo::where('clave', $clave)->lockForUpdate()->first();

            if (! $correlativo) {
                try {
                    $correlativo = Correlativo::create(['clave' => $clave, 'valor' => 0]);
                } catch (QueryException) {
                    // Otra transacción concurrente ya la creó; re-consultamos con lock.
                    $correlativo = Correlativo::where('clave', $clave)->lockForUpdate()->firstOrFail();
                }
            }

            $correlativo->increment('valor');

            return $correlativo->valor;
        });
    }
}
