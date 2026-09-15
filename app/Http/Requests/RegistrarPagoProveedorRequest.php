<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistrarPagoProveedorRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('pagar', $this->route('cuenta_por_pagar'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        return [
            'monto' => [
                'required', 'numeric', 'min:0.01',
                function ($attribute, $value, $fail) {
                    $cuenta = $this->route('cuenta_por_pagar');

                    if ($cuenta && bccomp((string) $value, (string) $cuenta->saldo, 2) > 0) {
                        $fail('El monto no puede ser mayor al saldo pendiente (S/ '.number_format((float) $cuenta->saldo, 2).').');
                    }
                },
            ],
            'fecha_pago' => ['required', 'date', 'before_or_equal:today'],
            'medio_pago' => ['required', Rule::in(['efectivo', 'tarjeta', 'transferencia', 'yape_plin', 'otro'])],
        ];
    }
}
