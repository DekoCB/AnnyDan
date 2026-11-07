@props(['name'])

@php
    $paths = [
        'dashboard' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'clientes' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87m6-8.13a4 4 0 110 8 4 4 0 010-8zm6 3a4 4 0 11-8 0 4 4 0 018 0z',
        'proveedores' => 'M3 9.5L12 4l9 5.5M4.5 10.5V19a1 1 0 001 1H9v-5a1 1 0 011-1h4a1 1 0 011 1v5h3.5a1 1 0 001-1v-8.5',
        'ventas' => 'M3 3h1.5l1.6 9.6a2 2 0 002 1.9h8.4a2 2 0 002-1.8L20 7H6',
        'cobrar' => 'M12 19V5m0 0l-5 5m5-5l5 5',
        'pagar' => 'M12 5v14m0 0l-5-5m5 5l5-5',
        'caja' => 'M3 7h18M3 7v11a1 1 0 001 1h16a1 1 0 001-1V7M3 7l2-3h14l2 3M9 11h6',
        'reportes' => 'M4 19V9m6 10V5m6 14v-6',
        'boletas' => 'M5 3h11l3 3v15l-3-2-3 2-3-2-3 2-2-2-3 2V6l3-3zM8 8h6m-6 4h6m-6 4h3',
        'auditoria' => 'M12 3l7 3v5c0 4.5-3 8-7 10-4-2-7-5.5-7-10V6l7-3zM9.5 12l1.8 1.8L14.5 10',
        'logout' => 'M9 5H6a1 1 0 00-1 1v12a1 1 0 001 1h3m4-14l5 5-5 5m5-5H9',
    ];
@endphp

<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" {{ $attributes->merge(['class' => 'w-5 h-5']) }}>
    <path d="{{ $paths[$name] ?? $paths['dashboard'] }}" />
</svg>
