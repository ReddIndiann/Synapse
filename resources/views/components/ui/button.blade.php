@props([
    'href' => null,
    'variant' => 'primary',
    'size' => 'md',
    'type' => 'button',
])

@php
    $base = 'inline-flex items-center justify-center font-semibold uppercase tracking-widest transition duration-150 focus:outline-none focus:ring-2 focus:ring-offset-2 disabled:opacity-50';

    $variants = [
        'primary' => 'bg-indigo-600 border border-indigo-600 text-white shadow-sm hover:bg-indigo-700 hover:border-indigo-700 focus:ring-indigo-500',
        'secondary' => 'bg-white border border-slate-300 text-slate-700 shadow-sm hover:bg-slate-50 focus:ring-indigo-500',
        'danger' => 'bg-red-600 border border-red-600 text-white shadow-sm hover:bg-red-700 hover:border-red-700 focus:ring-red-500',
        'ghost' => 'bg-transparent border border-transparent text-indigo-600 hover:bg-indigo-50 focus:ring-indigo-500 normal-case tracking-normal font-medium',
        'link' => 'bg-transparent border-0 text-indigo-600 hover:text-indigo-800 normal-case tracking-normal font-medium p-0 shadow-none focus:ring-0',
    ];

    $sizes = [
        'sm' => 'px-3 py-2 text-[10px] rounded-lg',
        'md' => 'px-4 py-2.5 text-xs rounded-xl',
        'lg' => 'px-5 py-3 text-sm rounded-xl',
    ];

    $classes = trim($base.' '.($variants[$variant] ?? $variants['primary']).' '.($sizes[$size] ?? $sizes['md']));
@endphp

@if ($href)
    <a href="{{ $href }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </a>
@else
    <button type="{{ $type }}" {{ $attributes->merge(['class' => $classes]) }}>
        {{ $slot }}
    </button>
@endif
