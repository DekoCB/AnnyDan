<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegistrarPagoClienteRequest;
use App\Http\Requests\StoreCuentaPorCobrarRequest;
use App\Models\Cliente;
use App\Models\CuentaPorCobrar;
use App\Models\PagoCliente;
use App\Models\Venta;
use App\Services\AuditoriaService;
use App\Services\MovimientoCajaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CuentaPorCobrarController extends Controller
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
        $this->authorize('viewAny', CuentaPorCobrar::class);

        $cuentas = CuentaPorCobrar::query()
            ->with('cliente')
            ->when($request->filled('cliente_id'), fn ($query) => $query->where('cliente_id', $request->integer('cliente_id')))
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

        $clientes = Cliente::query()->orderBy('nombre_razon_social')->get();

        return view('cuentas-cobrar.index', compact('cuentas', 'clientes'));
    }

    public function create(Request $request): View
    {
        $this->authorize('create', CuentaPorCobrar::class);

        $venta = null;

        if ($request->filled('venta_id')) {
            $venta = Venta::with('cliente')->findOrFail($request->integer('venta_id'));
        }

        $clientes = Cliente::query()->where('estado', true)->orderBy('nombre_razon_social')->get();

        return view('cuentas-cobrar.create', compact('venta', 'clientes'));
    }

    public function store(StoreCuentaPorCobrarRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        if (! empty($datos['venta_id'])) {
            $venta = Venta::findOrFail($datos['venta_id']);

            if ($venta->estado !== 'registrada') {
                return back()->withErrors(['venta_id' => 'No se puede generar una cuenta por cobrar de una venta anulada.'])->withInput();
            }

            if ($venta->cuentaPorCobrar()->exists()) {
                return back()->withErrors(['venta_id' => 'Esta venta ya tiene una cuenta por cobrar registrada.'])->withInput();
            }

            $datos['cliente_id'] = $venta->cliente_id;
            $datos['monto_total'] = $venta->total;
        }

        $cuenta = DB::transaction(function () use ($datos) {
            $cuenta = CuentaPorCobrar::create([
                'cliente_id' => $datos['cliente_id'],
                'venta_id' => $datos['venta_id'] ?? null,
                'monto_total' => $datos['monto_total'],
                'monto_pagado' => 0,
                'fecha_emision' => now(),
                'fecha_vencimiento' => $datos['fecha_vencimiento'],
                'estado' => 'pendiente',
            ]);

            $this->auditoria->registrar('cuenta_por_cobrar.creada', $cuenta, null, $cuenta->only([
                'cliente_id', 'venta_id', 'monto_total', 'fecha_vencimiento',
            ]));

            return $cuenta;
        });

        return redirect()->route('cuentas-cobrar.show', $cuenta)
            ->with('status', 'Cuenta por cobrar registrada correctamente.');
    }

    public function show(CuentaPorCobrar $cuenta_por_cobrar): View
    {
        $this->authorize('view', $cuenta_por_cobrar);

        $cuenta_por_cobrar->load(['cliente', 'venta', 'pagos' => fn ($query) => $query->latest('fecha_pago')]);

        return view('cuentas-cobrar.show', ['cuenta' => $cuenta_por_cobrar]);
    }

    public function registrarPago(RegistrarPagoClienteRequest $request, CuentaPorCobrar $cuenta_por_cobrar): RedirectResponse
    {
        if (in_array($cuenta_por_cobrar->estado, ['pagada', 'anulada'], true)) {
            return back()->with('error', 'Esta cuenta ya no admite pagos ('.$cuenta_por_cobrar->estado.').');
        }

        $datos = $request->validated();

        DB::transaction(function () use ($datos, $cuenta_por_cobrar, $request) {
            $pago = PagoCliente::create([
                'cuenta_por_cobrar_id' => $cuenta_por_cobrar->id,
                'monto' => $datos['monto'],
                'fecha_pago' => $datos['fecha_pago'],
                'medio_pago' => $datos['medio_pago'],
                'user_id' => $request->user()->id,
            ]);

            $nuevoMontoPagado = bcadd((string) $cuenta_por_cobrar->monto_pagado, (string) $datos['monto'], 2);
            $estado = bccomp($nuevoMontoPagado, (string) $cuenta_por_cobrar->monto_total, 2) >= 0 ? 'pagada' : 'parcial';

            $cuenta_por_cobrar->update(['monto_pagado' => $nuevoMontoPagado, 'estado' => $estado]);

            $this->movimientosCaja->registrarDesdeOrigen(
                tipo: 'ingreso',
                nombreCategoria: 'Cobro a clientes',
                monto: $datos['monto'],
                fecha: $pago->fecha_pago,
                origen: $pago,
                descripcion: 'Cobro cuenta por cobrar #'.$cuenta_por_cobrar->id,
                userId: $request->user()->id,
            );

            $this->auditoria->registrar('pago_cliente.registrado', $pago, null, [
                'cuenta_por_cobrar_id' => $cuenta_por_cobrar->id,
                'monto' => $datos['monto'],
                'nuevo_estado' => $estado,
            ]);
        });

        return redirect()->route('cuentas-cobrar.show', $cuenta_por_cobrar)
            ->with('status', 'Pago registrado correctamente.');
    }

    public function anular(CuentaPorCobrar $cuenta_por_cobrar): RedirectResponse
    {
        $this->authorize('anular', $cuenta_por_cobrar);

        if ($cuenta_por_cobrar->estado !== 'pendiente') {
            return back()->with('error', 'Solo se pueden anular cuentas pendientes sin pagos registrados.');
        }

        DB::transaction(function () use ($cuenta_por_cobrar) {
            $cuenta_por_cobrar->update(['estado' => 'anulada']);
            $this->auditoria->registrar('cuenta_por_cobrar.anulada', $cuenta_por_cobrar, ['estado' => 'pendiente'], ['estado' => 'anulada']);
        });

        return redirect()->route('cuentas-cobrar.show', $cuenta_por_cobrar)
            ->with('status', 'Cuenta por cobrar anulada correctamente.');
    }
}
