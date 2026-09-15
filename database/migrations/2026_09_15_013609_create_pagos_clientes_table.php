<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pagos_clientes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cuenta_por_cobrar_id')->constrained('cuentas_por_cobrar')->restrictOnDelete();
            $table->decimal('monto', 12, 2);
            $table->date('fecha_pago');
            $table->enum('medio_pago', ['efectivo', 'tarjeta', 'transferencia', 'yape_plin', 'otro']);
            $table->foreignId('user_id')->constrained('users')->restrictOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pagos_clientes');
    }
};
