<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
            Nueva cuenta por pagar
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-ink-surface overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form method="POST" action="{{ route('cuentas-pagar.store') }}" novalidate>
                    @csrf

                    <div>
                        <x-input-label for="proveedor_id" value="Proveedor" />
                        <select id="proveedor_id" name="proveedor_id" required
                            class="mt-1 block w-full border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] focus:border-brand-violet focus:ring-brand-violet rounded-md shadow-sm">
                            <option value="" disabled selected>Seleccione un proveedor…</option>
                            @foreach ($proveedores as $proveedor)
                                <option value="{{ $proveedor->id }}" @selected(old('proveedor_id') == $proveedor->id)>{{ $proveedor->nombre_razon_social }}</option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('proveedor_id')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="documento_origen" value="Documento de origen (factura, guía, etc.)" />
                        <x-text-input id="documento_origen" name="documento_origen" type="text" class="mt-1 block w-full"
                            :value="old('documento_origen')" maxlength="50" />
                        <x-input-error :messages="$errors->get('documento_origen')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="monto_total" value="Monto total" />
                        <x-text-input id="monto_total" name="monto_total" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                            :value="old('monto_total')" required />
                        <x-input-error :messages="$errors->get('monto_total')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="fecha_vencimiento" value="Fecha de vencimiento" />
                        <x-text-input id="fecha_vencimiento" name="fecha_vencimiento" type="date" class="mt-1 block w-full"
                            :value="old('fecha_vencimiento', now()->addDays(30)->toDateString())" required />
                        <x-input-error :messages="$errors->get('fecha_vencimiento')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6">
                        <a href="{{ route('cuentas-pagar.index') }}" class="text-sm text-paper-muted dark:text-ink-muted underline">
                            Cancelar
                        </a>
                        <x-primary-button>Registrar cuenta por pagar</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
