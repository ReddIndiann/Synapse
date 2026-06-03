<x-ui.page title="Media Library" description="Centralized digital assets for distribution.">
    <x-slot name="actions">
        <x-ui.button :href="route('distribution.media.create')" variant="primary" size="sm">Upload Media</x-ui.button>
    </x-slot>

    @if ($assets->isEmpty())
        <x-ui.empty-state title="No media uploaded" description="Upload images, videos, or documents to publish later.">
            <x-slot name="action">
                <x-ui.button :href="route('distribution.media.create')" variant="primary" size="sm">Upload first file</x-ui.button>
            </x-slot>
        </x-ui.empty-state>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach ($assets as $asset)
                <x-ui.card>
                    <div class="flex items-start justify-between gap-2 mb-3">
                        <div>
                            <p class="font-semibold text-slate-900">{{ $asset->title }}</p>
                            <p class="text-xs text-slate-500 mt-1">{{ $asset->filename }}</p>
                        </div>
                        <x-ui.badge variant="{{ $asset->status === 'ready' ? 'success' : 'warning' }}">{{ $asset->status }}</x-ui.badge>
                    </div>
                    <p class="text-xs text-slate-500 mb-4">{{ number_format($asset->size / 1024, 1) }} KB · {{ $asset->created_at->format('M j, Y') }}</p>
                    <div class="flex gap-2">
                        <x-ui.button :href="route('distribution.media.edit', $asset)" variant="secondary" size="sm">Edit</x-ui.button>
                        <form method="POST" action="{{ route('distribution.media.destroy', $asset) }}">@csrf @method('DELETE')
                            <x-ui.button type="submit" variant="danger" size="sm" onclick="return confirm('Delete file?')">Delete</x-ui.button>
                        </form>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-6">{{ $assets->links() }}</div>
    @endif
</x-ui.page>
