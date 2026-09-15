<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoriaMovimiento extends Model
{
    use HasFactory;

    protected $table = 'categorias_movimiento';

    protected $fillable = [
        'nombre',
        'tipo',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'estado' => 'boolean',
        ];
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoCaja::class, 'categoria_id');
    }
}
