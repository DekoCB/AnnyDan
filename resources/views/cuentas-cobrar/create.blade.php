<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Nueva cuenta por cobrar
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('cuentas-cobrar.store') }}" novalidate>
                    @csrf

                    @if ($venta)
                        <input type="hidden" name="venta_id" value="{{ $venta->id }}" />
                        <input type="hidden" name="cliente_id" value="{{ $venta->cliente_id }}" />
                        <input type="hidden" name="monto_total" value="{{ $venta->total }}" />

                        <div class="bg-gray-50 dark:bg-gray-900 rounded-md p-4 mb-4 text-sm">
                            <p class="text-gray-500 dark:text-gray-400">Generando cuenta por cobrar para la venta <strong>{{ $venta->numero_operacion }}</strong></p>
                            <p class="mt-1"><span class="text-gray-500 dark:text-gray-400">Cliente:</span> <strong>{{ $venta->cliente->nombre_razon_social }}</strong></p>
                            <p><span class="text-gray-500 dark:text-gray-400">Monto:</span> <strong>S/ {{ number_format((float) $venta->total, 2) }}</strong></p>
                        </div>
                    @else
                        <div>
                            <x-input-label for="cliente_id" value="Cliente" />
                            <select id="cliente_id" name="cliente_id" required
                                class="mt-1 block w-full border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm">
                                <option value="" disabled selected>Seleccione un cliente…</option>
                                @foreach ($clientes as $cliente)
                                    <option value="{{ $cliente->id }}" @selected(old('cliente_id') == $cliente->id)>{{ $cliente->nombre_razon_social }}</option>
                                @endforeach
                            </select>
                            <x-input-error :messages="$errors->get('cliente_id')" class="mt-2" />
                        </div>

                        <div class="mt-4">
                            <x-input-label for="monto_total" value="Monto total" />
                            <x-text-input id="monto_total" name="monto_total" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                                :value="old('monto_total')" required />
                            <x-input-error :messages="$errors->get('monto_total')" class="mt-2" />
                        </div>
                    @endif

                    <div class="mt-4">
                        <x-input-label for="fecha_vencimiento" value="Fecha de vencimiento" />
                        <x-text-input id="fecha_vencimiento" name="fecha_vencimiento" type="date" class="mt-1 block w-full"
                            :value="old('fecha_vencimiento', now()->addDays(30)->toDateString())" required />
                        <x-input-error :messages="$errors->get('fecha_vencimiento')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6">
                        <a href="{{ $venta ? route('ventas.show', $venta) : route('cuentas-cobrar.index') }}" class="text-sm text-gray-600 dark:text-gray-400 underline">
                            Cancelar
                        </a>
                        <x-primary-button>Registrar cuenta por cobrar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
