@php
    $mesActual = ucfirst(now()->translatedFormat('F Y'));
@endphp

<x-app-layout>
    <div class="px-4 sm:px-6 lg:px-8 py-8 space-y-6">

        <div class="flex flex-wrap items-center justify-between gap-4">
            <div>
                <h1 class="font-display text-2xl font-semibold">Bienvenido, {{ explode(' ', Auth::user()->name)[0] }}</h1>
                <p class="text-sm text-paper-muted dark:text-ink-muted mt-0.5">Esto es lo que está pasando en tu negocio hoy.</p>
            </div>
            <span class="inline-flex items-center gap-2 rounded-full bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line px-4 py-2 text-sm font-medium">
                <svg class="w-4 h-4 text-brand-violet" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M8 3v4M16 3v4M3 10h18"/></svg>
                {{ $mesActual }}
            </span>
        </div>

        @if (isset($cxcVencidasCantidad) && $cxcVencidasCantidad > 0)
            <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line px-5 py-3.5 flex flex-wrap items-center gap-3 text-sm">
                <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                Tienes <strong class="font-semibold">{{ $cxcVencidasCantidad }}</strong> cuenta(s) por cobrar vencida(s)
                (S/ {{ number_format((float) $cxcVencidasMonto, 2) }}).
                <a href="{{ route('cuentas-cobrar.index', ['estado' => 'vencida']) }}" class="text-brand-violet dark:text-violet-300 font-medium hover:underline">Ver detalle →</a>
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-12 gap-5">

            @isset($resultadoMes)
                <div class="lg:col-span-4 rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-6 flex flex-col">
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-paper-muted dark:text-ink-muted">Resultado del mes</span>
                    </div>

                    <div class="relative w-36 h-36 mx-auto my-5">
                        <svg viewBox="0 0 120 120" class="w-full h-full -rotate-90">
                            <circle cx="60" cy="60" r="52" fill="none" stroke-width="10" class="stroke-paper dark:stroke-white/5" />
                            <circle cx="60" cy="60" r="52" fill="none" stroke-width="10" stroke-linecap="round"
                                stroke-dasharray="326.7" stroke-dashoffset="{{ 326.7 - (326.7 * min(100, max(0, $ingresoRatioPct)) / 100) }}"
                                class="{{ $resultadoMes >= 0 ? 'stroke-brand-violet' : 'stroke-rose-400' }}" />
                        </svg>
                        <div class="absolute inset-0 flex flex-col items-center justify-center">
                            <span class="font-display text-xl font-semibold {{ $resultadoMes >= 0 ? '' : 'text-rose-500' }}">
                                {{ $resultadoMes >= 0 ? '+' : '' }}S/ {{ number_format((float) $resultadoMes, 0) }}
                            </span>
                            <span class="text-[11px] text-paper-muted dark:text-ink-muted mt-0.5">ingresos − egresos</span>
                        </div>
                    </div>

                    <div class="space-y-2 mt-1">
                        <div class="flex items-center justify-between rounded-xl bg-paper dark:bg-white/5 px-3.5 py-2.5 text-sm">
                            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-brand-violet"></span>Ingresos</span>
                            <span class="font-medium tabular-nums">S/ {{ number_format((float) $ingresosMes, 2) }}</span>
                        </div>
                        <div class="flex items-center justify-between rounded-xl bg-paper dark:bg-white/5 px-3.5 py-2.5 text-sm">
                            <span class="flex items-center gap-2"><span class="w-2 h-2 rounded-full bg-paper-muted dark:bg-ink-muted"></span>Egresos</span>
                            <span class="font-medium tabular-nums">S/ {{ number_format((float) $egresosMes, 2) }}</span>
                        </div>
                    </div>
                </div>
            @endisset

            <div class="lg:col-span-8 grid grid-cols-1 sm:grid-cols-3 gap-5 content-start">
                @isset($totalVentasMes)
                    <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-5">
                        <span class="text-xs font-medium text-paper-muted dark:text-ink-muted">Ventas del mes</span>
                        <p class="font-display text-xl font-semibold mt-2 tabular-nums">S/ {{ number_format((float) $totalVentasMes, 2) }}</p>
                        <p class="text-xs text-paper-muted dark:text-ink-muted mt-1">{{ $numeroVentasMes }} operaciones</p>
                    </div>
                @endisset
                @isset($cxcPendiente)
                    <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-5">
                        <span class="text-xs font-medium text-paper-muted dark:text-ink-muted">Cuentas por cobrar</span>
                        <p class="font-display text-xl font-semibold mt-2 tabular-nums">S/ {{ number_format((float) $cxcPendiente, 2) }}</p>
                        <p class="text-xs text-paper-muted dark:text-ink-muted mt-1">pendiente de cobro</p>
                    </div>
                @endisset
                @isset($cxpPendiente)
                    <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-5">
                        <span class="text-xs font-medium text-paper-muted dark:text-ink-muted">Cuentas por pagar</span>
                        <p class="font-display text-xl font-semibold mt-2 tabular-nums">S/ {{ number_format((float) $cxpPendiente, 2) }}</p>
                        <p class="text-xs text-paper-muted dark:text-ink-muted mt-1">pendiente de pago</p>
                    </div>
                @endisset
                @isset($clientesActivos)
                    <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-5">
                        <span class="text-xs font-medium text-paper-muted dark:text-ink-muted">Clientes activos</span>
                        <p class="font-display text-xl font-semibold mt-2 tabular-nums">{{ $clientesActivos }}</p>
                    </div>
                @endisset
                @isset($totalVentasDia)
                    <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-5">
                        <span class="text-xs font-medium text-paper-muted dark:text-ink-muted">Ventas de hoy</span>
                        <p class="font-display text-xl font-semibold mt-2 tabular-nums">S/ {{ number_format((float) $totalVentasDia, 2) }}</p>
                    </div>
                @endisset

                @isset($ventasPorDia)
                    @php $maximo = max(1, collect($ventasPorDia)->max('total')); @endphp
                    <div class="sm:col-span-3 rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-6">
                        <h3 class="text-sm font-medium text-paper-muted dark:text-ink-muted mb-5">Ventas de los últimos 7 días</h3>
                        <div class="flex items-end gap-3 h-28">
                            @foreach ($ventasPorDia as $dia)
                                <div class="flex-1 flex flex-col items-center justify-end h-full gap-1.5">
                                    <span class="text-[11px] text-paper-muted dark:text-ink-muted tabular-nums">{{ number_format($dia['total'], 0) }}</span>
                                    <div class="w-full rounded-t-md bg-brand-gradient opacity-90" style="height: {{ max(4, ($dia['total'] / $maximo) * 100) }}%"></div>
                                    <span class="text-[11px] text-paper-muted dark:text-ink-muted">{{ $dia['fecha'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endisset
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-5">
            @isset($ultimasVentas)
                <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-6">
                    <h3 class="text-sm font-medium text-paper-muted dark:text-ink-muted mb-3">Últimas ventas</h3>
                    @forelse ($ultimasVentas as $venta)
                        <a href="{{ route('ventas.show', $venta) }}" class="flex items-center justify-between py-2.5 border-b border-paper-line dark:border-ink-line last:border-0 text-sm hover:opacity-70 transition">
                            <span>{{ $venta->numero_operacion }} · {{ $venta->cliente->nombre_razon_social }}</span>
                            <span class="font-medium tabular-nums">S/ {{ number_format((float) $venta->total, 2) }}</span>
                        </a>
                    @empty
                        <p class="text-sm text-paper-muted dark:text-ink-muted py-2">Todavía no hay ventas registradas.</p>
                    @endforelse
                </div>
            @endisset

            @isset($pagosClientesRecientes)
                <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-6">
                    <h3 class="text-sm font-medium text-paper-muted dark:text-ink-muted mb-3">Pagos recientes</h3>
                    @forelse ($pagosClientesRecientes->merge($pagosProveedoresRecientes)->sortByDesc('fecha_pago')->take(6) as $pago)
                        <div class="flex items-center justify-between py-2.5 border-b border-paper-line dark:border-ink-line last:border-0 text-sm">
                            <span>
                                {{ $pago->fecha_pago->format('d/m/Y') }} ·
                                {{ $pago instanceof \App\Models\PagoCliente ? 'Cobro: '.$pago->cuentaPorCobrar->cliente->nombre_razon_social : 'Pago: '.$pago->cuentaPorPagar->proveedor->nombre_razon_social }}
                            </span>
                            <span class="font-medium tabular-nums {{ $pago instanceof \App\Models\PagoCliente ? 'text-emerald-600 dark:text-emerald-400' : 'text-rose-500 dark:text-rose-400' }}">
                                S/ {{ number_format((float) $pago->monto, 2) }}
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-paper-muted dark:text-ink-muted py-2">Todavía no hay pagos registrados.</p>
                    @endforelse
                </div>
            @endisset
        </div>

        @if (! isset($totalVentasMes) && ! isset($ingresosMes) && ! isset($cxcPendiente) && ! isset($cxpPendiente))
            <div class="rounded-2xl bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line p-6 text-sm text-paper-muted dark:text-ink-muted">
                Iniciaste sesión correctamente. Tu rol no tiene módulos con datos para mostrar aquí todavía.
            </div>
        @endif
    </div>
</x-app-layout>
