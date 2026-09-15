<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVentaRequest;
use App\Models\Cliente;
use App\Models\DetalleVenta;
use App\Models\Venta;
use App\Services\AuditoriaService;
use App\Services\CalculoVentaService;
use App\Services\CorrelativoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class VentaController extends Controller
{
    public function __construct(
        private readonly AuditoriaService $auditoria,
        private readonly CalculoVentaService $calculo,
        private readonly CorrelativoService $correlativos,
    ) {
        // Venta no es un recurso REST estándar (no se edita ni se borra, solo
        // se anula), así que autorizamos cada acción explícitamente en vez
        // de usar authorizeResource().
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Venta::class);

        $ventas = Venta::query()
            ->with('cliente')
            ->when($request->string('q')->trim()->toString(), function ($query, string $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('numero_operacion', 'like', "%{$q}%")
                        ->orWhereHas('cliente', fn ($query) => $query->where('nombre_razon_social', 'like', "%{$q}%"));
                });
            })
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->string('estado')))
            ->when($request->filled('desde'), fn ($query) => $query->whereDate('fecha', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($query) => $query->whereDate('fecha', '<=', $request->date('hasta')))
            ->latest('fecha')
            ->paginate(15)
            ->withQueryString();

        return view('ventas.index', compact('ventas'));
    }

    public function create(): View
    {
        $this->authorize('create', Venta::class);

        $clientes = Cliente::query()->where('estado', true)->orderBy('nombre_razon_social')->get();

        return view('ventas.create', compact('clientes'));
    }

    public function store(StoreVentaRequest $request): RedirectResponse
    {
        // La autorización de 'create' ya la resuelve StoreVentaRequest::authorize().
        $datos = $request->validated();
        $calculo = $this->calculo->calcular($datos['detalles']);

        $venta = DB::transaction(function () use ($datos, $calculo, $request) {
            $numero = $this->correlativos->siguiente('venta');

            $venta = Venta::create([
                'numero_operacion' => 'V-'.str_pad((string) $numero, 6, '0', STR_PAD_LEFT),
                'cliente_id' => $datos['cliente_id'],
                'user_id' => $request->user()->id,
                'fecha' => now(),
                'subtotal' => $calculo['subtotal'],
                'impuestos' => $calculo['impuestos'],
                'total' => $calculo['total'],
                'forma_pago' => $datos['forma_pago'],
                'estado' => 'registrada',
            ]);

            foreach ($calculo['lineas'] as $linea) {
                DetalleVenta::create([
                    'venta_id' => $venta->id,
                    ...$linea,
                ]);
            }

            $this->auditoria->registrar('venta.creada', $venta, null, [
                'numero_operacion' => $venta->numero_operacion,
                'total' => $calculo['total'],
                'items' => count($calculo['lineas']),
            ]);

            return $venta;
        });

        return redirect()->route('ventas.show', $venta)
            ->with('status', 'Venta registrada correctamente.');
    }

    public function show(Venta $venta): View
    {
        $this->authorize('view', $venta);

        $venta->load(['cliente', 'usuario', 'detalles', 'comprobante', 'cuentaPorCobrar']);

        return view('ventas.show', compact('venta'));
    }

    public function anular(Venta $venta): RedirectResponse
    {
        $this->authorize('anular', $venta);

        if ($venta->estado !== 'registrada') {
            return back()->with('error', 'Esta venta ya fue anulada anteriormente.');
        }

        if ($venta->comprobante && $venta->comprobante->estado === 'emitido') {
            return back()->with('error', 'Esta venta tiene una boleta emitida. Anula la boleta primero.');
        }

        if ($venta->cuentaPorCobrar && $venta->cuentaPorCobrar->estado !== 'anulada') {
            return back()->with('error', 'Esta venta tiene una cuenta por cobrar asociada. Anula la cuenta por cobrar primero.');
        }

        DB::transaction(function () use ($venta) {
            $valoresAntes = $venta->only('estado');
            $venta->update(['estado' => 'anulada']);
            $this->auditoria->registrar('venta.anulada', $venta, $valoresAntes, ['estado' => 'anulada']);
        });

        return redirect()->route('ventas.show', $venta)
            ->with('status', 'Venta anulada correctamente.');
    }
}
