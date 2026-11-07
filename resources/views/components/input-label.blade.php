@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-medium text-sm text-paper-muted dark:text-ink-muted']) }}>
    {{ $value ?? $slot }}
</label>
