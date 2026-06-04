<x-ui.page title="Tasks" description="Manage and schedule your assistant work.">
    <!-- Safely pass task records to JavaScript via script tag to prevent double quote HTML parsing breaks -->
    <script id="tasks-data" type="application/json">
        @json($tasks)
    </script>

    <div x-data="taskManager">
        <x-slot name="actions">
            <div class="flex items-center gap-3">
                <!-- View switcher buttons -->
                <div class="flex bg-[var(--bg2)] border border-[var(--border)] p-1 rounded-xl gap-1.5 shadow-sm">
                    <button 
                        @click="setViewMode('kanban')" 
                        :class="viewMode === 'kanban' ? 'bg-[var(--surface)] text-[var(--pur)] shadow-sm' : 'text-[var(--text-secondary)] hover:text-[var(--text)]'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition-all focus:outline-none"
                        title="Kanban Board View"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" /></svg>
                        Board
                    </button>
                    <button 
                        @click="setViewMode('list')" 
                        :class="viewMode === 'list' ? 'bg-[var(--surface)] text-[var(--pur)] shadow-sm' : 'text-[var(--text-secondary)] hover:text-[var(--text)]'"
                        class="px-3 py-1.5 text-xs font-semibold rounded-lg flex items-center gap-1.5 transition-all focus:outline-none"
                        title="List View"
                    >
                        <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" /></svg>
                        List
                    </button>
                </div>
                
                <x-ui.button :href="route('assistant.tasks.create')" variant="primary" size="sm">New Task</x-ui.button>
            </div>
        </x-slot>

        <!-- KANBAN BOARD VIEW -->
        <div x-show="viewMode === 'kanban'" class="grid md:grid-cols-2 lg:grid-cols-4 gap-5 mb-8" style="display: none;">
            
            <!-- Column 1: Pending -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-[var(--border)] pb-2 mb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-violet-500 shadow-[0_0_8px_rgba(139,92,246,0.6)]"></span>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[var(--text)]">Pending</h4>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[var(--bg2)] border border-[var(--border)] text-[var(--text-secondary)]" x-text="tasks.filter(t => t.status === 'pending').length"></span>
                </div>
                <div 
                    @dragover.prevent 
                    @drop="dropTask('pending')" 
                    class="flex flex-col gap-3 min-h-[400px] p-2.5 rounded-2xl bg-[var(--bg2)]/30 border border-dashed border-[var(--border)] transition-colors hover:bg-[var(--bg2)]/50"
                >
                    <template x-for="task in tasks.filter(t => t.status === 'pending')" :key="task.id">
                        <div 
                            draggable="true" 
                            @dragstart="dragStart($event, task.id)" 
                            class="p-4 rounded-xl border border-[var(--border)] bg-[var(--surface)] hover:border-[var(--pur)]/40 hover:shadow-md transition cursor-grab active:cursor-grabbing flex flex-col gap-2 relative group"
                        >
                            <div class="flex items-start justify-between gap-2 pr-6">
                                <h5 class="font-bold text-xs text-[var(--text)] leading-snug" x-text="task.title"></h5>
                                <a :href="'/assistant/tasks/' + task.id + '/edit'" class="p-1 rounded-lg text-[var(--text-secondary)] hover:text-[var(--pur)] hover:bg-[var(--pur)]/10 absolute right-3 top-3 opacity-0 group-hover:opacity-100 transition-opacity" title="Edit task">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                            <p x-show="task.description" class="text-[10px] text-[var(--text-secondary)] font-medium leading-relaxed truncate" x-text="task.description"></p>
                            <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-[var(--border)]/40">
                                <span :class="{
                                    'px-2 py-0.5 text-[8px] font-extrabold uppercase rounded-full': true,
                                    'bg-rose-500/10 text-rose-400 border border-rose-500/20': task.priority === 'high',
                                    'bg-amber-500/10 text-amber-400 border border-amber-500/20': task.priority === 'medium',
                                    'bg-slate-500/10 text-slate-400 border border-slate-500/20': task.priority === 'low'
                                }" x-text="task.priority"></span>
                                <div class="flex items-center gap-1 text-[9px] text-[var(--text-muted)] font-semibold">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span x-text="task.due_at ? new Date(task.due_at).toLocaleDateString('en-US', {month:'short', day:'numeric'}) : 'No date'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Column 2: In Progress -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-[var(--border)] pb-2 mb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shadow-[0_0_8px_rgba(245,158,11,0.6)]"></span>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[var(--text)]">In Progress</h4>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[var(--bg2)] border border-[var(--border)] text-[var(--text-secondary)]" x-text="tasks.filter(t => t.status === 'in_progress').length"></span>
                </div>
                <div 
                    @dragover.prevent 
                    @drop="dropTask('in_progress')" 
                    class="flex flex-col gap-3 min-h-[400px] p-2.5 rounded-2xl bg-[var(--bg2)]/30 border border-dashed border-[var(--border)] transition-colors hover:bg-[var(--bg2)]/50"
                >
                    <template x-for="task in tasks.filter(t => t.status === 'in_progress')" :key="task.id">
                        <div 
                            draggable="true" 
                            @dragstart="dragStart($event, task.id)" 
                            class="p-4 rounded-xl border border-[var(--border)] bg-[var(--surface)] hover:border-[var(--pur)]/40 hover:shadow-md transition cursor-grab active:cursor-grabbing flex flex-col gap-2 relative group"
                        >
                            <div class="flex items-start justify-between gap-2 pr-6">
                                <h5 class="font-bold text-xs text-[var(--text)] leading-snug" x-text="task.title"></h5>
                                <a :href="'/assistant/tasks/' + task.id + '/edit'" class="p-1 rounded-lg text-[var(--text-secondary)] hover:text-[var(--pur)] hover:bg-[var(--pur)]/10 absolute right-3 top-3 opacity-0 group-hover:opacity-100 transition-opacity" title="Edit task">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                            <p x-show="task.description" class="text-[10px] text-[var(--text-secondary)] font-medium leading-relaxed truncate" x-text="task.description"></p>
                            <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-[var(--border)]/40">
                                <span :class="{
                                    'px-2 py-0.5 text-[8px] font-extrabold uppercase rounded-full': true,
                                    'bg-rose-500/10 text-rose-400 border border-rose-500/20': task.priority === 'high',
                                    'bg-amber-500/10 text-amber-400 border border-amber-500/20': task.priority === 'medium',
                                    'bg-slate-500/10 text-slate-400 border border-slate-500/20': task.priority === 'low'
                                }" x-text="task.priority"></span>
                                <div class="flex items-center gap-1 text-[9px] text-[var(--text-muted)] font-semibold">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span x-text="task.due_at ? new Date(task.due_at).toLocaleDateString('en-US', {month:'short', day:'numeric'}) : 'No date'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Column 3: Completed -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-[var(--border)] pb-2 mb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shadow-[0_0_8px_rgba(16,185,129,0.6)]"></span>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[var(--text)]">Completed</h4>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[var(--bg2)] border border-[var(--border)] text-[var(--text-secondary)]" x-text="tasks.filter(t => t.status === 'completed').length"></span>
                </div>
                <div 
                    @dragover.prevent 
                    @drop="dropTask('completed')" 
                    class="flex flex-col gap-3 min-h-[400px] p-2.5 rounded-2xl bg-[var(--bg2)]/30 border border-dashed border-[var(--border)] transition-colors hover:bg-[var(--bg2)]/50"
                >
                    <template x-for="task in tasks.filter(t => t.status === 'completed')" :key="task.id">
                        <div 
                            draggable="true" 
                            @dragstart="dragStart($event, task.id)" 
                            class="p-4 rounded-xl border border-[var(--border)] bg-[var(--surface)] hover:border-[var(--pur)]/40 hover:shadow-md transition cursor-grab active:cursor-grabbing flex flex-col gap-2 relative group opacity-75 hover:opacity-100"
                        >
                            <div class="flex items-start justify-between gap-2 pr-6">
                                <h5 class="font-bold text-xs text-[var(--text)] leading-snug line-through opacity-70" x-text="task.title"></h5>
                                <a :href="'/assistant/tasks/' + task.id + '/edit'" class="p-1 rounded-lg text-[var(--text-secondary)] hover:text-[var(--pur)] hover:bg-[var(--pur)]/10 absolute right-3 top-3 opacity-0 group-hover:opacity-100 transition-opacity" title="Edit task">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                            <p x-show="task.description" class="text-[10px] text-[var(--text-secondary)] font-medium leading-relaxed truncate line-through opacity-60" x-text="task.description"></p>
                            <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-[var(--border)]/40">
                                <span class="px-2 py-0.5 text-[8px] font-extrabold uppercase rounded-full bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">Success</span>
                                <div class="flex items-center gap-1 text-[9px] text-[var(--text-muted)] font-semibold">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span x-text="task.due_at ? new Date(task.due_at).toLocaleDateString('en-US', {month:'short', day:'numeric'}) : 'No date'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>

            <!-- Column 4: Cancelled -->
            <div class="flex flex-col gap-4">
                <div class="flex items-center justify-between border-b border-[var(--border)] pb-2 mb-1">
                    <div class="flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-rose-500 shadow-[0_0_8px_rgba(239,68,68,0.6)]"></span>
                        <h4 class="font-bold text-xs uppercase tracking-wider text-[var(--text)]">Cancelled</h4>
                    </div>
                    <span class="text-[10px] font-bold px-2 py-0.5 rounded-md bg-[var(--bg2)] border border-[var(--border)] text-[var(--text-secondary)]" x-text="tasks.filter(t => t.status === 'cancelled').length"></span>
                </div>
                <div 
                    @dragover.prevent 
                    @drop="dropTask('cancelled')" 
                    class="flex flex-col gap-3 min-h-[400px] p-2.5 rounded-2xl bg-[var(--bg2)]/30 border border-dashed border-[var(--border)] transition-colors hover:bg-[var(--bg2)]/50"
                >
                    <template x-for="task in tasks.filter(t => t.status === 'cancelled')" :key="task.id">
                        <div 
                            draggable="true" 
                            @dragstart="dragStart($event, task.id)" 
                            class="p-4 rounded-xl border border-[var(--border)] bg-[var(--surface)] hover:border-[var(--pur)]/40 hover:shadow-md transition cursor-grab active:cursor-grabbing flex flex-col gap-2 relative group opacity-60 hover:opacity-90"
                        >
                            <div class="flex items-start justify-between gap-2 pr-6">
                                <h5 class="font-bold text-xs text-[var(--text)] leading-snug line-through opacity-60" x-text="task.title"></h5>
                                <a :href="'/assistant/tasks/' + task.id + '/edit'" class="p-1 rounded-lg text-[var(--text-secondary)] hover:text-[var(--pur)] hover:bg-[var(--pur)]/10 absolute right-3 top-3 opacity-0 group-hover:opacity-100 transition-opacity" title="Edit task">
                                    <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" /></svg>
                                </a>
                            </div>
                            <p x-show="task.description" class="text-[10px] text-[var(--text-secondary)] font-medium leading-relaxed truncate line-through opacity-50" x-text="task.description"></p>
                            <div class="flex items-center justify-between gap-2 mt-2 pt-2 border-t border-[var(--border)]/40">
                                <span class="px-2 py-0.5 text-[8px] font-extrabold uppercase rounded-full bg-rose-500/10 text-rose-400 border border-rose-500/20">Cancelled</span>
                                <div class="flex items-center gap-1 text-[9px] text-[var(--text-muted)] font-semibold">
                                    <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                                    <span x-text="task.due_at ? new Date(task.due_at).toLocaleDateString('en-US', {month:'short', day:'numeric'}) : 'No date'"></span>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
            
        </div>

        <!-- LIST VIEW -->
        <div x-show="viewMode === 'list'" class="space-y-4 mb-8" style="display: none;">
            <x-ui.table-shell>
                <thead>
                    <tr>
                        <th class="py-3 font-semibold text-slate-500 text-left">Task Description</th>
                        <th class="py-3 font-semibold text-slate-500 text-center">Priority</th>
                        <th class="py-3 font-semibold text-slate-500 text-center">Status</th>
                        <th class="py-3 font-semibold text-slate-500 text-left">Due Date</th>
                        <th class="py-3 font-semibold text-slate-500 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-if="tasks.length === 0">
                        <tr>
                            <td colspan="5">
                                <x-ui.empty-state title="No tasks scheduled yet">
                                    <x-slot name="action">
                                        <x-ui.button :href="route('assistant.tasks.create')" variant="primary" size="sm">Create task</x-ui.button>
                                    </x-slot>
                                </x-ui.empty-state>
                            </td>
                        </tr>
                    </template>
                    
                    <template x-for="task in tasks" :key="task.id">
                        <tr class="border-b border-[var(--border)] hover:bg-[var(--bg2)]/30 transition-colors">
                            <td class="py-4">
                                <div class="flex items-start gap-3">
                                    <div 
                                        @click="const s = task.status === 'completed' ? 'pending' : 'completed'; $data.draggedTaskId = task.id; $data.dropTask(s);"
                                        class="w-4.5 h-4.5 mt-0.5 rounded-md border border-[var(--border)] flex items-center justify-center shrink-0 text-white bg-[var(--surface)]/40 cursor-pointer hover:border-[var(--pur)] transition-colors"
                                        :class="task.status === 'completed' ? '!bg-[var(--pur)] !border-[var(--pur)]' : ''"
                                    >
                                        <template x-if="task.status === 'completed'">
                                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        </template>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-bold text-[var(--text)] text-xs" :class="task.status === 'completed' ? 'line-through text-[var(--text-secondary)] opacity-60' : ''" x-text="task.title"></p>
                                        <p x-show="task.description" class="text-[10px] text-[var(--text-secondary)] font-semibold mt-0.5 truncate max-w-[200px] sm:max-w-md" x-text="task.description"></p>
                                    </div>
                                </div>
                            </td>
                            <td class="py-4 text-center">
                                <span :class="{
                                    'px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-md border': true,
                                    'bg-rose-500/10 text-rose-400 border-rose-500/20': task.priority === 'high',
                                    'bg-amber-500/10 text-amber-400 border-amber-500/20': task.priority === 'medium',
                                    'bg-slate-500/10 text-slate-400 border-slate-500/20': task.priority === 'low'
                                }" x-text="task.priority"></span>
                            </td>
                            <td class="py-4 text-center">
                                <span :class="{
                                    'px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-md border': true,
                                    'bg-emerald-500/10 text-emerald-400 border-emerald-500/20': task.status === 'completed',
                                    'bg-violet-500/10 text-violet-400 border-violet-500/20': task.status === 'pending',
                                    'bg-amber-500/10 text-amber-400 border-amber-500/20': task.status === 'in_progress',
                                    'bg-rose-500/10 text-rose-400 border-rose-500/20': task.status === 'cancelled'
                                }" x-text="task.status.replace('_', ' ')"></span>
                            </td>
                            <td class="py-4 text-[var(--text-muted)] font-medium text-xs" x-text="formatDate(task.due_at)"></td>
                            <td class="py-4">
                                <div class="flex gap-2 justify-center items-center">
                                    <a :href="'/assistant/tasks/' + task.id + '/edit'" class="text-xs font-semibold text-indigo-600 dark:text-indigo-400 hover:opacity-80 transition-opacity">Edit</a>
                                    <span class="text-[var(--border)]">|</span>
                                    <form method="POST" :action="'/assistant/tasks/' + task.id" class="inline">
                                        @csrf @method('DELETE')
                                        <x-ui.button type="submit" variant="link" size="sm" class="!text-rose-600 dark:!text-rose-400" onclick="return confirm('Delete task?')">Delete</x-ui.button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </x-ui.table-shell>
        </div>
    </div>

    <script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('taskManager', () => ({
            tasks: JSON.parse(document.getElementById('tasks-data').textContent),
            viewMode: localStorage.getItem('taskViewMode') || 'kanban',
            draggedTaskId: null,
            
            init() {
                window.addEventListener('task-updated', () => {
                    window.location.reload();
                });
            },
            
            setViewMode(mode) {
                this.viewMode = mode;
                localStorage.setItem('taskViewMode', mode);
            },
            
            dragStart(event, taskId) {
                this.draggedTaskId = taskId;
                event.dataTransfer.effectAllowed = 'move';
                event.dataTransfer.setData('text/plain', taskId);
            },
            
            async dropTask(status) {
                if (!this.draggedTaskId) return;
                const taskId = this.draggedTaskId;
                this.draggedTaskId = null;
                
                const taskIndex = this.tasks.findIndex(t => t.id == taskId);
                if (taskIndex === -1) return;
                
                const originalStatus = this.tasks[taskIndex].status;
                this.tasks[taskIndex].status = status;
                
                try {
                    const response = await fetch(`/assistant/tasks/${taskId}`, {
                        method: 'PATCH',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                        },
                        body: JSON.stringify({ status: status })
                    });
                    
                    if (!response.ok) {
                        this.tasks[taskIndex].status = originalStatus;
                        alert('Failed to update task status.');
                    }
                } catch (error) {
                    this.tasks[taskIndex].status = originalStatus;
                    console.error('Error updating task:', error);
                }
            },

            formatDate(dateStr) {
                if (!dateStr) return 'No deadline';
                const date = new Date(dateStr);
                return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + 
                       ' at ' + 
                       date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
            }
        }));
    });
    </script>
</x-ui.page>
