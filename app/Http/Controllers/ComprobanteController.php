<?php

namespace App\Http\Controllers;

use App\Models\Comprobante;
use App\Models\Venta;
use App\Services\AuditoriaService;
use App\Services\CorrelativoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ComprobanteController extends Controller
{
    public function __construct(
        private readonly AuditoriaService $auditoria,
        private readonly CorrelativoService $correlativos,
    ) {
        // No es un recurso REST estándar (se emite desde una venta y solo
        // se anula, no se edita), así que autorizamos cada acción
        // explícitamente.
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', Comprobante::class);

        $comprobantes = Comprobante::query()
            ->with('venta.cliente')
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->string('estado')))
            ->latest('fecha_emision')
            ->latest('id')
            ->paginate(15)
            ->withQueryString();

        return view('comprobantes.index', compact('comprobantes'));
    }

    public function store(Venta $venta): RedirectResponse
    {
        $this->authorize('create', Comprobante::class);

        if ($venta->estado !== 'registrada') {
            return back()->with('error', 'No se puede emitir un comprobante de una venta anulada.');
        }

        if ($venta->comprobante) {
            return back()->with('error', 'Esta venta ya tiene un comprobante emitido.');
        }

        $serie = config('facturacion.serie_boleta');

        $comprobante = DB::transaction(function () use ($venta, $serie) {
            $numero = $this->correlativos->siguiente('boleta-'.$serie);

            $comprobante = Comprobante::create([
                'venta_id' => $venta->id,
                'tipo' => 'boleta',
                'serie' => $serie,
                'numero_correlativo' => $numero,
                'fecha_emision' => now(),
                'estado' => 'emitido',
            ]);

            $this->auditoria->registrar('comprobante.emitido', $comprobante, null, [
                'venta_id' => $venta->id,
                'numero_completo' => $comprobante->numero_completo,
            ]);

            return $comprobante;
        });

        return redirect()->route('comprobantes.show', $comprobante)
            ->with('status', 'Boleta emitida correctamente.');
    }

    public function show(Comprobante $comprobante): View
    {
        $this->authorize('view', $comprobante);

        $comprobante->load(['venta.cliente', 'venta.detalles']);

        return view('comprobantes.show', compact('comprobante'));
    }

    public function anular(Comprobante $comprobante): RedirectResponse
    {
        $this->authorize('anular', $comprobante);

        if ($comprobante->estado !== 'emitido') {
            return back()->with('error', 'Este comprobante ya fue anulado anteriormente.');
        }

        DB::transaction(function () use ($comprobante) {
            $comprobante->update(['estado' => 'anulado']);
            $this->auditoria->registrar('comprobante.anulado', $comprobante, ['estado' => 'emitido'], ['estado' => 'anulado']);
        });

        return redirect()->route('comprobantes.show', $comprobante)
            ->with('status', 'Comprobante anulado correctamente.');
    }
}
