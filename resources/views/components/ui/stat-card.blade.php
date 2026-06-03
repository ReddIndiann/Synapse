@props(['label', 'value', 'hint' => null])

<div {{ $attributes->merge(['class' => 'ui-surface p-5']) }}>
    <p class="text-xs font-semibold uppercase tracking-widest text-slate-500">{{ $label }}</p>
    <p class="mt-2 text-2xl font-bold text-slate-900">{{ $value }}</p>
    @if ($hint)
        <p class="mt-1 text-sm text-slate-500">{{ $hint }}</p>
    @endif
</div>
