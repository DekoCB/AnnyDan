<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Boletas emitidas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" action="{{ route('comprobantes.index') }}"
                class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 flex flex-wrap gap-4 items-end">
                <div>
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado"
                        class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        <option value="emitido" @selected(request('estado') === 'emitido')>Emitido</option>
                        <option value="anulado" @selected(request('estado') === 'anulado')>Anulado</option>
                    </select>
                </div>
                <x-secondary-button type="submit">Filtrar</x-secondary-button>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">N° Boleta</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Total</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($comprobantes as $comprobante)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('comprobantes.show', $comprobante) }}" class="hover:underline">{{ $comprobante->numero_completo }}</a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $comprobante->venta->cliente->nombre_razon_social }}</td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $comprobante->fecha_emision->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $comprobante->venta->total, 2) }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($comprobante->estado === 'emitido')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">Emitido</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Anulado</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No se encontraron boletas.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $comprobantes->links() }}
        </div>
    </div>
</x-app-layout>
