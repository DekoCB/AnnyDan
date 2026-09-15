<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ $proveedor->nombre_razon_social }}
            </h2>
            @can('update', $proveedor)
                <a href="{{ route('proveedores.edit', $proveedor) }}"
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
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $proveedor->tipo_documento }} {{ $proveedor->numero_documento }}</p>
                </div>
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Estado</p>
                    <p class="mt-1 text-lg font-semibold text-gray-900 dark:text-gray-100">{{ $proveedor->estado ? 'Activo' : 'Inactivo' }}</p>
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
                        <dd class="text-gray-900 dark:text-gray-100">{{ $proveedor->direccion ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Teléfono</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $proveedor->telefono ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-gray-500 dark:text-gray-400">Correo electrónico</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $proveedor->email ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase mb-3">Cuentas por pagar</h3>
                @forelse ($proveedor->cuentasPorPagar as $cuenta)
                    <a href="{{ route('cuentas-pagar.show', $cuenta) }}" class="py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 text-sm flex flex-wrap justify-between gap-2 hover:bg-gray-50 dark:hover:bg-gray-900/40 -mx-2 px-2">
                        <span class="text-gray-600 dark:text-gray-400">
                            Emitida {{ $cuenta->fecha_emision->format('d/m/Y') }} · vence {{ $cuenta->fecha_vencimiento->format('d/m/Y') }} · {{ ucfirst($cuenta->estado_mostrado) }}
                        </span>
                        <span class="font-medium text-gray-900 dark:text-gray-100">
                            S/ {{ number_format((float) $cuenta->monto_pagado, 2) }} / S/ {{ number_format((float) $cuenta->monto_total, 2) }}
                        </span>
                    </a>
                @empty
                    <p class="text-sm text-gray-500 dark:text-gray-400">Este proveedor no tiene cuentas por pagar registradas.</p>
                @endforelse
            </div>
        </div>
    </div>
</x-app-layout>
