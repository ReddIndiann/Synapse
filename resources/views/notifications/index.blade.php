<x-ui.page title="Notifications" description="In-app alerts for scheduled tasks, budget spending, and media distribution.">
    <x-slot name="actions">
        <div class="flex items-center gap-2">
            @if(auth()->user()->unreadNotifications->count() > 0)
                <form method="POST" action="{{ route('notifications.read-all') }}">
                    @csrf
                    <x-ui.button type="submit" variant="secondary" size="sm">Mark all read</x-ui.button>
                </form>
            @endif
            @if(auth()->user()->notifications->count() > 0)
                <form method="POST" action="{{ route('notifications.clear-all') }}">
                    @csrf @method('DELETE')
                    <x-ui.button type="submit" variant="danger" size="sm" data-confirm="Clear all notifications permanently?">Clear all</x-ui.button>
                </form>
            @endif
        </div>
    </x-slot>

    <!-- Notifications List -->
    <div class="space-y-4">
        @forelse($notifications as $n)
            @php
                $isUnread = $n->unread();
                $type = $n->data['type'] ?? 'info';
                
                // Color schemes & icons based on type
                if ($type === 'task') {
                    $iconBg = 'bg-violet-500/10 text-violet-400 border border-violet-500/20';
                    $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2-2m-6 9l2 2 4-4" />';
                    $targetUrl = route('assistant.tasks.index');
                } elseif ($type === 'finance') {
                    $iconBg = 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20';
                    $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                    $targetUrl = route('accounting.budgets.index');
                } elseif ($type === 'distribution') {
                    $iconBg = 'bg-sky-500/10 text-sky-400 border border-sky-500/20';
                    $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />';
                    $targetUrl = route('distribution.publish.index');
                } else {
                    $iconBg = 'bg-slate-500/10 text-slate-400 border border-slate-500/20';
                    $svg = '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />';
                    $targetUrl = route('dashboard');
                }
            @endphp
            
            <div @class([
                'ui-card flex flex-col sm:flex-row sm:items-center justify-between gap-4 transition-all !py-4 border-l-4 relative',
                '!border-l-[var(--pur)]' => $isUnread,
                '!border-l-slate-300 dark:!border-l-slate-800' => !$isUnread,
                'opacity-75 hover:opacity-100' => !$isUnread,
            ])>
                <div class="flex items-start gap-4 flex-1 min-w-0">
                    <!-- Icon badge -->
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0 {{ $iconBg }} mt-0.5">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            {!! $svg !!}
                        </svg>
                    </div>

                    <!-- Content -->
                    <a href="{{ $targetUrl }}" 
                       onclick="handleNotificationClick(event, '{{ $n->id }}', {{ $isUnread ? 'true' : 'false' }}, '{{ $targetUrl }}')"
                       class="flex-1 min-w-0 hover:underline group">
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold uppercase tracking-widest text-[var(--text-secondary)] group-hover:text-[var(--pur)] transition-colors">
                                {{ $type === 'distribution' ? 'distribution queue' : ($type === 'finance' ? 'accountant financials' : 'personal assistant') }}
                            </span>
                            <span class="text-[10px] text-[var(--text-secondary)] font-medium">•</span>
                            <span class="text-[10px] text-[var(--text-secondary)] font-medium">{{ $n->created_at->diffForHumans() }}</span>
                        </div>
                        
                        <p class="text-sm font-semibold text-[var(--text)] mt-1.5 leading-snug">
                            {{ $n->data['message'] ?? 'Notification alert detail' }}
                        </p>
                    </a>
                </div>

                <!-- Actions context (non-absolute for responsiveness) -->
                <div class="flex items-center gap-1.5 self-end sm:self-center shrink-0 pl-14 sm:pl-0">
                    @if($isUnread)
                        <form method="POST" action="{{ route('notifications.read', $n->id) }}" onclick="event.stopPropagation();">
                            @csrf
                            <button type="submit" class="p-1.5 rounded-lg text-indigo-600 dark:text-indigo-400 hover:bg-indigo-500/10 transition-colors" title="Mark as read">
                                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </button>
                        </form>
                    @endif
                    
                    <form method="POST" action="{{ route('notifications.destroy', $n->id) }}" onclick="event.stopPropagation();">
                        @csrf @method('DELETE')
                        <button type="submit" class="p-1.5 rounded-lg text-rose-500 hover:bg-rose-500/10 transition-colors" title="Delete notification" data-confirm="Delete notification?">
                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        @empty
            <x-ui.empty-state title="All caught up!" description="You have no notifications in your dashboard feed at the moment. New alerts will show up here.">
                <x-slot name="action">
                    <x-ui.button :href="route('dashboard')" variant="primary" size="sm">Go to Dashboard</x-ui.button>
                </x-slot>
            </x-ui.empty-state>
        @endforelse

        <div class="pt-4">
            {{ $notifications->links() }}
        </div>
    </div>

    <script>
    async function handleNotificationClick(event, notificationId, isUnread, targetUrl) {
        if (isUnread) {
            event.preventDefault();
            try {
                await fetch(`/notifications/${notificationId}/read`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                    }
                });
            } catch (error) {
                console.error('Error marking notification as read:', error);
            }
        }
        window.location.href = targetUrl;
    }
    </script>
</x-ui.page>
