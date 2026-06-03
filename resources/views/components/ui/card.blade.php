@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'ui-surface overflow-hidden'.($padding ? ' p-6' : '')]) }}>
    {{ $slot }}
</div>
