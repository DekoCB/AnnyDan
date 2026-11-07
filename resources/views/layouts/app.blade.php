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
    <body class="font-sans antialiased bg-paper text-[#1B1B24] dark:bg-ink dark:text-[#F2F1F7]"
        x-data="{
            sidebarOpen: false,
            sidebarCollapsed: (function () {
                try {
                    return localStorage.getItem('annydan-sidebar-collapsed') === 'true';
                } catch (e) {
                    return false;
                }
            })(),
            toggleSidebarCollapsed() {
                this.sidebarCollapsed = !this.sidebarCollapsed;
                try {
                    localStorage.setItem('annydan-sidebar-collapsed', this.sidebarCollapsed);
                } catch (e) {}
            },
        }">
        <div class="flex min-h-screen">
            @include('layouts.navigation')

            <div class="flex-1 flex flex-col min-w-0">
                @include('layouts.topbar')

                @isset($header)
                    <div class="px-4 sm:px-6 lg:px-8 pt-8">
                        {{ $header }}
                    </div>
                @endisset

                <main class="flex-1">
                    {{ $slot }}
                </main>
            </div>
        </div>
    </body>
</html>
