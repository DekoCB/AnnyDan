<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property-read Comprobante|null $comprobante
 * @property-read CuentaPorCobrar|null $cuentaPorCobrar
 */
class Venta extends Model
{
    use HasFactory;

    protected $fillable = [
        'numero_operacion',
        'cliente_id',
        'user_id',
        'fecha',
        'subtotal',
        'impuestos',
        'total',
        'forma_pago',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'datetime',
            'subtotal' => 'decimal:2',
            'impuestos' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetalleVenta::class);
    }

    public function comprobante(): HasOne
    {
        return $this->hasOne(Comprobante::class);
    }

    public function cuentaPorCobrar(): HasOne
    {
        return $this->hasOne(CuentaPorCobrar::class);
    }
}
