<x-ui.card :padding="false">
    <div class="p-6 overflow-x-auto">
        <table {{ $attributes->merge(['class' => 'ui-table']) }}>
            {{ $slot }}
        </table>
    </div>
    @isset($footer)
        <div class="px-6 pb-6">{{ $footer }}</div>
    @endisset
</x-ui.card>
