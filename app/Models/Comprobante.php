<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comprobante extends Model
{
    use HasFactory;

    protected $fillable = [
        'venta_id',
        'tipo',
        'serie',
        'numero_correlativo',
        'fecha_emision',
        'estado',
    ];

    protected function casts(): array
    {
        return [
            'fecha_emision' => 'date',
        ];
    }

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class);
    }

    public function getNumeroCompletoAttribute(): string
    {
        return sprintf('%s-%08d', $this->serie, $this->numero_correlativo);
    }
}
