<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProveedorRequest;
use App\Http\Requests\UpdateProveedorRequest;
use App\Models\Proveedor;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ProveedorController extends Controller
{
    public function __construct(private readonly AuditoriaService $auditoria)
    {
        $this->authorizeResource(Proveedor::class, 'proveedor');
    }

    public function index(Request $request): View
    {
        $proveedores = Proveedor::query()
            ->when($request->string('q')->trim()->toString(), function ($query, string $q) {
                $query->where(function ($query) use ($q) {
                    $query->where('nombre_razon_social', 'like', "%{$q}%")
                        ->orWhere('numero_documento', 'like', "%{$q}%");
                });
            })
            ->when($request->filled('estado'), fn ($query) => $query->where('estado', $request->boolean('estado')))
            ->orderBy('nombre_razon_social')
            ->paginate(15)
            ->withQueryString();

        return view('proveedores.index', compact('proveedores'));
    }

    public function create(): View
    {
        return view('proveedores.create');
    }

    public function store(StoreProveedorRequest $request): RedirectResponse
    {
        $proveedor = new Proveedor($request->validated());

        DB::transaction(function () use ($proveedor, $request) {
            $proveedor->save();
            $this->auditoria->registrar('proveedor.creado', $proveedor, null, $request->validated());
        });

        return redirect()->route('proveedores.show', $proveedor)
            ->with('status', 'Proveedor registrado correctamente.');
    }

    public function show(Proveedor $proveedor): View
    {
        $proveedor->load([
            'cuentasPorPagar' => fn ($query) => $query->latest('fecha_emision'),
            'cuentasPorPagar.pagos' => fn ($query) => $query->latest('fecha_pago'),
        ]);

        $deudaPendiente = $proveedor->cuentasPorPagar
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->sum('saldo');

        return view('proveedores.show', compact('proveedor', 'deudaPendiente'));
    }

    public function edit(Proveedor $proveedor): View
    {
        return view('proveedores.edit', compact('proveedor'));
    }

    public function update(UpdateProveedorRequest $request, Proveedor $proveedor): RedirectResponse
    {
        $valoresAntes = $proveedor->only(array_keys($request->validated()));

        DB::transaction(function () use ($proveedor, $request, $valoresAntes) {
            $proveedor->update($request->validated());
            $this->auditoria->registrar('proveedor.actualizado', $proveedor, $valoresAntes, $request->validated());
        });

        return redirect()->route('proveedores.show', $proveedor)
            ->with('status', 'Proveedor actualizado correctamente.');
    }

    /**
     * No elimina físicamente al proveedor: lo desactiva (desactivación
     * lógica, ver sección 6 del requerimiento).
     */
    public function destroy(Proveedor $proveedor): RedirectResponse
    {
        DB::transaction(function () use ($proveedor) {
            $proveedor->update(['estado' => false]);
            $this->auditoria->registrar('proveedor.desactivado', $proveedor);
        });

        return redirect()->route('proveedores.index')
            ->with('status', 'Proveedor desactivado correctamente.');
    }

    public function activate(Proveedor $proveedor): RedirectResponse
    {
        $this->authorize('restore', $proveedor);

        DB::transaction(function () use ($proveedor) {
            $proveedor->update(['estado' => true]);
            $this->auditoria->registrar('proveedor.activado', $proveedor);
        });

        return redirect()->route('proveedores.index')
            ->with('status', 'Proveedor activado correctamente.');
    }
}
