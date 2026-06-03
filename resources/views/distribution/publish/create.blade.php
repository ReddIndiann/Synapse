<x-ui.page title="Queue Publish Job" maxWidth="3xl">
    <x-ui.form-card title="Distribution Job Details">
        <form method="POST" action="{{ route('distribution.publish.store') }}" class="space-y-5">
            @csrf
            
            <div>
                <x-input-label for="media_asset_id" value="Select Media Asset" />
                <select id="media_asset_id" name="media_asset_id" class="auth-input mt-1" required>
                    <option value="">Select media asset...</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected(old('media_asset_id') == $asset->id)>{{ $asset->title }} ({{ $asset->filename }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('media_asset_id')" class="mt-1" />
                @if ($assets->isEmpty())
                    <p class="text-xs text-amber-600 font-semibold mt-1">
                        ⚠️ No media available. <a href="{{ route('distribution.media.create') }}" class="underline hover:text-amber-700">Upload media file</a> first.
                    </p>
                @endif
            </div>

            <div>
                <x-input-label for="distribution_channel_id" value="Target Platform Channel" />
                <select id="distribution_channel_id" name="distribution_channel_id" class="auth-input mt-1" required>
                    <option value="">Select platform channel...</option>
                    @foreach ($channels as $channel)
                        <option value="{{ $channel->id }}" @selected(old('distribution_channel_id') == $channel->id)>{{ $channel->name }}</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('distribution_channel_id')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="caption" value="Caption / Post Description" />
                <textarea id="caption" name="caption" rows="3" class="auth-input mt-1 resize-none h-[80px]" placeholder="Write copy description or credits...">{{ old('caption') }}</textarea>
                <x-input-error :messages="$errors->get('caption')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="scheduled_at" value="Schedule Publication (Optional)" />
                <x-text-input id="scheduled_at" class="mt-1" type="datetime-local" name="scheduled_at" :value="old('scheduled_at')" />
                <p class="text-[10px] text-slate-400 font-semibold mt-1">Leave blank to start publishing process immediately.</p>
                <x-input-error :messages="$errors->get('scheduled_at')" class="mt-1" />
            </div>

            <x-ui.alert variant="info">
                Synapse queues and chunk-uploads files in the background using automated listeners.
            </x-ui.alert>

            <div class="flex gap-3 pt-2">
                <x-primary-button>Queue job</x-primary-button>
                <x-ui.button :href="route('distribution.publish.index')" variant="ghost" size="sm">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>
</x-ui.page>
