@php
    $navItems = [
        ['route' => 'dashboard', 'match' => 'dashboard', 'label' => 'Dashboard', 'icon' => 'dashboard', 'can' => null],
        ['route' => 'clientes.index', 'match' => 'clientes.*', 'label' => 'Clientes', 'icon' => 'clientes', 'can' => ['viewAny', \App\Models\Cliente::class]],
        ['route' => 'proveedores.index', 'match' => 'proveedores.*', 'label' => 'Proveedores', 'icon' => 'proveedores', 'can' => ['viewAny', \App\Models\Proveedor::class]],
        ['route' => 'ventas.index', 'match' => 'ventas.*', 'label' => 'Ventas', 'icon' => 'ventas', 'can' => ['viewAny', \App\Models\Venta::class]],
        ['route' => 'cuentas-cobrar.index', 'match' => 'cuentas-cobrar.*', 'label' => 'Cuentas por cobrar', 'icon' => 'cobrar', 'can' => ['viewAny', \App\Models\CuentaPorCobrar::class]],
        ['route' => 'cuentas-pagar.index', 'match' => 'cuentas-pagar.*', 'label' => 'Cuentas por pagar', 'icon' => 'pagar', 'can' => ['viewAny', \App\Models\CuentaPorPagar::class]],
        ['route' => 'caja.index', 'match' => 'caja.*', 'label' => 'Caja', 'icon' => 'caja', 'can' => ['viewAny', \App\Models\MovimientoCaja::class]],
        ['route' => 'reportes.ventas', 'match' => 'reportes.*', 'label' => 'Reportes', 'icon' => 'reportes', 'can' => ['reportes.ver']],
        ['route' => 'comprobantes.index', 'match' => 'comprobantes.*', 'label' => 'Boletas', 'icon' => 'boletas', 'can' => ['viewAny', \App\Models\Comprobante::class]],
        ['route' => 'auditoria.index', 'match' => 'auditoria.*', 'label' => 'Auditoría', 'icon' => 'auditoria', 'can' => ['auditoria.ver']],
    ];
@endphp

<!-- Overlay móvil -->
<div x-show="sidebarOpen" x-cloak @click="sidebarOpen = false"
    class="fixed inset-0 z-30 bg-black/40 lg:hidden" x-transition.opacity></div>

<aside
    class="fixed inset-y-0 left-0 z-40 w-64 shrink-0 flex flex-col bg-white dark:bg-ink-surface border-r border-paper-line dark:border-ink-line transition-[transform,width] duration-200 ease-out lg:translate-x-0 lg:relative lg:duration-300 lg:ease-in-out"
    :class="[sidebarOpen ? 'translate-x-0' : '-translate-x-full', sidebarCollapsed ? 'lg:w-[76px]' : 'lg:w-64']"
>
    <button type="button" @click="toggleSidebarCollapsed()"
        class="hidden lg:flex absolute -right-3 top-[26px] z-50 w-6 h-6 items-center justify-center rounded-full bg-brand-gradient text-white shadow-md shadow-brand-violet/40 ring-2 ring-paper dark:ring-ink hover:opacity-90 transition-opacity"
        :aria-label="sidebarCollapsed ? 'Expandir menú' : 'Colapsar menú'" :aria-expanded="(!sidebarCollapsed).toString()">
        <svg class="w-3.5 h-3.5 transition-transform duration-300 ease-in-out" :class="sidebarCollapsed && 'rotate-180'"
            viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">
            <path d="M15 6l-6 6 6 6" />
        </svg>
    </button>

    <div class="h-16 flex items-center gap-2.5 px-6 shrink-0 overflow-hidden" :class="sidebarCollapsed && 'lg:px-[26px]'">
        <span class="w-8 h-8 rounded-lg bg-brand-gradient shrink-0"></span>
        <span class="font-display font-semibold text-[15px] tracking-tight leading-none whitespace-nowrap overflow-hidden max-w-[180px] opacity-100 transition-[max-width,opacity,margin] duration-300 ease-in-out"
            :class="sidebarCollapsed && 'lg:max-w-0 lg:opacity-0 lg:-ml-2.5'">
            AnnyDan
            <span class="block text-[10px] font-sans font-medium tracking-[0.14em] text-paper-muted dark:text-ink-muted mt-0.5">GESTIÓN ADMINISTRATIVA</span>
        </span>
    </div>

    <nav class="flex-1 overflow-y-auto overflow-x-hidden px-3 py-2 space-y-0.5">
        @foreach ($navItems as $item)
            @php
                $isActive = request()->routeIs($item['match']);
                $show = is_null($item['can']) || app(\Illuminate\Contracts\Auth\Access\Gate::class)->allows(...$item['can']);
            @endphp
            @if ($show)
                <a href="{{ route($item['route']) }}" title="{{ $item['label'] }}"
                    class="group flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition-colors duration-150
                        {{ $isActive
                            ? 'bg-brand-gradient text-white shadow-sm shadow-brand-violet/30'
                            : 'text-paper-muted dark:text-ink-muted hover:bg-paper dark:hover:bg-white/5 hover:text-[#1B1B24] dark:hover:text-[#F2F1F7]' }}">
                    <x-nav-icon :name="$item['icon']" class="w-5 h-5 shrink-0 {{ $isActive ? 'text-white' : '' }}" />
                    <span class="truncate overflow-hidden whitespace-nowrap max-w-[160px] opacity-100 transition-[max-width,opacity] duration-200 ease-in-out"
                        :class="sidebarCollapsed && 'lg:max-w-0 lg:opacity-0'">{{ $item['label'] }}</span>
                </a>
            @endif
        @endforeach
    </nav>

    <div class="p-3 border-t border-paper-line dark:border-ink-line">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" title="Cerrar sesión"
                class="w-full flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-paper-muted dark:text-ink-muted hover:bg-paper dark:hover:bg-white/5 hover:text-[#1B1B24] dark:hover:text-[#F2F1F7] transition-colors duration-150">
                <x-nav-icon name="logout" class="w-5 h-5 shrink-0" />
                <span class="truncate overflow-hidden whitespace-nowrap max-w-[160px] opacity-100 transition-[max-width,opacity] duration-200 ease-in-out"
                    :class="sidebarCollapsed && 'lg:max-w-0 lg:opacity-0'">Cerrar sesión</span>
            </button>
        </form>
    </div>
</aside>
