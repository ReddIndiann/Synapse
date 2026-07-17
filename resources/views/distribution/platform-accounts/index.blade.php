<x-ui.page title="Connected Accounts" description="Link your platform accounts for real API publishing.">
    <x-ui.back-link :href="route('distribution.publish.index')" label="Back to Publish Queue" />

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($channels as $channel)
            @php
                $account = $accounts->get($channel->id);
                $meta = $channelMeta[$channel->id] ?? ['requires_oauth' => false, 'is_configured' => false];
            @endphp
            <x-ui.card class="flex flex-col justify-between">
                <div>
                    <h3 class="font-semibold text-base">{{ $channel->name }}</h3>
                    @if ($account && $account->is_active)
                        <p class="text-sm text-emerald-600 mt-1">{{ $account->account_name }}</p>
                        @if ($account->account_handle)
                            <p class="text-xs text-muted">{{ $account->account_handle }}</p>
                        @endif
                        @if ($account->token_expires_at)
                            <p class="text-xs text-muted mt-1">Token expires {{ $account->token_expires_at->diffForHumans() }}</p>
                        @endif
                    @elseif ($meta['requires_oauth'])
                        <p class="text-sm text-muted mt-1">Not connected</p>
                        @unless ($meta['is_configured'])
                            <p class="text-xs text-amber-600 mt-1">OAuth credentials not configured in .env</p>
                        @endunless
                    @else
                        <p class="text-sm text-muted mt-1">No account connection required</p>
                    @endif
                </div>
                <div class="mt-4 flex gap-2">
                    @if ($meta['requires_oauth'] && $meta['is_configured'])
                        @if ($account && $account->is_active)
                            <form method="POST" action="{{ route('distribution.accounts.disconnect', $account) }}">@csrf @method('DELETE')
                                <x-ui.button type="submit" variant="ghost" size="sm" data-confirm="Disconnect account?">Disconnect</x-ui.button>
                            </form>
                        @else
                            <x-ui.button :href="route('distribution.accounts.connect', $channel)" variant="primary" size="sm">Connect</x-ui.button>
                        @endif
                    @endif
                </div>
            </x-ui.card>
        @endforeach
    </div>
</x-ui.page>
