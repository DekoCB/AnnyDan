<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'AnnyDan') }}</title>

        <!-- Aplica el tema guardado ANTES del primer pintado, para evitar
             un parpadeo claro→oscuro. Debe ser inline: el bundle de Vite
             carga demasiado tarde para esto. -->
        <script>
            (function () {
                try {
                    var tema = localStorage.getItem('annydan-theme');
                    if (tema !== 'light' && tema !== 'dark') {
                        tema = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
                    }
                    if (tema === 'dark') {
                        document.documentElement.classList.add('dark');
                    }
                } catch (e) {}
            })();
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Sora:wght@500;600;700&family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/ts/app.ts'])
    </head>
    <body class="font-sans antialiased bg-paper text-[#1B1B24] dark:bg-ink dark:text-[#F2F1F7]">
        <button type="button" id="theme-toggle" aria-label="Cambiar tema"
            class="fixed top-4 right-4 p-2 rounded-lg text-paper-muted dark:text-ink-muted hover:bg-white dark:hover:bg-white/5 transition">
            <svg class="w-5 h-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
            <svg class="w-5 h-5 hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4l1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4m11.4-11.4l1.4-1.4"/></svg>
        </button>

        <div class="min-h-screen flex flex-col sm:justify-center items-center pt-10 sm:pt-0 px-4">
            <a href="/" class="flex items-center gap-3">
                <span class="flex h-11 w-11 items-center justify-center rounded-xl bg-brand-gradient font-display font-bold text-white">A</span>
                <span class="font-display">
                    <span class="block text-base font-semibold leading-none">AnnyDan</span>
                    <span class="block text-[11px] font-medium uppercase tracking-widest text-paper-muted dark:text-ink-muted">Gestión administrativa</span>
                </span>
            </a>

            <div class="w-full sm:max-w-md mt-8 px-6 py-6 bg-white dark:bg-ink-surface shadow-sm border border-paper-line dark:border-ink-line overflow-hidden rounded-2xl">
                {{ $slot }}
            </div>
        </div>
    </body>
</html>
