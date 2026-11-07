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

<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
                Cuenta por cobrar — {{ $cuenta->cliente->nombre_razon_social }}
            </h2>
            @can('anular', $cuenta)
                @if ($cuenta->estado === 'pendiente')
                    <form method="POST" action="{{ route('cuentas-cobrar.anular', $cuenta) }}"
                        data-confirm="¿Anular esta cuenta por cobrar? Esta acción no se puede deshacer.">
                        @csrf
                        @method('PATCH')
                        <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                            Anular
                        </button>
                    </form>
                @endif
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
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

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Total</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $cuenta->monto_total, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Pagado</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $cuenta->monto_pagado, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Saldo</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $cuenta->saldo, 2) }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Estado</p>
                    <span class="inline-flex mt-1 px-2 py-1 text-xs font-semibold rounded-full {{ $colores[$estadoMostrado] ?? '' }}">
                        {{ ucfirst($estadoMostrado) }}
                    </span>
                </div>
            </div>

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4 text-sm flex flex-wrap gap-x-8 gap-y-1">
                <p><span class="text-paper-muted dark:text-ink-muted">Emisión:</span> {{ $cuenta->fecha_emision->format('d/m/Y') }}</p>
                <p><span class="text-paper-muted dark:text-ink-muted">Vencimiento:</span> {{ $cuenta->fecha_vencimiento->format('d/m/Y') }}</p>
                @if ($cuenta->venta)
                    <p><span class="text-paper-muted dark:text-ink-muted">Venta asociada:</span> <a href="{{ route('ventas.show', $cuenta->venta) }}" class="text-brand-violet dark:text-violet-300 hover:underline">{{ $cuenta->venta->numero_operacion }}</a></p>
                @endif
            </div>

            @can('pagar', $cuenta)
                @if (in_array($cuenta->estado, ['pendiente', 'parcial']))
                    <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase mb-3">Registrar pago</h3>
                        <form method="POST" action="{{ route('cuentas-cobrar.pagar', $cuenta) }}" class="grid grid-cols-1 sm:grid-cols-4 gap-4 items-end">
                            @csrf
                            <div>
                                <x-input-label for="monto" value="Monto" />
                                <x-text-input id="monto" name="monto" type="number" step="0.01" min="0.01" :max="$cuenta->saldo"
                                    class="mt-1 block w-full" required />
                            </div>
                            <div>
                                <x-input-label for="fecha_pago" value="Fecha" />
                                <x-text-input id="fecha_pago" name="fecha_pago" type="date" class="mt-1 block w-full"
                                    :value="now()->toDateString()" required />
                            </div>
                            <div>
                                <x-input-label for="medio_pago" value="Medio de pago" />
                                <select id="medio_pago" name="medio_pago" required
                                    class="mt-1 block w-full border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] rounded-md shadow-sm">
                                    @foreach (['efectivo' => 'Efectivo', 'tarjeta' => 'Tarjeta', 'transferencia' => 'Transferencia', 'yape_plin' => 'Yape / Plin', 'otro' => 'Otro'] as $valor => $etiqueta)
                                        <option value="{{ $valor }}">{{ $etiqueta }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <x-primary-button>Registrar pago</x-primary-button>
                        </form>
                        @if ($errors->any())
                            <div class="mt-3 text-sm text-red-600 dark:text-red-400">
                                @foreach ($errors->all() as $error)
                                    <p>{{ $error }}</p>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            @endcan

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase mb-3">Historial de pagos</h3>
                @forelse ($cuenta->pagos as $pago)
                    <div class="py-2 border-b border-paper-line dark:border-ink-line last:border-0 text-sm flex flex-wrap justify-between gap-2">
                        <span class="text-paper-muted dark:text-ink-muted">
                            {{ $pago->fecha_pago->format('d/m/Y') }} · {{ ucfirst(str_replace('_', ' / ', $pago->medio_pago)) }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $pago->monto, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-paper-muted dark:text-ink-muted">Todavía no se han registrado pagos.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
