<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('categorias_movimiento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100);
            $table->enum('tipo', ['ingreso', 'egreso']);
            $table->boolean('estado')->default(true);
            $table->timestamps();

            $table->unique(['nombre', 'tipo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('categorias_movimiento');
    }
};
