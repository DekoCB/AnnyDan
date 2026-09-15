<?php

namespace App\Http\Requests;

use App\Models\Venta;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreVentaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', Venta::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'cliente_id' => ['required', Rule::exists('clientes', 'id')->where('estado', true)],
            'forma_pago' => ['required', Rule::in(['efectivo', 'tarjeta', 'transferencia', 'yape_plin', 'otro'])],
            'detalles' => ['required', 'array', 'min:1'],
            'detalles.*.descripcion' => ['required', 'string', 'max:255'],
            'detalles.*.cantidad' => ['required', 'numeric', 'min:0.01', 'max:99999.99'],
            'detalles.*.precio_unitario' => ['required', 'numeric', 'min:0.01', 'max:999999.99'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'cliente_id.exists' => 'El cliente seleccionado no existe o está inactivo.',
            'detalles.required' => 'La venta debe tener al menos un ítem.',
            'detalles.min' => 'La venta debe tener al menos un ítem.',
            'detalles.*.descripcion.required' => 'Cada ítem debe tener una descripción.',
            'detalles.*.cantidad.min' => 'La cantidad debe ser mayor a 0.',
            'detalles.*.precio_unitario.min' => 'El precio unitario debe ser mayor a 0.',
        ];
    }
}
