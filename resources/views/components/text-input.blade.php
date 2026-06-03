@props(['disabled' => false])

<input @disabled($disabled) {{ $attributes->merge(['class' => 'w-full border-slate-300 bg-white/90 text-slate-800 rounded-xl shadow-sm placeholder:text-slate-400 focus:border-indigo-500 focus:ring-indigo-500']) }}>
