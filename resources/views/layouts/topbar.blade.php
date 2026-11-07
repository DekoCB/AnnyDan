<header class="h-16 shrink-0 flex items-center gap-3 px-4 sm:px-6 lg:px-8 border-b border-paper-line dark:border-ink-line bg-white/80 dark:bg-ink-surface/80 backdrop-blur">
    <button type="button" @click="sidebarOpen = !sidebarOpen"
        class="lg:hidden -ml-1 p-2 rounded-lg text-paper-muted dark:text-ink-muted hover:bg-paper dark:hover:bg-white/5">
        <span class="sr-only">Abrir menú</span>
        <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
    </button>

    <div class="flex-1"></div>

    <button type="button" id="theme-toggle" aria-label="Cambiar tema"
        class="p-2 rounded-lg text-paper-muted dark:text-ink-muted hover:bg-paper dark:hover:bg-white/5 transition">
        <svg class="w-5 h-5 dark:hidden" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 12.8A9 9 0 1111.2 3 7 7 0 0021 12.8z"/></svg>
        <svg class="w-5 h-5 hidden dark:block" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.9 4.9l1.4 1.4m11.4 11.4l1.4 1.4M2 12h2m16 0h2M4.9 19.1l1.4-1.4m11.4-11.4l1.4-1.4"/></svg>
    </button>

    @if (isset($cuentasVencidasCount) && $cuentasVencidasCount > 0)
        <a href="{{ Auth::user()->can('viewAny', \App\Models\CuentaPorCobrar::class) ? route('cuentas-cobrar.index', ['estado' => 'vencida']) : route('cuentas-pagar.index', ['estado' => 'vencida']) }}"
            class="relative p-2 rounded-lg text-paper-muted dark:text-ink-muted hover:bg-paper dark:hover:bg-white/5 transition" title="{{ $cuentasVencidasCount }} cuenta(s) vencida(s)">
            <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M12 3a6 6 0 00-6 6v3.6L4.3 15a1 1 0 00.7 1.7h14a1 1 0 00.7-1.7L18 12.6V9a6 6 0 00-6-6z"/><path d="M9.5 19a2.5 2.5 0 005 0"/></svg>
            <span class="absolute -top-0.5 -right-0.5 min-w-[16px] h-4 px-1 rounded-full bg-brand-gradient text-white text-[10px] font-semibold leading-4 text-center">{{ $cuentasVencidasCount }}</span>
        </a>
    @endif

    <x-dropdown align="right" width="48">
        <x-slot name="trigger">
            <button class="flex items-center gap-2.5 pl-1 pr-2 py-1 rounded-full hover:bg-paper dark:hover:bg-white/5 transition">
                <span class="w-8 h-8 rounded-full bg-brand-gradient flex items-center justify-center text-white text-xs font-semibold shrink-0">
                    {{ strtoupper(substr(Auth::user()->name, 0, 1)) }}
                </span>
                <span class="hidden sm:block text-sm font-medium">{{ Auth::user()->name }}</span>
                <svg class="hidden sm:block w-3.5 h-3.5 text-paper-muted dark:text-ink-muted" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" /></svg>
            </button>
        </x-slot>

        <x-slot name="content">
            <x-dropdown-link :href="route('profile.edit')">
                {{ __('Perfil') }}
            </x-dropdown-link>

            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <x-dropdown-link :href="route('logout')"
                        onclick="event.preventDefault(); this.closest('form').submit();">
                    {{ __('Cerrar sesión') }}
                </x-dropdown-link>
            </form>
        </x-slot>
    </x-dropdown>
</header>
