<x-ui.page title="Media Library" description="Centralized digital assets for distribution.">
    <x-slot name="actions">
        <x-ui.button :href="route('distribution.media.create')" variant="primary" size="sm">Upload Media</x-ui.button>
    </x-slot>

    @if ($assets->isEmpty())
        <x-ui.empty-state title="No media uploaded" description="Upload audio tracks, podcast videos, or album art images to distribute later.">
            <x-slot name="action">
                <x-ui.button :href="route('distribution.media.create')" variant="primary" size="sm">Upload first file</x-ui.button>
            </x-slot>
        </x-ui.empty-state>
    @else
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @foreach ($assets as $asset)
                @php
                    $isAudio = Str::contains($asset->mime_type, ['audio', 'mp3', 'ogg', 'wav']);
                    $isVideo = Str::contains($asset->mime_type, ['video', 'mp4', 'mkv', 'avi']);
                    $isImage = Str::contains($asset->mime_type, ['image', 'png', 'jpg', 'jpeg']);
                @endphp
                <x-ui.card class="relative flex flex-col justify-between overflow-hidden group">
                    <div>
                        <!-- File Icon Preview Header -->
                        <div class="h-32 mb-4 rounded-2xl bg-[var(--bg2)] border border-[var(--border)] flex items-center justify-center relative overflow-hidden shadow-inner">
                            <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 to-purple-500/5 group-hover:opacity-100 opacity-60 transition-opacity"></div>
                            
                            @if($isAudio)
                                <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center shadow-sm">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                                </div>
                            @elseif($isVideo)
                                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-sm">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                </div>
                            @elseif($isImage)
                                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-600 flex items-center justify-center shadow-sm">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                </div>
                            @else
                                <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shadow-sm">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                </div>
                            @endif

                            <div class="absolute bottom-2 left-2">
                                <x-ui.badge variant="{{ $asset->status === 'ready' ? 'success' : 'warning' }}" class="!text-[9px] font-bold shadow-sm">
                                    {{ $asset->status }}
                                </x-ui.badge>
                            </div>
                        </div>

                        <!-- Asset details -->
                        <div class="space-y-1 mb-4">
                            <p class="font-bold text-[var(--text)] text-xs tracking-tight truncate">{{ $asset->title }}</p>
                            <p class="text-[10px] text-[var(--text-secondary)] font-semibold truncate">{{ $asset->filename }}</p>
                        </div>
                    </div>

                    <div>
                        <!-- Details line -->
                        <div class="flex items-center justify-between text-[9px] text-[var(--text-secondary)] font-semibold border-t border-[var(--border)] pt-3 mb-4">
                            <span>{{ number_format($asset->size / (1024 * 1024), 2) }} MB</span>
                            <span>{{ $asset->created_at->format('M j, Y') }}</span>
                        </div>

                        <!-- Actions row -->
                        <div class="flex gap-2 w-full">
                            <x-ui.button :href="route('distribution.media.edit', $asset)" variant="secondary" size="sm" class="flex-1 !py-1.5 !text-[10px]">
                                Edit Asset
                            </x-ui.button>
                            <form method="POST" action="{{ route('distribution.media.destroy', $asset) }}" class="flex-1">
                                @csrf @method('DELETE')
                                <x-ui.button type="submit" variant="danger" size="sm" class="w-full !py-1.5 !text-[10px]" onclick="return confirm('Delete media file?')">
                                    Delete
                                </x-ui.button>
                            </form>
                        </div>
                    </div>
                </x-ui.card>
            @endforeach
        </div>
        <div class="mt-6">{{ $assets->links() }}</div>
    @endif
</x-ui.page>
