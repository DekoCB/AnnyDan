@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'border-paper-line dark:border-ink-line dark:bg-white/5 dark:text-[#F2F1F7] focus:border-brand-violet dark:focus:border-brand-violet focus:ring-brand-violet dark:focus:ring-brand-violet rounded-xl shadow-sm']) }}>
