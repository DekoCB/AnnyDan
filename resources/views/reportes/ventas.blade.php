@php
    $nombresMeses = [1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'];
    $nombresFormaPago = ['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia', 'yape_plin' => 'Yape / Plin', 'otro' => 'Otro'];
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
            Reporte de ventas
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-6xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <form method="GET" action="{{ route('reportes.ventas') }}"
                class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4 flex flex-wrap gap-4 items-end">
                <div>
                    <x-input-label for="mes" value="Mes" />
                    <select id="mes" name="mes"
                        class="mt-1 block border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] rounded-md shadow-sm">
                        @foreach ($nombresMeses as $numero => $nombre)
                            <option value="{{ $numero }}" @selected($mes === $numero)>{{ $nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <x-input-label for="anio" value="Año" />
                    <select id="anio" name="anio"
                        class="mt-1 block border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] rounded-md shadow-sm">
                        @foreach (range(now()->year, now()->year - 4) as $anioOpcion)
                            <option value="{{ $anioOpcion }}" @selected($anio === $anioOpcion)>{{ $anioOpcion }}</option>
                        @endforeach
                    </select>
                </div>
                <x-secondary-button type="submit">Consultar</x-secondary-button>
            </form>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Total vendido</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $totalVentas, 2) }}</p>
                    @if (! is_null($variacion))
                        <p class="text-xs mt-1 {{ $variacion >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            {{ $variacion >= 0 ? '▲' : '▼' }} {{ number_format(abs($variacion), 1) }}% vs. mes anterior (S/ {{ number_format((float) $totalMesAnterior, 2) }})
                        </p>
                    @endif
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">N° de operaciones</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">{{ $numeroOperaciones }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Ticket promedio</p>
                    <p class="mt-1 text-2xl font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $ticketPromedio, 2) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg overflow-hidden">
                    <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase px-4 pt-4">Ventas por cliente</h3>
                    <table class="min-w-full divide-y divide-paper-line dark:divide-ink-line mt-2">
                        <thead class="bg-paper dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Cliente</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Ops.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-paper-line dark:divide-ink-line">
                            @forelse ($ventasPorCliente as $fila)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $fila->cliente }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-paper-muted dark:text-ink-muted">{{ $fila->operaciones }}</td>
                                    <td class="px-4 py-2 text-sm text-right font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $fila->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-sm text-paper-muted dark:text-ink-muted">Sin ventas en el periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg overflow-hidden">
                    <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase px-4 pt-4">Ventas por forma de pago</h3>
                    <table class="min-w-full divide-y divide-paper-line dark:divide-ink-line mt-2">
                        <thead class="bg-paper dark:bg-white/5">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Forma de pago</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Ops.</th>
                                <th class="px-4 py-2 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Total</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-paper-line dark:divide-ink-line">
                            @forelse ($ventasPorFormaPago as $fila)
                                <tr>
                                    <td class="px-4 py-2 text-sm text-gray-900 dark:text-gray-100">{{ $nombresFormaPago[$fila->forma_pago] ?? $fila->forma_pago }}</td>
                                    <td class="px-4 py-2 text-sm text-right text-paper-muted dark:text-ink-muted">{{ $fila->operaciones }}</td>
                                    <td class="px-4 py-2 text-sm text-right font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $fila->total, 2) }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="3" class="px-4 py-6 text-center text-sm text-paper-muted dark:text-ink-muted">Sin ventas en el periodo.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
