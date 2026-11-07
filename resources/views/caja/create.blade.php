<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
            Registrar movimiento de caja
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-2xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-ink-surface overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form id="movimiento-caja-form" method="POST" action="{{ route('caja.store') }}" novalidate>
                    @csrf

                    <div>
                        <x-input-label for="tipo" value="Tipo de movimiento" />
                        <select id="tipo" name="tipo" required
                            class="mt-1 block w-full border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] focus:border-brand-violet focus:ring-brand-violet rounded-md shadow-sm">
                            <option value="ingreso" @selected(old('tipo') === 'ingreso')>Ingreso</option>
                            <option value="egreso" @selected(old('tipo', 'egreso') === 'egreso')>Egreso</option>
                        </select>
                        <x-input-error :messages="$errors->get('tipo')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="categoria_id" value="Categoría" />
                        <select id="categoria_id" name="categoria_id" required
                            class="mt-1 block w-full border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] focus:border-brand-violet focus:ring-brand-violet rounded-md shadow-sm">
                            @foreach ($categorias as $categoria)
                                <option value="{{ $categoria->id }}" data-tipo="{{ $categoria->tipo }}" @selected(old('categoria_id') == $categoria->id)>
                                    {{ $categoria->nombre }}
                                </option>
                            @endforeach
                        </select>
                        <x-input-error :messages="$errors->get('categoria_id')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="monto" value="Monto" />
                        <x-text-input id="monto" name="monto" type="number" step="0.01" min="0.01" class="mt-1 block w-full"
                            :value="old('monto')" required />
                        <x-input-error :messages="$errors->get('monto')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="fecha" value="Fecha" />
                        <x-text-input id="fecha" name="fecha" type="date" class="mt-1 block w-full"
                            :value="old('fecha', now()->toDateString())" required />
                        <x-input-error :messages="$errors->get('fecha')" class="mt-2" />
                    </div>

                    <div class="mt-4">
                        <x-input-label for="descripcion" value="Descripción (opcional)" />
                        <x-text-input id="descripcion" name="descripcion" type="text" class="mt-1 block w-full"
                            :value="old('descripcion')" maxlength="255" />
                        <x-input-error :messages="$errors->get('descripcion')" class="mt-2" />
                    </div>

                    <div class="flex items-center justify-end gap-4 mt-6">
                        <a href="{{ route('caja.index') }}" class="text-sm text-paper-muted dark:text-ink-muted underline">
                            Cancelar
                        </a>
                        <x-primary-button>Registrar movimiento</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
