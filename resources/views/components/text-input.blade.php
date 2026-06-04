@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full h-11 px-4 text-slate-800 dark:text-slate-100 rounded-xl shadow-sm placeholder:text-slate-400/70 transition-all duration-200 focus:bg-white dark:focus:bg-slate-950 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none']) }}
       style="background-color: var(--surface); border: 1px solid var(--border);">
