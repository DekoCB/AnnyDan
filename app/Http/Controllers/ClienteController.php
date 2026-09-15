<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreClienteRequest;
use App\Http\Requests\UpdateClienteRequest;
use App\Models\Cliente;
use App\Services\AuditoriaService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class ClienteController extends Controller
{
    public function __construct(private readonly AuditoriaService $auditoria)
    {
        $this->authorizeResource(Cliente::class, 'cliente');
    }

    public function index(Request $request): View
    {
        $clientes = Cliente::query()
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

        return view('clientes.index', compact('clientes'));
    }

    public function create(): View
    {
        return view('clientes.create');
    }

    public function store(StoreClienteRequest $request): RedirectResponse
    {
        $cliente = new Cliente($request->validated());

        DB::transaction(function () use ($cliente, $request) {
            $cliente->save();
            $this->auditoria->registrar('cliente.creado', $cliente, null, $request->validated());
        });

        return redirect()->route('clientes.show', $cliente)
            ->with('status', 'Cliente registrado correctamente.');
    }

    public function show(Cliente $cliente): View
    {
        $cliente->load([
            'ventas' => fn ($query) => $query->latest('fecha')->limit(10),
            'cuentasPorCobrar' => fn ($query) => $query->latest('fecha_emision'),
            'cuentasPorCobrar.pagos' => fn ($query) => $query->latest('fecha_pago'),
        ]);

        $deudaPendiente = $cliente->cuentasPorCobrar
            ->whereNotIn('estado', ['pagada', 'anulada'])
            ->sum('saldo');

        return view('clientes.show', compact('cliente', 'deudaPendiente'));
    }

    public function edit(Cliente $cliente): View
    {
        return view('clientes.edit', compact('cliente'));
    }

    public function update(UpdateClienteRequest $request, Cliente $cliente): RedirectResponse
    {
        $valoresAntes = $cliente->only(array_keys($request->validated()));

        DB::transaction(function () use ($cliente, $request, $valoresAntes) {
            $cliente->update($request->validated());
            $this->auditoria->registrar('cliente.actualizado', $cliente, $valoresAntes, $request->validated());
        });

        return redirect()->route('clientes.show', $cliente)
            ->with('status', 'Cliente actualizado correctamente.');
    }

    /**
     * No elimina físicamente al cliente: lo desactiva (ver sección 5 del
     * requerimiento — desactivación lógica en vez de borrado).
     */
    public function destroy(Cliente $cliente): RedirectResponse
    {
        DB::transaction(function () use ($cliente) {
            $cliente->update(['estado' => false]);
            $this->auditoria->registrar('cliente.desactivado', $cliente);
        });

        return redirect()->route('clientes.index')
            ->with('status', 'Cliente desactivado correctamente.');
    }

    public function activate(Cliente $cliente): RedirectResponse
    {
        $this->authorize('restore', $cliente);

        DB::transaction(function () use ($cliente) {
            $cliente->update(['estado' => true]);
            $this->auditoria->registrar('cliente.activado', $cliente);
        });

        return redirect()->route('clientes.index')
            ->with('status', 'Cliente activado correctamente.');
    }
}
