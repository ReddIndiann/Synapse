<x-ui.page title="Connected Accounts" description="Link your platform accounts for real API publishing.">
    <x-ui.back-link :href="route('distribution.publish.index')" label="Back to Publish Queue" />

    @if (session('status'))
        <x-ui.alert variant="success" class="mb-4">{{ session('status') }}</x-ui.alert>
    @endif

    @if (session('error'))
        <x-ui.alert variant="danger" class="mb-4">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
        @foreach ($channels as $channel)
            @php
                $account = $accounts->get($channel->id);
                $meta = $channelMeta[$channel->id] ?? [];
                $action = $meta['action'] ?? ['url' => '#', 'label' => 'Open', 'external' => true];
                $isConnected = $account && $account->is_active;
                $profileUrl = $meta['profile_url'] ?? null;
            @endphp

            @if ($isConnected)
                <x-ui.card class="h-full flex flex-col border-emerald-500/20 !p-0 overflow-hidden">
                    <div class="p-5 flex-1 flex flex-col">
                        <div class="flex items-start justify-between gap-3 mb-4">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-11 h-11 rounded-xl bg-emerald-500/10 border border-emerald-500/20 flex items-center justify-center text-sm font-bold text-emerald-700 dark:text-emerald-400 uppercase shrink-0">
                                    {{ strtoupper(substr($channel->name, 0, 2)) }}
                                </div>
                                <div class="min-w-0">
                                    <h3 class="font-semibold text-base text-[var(--text)] truncate">{{ $channel->name }}</h3>
                                    <p class="text-xs text-emerald-600 dark:text-emerald-400 font-semibold mt-0.5">Linked account</p>
                                </div>
                            </div>
                            <x-ui.badge variant="success">Connected</x-ui.badge>
                        </div>

                        <div class="rounded-2xl border border-[var(--border)] bg-[var(--bg2)]/60 p-4 space-y-3 flex-1">
                            <p class="text-[10px] font-bold uppercase tracking-wider text-muted">Account details</p>

                            @foreach ($account->displayDetails() as $detail)
                                <div class="flex flex-col gap-0.5">
                                    <span class="text-[10px] font-semibold uppercase tracking-wide text-muted">{{ $detail['label'] }}</span>
                                    <span @class([
                                        'text-sm font-medium break-all',
                                        'text-rose-600 dark:text-rose-400' => $detail['label'] === 'Token status' && $detail['value'] === 'Expired',
                                        'text-emerald-600 dark:text-emerald-400' => $detail['label'] === 'Token status' && $detail['value'] === 'Active',
                                        'text-[var(--text)]' => ! in_array($detail['label'], ['Token status']),
                                    ])>{{ $detail['value'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <div class="border-t border-[var(--border)] bg-[var(--bg2)]/40 px-4 py-3 grid grid-cols-2 gap-2">
                        @if ($profileUrl)
                            <a
                                href="{{ $profileUrl }}"
                                target="_blank"
                                rel="noopener noreferrer"
                                class="inline-flex items-center justify-center gap-2 h-10 px-3 rounded-xl text-xs font-semibold
                                       bg-[var(--surface)] border border-[var(--border)] text-[var(--text)]
                                       hover:border-indigo-400/60 hover:text-indigo-600 dark:hover:text-indigo-400
                                       transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40"
                            >
                                <svg class="w-3.5 h-3.5 shrink-0 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                View profile
                            </a>
                        @else
                            <span class="h-10"></span>
                        @endif

                        <form method="POST" action="{{ route('distribution.accounts.disconnect', $account) }}" class="contents">
                            @csrf @method('DELETE')
                            <button
                                type="submit"
                                data-confirm="Disconnect {{ $channel->name }} account?"
                                class="inline-flex items-center justify-center gap-2 h-10 px-3 rounded-xl text-xs font-semibold
                                       bg-rose-500/10 border border-rose-500/25 text-rose-700 dark:text-rose-300
                                       hover:bg-rose-500/15 hover:border-rose-500/40
                                       transition-colors focus:outline-none focus-visible:ring-2 focus-visible:ring-rose-500/30"
                            >
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                Disconnect
                            </button>
                        </form>
                    </div>
                </x-ui.card>
            @else
                <div class="platform-card group h-full">
                    <x-ui.card class="h-full flex flex-col !p-0 overflow-hidden transition-all duration-200 group-hover:border-indigo-400/50 group-hover:shadow-lg">
                        <a
                            href="{{ $action['url'] }}"
                            @if ($action['external']) target="_blank" rel="noopener noreferrer" @endif
                            class="block flex-1 p-5 focus:outline-none focus-visible:ring-2 focus-visible:ring-indigo-500/40 rounded-t-3xl"
                        >
                            <div class="flex items-start justify-between gap-3 mb-3">
                                <div class="w-10 h-10 rounded-xl bg-[var(--bg2)] border border-[var(--border)] flex items-center justify-center text-sm font-bold text-indigo-600 uppercase">
                                    {{ strtoupper(substr($channel->name, 0, 2)) }}
                                </div>
                                @if ($meta['requires_oauth'] ?? false)
                                    <x-ui.badge variant="warning">Not linked</x-ui.badge>
                                @else
                                    <x-ui.badge variant="primary">Ready</x-ui.badge>
                                @endif
                            </div>

                            <h3 class="font-semibold text-base text-[var(--text)]">{{ $channel->name }}</h3>

                            @if ($meta['requires_oauth'] ?? false)
                                <p class="text-sm text-muted mt-1">
                                    @if ($meta['is_configured'] ?? false)
                                        Click to authorize with {{ $channel->name }}
                                    @else
                                        Click to sign in on {{ $channel->name }}
                                    @endif
                                </p>
                                @unless ($meta['is_configured'] ?? false)
                                    <p class="text-xs text-amber-600 dark:text-amber-400 mt-1">Add OAuth credentials in .env to enable in-app linking</p>
                                @endunless
                            @else
                                <p class="text-sm text-muted mt-1">No OAuth required — click to visit platform</p>
                            @endif

                            <p class="mt-4 text-xs font-semibold text-indigo-600 dark:text-indigo-400 group-hover:text-indigo-700 dark:group-hover:text-indigo-300 inline-flex items-center gap-1.5">
                                {{ $action['label'] }}
                                <svg class="w-3.5 h-3.5 transition-transform group-hover:translate-x-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                                </svg>
                            </p>
                        </a>

                        @if (($meta['login_url'] ?? null) || ($meta['signup_url'] ?? null) || (!($meta['is_configured'] ?? true) && ($meta['developer_url'] ?? null)))
                            <div class="border-t border-[var(--border)] bg-[var(--bg2)]/40 px-4 py-3 flex flex-wrap gap-2">
                                @if ($meta['login_url'] ?? null)
                                    <a
                                        href="{{ $meta['login_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-[11px] font-semibold
                                               bg-[var(--surface)] border border-[var(--border)] text-[var(--text-muted)]
                                               hover:text-indigo-600 hover:border-indigo-400/50 dark:hover:text-indigo-400
                                               transition-colors"
                                    >
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                                        </svg>
                                        Sign in
                                    </a>
                                @endif
                                @if ($meta['signup_url'] ?? null)
                                    <a
                                        href="{{ $meta['signup_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-[11px] font-semibold
                                               bg-[var(--surface)] border border-[var(--border)] text-[var(--text-muted)]
                                               hover:text-indigo-600 hover:border-indigo-400/50 dark:hover:text-indigo-400
                                               transition-colors"
                                    >
                                        <svg class="w-3 h-3 opacity-70" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                                        </svg>
                                        Create account
                                    </a>
                                @endif
                                @if (!($meta['is_configured'] ?? true) && ($meta['developer_url'] ?? null))
                                    <a
                                        href="{{ $meta['developer_url'] }}"
                                        target="_blank"
                                        rel="noopener noreferrer"
                                        class="inline-flex items-center gap-1.5 h-8 px-3 rounded-lg text-[11px] font-semibold
                                               bg-amber-500/10 border border-amber-500/25 text-amber-700 dark:text-amber-300
                                               hover:bg-amber-500/15 hover:border-amber-500/40
                                               transition-colors"
                                    >
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        Developer setup
                                    </a>
                                @endif
                            </div>
                        @endif
                    </x-ui.card>
                </div>
            @endif
        @endforeach
    </div>
</x-ui.page>
