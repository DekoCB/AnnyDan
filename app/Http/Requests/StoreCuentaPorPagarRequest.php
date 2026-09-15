<?php

namespace App\Http\Requests;

use App\Models\CuentaPorPagar;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreCuentaPorPagarRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', CuentaPorPagar::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'proveedor_id' => ['required', Rule::exists('proveedores', 'id')->where('estado', true)],
            'documento_origen' => ['nullable', 'string', 'max:50'],
            'monto_total' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'fecha_vencimiento' => ['required', 'date', 'after_or_equal:today'],
        ];
    }
}
