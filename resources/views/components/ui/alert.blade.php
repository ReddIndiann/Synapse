@props(['variant' => 'info', 'dismissible' => false])

@php
    $variants = [
        'info' => 'bg-indigo-50 border-indigo-200 text-indigo-800',
        'success' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
        'warning' => 'bg-amber-50 border-amber-200 text-amber-800',
        'danger' => 'bg-red-50 border-red-200 text-red-800',
    ];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-xl border px-4 py-3 text-sm '.($variants[$variant] ?? $variants['info'])]) }} role="alert">
    {{ $slot }}
</div>
