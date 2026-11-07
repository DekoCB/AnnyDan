@php
    $hoy = now();
@endphp

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
                Flujo de caja
            </h2>
            @can('create', \App\Models\MovimientoCaja::class)
                <a href="{{ route('caja.create') }}"
                    class="inline-flex items-center px-4 py-2.5 bg-brand-gradient border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm shadow-brand-violet/30 hover:opacity-90 transition">
                    Registrar movimiento
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

            <div class="flex flex-wrap gap-2 text-sm">
                <a href="{{ route('caja.index', ['desde' => $hoy->toDateString(), 'hasta' => $hoy->toDateString()]) }}"
                    class="px-3 py-1.5 rounded-md border border-paper-line dark:border-ink-line hover:bg-paper dark:hover:bg-white/5">Hoy</a>
                <a href="{{ route('caja.index', ['desde' => $hoy->copy()->startOfWeek()->toDateString(), 'hasta' => $hoy->copy()->endOfWeek()->toDateString()]) }}"
                    class="px-3 py-1.5 rounded-md border border-paper-line dark:border-ink-line hover:bg-paper dark:hover:bg-white/5">Esta semana</a>
                <a href="{{ route('caja.index', ['desde' => $hoy->copy()->startOfMonth()->toDateString(), 'hasta' => $hoy->copy()->endOfMonth()->toDateString()]) }}"
                    class="px-3 py-1.5 rounded-md border border-paper-line dark:border-ink-line hover:bg-paper dark:hover:bg-white/5">Este mes</a>
                <a href="{{ route('caja.index', ['desde' => $hoy->copy()->startOfYear()->toDateString(), 'hasta' => $hoy->copy()->endOfYear()->toDateString()]) }}"
                    class="px-3 py-1.5 rounded-md border border-paper-line dark:border-ink-line hover:bg-paper dark:hover:bg-white/5">Este año</a>
            </div>

            <form method="GET" action="{{ route('caja.index') }}"
                class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4 flex flex-wrap gap-4 items-end">
                <div>
                    <x-input-label for="desde" value="Desde" />
                    <x-text-input id="desde" name="desde" type="date" class="mt-1 block w-full" value="{{ $desde->toDateString() }}" />
                </div>
                <div>
                    <x-input-label for="hasta" value="Hasta" />
                    <x-text-input id="hasta" name="hasta" type="date" class="mt-1 block w-full" value="{{ $hasta->toDateString() }}" />
                </div>
                <div>
                    <x-input-label for="tipo" value="Tipo" />
                    <select id="tipo" name="tipo"
                        class="mt-1 block border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] rounded-md shadow-sm">
                        <option value="">Todos</option>
                        <option value="ingreso" @selected(request('tipo') === 'ingreso')>Ingreso</option>
                        <option value="egreso" @selected(request('tipo') === 'egreso')>Egreso</option>
                    </select>
                </div>
                <div>
                    <x-input-label for="categoria_id" value="Categoría" />
                    <select id="categoria_id" name="categoria_id"
                        class="mt-1 block border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] rounded-md shadow-sm">
                        <option value="">Todas</option>
                        @foreach ($categorias as $categoria)
                            <option value="{{ $categoria->id }}" @selected(request('categoria_id') == $categoria->id)>{{ $categoria->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <x-secondary-button type="submit">Filtrar</x-secondary-button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Ingresos</p>
                    <p class="mt-1 text-2xl font-semibold text-green-600 dark:text-green-400">S/ {{ number_format((float) $totalIngresos, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Egresos</p>
                    <p class="mt-1 text-2xl font-semibold text-red-600 dark:text-red-400">S/ {{ number_format((float) $totalEgresos, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Resultado del periodo</p>
                    <p class="mt-1 text-2xl font-semibold {{ $utilidad >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                        S/ {{ number_format((float) $utilidad, 2) }}
                    </p>
                </div>
            </div>

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-paper-line dark:divide-ink-line">
                    <thead class="bg-paper dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Fecha</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Categoría</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Descripción</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Tipo</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Monto</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-paper-line dark:divide-ink-line">
                        @forelse ($movimientos as $movimiento)
                            <tr>
                                <td class="px-4 py-3 text-sm text-paper-muted dark:text-ink-muted">{{ $movimiento->fecha->format('d/m/Y') }}</td>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $movimiento->categoria->nombre }}</td>
                                <td class="px-4 py-3 text-sm text-paper-muted dark:text-ink-muted">{{ $movimiento->descripcion ?? '—' }}</td>
                                <td class="px-4 py-3 text-sm">
                                    @if ($movimiento->tipo === 'ingreso')
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-200">Ingreso</span>
                                    @else
                                        <span class="inline-flex px-2 py-1 text-xs font-semibold rounded-full bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-200">Egreso</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-sm text-right font-medium {{ $movimiento->tipo === 'ingreso' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $movimiento->tipo === 'ingreso' ? '+' : '-' }} S/ {{ number_format((float) $movimiento->monto, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-4 py-8 text-center text-sm text-paper-muted dark:text-ink-muted">
                                    No se encontraron movimientos en el periodo seleccionado.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{ $movimientos->links() }}
        </div>
    </div>
</x-app-layout>
