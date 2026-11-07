<button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2.5 bg-white dark:bg-ink-surface border border-paper-line dark:border-ink-line rounded-xl font-semibold text-xs uppercase tracking-widest shadow-sm hover:bg-paper dark:hover:bg-white/5 focus:outline-none focus:ring-2 focus:ring-brand-violet focus:ring-offset-2 dark:focus:ring-offset-ink-surface disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button>
