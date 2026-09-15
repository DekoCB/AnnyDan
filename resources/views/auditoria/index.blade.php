<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            Auditoría
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" action="{{ route('auditoria.index') }}"
                class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4 flex flex-wrap gap-4 items-end">
                <div>
                    <x-input-label for="usuario_id" value="Usuario" />
                    <select id="usuario_id" name="usuario_id"
                        class="mt-1 block border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 rounded-md shadow-sm">
                        <option value="">Todos</option>
                        @foreach ($usuarios as $usuario)
                            <option value="{{ $usuario->id }}" @selected(request('usuario_id') == $usuario->id)>{{ $usuario->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <x-input-label for="accion" value="Acción" />
                    <x-text-input id="accion" name="accion" type="text" class="mt-1 block w-full"
                        value="{{ request('accion') }}" placeholder="ej. venta.creada" />
                </div>
                <div>
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" class="mt-1 block w-full" value="{{ request('desde') }}" />
                </div>
                <div>
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" class="mt-1 block w-full" value="{{ request('hasta') }}" />
                </div>
                <x-secondary-button type="submit">Filtrar</x-secondary-button>
            </form>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-700">
                    <thead class="bg-gray-50 dark:bg-gray-900">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Fecha / hora</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Usuario</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Acción</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase">Registro afectado</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                        @forelse ($auditorias as $registro)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">{{ $registro->created_at->format('d/m/Y H:i:s') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $registro->usuario?->name ?? 'Sistema' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    <a href="{{ route('auditoria.show', $registro) }}" class="text-indigo-600 dark:text-indigo-400 hover:underline font-mono text-xs">
                                        {{ $registro->accion }}
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-sm text-gray-600 dark:text-gray-400">
                                    {{ $etiquetas[$registro->auditable_type] ?? '—' }}
                                    @if ($registro->auditable_id)
                                        <span class="text-gray-400">#{{ $registro->auditable_id }}</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-4 py-8 text-center text-sm text-gray-500 dark:text-gray-400">
                                    No se encontraron registros de auditoría.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $auditorias->links() }}
        </div>
    </div>
</x-app-layout>
