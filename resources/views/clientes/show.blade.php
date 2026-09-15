<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $cliente->nombre_razon_social }}
            </h2>
            @can('update', $cliente)
                <a href="{{ route('clientes.edit', $cliente) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white">
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
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Documento</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $cliente->tipo_documento }} {{ $cliente->numero_documento }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Estado</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $cliente->estado ? 'Activo' : 'Inactivo' }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Deuda pendiente</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $deudaPendiente, 2) }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase mb-3">Datos de contacto</h3>
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Dirección</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $cliente->direccion ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Teléfono</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $cliente->telefono ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Correo electrónico</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $cliente->email ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase mb-3">Cuentas por cobrar</h3>
                @forelse ($cliente->cuentasPorCobrar as $cuenta)
                    <a href="{{ route('cuentas-cobrar.show', $cuenta) }}" class="py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 text-sm flex flex-wrap justify-between gap-2 hover:bg-gray-50 dark:hover:bg-gray-900/40 -mx-2 px-2">
                        <span class="text-gray-600 dark:text-gray-400">
                            Emitida {{ $cuenta->fecha_emision->format('d/m/Y') }} · vence {{ $cuenta->fecha_vencimiento->format('d/m/Y') }} · {{ ucfirst($cuenta->estado_mostrado) }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">
                            S/ {{ number_format((float) $cuenta->monto_pagado, 2) }} / S/ {{ number_format((float) $cuenta->monto_total, 2) }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Este cliente no tiene cuentas por cobrar registradas.</p>
                @endforelse
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase mb-3">Últimas ventas</h3>
                @forelse ($cliente->ventas as $venta)
                    <div class="py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 text-sm flex flex-wrap justify-between gap-2">
                        <span class="text-gray-600 dark:text-gray-400">
                            {{ $venta->numero_operacion }} · {{ $venta->fecha->format('d/m/Y') }} · {{ ucfirst($venta->estado) }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $venta->total, 2) }}</span>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Este cliente no tiene ventas registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
