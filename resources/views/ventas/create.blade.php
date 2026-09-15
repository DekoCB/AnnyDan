@php
    $tasaIgv = config('facturacion.tasa_igv');
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nueva venta
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form id="venta-form" method="POST" action="{{ route('ventas.store') }}" data-tasa-igv="{{ $tasaIgv }}" novalidate>
                    @csrf

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div>
                            <x-input-label for="cliente_id" value="Cliente" />
                            <select id="cliente_id" name="cliente_id" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled selected>Seleccione un cliente…</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>
                                        {{ $cliente->nombre_razon_social }} — {{ $cliente->tipo_documento }} {{ $cliente->numero_documento }}
                                    </option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
                        </div>

                        <div>
                            <x-input-label for="forma_pago" value="Forma de pago" />
                            <select id="forma_pago" name="forma_pago" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                @foreach (['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia', 'yape_plin' => 'Yape / Plin', 'otro' => 'Otro'] as $valor => $etiqueta)
                                    <option value="{{ $valor }}" @selected(old('forma_pago') === $valor)>{{ $etiqueta }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('forma_pago')" class="mt-2" />
                        </div>
                    </div>

                    <div class="mt-6">
                        <div class="flex items-center justify-between mb-2">
                            <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase">Ítems</h3>
                            <button type="button" id="agregar-item"
                                class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">
                                + Agregar ítem
                            </button>
                        </div>
                        <x-input-error :messages="$errors->get('detalles')" class="mb-2" />
                        <p id="items-client-error" class="hidden text-sm text-red-600 dark:text-red-400 mb-2"></p>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                                <thead>
                                    <tr>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Descripción</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-24">Cantidad</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">P. unitario</th>
                                        <th class="px-2 py-2 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase w-32">Subtotal</th>
                                        <th class="w-8"></th>
                                    </tr>
                                </thead>
                                <tbody id="items-body" class="divide-y divide-gray-100 dark:divide-gray-700"></tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end">
                        <dl class="w-64 text-sm space-y-1">
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">Subtotal (referencial)</dt>
                                <dd id="resumen-subtotal" class="text-gray-900 dark:text-gray-100">S/ 0.00</dd>
                            </div>
                            <div class="flex justify-between">
                                <dt class="text-gray-500 dark:text-gray-400">IGV ({{ number_format($tasaIgv * 100, 0) }}%)</dt>
                                <dd id="resumen-igv" class="text-gray-900 dark:text-gray-100">S/ 0.00</dd>
                            </div>
                            <div class="flex justify-between font-semibold text-base border-t border-gray-200 dark:border-gray-700 pt-1">
                                <dt>Total (referencial)</dt>
                                <dd id="resumen-total">S/ 0.00</dd>
                            </div>
                            <p class="text-xs text-gray-400">El total definitivo se calcula y valida en el servidor.</p>
                        </dl>
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6">
                        <a href="{{ route('ventas.index') }}" class="text-sm text-gray-600 dark:text-gray-400 underline">
                            Cancelar
                        </a>
                        <x-primary-button>Registrar venta</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <template id="item-row-template">
        <tr class="item-row">
            <td class="px-2 py-2">
                <input type="text" name="detalles[__INDEX__][descripcion]" required maxlength="255"
                    class="block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" />
            </td>
            <td class="px-2 py-2">
                <input type="number" step="0.01" min="0.01" name="detalles[__INDEX__][cantidad]" required
                    class="item-cantidad block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" />
            </td>
            <td class="px-2 py-2">
                <input type="number" step="0.01" min="0.01" name="detalles[__INDEX__][precio_unitario]" required
                    class="item-precio block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm text-sm" />
            </td>
            <td class="px-2 py-2 text-sm text-gray-700 dark:text-gray-300 item-subtotal">S/ 0.00</td>
            <td class="px-2 py-2 text-right">
                <button type="button" class="quitar-item text-red-500 hover:text-red-700" aria-label="Quitar ítem">&times;</button>
            </td>
        </tr>
    </template>
</x-app-layout>
