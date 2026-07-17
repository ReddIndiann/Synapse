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
        <!-- Wrap in Alpine.js component for Lightbox functionality -->
        <div x-data="{ lightboxOpen: false, lightboxUrl: '', lightboxTitle: '', lightboxType: '' }" class="relative">
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
                @foreach ($assets as $asset)
                    @php
                        $isAudio = Str::contains($asset->mime_type, ['audio', 'mp3', 'ogg', 'wav']);
                        $isVideo = Str::contains($asset->mime_type, ['video', 'mp4', 'mkv', 'avi']);
                        $isImage = Str::contains($asset->mime_type, ['image', 'png', 'jpg', 'jpeg']);
                    @endphp
                    <x-ui.card class="relative flex flex-col justify-between overflow-hidden group">
                        <div>
                            <!-- File Preview Header -->
                            <div @class([
                                'h-32 mb-4 rounded-2xl bg-[var(--bg2)] border border-[var(--border)] flex items-center justify-center relative overflow-hidden shadow-inner select-none',
                                'cursor-pointer' => $isImage || $isVideo || $isAudio
                            ])
                            @if($isImage || $isVideo || $isAudio)
                                @click="lightboxOpen = true; lightboxUrl = '{{ Storage::url($asset->path) }}'; lightboxTitle = '{{ addslashes($asset->title) }}'; lightboxType = '{{ $isImage ? 'image' : ($isVideo ? 'video' : ($isAudio ? 'audio' : 'other')) }}'"
                            @endif>
                                <div class="absolute inset-0 bg-gradient-to-tr from-indigo-500/5 to-purple-500/5 group-hover:opacity-100 opacity-60 transition-opacity"></div>
                                
                                @if($isAudio)
                                    <div class="w-12 h-12 rounded-2xl bg-sky-100 text-sky-600 flex items-center justify-center shadow-sm z-10">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                                    </div>
                                @elseif($isVideo)
                                    <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-600 flex items-center justify-center shadow-sm z-10">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                                    </div>
                                @elseif($isImage)
                                    <img src="{{ Storage::url($asset->path) }}" class="w-full h-full object-cover select-none pointer-events-none" alt="{{ $asset->title }}">
                                @else
                                    <div class="w-12 h-12 rounded-2xl bg-slate-100 text-slate-600 flex items-center justify-center shadow-sm z-10">
                                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                    </div>
                                @endif

                                @if($isImage || $isVideo || $isAudio)
                                    <!-- Hover Glass Overlay -->
                                    <div class="absolute inset-0 bg-slate-950/40 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity duration-200 z-20">
                                        <div class="p-2 rounded-full bg-white/20 backdrop-blur-md text-white border border-white/10 shadow-md">
                                            @if($isImage)
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            @else
                                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @endif
                                        </div>
                                    </div>
                                @endif

                                <div class="absolute bottom-2 left-2 z-10">
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
                                <x-ui.button :href="route('distribution.publish.create', ['media_asset_id' => $asset->id])" variant="primary" size="sm" class="flex-1 !py-1.5 !text-[10px]">
                                    Publish
                                </x-ui.button>
                                <x-ui.button :href="route('distribution.media.edit', $asset)" variant="secondary" size="sm" class="flex-1 !py-1.5 !text-[10px]">
                                    Edit
                                </x-ui.button>
                                <form method="POST" action="{{ route('distribution.media.destroy', $asset) }}" class="flex-1">
                                    @csrf @method('DELETE')
                                    <x-ui.button type="submit" variant="danger" size="sm" class="w-full !py-1.5 !text-[10px]" data-confirm="Delete media file?">
                                        Delete
                                    </x-ui.button>
                                </form>
                            </div>
                        </div>
                    </x-ui.card>
                @endforeach
            </div>
            <div class="mt-6">{{ $assets->links() }}</div>

            <!-- Lightbox Modal Overlay -->
            <div x-show="lightboxOpen" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-slate-950/80 backdrop-blur-md"
                 @click.away="lightboxOpen = false"
                 @keydown.escape.window="lightboxOpen = false"
                 style="display: none;">
                
                <div class="relative max-w-4xl w-full max-h-[85vh] flex flex-col items-center justify-center">
                    <button @click="lightboxOpen = false" class="absolute -top-12 right-0 p-2 text-white hover:text-indigo-400 transition-colors focus:outline-none" aria-label="Close modal">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                    
                    <div class="w-full flex items-center justify-center p-2">
                        <template x-if="lightboxType === 'image'">
                            <img :src="lightboxUrl" class="max-w-full max-h-[75vh] rounded-2xl border border-white/10 shadow-2xl object-contain">
                        </template>
                        <template x-if="lightboxType === 'video'">
                            <video :src="lightboxUrl" controls autoplay class="max-w-full max-h-[75vh] rounded-2xl border border-white/10 shadow-2xl"></video>
                        </template>
                        <template x-if="lightboxType === 'audio'">
                            <div class="bg-[var(--surface)] border border-[var(--border)] p-6 rounded-2xl shadow-2xl w-full max-w-md flex flex-col items-center gap-4">
                                <div class="w-16 h-16 rounded-2xl bg-sky-500/10 text-sky-400 border border-sky-500/20 flex items-center justify-center shadow-inner">
                                    <svg class="w-8 h-8" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19V6l12-3v13M9 19c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zm12-3c0 1.105-1.343 2-3 2s-3-.895-3-2 1.343-2 3-2 3 .895 3 2zM9 10l12-3"/></svg>
                                </div>
                                <p class="text-sm font-semibold text-[var(--text)] text-center" x-text="lightboxTitle"></p>
                                <audio :src="lightboxUrl" controls autoplay class="w-full"></audio>
                            </div>
                        </template>
                    </div>
                    
                    <template x-if="lightboxType !== 'audio'">
                        <p class="mt-4 text-sm font-semibold text-white/90 text-center" x-text="lightboxTitle"></p>
                    </template>
                </div>
            </div>
        </div>
    @endif
</x-ui.page>
