<x-ui.page title="Calendar Workspace" description="Intelligent timeline mapping tasks, financials, and publication queues.">
    <x-slot name="actions">
        <div class="flex items-center gap-2">
            <x-ui.button :href="route('calendar.index', ['month' => $prevMonth, 'year' => $prevYear])" variant="secondary" size="sm" title="Previous Month">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </x-ui.button>
            <span class="text-sm font-bold px-3 text-[var(--text)] tracking-tight">{{ $currentMonthName }}</span>
            <x-ui.button :href="route('calendar.index', ['month' => $nextMonth, 'year' => $nextYear])" variant="secondary" size="sm" title="Next Month">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </x-ui.button>
            <x-ui.button :href="route('calendar.index')" variant="ghost" size="sm" class="ml-2">
                Today
            </x-ui.button>
        </div>
    </x-slot>

    <!-- Calendar Layout Grid -->
    <div x-data="{ selectedDay: null, selectedDayStr: '', selectedEvents: [] }" class="grid lg:grid-cols-4 gap-6">
        
        <!-- Monthly Grid Card -->
        <div class="lg:col-span-3">
            <x-ui.card class="!p-0 overflow-hidden border border-[var(--border)]">
                <!-- Day Headers -->
                <div class="grid grid-cols-7 border-b border-[var(--border)] bg-[var(--bg2)]/60 text-center text-xs font-semibold text-[var(--text-secondary)] uppercase py-3">
                    <div>Sun</div>
                    <div>Mon</div>
                    <div>Tue</div>
                    <div>Wed</div>
                    <div>Thu</div>
                    <div>Fri</div>
                    <div>Sat</div>
                </div>

                <!-- Days cells -->
                <div class="grid grid-cols-7 bg-[var(--surface)] text-[var(--text)] divide-x divide-y divide-[var(--border)]">
                    @foreach($days as $day)
                        @php
                            $isToday = $day['is_today'];
                            $isCurrentMonth = $day['is_current_month'];
                            $hasEvents = count($day['events']) > 0;
                        @endphp
                        <div 
                            @click="selectedDay = '{{ $day['date']->toDateString() }}'; selectedDayStr = '{{ $day['date']->format('F d, Y') }}'; selectedEvents = {{ json_encode($day['events']) }}"
                            @class([
                                'min-h-[90px] sm:min-h-[110px] p-2 flex flex-col justify-between transition-all cursor-pointer relative group',
                                'bg-[var(--bg)]/10' => !$isCurrentMonth,
                                'hover:bg-[var(--bg2)]/50' => $isCurrentMonth,
                                'bg-[var(--pur)]/5 hover:bg-[var(--pur)]/10' => $isToday,
                            ])
                        >
                            <!-- Date Number -->
                            <div class="flex items-center justify-between">
                                <span @class([
                                    'text-xs font-bold w-6 h-6 rounded-lg flex items-center justify-center',
                                    'text-[var(--text-secondary)]' => !$isCurrentMonth,
                                    'text-[var(--text)]' => $isCurrentMonth && !$isToday,
                                    'bg-[var(--pur)] text-white shadow-md shadow-[var(--pur)]/25' => $isToday,
                                ])>
                                    {{ $day['day_number'] }}
                                </span>
                                
                                @if($hasEvents)
                                    <span class="w-1.5 h-1.5 rounded-full bg-[var(--pur)]/60 block sm:hidden"></span>
                                @endif
                            </div>

                            <!-- Events Lists (Hidden on Mobile) -->
                            <div class="hidden sm:flex flex-col gap-1.5 mt-2 overflow-y-auto max-h-[70px]">
                                @foreach(array_slice($day['events'], 0, 3) as $ev)
                                    @php
                                        $badgeColor = $ev['color'] === 'violet' ? 'bg-violet-500/10 text-violet-400 border border-violet-500/20' 
                                            : ($ev['color'] === 'emerald' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' 
                                            : 'bg-sky-500/10 text-sky-400 border border-sky-500/20');
                                    @endphp
                                    <div class="text-[9px] font-bold px-1.5 py-0.5 rounded-md truncate {{ $badgeColor }}" title="{{ $ev['title'] }}">
                                        {{ $ev['time'] }} · {{ $ev['title'] }}
                                    </div>
                                @endforeach
                                @if(count($day['events']) > 3)
                                    <div class="text-[8px] font-semibold text-[var(--text-secondary)] pl-1">
                                        + {{ count($day['events']) - 3 }} more
                                    </div>
                                @endif
                            </div>

                            <!-- Add Hover Border Glow -->
                            <div class="absolute inset-0 border border-transparent group-hover:border-[var(--pur)]/20 rounded-none pointer-events-none transition-colors"></div>
                        </div>
                    @endforeach
                </div>
            </x-ui.card>
        </div>

        <!-- Selected Day Details Sidebar Panel -->
        <div class="lg:col-span-1">
            <div class="sticky top-20">
                <x-ui.card class="border border-[var(--border)] overflow-hidden">
                    <!-- Day Header details -->
                    <div class="border-b border-[var(--border)] pb-3 mb-4">
                        <h3 class="font-bold text-sm text-[var(--text)] tracking-tight uppercase">Daily Timeline</h3>
                        <p class="text-xs text-[var(--text-secondary)] font-medium mt-0.5" x-text="selectedDayStr || 'Select a day to view'"></p>
                    </div>

                    <!-- Events list loop -->
                    <div class="space-y-3.5 max-h-[450px] overflow-y-auto pr-1">
                        <template x-if="selectedEvents.length === 0">
                            <div class="py-12 text-center">
                                <div class="w-12 h-12 rounded-full bg-[var(--bg2)]/80 flex items-center justify-center mx-auto mb-3 text-[var(--text-secondary)]">
                                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                </div>
                                <p class="text-xs text-[var(--text-secondary)] font-medium">No tasks, financial items, or publications scheduled.</p>
                            </div>
                        </template>

                        <template x-for="event in selectedEvents" :key="event.id">
                            <div class="p-3.5 rounded-2xl border border-[var(--border)] bg-[var(--bg3)]/20 hover:bg-[var(--bg3)]/40 transition-all flex flex-col gap-2">
                                <div class="flex items-center justify-between">
                                    <span :class="{
                                        'px-2 py-0.5 text-[8px] font-extrabold uppercase rounded-full': true,
                                        'bg-violet-500/10 text-violet-400 border border-violet-500/20': event.type === 'task',
                                        'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20': event.type === 'transaction',
                                        'bg-sky-500/10 text-sky-400 border border-sky-500/20': event.type === 'publish_job'
                                    }" x-text="event.type.replace('_', ' ')"></span>
                                    <span class="text-[9px] text-[var(--text-secondary)] font-semibold" x-text="event.time"></span>
                                </div>
                                
                                <div>
                                    <h4 class="text-xs font-bold text-[var(--text)] leading-snug" x-text="event.title"></h4>
                                    <p class="text-[10px] text-[var(--text-secondary)] font-semibold mt-1" x-text="event.detail"></p>
                                </div>

                                <div class="pt-1 flex justify-end">
                                    <a :href="event.url" class="inline-flex items-center gap-1 text-[10px] font-bold text-indigo-600 dark:text-indigo-400 hover:opacity-85 transition">
                                        View Details
                                        <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                    </a>
                                </div>
                            </div>
                        </template>
                    </div>
                </x-ui.card>
            </div>
        </div>
    </div>

    <!-- Automatically open current day on load -->
    @php
        $todayDay = collect($days)->firstWhere('is_today', true);
    @endphp
    @if($todayDay)
        <script>
            document.addEventListener('DOMContentLoaded', () => {
                setTimeout(() => {
                    const cell = document.querySelector('[class*="bg-[var(--pur)]/5"]');
                    if (cell) cell.click();
                }, 100);
            });
        </script>
    @endif
</x-ui.page>
