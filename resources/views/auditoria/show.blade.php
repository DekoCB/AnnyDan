@php
    $enlace = \App\Http\Controllers\AuditoriaController::enlaceParaAuditable($auditoria);
@endphp

<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-[#1B1B24] dark:text-[#F2F1F7] leading-tight">
            Detalle de auditoría
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-6">
                <dl class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-sm">
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Fecha / hora</dt>
                        <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $auditoria->created_at->format('d/m/Y H:i:s') }}</dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Usuario</dt>
                        <dd class="font-semibold text-gray-900 dark:text-gray-100">{{ $auditoria->usuario?->name ?? 'Sistema' }}</dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Acción</dt>
                        <dd class="font-mono text-gray-900 dark:text-gray-100">{{ $auditoria->accion }}</dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Registro afectado</dt>
                        <dd class="text-gray-900 dark:text-gray-100">
                            {{ $etiquetas[$auditoria->auditable_type] ?? '—' }}
                            @if ($auditoria->auditable_id)
                                #{{ $auditoria->auditable_id }}
                            @endif
                            @if ($enlace)
                                · <a href="{{ $enlace }}" class="text-brand-violet dark:text-violet-300 hover:underline">Ver registro</a>
                            @endif
                        </dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Dirección IP</dt>
                        <dd class="text-gray-900 dark:text-gray-100">{{ $auditoria->ip_address ?? '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-paper-muted dark:text-ink-muted">Navegador / agente</dt>
                        <dd class="text-gray-900 dark:text-gray-100 truncate" title="{{ $auditoria->user_agent }}">{{ $auditoria->user_agent ?? '—' }}</dd>
                    </div>
                </dl>
            </div>

            @if ($auditoria->valores_antes || $auditoria->valores_despues)
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                    <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase mb-2">Valores antes</h3>
                        @if ($auditoria->valores_antes)
                            <pre class="text-xs bg-paper dark:bg-white/5 rounded p-3 overflow-x-auto text-paper-muted dark:text-ink-muted">{{ json_encode($auditoria->valores_antes, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                            <p class="text-sm text-paper-muted dark:text-ink-muted">No aplica (registro nuevo).</p>
                        @endif
                    </div>
                    <div class="bg-white dark:bg-ink-surface shadow-sm rounded-lg p-4">
                        <h3 class="text-sm font-semibold text-paper-muted dark:text-ink-muted uppercase mb-2">Valores después</h3>
                        @if ($auditoria->valores_despues)
                            <pre class="text-xs bg-paper dark:bg-white/5 rounded p-3 overflow-x-auto text-paper-muted dark:text-ink-muted">{{ json_encode($auditoria->valores_despues, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
                        @else
                            <p class="text-sm text-paper-muted dark:text-ink-muted">—</p>
                        @endif
                    </div>
                </div>
            @endif

            <a href="{{ route('auditoria.index') }}" class="text-sm text-paper-muted dark:text-ink-muted underline">
                ← Volver al historial
            </a>
        </div>
    </div>
</x-app-layout>
