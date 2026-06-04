@props(['label', 'value', 'hint' => null])

@php
    $labelLower = strtolower($label);
    
    // Choose icon and color scheme based on label keywords
    if (str_contains($labelLower, 'task')) {
        $iconBg = 'bg-violet-100 text-violet-600 dark:bg-violet-500/10 dark:text-violet-400';
        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />';
    } elseif (str_contains($labelLower, 'income') || str_contains($labelLower, 'balance') || str_contains($labelLower, 'position')) {
        $iconBg = 'bg-emerald-100 text-emerald-600 dark:bg-emerald-500/10 dark:text-emerald-400';
        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
    } elseif (str_contains($labelLower, 'expense')) {
        $iconBg = 'bg-rose-100 text-rose-600 dark:bg-rose-500/10 dark:text-rose-400';
        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z" />';
    } elseif (str_contains($labelLower, 'media') || str_contains($labelLower, 'publish') || str_contains($labelLower, 'channel')) {
        $iconBg = 'bg-sky-100 text-sky-600 dark:bg-sky-500/10 dark:text-sky-400';
        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />';
    } else {
        $iconBg = 'bg-slate-100 text-slate-600 dark:bg-slate-500/10 dark:text-slate-400';
        $svgPath = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />';
    }
@endphp

<div {{ $attributes->merge(['class' => 'backdrop-blur-md shadow-[0_8px_30px_rgb(0,0,0,0.015)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] rounded-3xl p-6 transition-all duration-300 hover:shadow-[0_12px_40px_rgba(99,102,241,0.04)] hover:-translate-y-0.5 flex justify-between items-center']) }}
     style="background-color: var(--surface); border: 1px solid var(--border); color: var(--text);">
    <div class="space-y-1">
        <p class="text-xs font-semibold uppercase tracking-widest text-slate-400 dark:text-slate-500">{{ $label }}</p>
        <p class="text-2xl font-extrabold tracking-tight">{{ $value }}</p>
        @if ($hint)
            <p class="text-xs text-slate-400 dark:text-slate-500 font-medium">{{ $hint }}</p>
        @endif
    </div>
    <div class="w-12 h-12 rounded-2xl flex items-center justify-center shrink-0 shadow-inner {{ $iconBg }}">
        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            {!! $svgPath !!}
        </svg>
    </div>
</div>
