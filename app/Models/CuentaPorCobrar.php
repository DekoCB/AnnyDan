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
class CuentaPorCobrar extends Model
{
    use HasFactory;

    protected $table = 'cuentas_por_cobrar';

    protected $fillable = [
        'cliente_id',
        'venta_id',
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

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function pagos(): HasMany
    {
        return $this->hasMany(PagoCliente::class);
    }

    protected function saldo(): Attribute
    {
        return Attribute::make(
            get: fn () => bcsub((string) $this->monto_total, (string) $this->monto_pagado, 2),
        );
    }

    /**
     * "Vencida" no se persiste en `estado` (no depende de un evento sino
     * del paso del tiempo) — se calcula al vuelo para no necesitar un job
     * programado solo para mantenerla sincronizada. Ver AC-05 del
     * requerimiento (sección 27).
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

    /**
     * Mismo criterio que estaVencida(), expresado como query — para
     * agregados (conteos/sumas) donde cargar cada fila solo para llamar
     * al método en PHP sería innecesariamente costoso.
     */
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
