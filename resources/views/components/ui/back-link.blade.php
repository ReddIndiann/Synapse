@props(['href', 'label' => 'Back'])

<div {{ $attributes->merge(['class' => 'mb-4']) }}>
    <x-ui.button :href="$href" variant="secondary" size="sm">
        &larr; {{ $label }}
    </x-ui.button>
</div>
