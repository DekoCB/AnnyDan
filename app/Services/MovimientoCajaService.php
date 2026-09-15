<?php

namespace App\Services;

use App\Models\CategoriaMovimiento;
use App\Models\MovimientoCaja;
use Illuminate\Database\Eloquent\Model;

/**
 * Único punto donde se crean movimientos de caja. Un movimiento de
 * ingreso solo nace cuando se registra un cobro real (PagoCliente), y uno
 * de egreso solo cuando se registra un pago real a proveedor
 * (PagoProveedor) — nunca al crear una venta o una cuenta por
 * cobrar/pagar. Ver sección 11 del requerimiento: VENTA ≠ INGRESO DE
 * CAJA, CUENTA POR COBRAR ≠ DINERO COBRADO.
 */
class MovimientoCajaService
{
    public function registrarDesdeOrigen(
        string $tipo,
        string $nombreCategoria,
        string $monto,
        \DateTimeInterface $fecha,
        Model $origen,
        ?string $descripcion,
        int $userId,
    ): MovimientoCaja {
        $categoria = CategoriaMovimiento::where('nombre', $nombreCategoria)->where('tipo', $tipo)->firstOrFail();

        return MovimientoCaja::create([
            'tipo' => $tipo,
            'categoria_id' => $categoria->id,
            'monto' => $monto,
            'fecha' => $fecha,
            'origen_type' => $origen->getMorphClass(),
            'origen_id' => $origen->getKey(),
            'descripcion' => $descripcion,
            'user_id' => $userId,
        ]);
    }
}
