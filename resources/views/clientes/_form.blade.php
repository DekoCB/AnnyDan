@php
    /** @var \App\Models\Cliente|null $cliente */
    $cliente ??= null;
@endphp

<div>
    <x-input-label for="tipo_documento" value="Tipo de documento" />
    <select id="tipo_documento" name="tipo_documento" required
        class="mt-1 block w-full border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] focus:border-brand-violet focus:ring-brand-violet rounded-md shadow-sm">
        @foreach (['DNI', 'RUC', 'CE', 'PASAPORTE'] as $tipo)
            <option value="{{ $tipo }}" @selected(old('tipo_documento', $cliente?->tipo_documento) === $tipo)>{{ $tipo }}</option>
        @endforeach
    </select>
    <x-input-error :messages="$errors->get('tipo_documento')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="numero_documento" value="Número de documento" />
    <x-text-input id="numero_documento" name="numero_documento" type="text" class="mt-1 block w-full"
        :value="old('numero_documento', $cliente?->numero_documento)" required autofocus maxlength="20" />
    <x-input-error :messages="$errors->get('numero_documento')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="nombre_razon_social" value="Nombre / Razón social" />
    <x-text-input id="nombre_razon_social" name="nombre_razon_social" type="text" class="mt-1 block w-full"
        :value="old('nombre_razon_social', $cliente?->nombre_razon_social)" required maxlength="150" />
    <x-input-error :messages="$errors->get('nombre_razon_social')" class="mt-2" />
</div>

<div class="mt-4">
    <x-input-label for="direccion" value="Dirección" />
    <x-text-input id="direccion" name="direccion" type="text" class="mt-1 block w-full"
        :value="old('direccion', $cliente?->direccion)" maxlength="255" />
    <x-input-error :messages="$errors->get('direccion')" class="mt-2" />
</div>

<div class="mt-4 grid grid-cols-1 sm:grid-cols-2 gap-4">
    <div>
        <x-input-label for="telefono" value="Teléfono" />
        <x-text-input id="telefono" name="telefono" type="text" class="mt-1 block w-full"
            :value="old('telefono', $cliente?->telefono)" maxlength="20" />
        <x-input-error :messages="$errors->get('telefono')" class="mt-2" />
    </div>

    <div>
        <x-input-label for="email" value="Correo electrónico" />
        <x-text-input id="email" name="email" type="email" class="mt-1 block w-full"
            :value="old('email', $cliente?->email)" maxlength="150" />
        <x-input-error :messages="$errors->get('email')" class="mt-2" />
    </div>
</div>
