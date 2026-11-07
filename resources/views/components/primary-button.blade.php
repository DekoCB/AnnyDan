<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2.5 bg-brand-gradient border border-transparent rounded-xl font-semibold text-xs text-white uppercase tracking-widest shadow-sm shadow-brand-violet/30 hover:opacity-90 focus:outline-none focus:ring-2 focus:ring-brand-violet focus:ring-offset-2 dark:focus:ring-offset-ink-surface active:opacity-80 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
