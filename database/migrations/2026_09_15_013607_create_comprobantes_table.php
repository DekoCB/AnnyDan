<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comprobantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('venta_id')->constrained('ventas')->restrictOnDelete();
            $table->enum('tipo', ['boleta']);
            $table->string('serie', 4);
            $table->unsignedInteger('numero_correlativo');
            $table->date('fecha_emision');
            $table->enum('estado', ['emitido', 'anulado'])->default('emitido');
            $table->timestamps();

            $table->unique(['tipo', 'serie', 'numero_correlativo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comprobantes');
    }
};
