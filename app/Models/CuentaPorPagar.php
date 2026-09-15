<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

/**
 * @property Carbon $fecha_emision
 * @property Carbon $fecha_vencimiento
 */
class CuentaPorPagar extends Model
{
    use HasFactory;

    protected $table = 'cuentas_por_pagar';

    protected $fillable = [
        'proveedor_id',
        'documento_origen',
        'monto_total',
        'monto_pagado',
        'fecha_emision',
        'fecha_vencimiento',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'monto_total' => 'decimal:2',
            'monto_pagado' => 'decimal:2',
            'fecha_emision' => 'date',
            'fecha_vencimiento' => 'date',
        ];
    }

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoProveedor::class);
    }

    protected function saldo(): Attribute
    {
        return Attribute::make(
            get: fn () => bcsub((string) $this->monto_total, (string) $this->monto_pagado, 2),
        );
    }

    /**
     * "Vencida" no se persiste en `estado` — se calcula al vuelo (mismo
     * criterio que CuentaPorCobrar::estaVencida()).
     */
    public function estaVencida(): bool
    {
        return in_array($this->estado, ['pendiente', 'parcial'], true)
            && $this->fecha_vencimiento->isPast();
    }

    protected function estadoMostrado(): Attribute
    {
        return Attribute::make(
            get: fn () => $this->estaVencida() ? 'vencida' : $this->estado,
        );
    }

    public function scopeVencidas(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pendiente', 'parcial'])
            ->whereDate('fecha_vencimiento', '<', now());
    }

    public function scopePendientesOParciales(Builder $query): Builder
    {
        return $query->whereIn('estado', ['pendiente', 'parcial']);
    }
}
