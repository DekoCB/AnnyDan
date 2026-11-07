<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
            Editar cliente
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-ink-surface overflow-hidden shadow-sm sm:rounded-lg p-6">
                <form id="cliente-form" method="POST" action="{{ route('clientes.update', $cliente) }}" novalidate>
                    @csrf
                    @method('PUT')
                    @include('clientes._form')

                    <div class="flex items-center justify-end gap-4 mt-6">
                        <a href="{{ route('clientes.show', $cliente) }}" class="text-sm text-paper-muted dark:text-ink-muted underline">
                            Cancelar
                        </a>
                        <x-primary-button>Guardar cambios</x-primary-button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
