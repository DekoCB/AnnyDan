<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
                Proveedores
            </h2>
            @can('create', \App\Models\Proveedor::class)
                <a href="{{ route('proveedores.create') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-brand-gradient border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm shadow-brand-violet/30 hover:opacity-90 transition">
                    Nuevo proveedor
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

            <form id="proveedor-search-form" method="GET" action="{{ route('proveedores.index') }}" data-debounce-search
                class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4 flex flex-wrap gap-4 items-end">
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="q" value="Buscar por nombre o documento" />
                    <x-text-input id="q" name="q" type="text" class="mt-1 block w-full" value="{{ request('q') }}" />
                </div>
                <div>
                    <x-input-label for="estado" value="Estado" />
                    <select id="estado" name="estado"
                        class="mt-1 block border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] rounded-md shadow-sm">
                        <option value="">Todos</option>
                        <option value="1" @selected(request('estado') === '1')>Activos</option>
                        <option value="0" @selected(request('estado') === '0')>Inactivos</option>
                    </select>
                </div>
                <x-secondary-button type="submit">Filtrar</x-secondary-button>
            </form>

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-paper-line dark:divide-ink-line">
                    <thead class="bg-paper dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Proveedor</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Documento</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Contacto</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Estado</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Acciones</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-paper-line dark:divide-ink-line">
                        @forelse ($proveedores as $proveedor)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">
                                    <a href="{{ route('proveedores.show', $proveedor) }}" class="hover:underline">
                                        {{ $proveedor->nombre_razon_social }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-paper-muted dark:text-ink-muted">
                                    {{ $proveedor->tipo_documento }} {{ $proveedor->numero_documento }}
                                </td>
                                <td class="px-4 py-3 text-sm text-paper-muted dark:text-ink-muted">
                                    {{ $proveedor->telefono ?? $proveedor->email ?? '—' }}
                                </td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($proveedor->estado)
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">Activo</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-600 dark:bg-gray-700 dark:text-gray-300">Inactivo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right space-x-2 whitespace-nowrap">
                                    @can('update', $proveedor)
                                        <a href="{{ route('proveedores.edit', $proveedor) }}" class="text-brand-violet dark:text-violet-300 hover:underline">Editar</a>
                                    @endcan
                                    @can('delete', $proveedor)
                                        @if ($proveedor->estado)
                                            <form method="POST" action="{{ route('proveedores.destroy', $proveedor) }}" class="inline" data-confirm="¿Desactivar a {{ $proveedor->nombre_razon_social }}? No podrá usarse en nuevas cuentas por pagar.">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="text-red-600 dark:text-red-400 hover:underline">Desactivar</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('proveedores.activate', $proveedor) }}" class="inline" data-confirm="¿Activar nuevamente a {{ $proveedor->nombre_razon_social }}?">
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
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-paper-muted dark:text-ink-muted">
                                    No se encontraron proveedores.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $proveedores->links() }}
        </div>
    </div>
</x-app-layout>
