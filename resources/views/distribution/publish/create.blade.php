<x-ui.page title="Queue Publish" maxWidth="3xl">
    <x-ui.form-card title="Distribution job">
        <form method="POST" action="{{ route('distribution.publish.store') }}" class="space-y-4">
            @csrf
            <div>
                <x-input-label for="media_asset_id" value="Media asset" />
                <select id="media_asset_id" name="media_asset_id" class="auth-input mt-1" required>
                    <option value="">Select media...</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected(old('media_asset_id') == $asset->id)>{{ $asset->title }}</option>
                    @endforeach
                </select>
                @if ($assets->isEmpty())
                    <p class="text-xs text-amber-600 mt-1"><a href="{{ route('distribution.media.create') }}" class="underline">Upload media</a> first.</p>
                @endif
            </div>
            <div>
                <x-input-label for="distribution_channel_id" value="Platform" />
                <select id="distribution_channel_id" name="distribution_channel_id" class="auth-input mt-1" required>
                    <option value="">Select channel...</option>
                    @foreach ($channels as $channel)
                        <option value="{{ $channel->id }}" @selected(old('distribution_channel_id') == $channel->id)>{{ $channel->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <x-input-label for="caption" value="Caption / description" />
                <textarea id="caption" name="caption" rows="3" class="auth-input mt-1 resize-none">{{ old('caption') }}</textarea>
            </div>
            <div>
                <x-input-label for="scheduled_at" value="Schedule (optional)" />
                <x-text-input id="scheduled_at" class="mt-1" type="datetime-local" name="scheduled_at" :value="old('scheduled_at')" />
            </div>
            <x-ui.alert variant="info">Jobs are stored locally. External platform APIs will be wired in Phase 4.</x-ui.alert>
            <div class="flex gap-3">
                <x-primary-button>Queue job</x-primary-button>
                <x-ui.button :href="route('distribution.publish.index')" variant="ghost" size="sm">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>
</x-ui.page>
