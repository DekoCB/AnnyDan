<?php

namespace Database\Seeders;

use App\Models\CategoriaMovimiento;
use Illuminate\Database\Seeder;

class CategoriasMovimientoSeeder extends Seeder
{
    public function run(): void
    {
        $categorias = [
            ['nombre' => 'Cobro a clientes', 'tipo' => 'ingreso'],
            ['nombre' => 'Otros ingresos', 'tipo' => 'ingreso'],
            ['nombre' => 'Pago a proveedores', 'tipo' => 'egreso'],
            ['nombre' => 'Gastos operativos', 'tipo' => 'egreso'],
            ['nombre' => 'Otros egresos', 'tipo' => 'egreso'],
        ];

        foreach ($categorias as $categoria) {
            CategoriaMovimiento::firstOrCreate(
                ['nombre' => $categoria['nombre'], 'tipo' => $categoria['tipo']]
            );
        }
    }
}
