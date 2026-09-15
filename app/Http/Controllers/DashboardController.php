<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use App\Models\MovimientoCaja;
use App\Models\PagoCliente;
use App\Models\PagoProveedor;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    /**
     * El dashboard es adaptativo: cada sección solo se consulta y se
     * muestra si el usuario tiene permiso sobre ese módulo (sección 2.10
     * del requerimiento — ocultar según permisos, no solo en Blade).
     */
    public function index(Request $request): View
    {
        $usuario = $request->user();
        $datos = [];

        if ($usuario->can('ventas.ver')) {
            $inicioMes = now()->startOfMonth();
            $finMes = now()->endOfMonth();

            $ventasMes = Venta::query()->where('estado', 'registrada')->whereBetween('fecha', [$inicioMes, $finMes]);

            $datos['totalVentasMes'] = (clone $ventasMes)->sum('total');
            $datos['numeroVentasMes'] = (clone $ventasMes)->count();
            $datos['ticketPromedioMes'] = $datos['numeroVentasMes'] > 0
                ? $datos['totalVentasMes'] / $datos['numeroVentasMes']
                : 0;
            $datos['totalVentasDia'] = Venta::query()->where('estado', 'registrada')->whereDate('fecha', now())->sum('total');
            $datos['ultimasVentas'] = Venta::query()->with('cliente')->latest('fecha')->limit(5)->get();
            $datos['ventasPorDia'] = $this->ventasPorDia();
        }

        if ($usuario->can('cuentas_cobrar.ver')) {
            $datos['cxcPendiente'] = CuentaPorCobrar::query()->pendientesOParciales()
                ->selectRaw('COALESCE(SUM(monto_total - monto_pagado), 0) as total')->value('total');
            $datos['cxcVencidasCantidad'] = CuentaPorCobrar::query()->vencidas()->count();
            $datos['cxcVencidasMonto'] = CuentaPorCobrar::query()->vencidas()
                ->selectRaw('COALESCE(SUM(monto_total - monto_pagado), 0) as total')->value('total');
        }

        if ($usuario->can('cuentas_pagar.ver')) {
            $datos['cxpPendiente'] = CuentaPorPagar::query()->pendientesOParciales()
                ->selectRaw('COALESCE(SUM(monto_total - monto_pagado), 0) as total')->value('total');
            $datos['cxpVencidasCantidad'] = CuentaPorPagar::query()->vencidas()->count();
        }

        if ($usuario->can('caja.ver')) {
            $inicioMes = now()->startOfMonth();
            $finMes = now()->endOfMonth();

            $movimientosMes = MovimientoCaja::query()->whereBetween('fecha', [$inicioMes, $finMes]);

            $datos['ingresosMes'] = (clone $movimientosMes)->where('tipo', 'ingreso')->sum('monto');
            $datos['egresosMes'] = (clone $movimientosMes)->where('tipo', 'egreso')->sum('monto');
            $datos['resultadoMes'] = $datos['ingresosMes'] - $datos['egresosMes'];

            $datos['pagosClientesRecientes'] = PagoCliente::query()->with('cuentaPorCobrar.cliente')->latest('fecha_pago')->limit(5)->get();
            $datos['pagosProveedoresRecientes'] = PagoProveedor::query()->with('cuentaPorPagar.proveedor')->latest('fecha_pago')->limit(5)->get();
        }

        if ($usuario->can('clientes.ver')) {
            $datos['clientesActivos'] = Cliente::query()->where('estado', true)->count();
        }

        return view('dashboard', $datos);
    }

    /**
     * @return array<int, array{fecha: string, total: float}>
     */
    private function ventasPorDia(): array
    {
        $desde = now()->subDays(6)->startOfDay();

        $totales = Venta::query()
            ->where('estado', 'registrada')
            ->where('fecha', '>=', $desde)
            ->selectRaw('DATE(fecha) as dia, SUM(total) as total')
            ->groupBy('dia')
            ->pluck('total', 'dia');

        $serie = [];

        for ($i = 6; $i >= 0; $i--) {
            $fecha = now()->subDays($i);
            $clave = $fecha->toDateString();

            $serie[] = [
                'fecha' => $fecha->format('d/m'),
                'total' => (float) ($totales[$clave] ?? 0),
            ];
        }

        return $serie;
    }
}
