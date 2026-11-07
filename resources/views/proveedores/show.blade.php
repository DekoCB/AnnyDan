<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
                {{ $proveedor->nombre_razon_social }}
            </h2>
            @can('update', $proveedor)
                <a href="{{ route('proveedores.edit', $proveedor) }}"
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
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $proveedor->tipo_documento }} {{ $proveedor->numero_documento }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Estado</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $proveedor->estado ? 'Activo' : 'Inactivo' }}</p>
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
                        <dd class="text-gray-900 dark:text-gray-100">{{ $proveedor->direccion ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Teléfono</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $proveedor->telefono ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Correo electrónico</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $proveedor->email ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase mb-3">Cuentas por pagar</h3>
                @forelse ($proveedor->cuentasPorPagar as $cuenta)
                    <a href="{{ route('cuentas-pagar.show', $cuenta) }}" class="py-2 border-b border-paper-line dark:border-ink-line last:border-0 text-sm flex flex-wrap justify-between gap-2 hover:bg-paper dark:hover:bg-white/5 -mx-2 px-2">
                        <span class="text-paper-muted dark:text-ink-muted">
                            Emitida {{ $cuenta->fecha_emision->format('d/m/Y') }} · vence {{ $cuenta->fecha_vencimiento->format('d/m/Y') }} · {{ ucfirst($cuenta->estado_mostrado) }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">
                            S/ {{ number_format((float) $cuenta->monto_pagado, 2) }} / S/ {{ number_format((float) $cuenta->monto_total, 2) }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-paper-muted dark:text-ink-muted">Este proveedor no tiene cuentas por pagar registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
