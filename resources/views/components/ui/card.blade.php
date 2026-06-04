@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'backdrop-blur-md shadow-[0_8px_30px_rgb(0,0,0,0.015)] dark:shadow-[0_8px_30px_rgb(0,0,0,0.2)] rounded-3xl transition-all duration-300 hover:shadow-[0_12px_40px_rgba(99,102,241,0.04)] hover:-translate-y-0.5'.($padding ? ' p-6 sm:p-7' : '')]) }}
     style="background-color: var(--surface); border: 1px solid var(--border); color: var(--text);">
    {{ $slot }}
</div>
