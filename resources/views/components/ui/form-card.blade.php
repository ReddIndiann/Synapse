@props(['title' => null, 'description' => null])

<x-ui.card>
    @if ($title)
        <div class="mb-6">
            <h2 class="text-lg font-semibold text-slate-900">{{ $title }}</h2>
            @if ($description)
                <p class="mt-1 text-sm text-slate-500">{{ $description }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</x-ui.card>
