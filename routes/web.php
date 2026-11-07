<?php

use App\Http\Controllers\AuditoriaController;
use App\Http\Controllers\ClienteController;
use App\Http\Controllers\ComprobanteController;
use App\Http\Controllers\CuentaPorCobrarController;
use App\Http\Controllers\CuentaPorPagarController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\MovimientoCajaController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteVentaController;
use App\Http\Controllers\VentaController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route(auth()->check() ? 'dashboard' : 'login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::patch('/clientes/{cliente}/activar', [ClienteController::class, 'activate'])->name('clientes.activate');
    Route::resource('clientes', ClienteController::class);

    Route::patch('/proveedores/{proveedor}/activar', [ProveedorController::class, 'activate'])->name('proveedores.activate');
    Route::resource('proveedores', ProveedorController::class)->parameters(['proveedores' => 'proveedor']);

    Route::get('/ventas', [VentaController::class, 'index'])->name('ventas.index');
    Route::get('/ventas/create', [VentaController::class, 'create'])->name('ventas.create');
    Route::post('/ventas', [VentaController::class, 'store'])->name('ventas.store');
    Route::get('/ventas/{venta}', [VentaController::class, 'show'])->name('ventas.show');
    Route::patch('/ventas/{venta}/anular', [VentaController::class, 'anular'])->name('ventas.anular');
    Route::post('/ventas/{venta}/comprobante', [ComprobanteController::class, 'store'])->name('comprobantes.store');

    Route::get('/comprobantes', [ComprobanteController::class, 'index'])->name('comprobantes.index');
    Route::get('/comprobantes/{comprobante}', [ComprobanteController::class, 'show'])->name('comprobantes.show');
    Route::patch('/comprobantes/{comprobante}/anular', [ComprobanteController::class, 'anular'])->name('comprobantes.anular');

    Route::get('/cuentas-por-cobrar', [CuentaPorCobrarController::class, 'index'])->name('cuentas-cobrar.index');
    Route::get('/cuentas-por-cobrar/create', [CuentaPorCobrarController::class, 'create'])->name('cuentas-cobrar.create');
    Route::post('/cuentas-por-cobrar', [CuentaPorCobrarController::class, 'store'])->name('cuentas-cobrar.store');
    Route::get('/cuentas-por-cobrar/{cuenta_por_cobrar}', [CuentaPorCobrarController::class, 'show'])->name('cuentas-cobrar.show');
    Route::post('/cuentas-por-cobrar/{cuenta_por_cobrar}/pagos', [CuentaPorCobrarController::class, 'registrarPago'])->name('cuentas-cobrar.pagar');
    Route::patch('/cuentas-por-cobrar/{cuenta_por_cobrar}/anular', [CuentaPorCobrarController::class, 'anular'])->name('cuentas-cobrar.anular');

    Route::get('/cuentas-por-pagar', [CuentaPorPagarController::class, 'index'])->name('cuentas-pagar.index');
    Route::get('/cuentas-por-pagar/create', [CuentaPorPagarController::class, 'create'])->name('cuentas-pagar.create');
    Route::post('/cuentas-por-pagar', [CuentaPorPagarController::class, 'store'])->name('cuentas-pagar.store');
    Route::get('/cuentas-por-pagar/{cuenta_por_pagar}', [CuentaPorPagarController::class, 'show'])->name('cuentas-pagar.show');
    Route::post('/cuentas-por-pagar/{cuenta_por_pagar}/pagos', [CuentaPorPagarController::class, 'registrarPago'])->name('cuentas-pagar.pagar');
    Route::patch('/cuentas-por-pagar/{cuenta_por_pagar}/anular', [CuentaPorPagarController::class, 'anular'])->name('cuentas-pagar.anular');

    Route::get('/caja', [MovimientoCajaController::class, 'index'])->name('caja.index');
    Route::get('/caja/create', [MovimientoCajaController::class, 'create'])->name('caja.create');
    Route::post('/caja', [MovimientoCajaController::class, 'store'])->name('caja.store');

    Route::get('/reportes/ventas', [ReporteVentaController::class, 'index'])->name('reportes.ventas');

    Route::get('/auditoria', [AuditoriaController::class, 'index'])->name('auditoria.index');
    Route::get('/auditoria/{auditoria}', [AuditoriaController::class, 'show'])->name('auditoria.show');
});

require __DIR__.'/auth.php';
