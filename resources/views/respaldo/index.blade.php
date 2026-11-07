<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
            Respaldo
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-6 space-y-4 text-[#1B1B24] dark:text-[#F2F1F7]">
                <p>
                    Todos los datos del sistema se guardan en esta computadora. Descarga una copia de seguridad
                    con regularidad y guárdala fuera de este equipo (un USB o un disco externo).
                </p>

                @if ($disponible)
                    <a href="{{ route('respaldo.exportar') }}">
                        <x-primary-button type="button">Descargar copia de seguridad</x-primary-button>
                    </a>
                @else
                    <p class="text-sm">La copia de seguridad solo está disponible en la aplicación de escritorio.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>
