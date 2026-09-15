<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Índices agregados a partir de medir los patrones de consulta reales del
 * código (no supuestos) — ver sección 19 del requerimiento:
 * - auditorias.created_at: el índice de listado se ordena y filtra por
 *   fecha en cada carga de /auditoria.
 * - movimientos_caja.fecha: además del índice compuesto (tipo, fecha) ya
 *   existente, el listado de /caja también filtra por rango de fecha sin
 *   tipo seleccionado.
 * - pagos_clientes.fecha_pago / pagos_proveedores.fecha_pago: el
 *   dashboard ordena "pagos recientes" sobre toda la tabla, sin filtrar
 *   primero por cuenta.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->index('created_at');
        });

        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->index('fecha');
        });

        Schema::table('pagos_clientes', function (Blueprint $table) {
            $table->index('fecha_pago');
        });

        Schema::table('pagos_proveedores', function (Blueprint $table) {
            $table->index('fecha_pago');
        });
    }

    public function down(): void
    {
        Schema::table('auditorias', function (Blueprint $table) {
            $table->dropIndex(['created_at']);
        });

        Schema::table('movimientos_caja', function (Blueprint $table) {
            $table->dropIndex(['fecha']);
        });

        Schema::table('pagos_clientes', function (Blueprint $table) {
            $table->dropIndex(['fecha_pago']);
        });

        Schema::table('pagos_proveedores', function (Blueprint $table) {
            $table->dropIndex(['fecha_pago']);
        });
    }
};
