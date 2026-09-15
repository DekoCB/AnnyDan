<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Cuentas por pagar
            </h2>
            @can('create', \App\Models\CuentaPorPagar::class)
                <a href="{{ route('cuentas-pagar.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                    Nueva cuenta por pagar
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/40 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-50 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-sm rounded-md p-4">
                    {{ session('error') }}
                </div>
            @endif

            <form method="GET" action="{{ route('cuentas-pagar.index') }}"
                class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 flex flex-wrap gap-4 items-end">
                <div>
                    <x-input-label for="proveedor_id" value="Proveedor" />
                    <select id="proveedor_id" name="proveedor_id"
                        class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        @foreach ($proveedores as $proveedor)
                            <option value="{{ $proveedor->id }}" @selected(request('proveedor_id') == $proveedor->id)>{{ $proveedor->nombre_razon_social }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado"
                        class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        <option value="pendiente" @selected(request('estado') === 'pendiente')>Pendiente</option>
                        <option value="parcial" @selected(request('estado') === 'parcial')>Parcialmente pagada</option>
                        <option value="pagada" @selected(request('estado') === 'pagada')>Pagada</option>
                        <option value="vencida" @selected(request('estado') === 'vencida')>Vencida</option>
                        <option value="anulada" @selected(request('estado') === 'anulada')>Anulada</option>
                    </select>
                </div>
                <x-secondary-button type="submit">Filtrar</x-secondary-button>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Proveedor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Vencimiento</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Saldo</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($cuentas as $cuenta)
                            @php
                                $estadoMostrado = $cuenta->estado_mostrado;
                                $colores = [
                                    'pendiente' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-200',
                                    'parcial' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-200',
                                    'pagada' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200',
                                    'vencida' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200',
                                    'anulada' => 'bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300',
                                ];
                            @endphp
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('cuentas-pagar.show', $cuenta) }}" class="hover:underline">{{ $cuenta->proveedor->nombre_razon_social }}</a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $cuenta->fecha_vencimiento->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $cuenta->monto_total, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $cuenta->saldo, 2) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full {{ $colores[$estadoMostrado] ?? '' }}">
                                        {{ ucfirst($estadoMostrado) }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No se encontraron cuentas por pagar.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $cuentas->links() }}
        </div>
    </div>
</x-app-layout>
