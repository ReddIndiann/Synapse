@props(['title', 'description' => null])

<div {{ $attributes->merge(['class' => 'flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4']) }}>
    <div>
        <h1 class="ui-title text-xl sm:text-2xl">{{ $title }}</h1>
        @if ($description)
            <p class="mt-1 text-sm text-[var(--text-secondary)]">{{ $description }}</p>
        @endif
    </div>
    @isset($actions)
        <div class="flex items-center gap-3 shrink-0">
            {{ $actions }}
        </div>
    @endisset
</div>
