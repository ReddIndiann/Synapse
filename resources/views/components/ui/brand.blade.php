@props(['size' => 'md'])

@php
    $sizes = [
        'sm' => 'w-8 h-8 text-sm',
        'md' => 'w-9 h-9 text-base',
        'lg' => 'w-12 h-12 text-lg',
    ];
@endphp

<a {{ $attributes->merge(['href' => url('/')]) }} class="inline-flex items-center gap-3 group">
    <div class="{{ $sizes[$size] ?? $sizes['md'] }} rounded-xl bg-indigo-600 text-white flex items-center justify-center font-bold shadow-sm group-hover:bg-indigo-700 transition">
        S
    </div>
    <span class="font-semibold text-slate-800 tracking-tight">Synapse</span>
</a>
