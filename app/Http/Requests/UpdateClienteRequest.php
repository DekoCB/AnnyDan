<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateClienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->can('update', $this->route('cliente'));
    }

    /**
     * @return array<string, mixed>
     */
    public function rules(): array
    {
        $cliente = $this->route('cliente');

        return [
            'tipo_documento' => ['required', Rule::in(['DNI', 'RUC', 'CE', 'PASAPORTE'])],
            'numero_documento' => [
                'required', 'string', 'max:20',
                match ($this->input('tipo_documento')) {
                    'DNI' => 'regex:/^\d{8}$/',
                    'RUC' => 'regex:/^\d{11}$/',
                    default => 'alpha_num',
                },
                Rule::unique('clientes', 'numero_documento')
                    ->where('tipo_documento', $this->input('tipo_documento'))
                    ->ignore($cliente->id),
            ],
            'nombre_razon_social' => ['required', 'string', 'max:150'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:20', 'regex:/^[0-9+\-\s()]+$/'],
            'email' => ['nullable', 'email', 'max:150'],
        ];
    }

    /**
     * @return array<string, string>
     */
    public function messages(): array
    {
        return [
            'numero_documento.unique' => 'Ya existe un cliente registrado con este tipo y número de documento.',
            'numero_documento.regex' => 'El número de documento no tiene el formato esperado para el tipo seleccionado (DNI: 8 dígitos, RUC: 11 dígitos).',
            'numero_documento.alpha_num' => 'El número de documento solo puede contener letras y números.',
            'telefono.regex' => 'El teléfono solo puede contener números, espacios y los símbolos + - ( ).',
        ];
    }
}
