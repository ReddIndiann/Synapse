@props(['padding' => true])

<div {{ $attributes->merge(['class' => 'bg-white/80 backdrop-blur-md border border-white/60 shadow-[0_8px_30px_rgb(0,0,0,0.04)] rounded-3xl transition-all duration-300 hover:shadow-[0_12px_40px_rgba(99,102,241,0.05)] hover:-translate-y-0.5'.($padding ? ' p-6 sm:p-7' : '')]) }}>
    {{ $slot }}
</div>
