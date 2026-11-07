<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
                {{ $cliente->nombre_razon_social }}
            </h2>
            @can('update', $cliente)
                <a href="{{ route('clientes.edit', $cliente) }}"
                    class="inline-flex items-center px-4 py-2.5 bg-brand-gradient border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm shadow-brand-violet/30 hover:opacity-90 transition">
                    Editar
                </a>
            @endcan
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/40 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Documento</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $cliente->tipo_documento }} {{ $cliente->numero_documento }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Estado</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $cliente->estado ? 'Activo' : 'Inactivo' }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Deuda pendiente</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $deudaPendiente, 2) }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase mb-3">Datos de contacto</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Dirección</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $cliente->direccion ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Teléfono</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $cliente->telefono ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Correo electrónico</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $cliente->email ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase mb-3">Cuentas por cobrar</h3>
                @forelse ($cliente->cuentasPorCobrar as $cuenta)
                    <a href="{{ route('cuentas-cobrar.show', $cuenta) }}" class="py-2 border-b border-paper-line dark:border-ink-line last:border-0 text-sm flex flex-wrap justify-between gap-2 hover:bg-paper dark:hover:bg-white/5 -mx-2 px-2">
                        <span class="text-paper-muted dark:text-ink-muted">
                            Emitida {{ $cuenta->fecha_emision->format('d/m/Y') }} · vence {{ $cuenta->fecha_vencimiento->format('d/m/Y') }} · {{ ucfirst($cuenta->estado_mostrado) }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">
                            S/ {{ number_format((float) $cuenta->monto_pagado, 2) }} / S/ {{ number_format((float) $cuenta->monto_total, 2) }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-paper-muted dark:text-ink-muted">Este cliente no tiene cuentas por cobrar registradas.</p>
                @endforelse
            </div>

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase mb-3">Últimas ventas</h3>
                @forelse ($cliente->ventas as $venta)
                    <div class="py-2 border-b border-paper-line dark:border-ink-line last:border-0 text-sm flex flex-wrap justify-between gap-2">
                        <span class="text-paper-muted dark:text-ink-muted">
                            {{ $venta->numero_operacion }} · {{ $venta->fecha->format('d/m/Y') }} · {{ ucfirst($venta->estado) }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $venta->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-paper-muted dark:text-ink-muted">Este cliente no tiene ventas registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
