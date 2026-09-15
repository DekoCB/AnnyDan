<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMovimientoCajaRequest;
use App\Models\CategoriaMovimiento;
use App\Models\MovimientoCaja;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class MovimientoCajaController extends Controller
{
    public function __construct(private readonly AuditoriaService $auditoria)
    {
        // No es un recurso REST estándar (no se edita ni se borra un
        // movimiento de caja; ver Fase 8 en la memoria del proyecto sobre
        // el alcance de MVP para correcciones).
    }

    public function index(Request $request): View
    {
        $this->authorize('viewAny', MovimientoCaja::class);

        $desde = $request->filled('desde')
            ? Carbon::parse($request->string('desde'))
            : now()->startOfMonth();

        $hasta = $request->filled('hasta')
            ? Carbon::parse($request->string('hasta'))
            : now()->endOfMonth();

        $consulta = MovimientoCaja::query()
            ->with('categoria')
            ->whereDate('fecha', '>=', $desde)
            ->whereDate('fecha', '<=', $hasta)
            ->when($request->filled('tipo'), fn ($query) => $query->where('tipo', $request->string('tipo')))
            ->when($request->filled('categoria_id'), fn ($query) => $query->where('categoria_id', $request->integer('categoria_id')));

        $totalIngresos = (clone $consulta)->where('tipo', 'ingreso')->sum('monto');
        $totalEgresos = (clone $consulta)->where('tipo', 'egreso')->sum('monto');
        $utilidad = $totalIngresos - $totalEgresos;

        $movimientos = $consulta->latest('fecha')->latest('id')->paginate(20)->withQueryString();

        $categorias = CategoriaMovimiento::query()->where('estado', true)->orderBy('nombre')->get();

        return view('caja.index', compact(
            'movimientos', 'categorias', 'desde', 'hasta', 'totalIngresos', 'totalEgresos', 'utilidad'
        ));
    }

    public function create(): View
    {
        $this->authorize('create', MovimientoCaja::class);

        $categorias = CategoriaMovimiento::query()->where('estado', true)->orderBy('nombre')->get();

        return view('caja.create', compact('categorias'));
    }

    public function store(StoreMovimientoCajaRequest $request): RedirectResponse
    {
        $datos = $request->validated();

        $movimiento = MovimientoCaja::create([
            'tipo' => $datos['tipo'],
            'categoria_id' => $datos['categoria_id'],
            'monto' => $datos['monto'],
            'fecha' => $datos['fecha'],
            'descripcion' => $datos['descripcion'] ?? null,
            'user_id' => $request->user()->id,
        ]);

        $this->auditoria->registrar('movimiento_caja.creado', $movimiento, null, $movimiento->only([
            'tipo', 'categoria_id', 'monto', 'fecha', 'descripcion',
        ]));

        return redirect()->route('caja.index')
            ->with('status', 'Movimiento registrado correctamente.');
    }
}
