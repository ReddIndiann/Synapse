<x-ui.page title="AI Assistant" description="Natural-language workspace — Schedule tasks, log transactions, and queue platform distribution." maxWidth="5xl">
    <x-slot name="actions">
        <form method="POST" action="{{ route('assistant.chat.clear') }}" onsubmit="return confirm('Clear chat history?')">
            @csrf
            <x-ui.button type="submit" variant="danger" size="sm">Clear Chat</x-ui.button>
        </form>
    </x-slot>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-ui.card class="h-[600px] flex flex-col relative overflow-hidden">
                <!-- Chat Messages Scroll Area -->
                <div id="chat-messages" class="flex-1 overflow-y-auto space-y-4 mb-6 pr-2 scroll-smooth">
                    @if (session('status'))
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 flex items-center justify-center text-xs font-bold shrink-0">✓</div>
                            <div class="rounded-xl border border-emerald-500/20 bg-emerald-500/5 p-4 flex-1 text-sm text-emerald-400">
                                {{ session('status') }}
                            </div>
                        </div>
                    @endif

                    @foreach ($messages as $msg)
                        @if ($msg->role === 'user')
                            <!-- User Message -->
                            <div class="flex justify-end items-end gap-2">
                                <div class="cbbl cbus text-sm">
                                    {{ $msg->content }}
                                </div>
                                <div class="w-7 h-7 rounded-full bg-[var(--pur)]/20 text-[var(--pur)] border border-[var(--pur)]/20 flex items-center justify-center text-[10px] font-bold shrink-0 shadow-sm">
                                    ME
                                </div>
                            </div>
                        @else
                            <!-- Assistant Message -->
                            <div class="flex justify-start items-start gap-2">
                                <div class="logo-i !w-7 !h-7 !rounded-full !text-[9px] shadow-sm shrink-0 flex items-center justify-center">
                                    AI
                                </div>
                                <div class="cbbl cbai text-sm flex-1">
                                    <div class="whitespace-pre-line">{{ $msg->content }}</div>

                                    <!-- Render Action Buttons for Conflict Resolution -->
                                    @if ($msg->metadata && isset($msg->metadata['type']) && $msg->metadata['type'] === 'task_conflict')
                                        <div class="mt-4 pt-3 border-t border-[var(--border)] flex flex-wrap gap-2">
                                            <form method="POST" action="{{ route('assistant.chat.resolve', $msg->id) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="reschedule">
                                                <x-ui.button type="submit" variant="primary" size="sm" class="!py-1.5 !px-3 !text-xs font-medium">
                                                    Reschedule to {{ \Illuminate\Support\Carbon::parse($msg->metadata['alternative_due_at'])->format('h:i A') }}
                                                </x-ui.button>
                                            </form>

                                            <form method="POST" action="{{ route('assistant.chat.resolve', $msg->id) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="confirm">
                                                <x-ui.button type="submit" variant="secondary" size="sm" class="!py-1.5 !px-3 !text-xs font-medium">
                                                    Force Schedule
                                                </x-ui.button>
                                            </form>

                                            <form method="POST" action="{{ route('assistant.chat.resolve', $msg->id) }}">
                                                @csrf
                                                <input type="hidden" name="action" value="cancel">
                                                <x-ui.button type="submit" variant="danger" size="sm" class="!py-1.5 !px-3 !text-xs font-medium">
                                                    Cancel
                                                </x-ui.button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>

                <!-- Input Form -->
                <form method="POST" action="{{ route('assistant.chat.store') }}" class="border-t border-[var(--border)] pt-4 flex gap-3 items-end">
                    @csrf
                    <div class="flex-1">
                        <label for="prompt" class="sr-only">Command</label>
                        <textarea id="prompt" name="prompt" rows="2" required placeholder="Type a command (e.g. Schedule meeting tomorrow at 9 AM, spent 120 GHS on Internet Utilities...)" class="auth-input resize-none h-[50px] !py-3 !px-4">{{ old('prompt') }}</textarea>
                        <x-input-error :messages="$errors->get('prompt')" class="mt-1" />
                    </div>
                    <x-ui.button type="submit" variant="primary" class="h-[50px] shrink-0 !px-6">Send</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card>
                <h3 class="font-bold text-[var(--text)] text-sm tracking-tight uppercase border-b border-[var(--border)] pb-2 mb-3">Quick actions</h3>
                <div class="space-y-2">
                    <x-ui.button :href="route('assistant.tasks.create')" variant="ghost" size="sm" class="w-full justify-start">+ New task</x-ui.button>
                    <x-ui.button :href="route('accounting.transactions.create')" variant="ghost" size="sm" class="w-full justify-start">+ Record transaction</x-ui.button>
                    <x-ui.button :href="route('distribution.media.create')" variant="ghost" size="sm" class="w-full justify-start">+ Upload media</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card class="flex flex-col max-h-[350px]">
                <h3 class="font-bold text-[var(--text)] text-sm tracking-tight uppercase border-b border-[var(--border)] pb-2 mb-3 shrink-0">Recent tasks</h3>
                <div class="flex-1 overflow-y-auto space-y-2 pr-1">
                    @forelse ($recentTasks as $task)
                        <div class="py-2 border-b border-[var(--border)] last:border-0">
                            <p class="text-sm font-semibold text-[var(--text)]">{{ $task->title }}</p>
                            <p class="text-xs text-[var(--text-muted)] mt-0.5 font-medium">
                                @if($task->due_at)
                                    Due: {{ $task->due_at->format('M j, g:i A') }} · 
                                @endif
                                <span class="capitalize font-bold text-[var(--pur)]">{{ $task->priority }}</span>
                            </p>
                        </div>
                    @empty
                        <p class="text-sm text-[var(--text-muted)] font-medium">No tasks captured yet.</p>
                    @endforelse
                </div>
            </x-ui.card>
        </div>
    </div>

    <!-- Auto scroll javascript -->
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            const chatContainer = document.getElementById("chat-messages");
            if (chatContainer) {
                chatContainer.scrollTop = chatContainer.scrollHeight;
            }
        });
    </script>
</x-ui.page>
