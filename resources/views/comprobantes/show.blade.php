<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between print:hidden">
            <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
                Boleta {{ $comprobante->numero_completo }}
            </h2>
            <div class="flex items-center gap-3">
                <button type="button" onclick="window.print()"
                    class="inline-flex items-center px-4 py-2.5 bg-brand-gradient border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm shadow-brand-violet/30 hover:opacity-90 transition">
                    Imprimir
                </button>
                @can('anular', $comprobante)
                    @if ($comprobante->estado === 'emitido')
                        <form method="POST" action="{{ route('comprobantes.anular', $comprobante) }}"
                            data-confirm="¿Anular la boleta {{ $comprobante->numero_completo }}? Esta acción no se puede deshacer.">
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
        </div>
    </x-slot>

    <div class="py-12 print:py-0">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8 print:max-w-full print:px-0">
            @if (session('status'))
                <div class="bg-green-50 dark:bg-green-900/40 text-green-800 dark:text-green-200 text-sm rounded-md p-4 mb-6 print:hidden">
                    {{ session('status') }}
                </div>
            @endif

            <div class="bg-white dark:bg-ink-surface print:bg-white shadow-sm print:shadow-none rounded-lg print:rounded-none p-8 border border-paper-line dark:border-ink-line print:border-gray-900">
                @if ($comprobante->estado === 'anulado')
                    <div class="bg-red-50 dark:bg-red-900/40 text-red-800 dark:text-red-200 text-sm rounded-md p-4 mb-6 text-center font-semibold print:border print:border-red-600">
                        ANULADA
                    </div>
                @endif

                <div class="flex items-start justify-between border-b border-paper-line dark:border-ink-line print:border-gray-900 pb-4 mb-4">
                    <div>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100 print:text-black">{{ config('app.name') }}</p>
                        <p class="text-sm text-paper-muted dark:text-ink-muted print:text-black">Boleta de venta</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-paper-muted dark:text-ink-muted print:text-black">N° de boleta</p>
                        <p class="text-lg font-bold text-gray-900 dark:text-gray-100 print:text-black">{{ $comprobante->numero_completo }}</p>
                        <p class="text-sm text-paper-muted dark:text-ink-muted print:text-black">{{ $comprobante->fecha_emision->format('d/m/Y') }}</p>
                    </div>
                </div>

                <div class="mb-4 text-sm">
                    <p class="text-paper-muted dark:text-ink-muted print:text-black">Cliente</p>
                    <p class="font-semibold text-gray-900 dark:text-gray-100 print:text-black">{{ $comprobante->venta->cliente->nombre_razon_social }}</p>
                    <p class="text-paper-muted dark:text-ink-muted print:text-black">{{ $comprobante->venta->cliente->tipo_documento }} {{ $comprobante->venta->cliente->numero_documento }}</p>
                </div>

                <table class="min-w-full text-sm mb-4">
                    <thead>
                        <tr class="border-b border-paper-line dark:border-ink-line print:border-gray-900">
                            <th class="py-2 text-left text-xs font-medium text-paper-muted dark:text-ink-muted print:text-black uppercase">Descripción</th>
                            <th class="py-2 text-right text-xs font-medium text-paper-muted dark:text-ink-muted print:text-black uppercase">Cant.</th>
                            <th class="py-2 text-right text-xs font-medium text-paper-muted dark:text-ink-muted print:text-black uppercase">P. Unit.</th>
                            <th class="py-2 text-right text-xs font-medium text-paper-muted dark:text-ink-muted print:text-black uppercase">Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($comprobante->venta->detalles as $detalle)
                            <tr class="border-b border-paper-line dark:border-ink-line print:border-gray-300">
                                <td class="py-2 text-gray-900 dark:text-gray-100 print:text-black">{{ $detalle->descripcion }}</td>
                                <td class="py-2 text-right text-paper-muted dark:text-ink-muted print:text-black">{{ number_format((float) $detalle->cantidad, 2) }}</td>
                                <td class="py-2 text-right text-paper-muted dark:text-ink-muted print:text-black">S/ {{ number_format((float) $detalle->precio_unitario, 2) }}</td>
                                <td class="py-2 text-right text-gray-900 dark:text-gray-100 print:text-black">S/ {{ number_format((float) $detalle->subtotal, 2) }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>

                <div class="flex justify-end mb-4">
                    <dl class="w-56 text-sm space-y-1">
                        <div class="flex justify-between">
                            <dt class="text-paper-muted dark:text-ink-muted print:text-black">Subtotal</dt>
                            <dd class="text-gray-900 dark:text-gray-100 print:text-black">S/ {{ number_format((float) $comprobante->venta->subtotal, 2) }}</dd>
                        </div>
                        <div class="flex justify-between">
                            <dt class="text-paper-muted dark:text-ink-muted print:text-black">IGV</dt>
                            <dd class="text-gray-900 dark:text-gray-100 print:text-black">S/ {{ number_format((float) $comprobante->venta->impuestos, 2) }}</dd>
                        </div>
                        <div class="flex justify-between font-bold text-base border-t border-paper-line dark:border-ink-line print:border-gray-900 pt-1">
                            <dt class="print:text-black">Total</dt>
                            <dd class="print:text-black">S/ {{ number_format((float) $comprobante->venta->total, 2) }}</dd>
                        </div>
                    </dl>
                </div>

                <p class="text-xs text-paper-muted dark:text-ink-muted print:text-black border-t border-paper-line dark:border-ink-line print:border-gray-900 pt-3">
                    Forma de pago: {{ ucfirst(str_replace('_', ' / ', $comprobante->venta->forma_pago)) }} ·
                    Venta {{ $comprobante->venta->numero_operacion }}
                </p>
            </div>
        </div>
    </div>
</x-app-layout>
