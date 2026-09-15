<?php

namespace App\Http\Controllers;

use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ReporteVentaController extends Controller
{
    public function index(Request $request): View
    {
        $this->authorize('reportes.ver');

        $anio = $request->integer('anio', now()->year);
        $mes = $request->integer('mes', now()->month);

        $inicio = Carbon::create($anio, $mes, 1)->startOfMonth();
        $fin = $inicio->copy()->endOfMonth();

        $ventasDelMes = Venta::query()
            ->where('ventas.estado', 'registrada')
            ->whereBetween('ventas.fecha', [$inicio, $fin]);

        $totalVentas = (clone $ventasDelMes)->sum('total');
        $numeroOperaciones = (clone $ventasDelMes)->count();
        $ticketPromedio = $numeroOperaciones > 0 ? $totalVentas / $numeroOperaciones : 0;

        $ventasPorCliente = (clone $ventasDelMes)
            ->join('clientes', 'clientes.id', '=', 'ventas.cliente_id')
            ->selectRaw('clientes.nombre_razon_social as cliente, SUM(ventas.total) as total, COUNT(*) as operaciones')
            ->groupBy('clientes.id', 'clientes.nombre_razon_social')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $ventasPorFormaPago = (clone $ventasDelMes)
            ->selectRaw('forma_pago, SUM(total) as total, COUNT(*) as operaciones')
            ->groupBy('forma_pago')
            ->orderByDesc('total')
            ->get();

        $inicioAnterior = $inicio->copy()->subMonthNoOverflow()->startOfMonth();
        $finAnterior = $inicioAnterior->copy()->endOfMonth();
        $totalMesAnterior = Venta::query()->where('estado', 'registrada')
            ->whereBetween('fecha', [$inicioAnterior, $finAnterior])->sum('total');

        $variacion = $totalMesAnterior > 0
            ? (($totalVentas - $totalMesAnterior) / $totalMesAnterior) * 100
            : null;

        return view('reportes.ventas', compact(
            'anio', 'mes', 'totalVentas', 'numeroOperaciones', 'ticketPromedio',
            'ventasPorCliente', 'ventasPorFormaPago', 'totalMesAnterior', 'variacion',
        ));
    }
}
