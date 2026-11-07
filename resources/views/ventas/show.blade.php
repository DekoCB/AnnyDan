<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
                Venta {{ $venta->numero_operacion }}
            </h2>
            <div class="flex items-center gap-3">
                @if ($venta->estado === 'registrada' && ! $venta->comprobante)
                    @can('create', \App\Models\Comprobante::class)
                        <form method="POST" action="{{ route('comprobantes.store', $venta) }}">
                            @csrf
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2.5 bg-brand-gradient border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm shadow-brand-violet/30 hover:opacity-90 transition">
                                Emitir boleta
                            </button>
                        </form>
                    @endcan
                @endif
                @if ($venta->estado === 'registrada' && ! $venta->cuentaPorCobrar)
                    @can('create', \App\Models\CuentaPorCobrar::class)
                        <a href="{{ route('cuentas-cobrar.create', ['venta_id' => $venta->id]) }}"
                            class="inline-flex items-center px-4 py-2.5 bg-brand-gradient border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm shadow-brand-violet/30 hover:opacity-90 transition">
                            Generar cuenta por cobrar
                        </a>
                    @endcan
                @endif
                @can('anular', $venta)
                    @if ($venta->estado === 'registrada' && (! $venta->comprobante || $venta->comprobante->estado !== 'emitido') && (! $venta->cuentaPorCobrar || $venta->cuentaPorCobrar->estado === 'anulada'))
                        <form method="POST" action="{{ route('ventas.anular', $venta) }}"
                            data-confirm="¿Anular la venta {{ $venta->numero_operacion }}? Esta acción no se puede deshacer.">
                            @csrf
                            @method('PATCH')
                            <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-red-600 border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-red-700">
                                Anular venta
                            </button>
                        </form>
                    @endif
                @endcan
            </div>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/40 text-green-800 dark:text-green-200 text-sm rounded-md p-4">
                    {{ session('status') }}
                </div>
            @endif

            @if ($venta->estado === 'anulada')
                <div class="bg-red-50 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-sm rounded-md p-4">
                    Esta venta fue anulada y no representa una operación válida.
                </div>
            @endif

            <div class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Cliente</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $venta->cliente->nombre_razon_social }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Fecha</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $venta->fecha->format('d/m/Y H:i') }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Forma de pago</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ ucfirst(str_replace('_', ' / ', $venta->forma_pago)) }}</p>
                </div>
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <p class="text-xs uppercase text-paper-muted dark:text-ink-muted">Registrada por</p>
                    <p class="mt-1 font-semibold text-gray-900 dark:text-gray-100">{{ $venta->usuario->name }}</p>
                </div>
            </div>

            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg overflow-hidden">
                <table class="min-w-full divide-y divide-paper-line dark:divide-ink-line">
                    <thead class="bg-paper dark:bg-white/5">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Descripción</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Cantidad</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">P. unitario</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-paper-muted dark:text-ink-muted uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-paper-line dark:divide-ink-line">
                        @foreach ($venta->detalles as $detalle)
                            <tr>
                                <td class="px-4 py-3 text-sm text-gray-900 dark:text-gray-100">{{ $detalle->descripcion }}</td>
                                <td class="px-4 py-3 text-sm text-right text-paper-muted dark:text-ink-muted">{{ number_format((float) $detalle->cantidad, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-paper-muted dark:text-ink-muted">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                <td class="px-4 py-3 text-sm text-right text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="flex justify-end">
                <dl class="w-64 text-sm space-y-1 bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                    <div class="flex justify-between">
                        <dt class="text-paper-muted dark:text-ink-muted">Subtotal</dt>
                        <dd class="text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $venta->subtotal, 2) }}</dd>
                    </div>
                    <div class="flex justify-between">
                        <dt class="text-paper-muted dark:text-ink-muted">IGV</dt>
                        <dd class="text-gray-900 dark:text-gray-100">S/ {{ number_format((float) $venta->impuestos, 2) }}</dd>
                    </div>
                    <div class="flex justify-between font-semibold text-base border-t border-paper-line dark:border-ink-line pt-1">
                        <dt>Total</dt>
                        <dd>S/ {{ number_format((float) $venta->total, 2) }}</dd>
                    </div>
                </dl>
            </div>

            @if ($venta->comprobante)
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4 text-sm">
                    <span class="text-paper-muted dark:text-ink-muted">Comprobante:</span>
                    <a href="{{ route('comprobantes.show', $venta->comprobante) }}" class="font-semibold text-brand-violet dark:text-violet-300 hover:underline">
                        {{ $venta->comprobante->numero_completo }}
                    </a>
                </div>
            @endif

            @if ($venta->cuentaPorCobrar)
                <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4 text-sm">
                    <span class="text-paper-muted dark:text-ink-muted">Cuenta por cobrar:</span>
                    <a href="{{ route('cuentas-cobrar.show', $venta->cuentaPorCobrar) }}" class="font-semibold text-brand-violet dark:text-violet-300 hover:underline">
                        Ver detalle (saldo S/ {{ number_format((float) $venta->cuentaPorCobrar->saldo, 2) }})
                    </a>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
