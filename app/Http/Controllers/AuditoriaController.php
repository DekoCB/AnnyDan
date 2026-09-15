<?php

namespace App\Http\Controllers;

use App\Models\Auditoria;
use App\Models\Cliente;
use App\Models\Comprobante;
use App\Models\CuentaPorCobrar;
use App\Models\CuentaPorPagar;
use App\Models\MovimientoCaja;
use App\Models\PagoCliente;
use App\Models\PagoProveedor;
use App\Models\Proveedor;
use App\Models\User;
use App\Models\Venta;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Consulta de solo lectura del historial de auditoría. No existe ninguna
 * ruta ni acción para editar o borrar un registro — la trazabilidad debe
 * permanecer intacta (sección 15 del requerimiento: "No permitir que un
 * usuario común pueda borrar o alterar fácilmente el historial").
 */
class AuditoriaController extends Controller
{
    private const ETIQUETAS_MODELO = [
        Cliente::class => 'Cliente',
        Proveedor::class => 'Proveedor',
        Venta::class => 'Venta',
        CuentaPorCobrar::class => 'Cuenta por cobrar',
        CuentaPorPagar::class => 'Cuenta por pagar',
        PagoCliente::class => 'Pago de cliente',
        PagoProveedor::class => 'Pago a proveedor',
        MovimientoCaja::class => 'Movimiento de caja',
        Comprobante::class => 'Comprobante',
    ];

    public function index(Request $request): View
    {
        $this->authorize('auditoria.ver');

        $auditorias = Auditoria::query()
            ->with('usuario')
            ->when($request->filled('usuario_id'), fn ($query) => $query->where('user_id', $request->integer('usuario_id')))
            ->when($request->filled('accion'), fn ($query) => $query->where('accion', 'like', '%'.$request->string('accion').'%'))
            ->when($request->filled('desde'), fn ($query) => $query->whereDate('created_at', '>=', $request->date('desde')))
            ->when($request->filled('hasta'), fn ($query) => $query->whereDate('created_at', '<=', $request->date('hasta')))
            ->latest('created_at')
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        $usuarios = User::query()->orderBy('name')->get();

        return view('auditoria.index', [
            'auditorias' => $auditorias,
            'usuarios' => $usuarios,
            'etiquetas' => self::ETIQUETAS_MODELO,
        ]);
    }

    public function show(Auditoria $auditoria): View
    {
        $this->authorize('auditoria.ver');

        $auditoria->load('usuario');

        return view('auditoria.show', [
            'auditoria' => $auditoria,
            'etiquetas' => self::ETIQUETAS_MODELO,
        ]);
    }

    /**
     * Enlace directo al registro afectado cuando existe una vista de
     * detalle para ese tipo de modelo. Devuelve null en vez de forzar un
     * enlace a modelos sin página propia (PagoCliente, MovimientoCaja...).
     */
    public static function enlaceParaAuditable(Auditoria $auditoria): ?string
    {
        if (! $auditoria->auditable_type || ! $auditoria->auditable_id) {
            return null;
        }

        return match ($auditoria->auditable_type) {
            Cliente::class => route('clientes.show', $auditoria->auditable_id),
            Proveedor::class => route('proveedores.show', $auditoria->auditable_id),
            Venta::class => route('ventas.show', $auditoria->auditable_id),
            CuentaPorCobrar::class => route('cuentas-cobrar.show', $auditoria->auditable_id),
            CuentaPorPagar::class => route('cuentas-pagar.show', $auditoria->auditable_id),
            Comprobante::class => route('comprobantes.show', $auditoria->auditable_id),
            default => null,
        };
    }
}
