<?php

namespace App\Http\Requests;

use App\Models\CategoriaMovimiento;
use App\Models\MovimientoCaja;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreMovimientoCajaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('create', MovimientoCaja::class);
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'tipo' => ['required', Rule::in(['ingreso', 'egreso'])],
            'categoria_id' => [
                'required',
                Rule::exists('categorias_movimiento', 'id')->where('estado', true),
                function ($attribute, $value, $fail) {
                    $categoria = CategoriaMovimiento::find($value);

                    if ($categoria && $categoria->tipo !== $this->input('tipo')) {
                        $fail('La categoría seleccionada no corresponde al tipo de movimiento.');
                    }
                },
            ],
            'monto' => ['required', 'numeric', 'min:0.01', 'max:9999999.99'],
            'fecha' => ['required', 'date', 'before_or_equal:today'],
            'descripcion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
