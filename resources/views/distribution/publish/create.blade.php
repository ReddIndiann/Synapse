<x-ui.page title="Queue Publish Campaign" maxWidth="4xl">
    <x-ui.back-link :href="route('distribution.publish.index')" label="Back to Publish Queue" />
    <x-ui.form-card title="Multi-Platform Distribution">
        <form method="POST" action="{{ route('distribution.publish.store') }}" class="space-y-5" x-data="{ recordCost: {{ old('record_cost') ? 'true' : 'false' }}, selectAll: false }">
            @csrf

            <div>
                <x-input-label for="media_asset_id" value="Select Media Asset" />
                <select id="media_asset_id" name="media_asset_id" class="auth-input mt-1" required>
                    <option value="">Select media asset...</option>
                    @foreach ($assets as $asset)
                        <option value="{{ $asset->id }}" @selected(old('media_asset_id', $prefillMediaId) == $asset->id)>{{ $asset->title }} ({{ $asset->filename }})</option>
                    @endforeach
                </select>
                <x-input-error :messages="$errors->get('media_asset_id')" class="mt-1" />
            </div>

            <div>
                <div class="flex items-center justify-between mb-2">
                    <x-input-label value="Target Platforms" />
                    <label class="text-xs font-semibold text-muted flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="selectAll" @change="
                            document.querySelectorAll('input[name=\'distribution_channel_ids[]\']').forEach(el => {
                                if (!el.disabled) el.checked = selectAll;
                            })
                        ">
                        Select all
                    </label>
                </div>
                <div class="grid sm:grid-cols-2 gap-3">
                    @foreach ($channels as $channel)
                        @php
                            $account = $connectedAccounts->get($channel->id);
                            $requiresOAuth = in_array($channel->slug, config('distribution.requires_oauth', []));
                            $connected = !$requiresOAuth || ($account && $account->is_active);
                        @endphp
                        <label @class([
                            'flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition',
                            'border-border bg-surface hover:border-indigo-300' => $connected,
                            'border-border/60 bg-surface/50 opacity-60 cursor-not-allowed' => !$connected,
                        ])>
                            <input type="checkbox"
                                name="distribution_channel_ids[]"
                                value="{{ $channel->id }}"
                                class="mt-1 rounded border-border"
                                @checked(in_array($channel->id, old('distribution_channel_ids', [])))
                                @disabled(!$connected)>
                            <div class="min-w-0">
                                <p class="font-semibold text-sm">{{ $channel->name }}</p>
                                @if ($requiresOAuth)
                                    @if ($account && $account->is_active)
                                        <p class="text-xs text-emerald-600">Connected: {{ $account->account_name }}</p>
                                    @else
                                        <p class="text-xs text-amber-600">Not connected — <a href="{{ route('distribution.accounts.index') }}" class="underline">link account</a></p>
                                    @endif
                                @else
                                    <p class="text-xs text-muted">No OAuth required</p>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
                <x-input-error :messages="$errors->get('distribution_channel_ids')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="caption" value="Caption / Post Description" />
                <textarea id="caption" name="caption" rows="3" class="auth-input mt-1 resize-none h-[80px]" placeholder="Write copy description or credits...">{{ old('caption') }}</textarea>
                <x-input-error :messages="$errors->get('caption')" class="mt-1" />
            </div>

            <div>
                <x-input-label for="scheduled_at" value="Schedule Publication (Optional)" />
                <x-text-input id="scheduled_at" class="mt-1" type="datetime-local" name="scheduled_at" :value="old('scheduled_at')" />
                <p class="text-[10px] text-muted font-semibold mt-1">Leave blank to publish immediately.</p>
            </div>

            <div class="p-4 rounded-xl border border-border bg-surface space-y-3">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="checkbox" name="record_cost" value="1" x-model="recordCost" @checked(old('record_cost')) class="rounded border-border">
                    <span class="text-sm font-semibold">Record estimated distribution cost (Marketing)</span>
                </label>
                <div x-show="recordCost" x-cloak class="grid sm:grid-cols-2 gap-3">
                    <div>
                        <x-input-label for="estimated_cost_per_channel" value="Cost per channel" />
                        <x-text-input id="estimated_cost_per_channel" class="mt-1" type="number" step="0.01" min="0" name="estimated_cost_per_channel" :value="old('estimated_cost_per_channel', 25)" />
                    </div>
                    <div>
                        <x-input-label for="currency" value="Currency" />
                        <x-text-input id="currency" class="mt-1" type="text" name="currency" maxlength="3" :value="old('currency', 'GHS')" />
                    </div>
                </div>
            </div>

            <div class="flex gap-3 pt-2">
                <x-primary-button>Queue campaign</x-primary-button>
                <x-ui.button :href="route('distribution.publish.index')" variant="ghost" size="sm">Cancel</x-ui.button>
            </div>
        </form>
    </x-ui.form-card>
</x-ui.page>
