<?php

namespace App\Services;

/**
 * Única fuente de verdad para el cálculo de subtotal/impuestos/total de una
 * venta. El frontend puede mostrar un cálculo visual (ver
 * resources/ts/modules/venta-form.ts) pero nunca se confía en un total
 * enviado por el navegador — ver sección 2.7 del requerimiento.
 */
class CalculoVentaService
{
    /**
     * @param  array<int, array{descripcion: string, cantidad: numeric-string|float|int, precio_unitario: numeric-string|float|int}>  $detalles
     * @return array{lineas: array<int, array{descripcion: string, cantidad: string, precio_unitario: string, subtotal: string}>, subtotal: string, impuestos: string, total: string}
     */
    public function calcular(array $detalles): array
    {
        $tasaIgv = (string) config('facturacion.tasa_igv');
        $subtotal = '0.00';
        $lineas = [];

        foreach ($detalles as $detalle) {
            $cantidad = number_format((float) $detalle['cantidad'], 2, '.', '');
            $precioUnitario = number_format((float) $detalle['precio_unitario'], 2, '.', '');
            $subtotalLinea = bcmul($cantidad, $precioUnitario, 2);

            $subtotal = bcadd($subtotal, $subtotalLinea, 2);

            $lineas[] = [
                'descripcion' => $detalle['descripcion'],
                'cantidad' => $cantidad,
                'precio_unitario' => $precioUnitario,
                'subtotal' => $subtotalLinea,
            ];
        }

        $impuestos = bcmul($subtotal, $tasaIgv, 2);
        $total = bcadd($subtotal, $impuestos, 2);

        return [
            'lineas' => $lineas,
            'subtotal' => $subtotal,
            'impuestos' => $impuestos,
            'total' => $total,
        ];
    }
}
