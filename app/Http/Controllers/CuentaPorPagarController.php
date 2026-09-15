<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrarPagoProveedorRequest;
use App\Http\Requests\StoreCuentaPorPagarRequest;
use App\Models\CuentaPorPagar;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Services\AuditoriaService;
use App\Services\MovimientoCajaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CuentaPorPagarController extends Controller
{
    public function __construct(
        private readonly AuditoriaService $auditoria,
        private readonly MovimientoCajaService $movimientosCaja,
    ) {
        // No es un recurso REST estándar (no se edita; se paga o se anula),
        // así que autorizamos cada acción explícitamente.
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', CuentaPorPagar::class);

        $cuentas = CuentaPorPagar::query()
            ->with('proveedor')
            ->when($request->filled('proveedor_id'), fn ($query) => $query->where('proveedor_id', $request->integer('proveedor_id')))
            ->when($request->filled('estado'), function ($query) use ($request) {
                if ($request->string('estado')->toString() === 'vencida') {
                    $query->vencidas();
                } else {
                    $query->where('estado', $request->string('estado'));
                }
            })
            ->latest('fecha_emision')
            ->paginate(15)
            ->withQueryString();

        $proveedores = Proveedor::query()->orderBy('nombre_razon_social')->get();

        return view('cuentas-pagar.index', compact('cuentas', 'proveedores'));
    }

    public function create(): View
    {
        $this->authorize('create', CuentaPorPagar::class);

        $proveedores = Proveedor::query()->where('estado', true)->orderBy('nombre_razon_social')->get();

        return view('cuentas-pagar.create', compact('proveedores'));
    }

    public function store(StoreCuentaPorPagarRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $cuenta = DB::transaction(function () use ($datos) {
            $cuenta = CuentaPorPagar::create([
                'proveedor_id' => $datos['proveedor_id'],
                'documento_origen' => $datos['documento_origen'] ?? null,
                'monto_total' => $datos['monto_total'],
                'monto_pagado' => 0,
                'fecha_emision' => now(),
                'fecha_vencimiento' => $datos['fecha_vencimiento'],
                'estado' => 'pendiente',
            ]);

            $this->auditoria->registrar('cuenta_por_pagar.creada', $cuenta, null, $cuenta->only([
                'proveedor_id', 'documento_origen', 'monto_total', 'fecha_vencimiento',
            ]));

            return $cuenta;
        });

        return redirect()->route('cuentas-pagar.show', $cuenta)
            ->with('status', 'Cuenta por pagar registrada correctamente.');
    }

    public function show(CuentaPorPagar $cuenta_por_pagar): View
    {
        $this->authorize('view', $cuenta_por_pagar);

        $cuenta_por_pagar->load(['proveedor', 'pagos' => fn ($query) => $query->latest('fecha_pago')]);

        return view('cuentas-pagar.show', ['cuenta' => $cuenta_por_pagar]);
    }

    public function registrarPago(RegistrarPagoProveedorRequest $request, CuentaPorPagar $cuenta_por_pagar): RedirectResponse
    {
        if (in_array($cuenta_por_pagar->estado, ['pagada', 'anulada'], true)) {
            return back()->with('error', 'Esta cuenta ya no admite pagos ('.$cuenta_por_pagar->estado.').');
        }

        $datos = $request->validated();

        DB::transaction(function () use ($datos, $cuenta_por_pagar, $request) {
            $pago = PagoProveedor::create([
                'cuenta_por_pagar_id' => $cuenta_por_pagar->id,
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'medio_pago' => $datos['medio_pago'],
                'user_id' => $request->user()->id,
            ]);

            $nuevoMontoPagado = bcadd((string) $cuenta_por_pagar->monto_pagado, (string) $datos['monto'], 2);
            $estado = bccomp($nuevoMontoPagado, (string) $cuenta_por_pagar->monto_total, 2) >= 0 ? 'pagada' : 'parcial';

            $cuenta_por_pagar->update(['monto_pagado' => $nuevoMontoPagado, 'estado' => $estado]);

            $this->movimientosCaja->registrarDesdeOrigen(
                tipo: 'egreso',
                nombreCategoria: 'Pago a proveedores',
                monto: $datos['monto'],
                fecha: $pago->fecha_pago,
                origen: $pago,
                descripcion: 'Pago cuenta por pagar #'.$cuenta_por_pagar->id,
                userId: $request->user()->id,
            );

            $this->auditoria->registrar('pago_proveedor.registrado', $pago, null, [
                'cuenta_por_pagar_id' => $cuenta_por_pagar->id,
                'monto' => $datos['monto'],
                'nuevo_estado' => $estado,
            ]);
        });

        return redirect()->route('cuentas-pagar.show', $cuenta_por_pagar)
            ->with('status', 'Pago registrado correctamente.');
    }

    public function anular(CuentaPorPagar $cuenta_por_pagar): RedirectResponse
    {
        $this->authorize('anular', $cuenta_por_pagar);

        if ($cuenta_por_pagar->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden anular cuentas pendientes sin pagos registrados.');
        }

        DB::transaction(function () use ($cuenta_por_pagar) {
            $cuenta_por_pagar->update(['estado' => 'anulada']);
            $this->auditoria->registrar('cuenta_por_pagar.anulada', $cuenta_por_pagar, ['estado' => 'pendiente'], ['estado' => 'anulada']);
        });

        return redirect()->route('cuentas-pagar.show', $cuenta_por_pagar)
            ->with('status', 'Cuenta por pagar anulada correctamente.');
    }
}
