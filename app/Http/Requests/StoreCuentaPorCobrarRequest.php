<?php

namespace App\Http\Requests;

use App\Models\CuentaPorCobrar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCuentaPorCobrarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CuentaPorCobrar::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'venta_id' => ['nullable', 'integer', Rule::exists('ventas', 'id')],
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->where('estado', true)],
            'monto_total' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
