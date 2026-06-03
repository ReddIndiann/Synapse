<x-ui.page title="AI Assistant" description="Natural-language workspace — AI integration comes in Phase 2." maxWidth="5xl">
    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2">
            <x-ui.card class="min-h-[420px] flex flex-col">
                <div class="flex-1 space-y-4 mb-6">
                    <div class="flex gap-3">
                        <div class="w-8 h-8 rounded-full bg-indigo-100 text-indigo-700 flex items-center justify-center text-xs font-bold shrink-0">AI</div>
                        <div class="ui-surface p-4 flex-1 text-sm text-slate-700">
                            Hi {{ Auth::user()->first_name ?? Auth::user()->name }}! Tell me what you need — schedule a task, log an expense, or queue content for publishing.
                        </div>
                    </div>
                    @if (session('status'))
                        <div class="flex gap-3">
                            <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs font-bold shrink-0">✓</div>
                            <div class="rounded-xl border border-emerald-200 bg-emerald-50 p-4 flex-1 text-sm text-emerald-800">{{ session('status') }}</div>
                        </div>
                    @endif
                </div>

                <form method="POST" action="{{ route('assistant.chat.store') }}" class="border-t border-slate-100 pt-4">
                    @csrf
                    <label for="prompt" class="sr-only">Command</label>
                    <textarea id="prompt" name="prompt" rows="3" required placeholder="e.g. Schedule client follow-up Friday 10am, high priority..." class="auth-input resize-none mb-3">{{ old('prompt') }}</textarea>
                    <x-input-error :messages="$errors->get('prompt')" class="mb-3" />
                    <x-ui.button type="submit" variant="primary">Send command</x-ui.button>
                </form>
            </x-ui.card>
        </div>

        <div class="space-y-4">
            <x-ui.card>
                <h3 class="font-semibold text-slate-900 mb-3">Quick actions</h3>
                <div class="space-y-2">
                    <x-ui.button :href="route('assistant.tasks.create')" variant="ghost" size="sm" class="w-full justify-start">+ New task</x-ui.button>
                    <x-ui.button :href="route('accounting.transactions.create')" variant="ghost" size="sm" class="w-full justify-start">+ Record transaction</x-ui.button>
                    <x-ui.button :href="route('distribution.media.create')" variant="ghost" size="sm" class="w-full justify-start">+ Upload media</x-ui.button>
                </div>
            </x-ui.card>

            <x-ui.card>
                <h3 class="font-semibold text-slate-900 mb-3">Recent tasks</h3>
                @forelse ($recentTasks as $task)
                    <p class="text-sm text-slate-600 py-1 border-b border-slate-50 last:border-0">{{ $task->title }}</p>
                @empty
                    <p class="text-sm text-slate-500">No tasks captured yet.</p>
                @endforelse
            </x-ui.card>
        </div>
    </div>
</x-ui.page>
