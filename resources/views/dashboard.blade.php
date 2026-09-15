<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            @isset($cxcVencidasCantidad)
                @if ($cxcVencidasCantidad > 0)
                    <div class="bg-red-50 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-sm rounded-md p-4">
                        Tienes <strong>{{ $cxcVencidasCantidad }}</strong> cuenta(s) por cobrar vencida(s)
                        (S/ {{ number_format((float) $cxcVencidasMonto, 2) }}).
                        <a href="{{ route('cuentas-cobrar.index', ['estado' => 'vencida']) }}" class="underline">Ver detalle</a>
                    </div>
                @endif
            @endisset

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                @isset($totalVentasMes)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Ventas del mes</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $totalVentasMes, 2) }}</p>
                        <p class="text-xs text-gray-400 mt-1">{{ $numeroVentasMes }} operaciones · ticket prom. S/ {{ number_format((float) $ticketPromedioMes, 2) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Ventas del día</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $totalVentasDia, 2) }}</p>
                    </div>
                @endisset

                @isset($cxcPendiente)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Cuentas por cobrar</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $cxcPendiente, 2) }}</p>
                        @if ($cxcVencidasCantidad > 0)
                            <p class="text-xs text-red-500 mt-1">{{ $cxcVencidasCantidad }} vencida(s)</p>
                        @endif
                    </div>
                @endisset

                @isset($cxpPendiente)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Cuentas por pagar</p>
                        <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $cxpPendiente, 2) }}</p>
                        @if ($cxpVencidasCantidad > 0)
                            <p class="text-xs text-red-500 mt-1">{{ $cxpVencidasCantidad }} vencida(s)</p>
                        @endif
                    </div>
                @endisset
            </div>

            @isset($ingresosMes)
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Ingresos del mes</p>
                        <p class="mt-1 text-xl font-semibold text-green-600 dark:text-green-400">S/ {{ number_format((float) $ingresosMes, 2) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Egresos del mes</p>
                        <p class="mt-1 text-xl font-semibold text-red-600 dark:text-red-400">S/ {{ number_format((float) $egresosMes, 2) }}</p>
                    </div>
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                        <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Resultado del mes</p>
                        <p class="mt-1 text-xl font-semibold {{ $resultadoMes >= 0 ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                            S/ {{ number_format((float) $resultadoMes, 2) }}
                        </p>
                    </div>
                    @isset($clientesActivos)
                        <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                            <p class="text-xs uppercase text-gray-500 dark:text-gray-400">Clientes activos</p>
                            <p class="mt-1 text-xl font-semibold text-gray-900 dark:text-gray-100">{{ $clientesActivos }}</p>
                        </div>
                    @endisset
                </div>
            @endisset

            @isset($ventasPorDia)
                @php $maximo = max(1, collect($ventasPorDia)->max('total')); @endphp
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-4">
                    <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase mb-4">Ventas de los últimos 7 días</h3>
                    <div class="flex items-end gap-3 h-32">
                        @foreach ($ventasPorDia as $dia)
                            <div class="flex-1 flex flex-col items-center justify-end h-full">
                                <span class="text-xs text-gray-500 dark:text-gray-400 mb-1">S/ {{ number_format($dia['total'], 0) }}</span>
                                <div class="w-full bg-indigo-500 dark:bg-indigo-400 rounded-t" style="height: {{ max(4, ($dia['total'] / $maximo) * 100) }}%"></div>
                                <span class="text-xs text-gray-500 dark:text-gray-400 mt-1">{{ $dia['fecha'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endisset

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                @isset($ultimasVentas)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase mb-3">Últimas ventas</h3>
                        @forelse ($ultimasVentas as $venta)
                            <a href="{{ route('ventas.show', $venta) }}" class="py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 text-sm flex flex-wrap justify-between gap-2 hover:bg-gray-50 dark:hover:bg-gray-900/40 -mx-2 px-2">
                                <span class="text-gray-600 dark:text-gray-400">{{ $venta->numero_operacion }} · {{ $venta->cliente->nombre_razon_social }}</span>
                                <span class="font-medium text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $venta->total, 2) }}</span>
                            </a>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Todavía no hay ventas registradas.</p>
                        @endforelse
                    </div>
                @endisset

                @isset($pagosClientesRecientes)
                    <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6">
                        <h3 class="text-sm font-semibold text-gray-700 dark:text-gray-300 uppercase mb-3">Pagos recientes</h3>
                        @forelse ($pagosClientesRecientes->merge($pagosProveedoresRecientes)->sortByDesc('fecha_pago')->take(6) as $pago)
                            <div class="py-2 border-b border-gray-100 dark:border-gray-700 last:border-0 text-sm flex flex-wrap justify-between gap-2">
                                <span class="text-gray-600 dark:text-gray-400">
                                    {{ $pago->fecha_pago->format('d/m/Y') }} ·
                                    {{ $pago instanceof \App\Models\PagoCliente ? 'Cobro: '.$pago->cuentaPorCobrar->cliente->nombre_razon_social : 'Pago: '.$pago->cuentaPorPagar->proveedor->nombre_razon_social }}
                                </span>
                                <span class="font-medium {{ $pago instanceof \App\Models\PagoCliente ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400' }}">
                                    S/ {{ number_format((float) $pago->monto, 2) }}
                                </span>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 dark:text-gray-400">Todavía no hay pagos registrados.</p>
                        @endforelse
                    </div>
                @endisset
            </div>

            @if (! isset($totalVentasMes) && ! isset($ingresosMes) && ! isset($cxcPendiente) && ! isset($cxpPendiente))
                <div class="bg-white dark:bg-gray-800 shadow-sm rounded-lg p-6 text-sm text-gray-600 dark:text-gray-400">
                    {{ __("You're logged in!") }}
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
