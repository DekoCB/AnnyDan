<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                Clientes
            </h2>
            @can('create', \App\Models\Cliente::class)
                <a href="{{ route('clientes.create') }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
                    Nuevo cliente
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

            <form id="cliente-search-form" method="GET" action="{{ route('clientes.index') }}" data-debounce-search
                class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="q" value="Buscar por nombre o documento" />
                    <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" value="{{ request('q') }}" />
                </div>
                <div>
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado"
                        class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        <option value="1" @selected(request('estado') === '1')>Activos</option>
                        <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                    </select>
                </div>
                <x-secondary-button type="submit">Filtrar</x-secondary-button>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Cliente</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Documento</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Contacto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Estado</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($clientes as $cliente)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('clientes.show', $cliente) }}" class="hover:underline">
                                        {{ $cliente->nombre_razon_social }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $cliente->tipo_documento }} {{ $cliente->numero_documento }}
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $cliente->telefono ?? $cliente->email ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($cliente->estado)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">Activo</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right space-x-2 whitespace-nowrap">
                                    @can('update', $cliente)
                                        <a href="{{ route('clientes.edit', $cliente) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Editar</a>
                                    @endcan
                                    @can('delete', $cliente)
                                        @if ($cliente->estado)
                                            <form method="POST" action="{{ route('clientes.destroy', $cliente) }}" class="inline" data-confirm="¿Desactivar a {{ $cliente->nombre_razon_social }}? No podrá usarse en nuevas ventas.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Desactivar</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('clientes.activate', $cliente) }}" class="inline" data-confirm="¿Activar nuevamente a {{ $cliente->nombre_razon_social }}?">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-green-600 dark:text-green-400 hover:underline">Activar</button>
                                            </form>
                                        @endif
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No se encontraron clientes.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $clientes->links() }}
        </div>
    </div>
</x-app-layout>
