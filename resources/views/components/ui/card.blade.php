@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'ui-card'.($padding ? '' : ' !p-0')]) }}>
    {{ $slot }}
</div>
