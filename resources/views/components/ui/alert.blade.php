@props(['variant' => 'info', 'dismissible' => false])

@php
    $variants = [
        'info' => [
            'class' => 'bg-indigo-50/80 border-indigo-100 text-indigo-900',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
        'success' => [
            'class' => 'bg-emerald-50/80 border-emerald-100 text-emerald-900',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
        'warning' => [
            'class' => 'bg-amber-50/80 border-amber-100 text-amber-900',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />',
        ],
        'danger' => [
            'class' => 'bg-rose-50/80 border-rose-100 text-rose-900',
            'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />',
        ],
    ];

    $current = $variants[$variant] ?? $variants['info'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border backdrop-blur-md px-4 py-3 text-sm flex items-center gap-3 shadow-[0_4px_12px_rgba(0,0,0,0.01)] transition-all duration-300 ' . $current['class']]) }} role="alert">
    <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
        {!! $current['icon'] !!}
    </svg>
    <div class="flex-1 font-medium">
        {{ $slot }}
    </div>
</div>
