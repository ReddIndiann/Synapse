@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full h-11 px-4 border border-slate-200/85 bg-white/60 text-slate-800 rounded-xl shadow-sm placeholder:text-slate-400/80 transition-all duration-200 focus:bg-white focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 focus:outline-none']) }}>
