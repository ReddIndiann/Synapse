@props(['title' => null, 'description' => null, 'maxWidth' => '7xl'])

<x-app-layout>
    @if ($title)
        <x-slot name="header">
            <x-ui.page-header :title="$title" :description="$description">
                @isset($actions)
                    <x-slot name="actions">{{ $actions }}</x-slot>
                @endisset
            </x-ui.page-header>
        </x-slot>
    @endif

    <div class="ui-page-wrap">
        <div @class([
            'mx-auto px-4 sm:px-6 lg:px-8',
            'max-w-3xl' => $maxWidth === '3xl',
            'max-w-5xl' => $maxWidth === '5xl',
            'max-w-7xl' => $maxWidth === '7xl',
        ])>
            @if (session('status'))
                <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
            @endif

            {{ $slot }}
        </div>
    </div>
</x-app-layout>
