<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'Synapse') }}</title>

        <!-- Theme Switcher Initializer to block light flashes (Default to Dark) -->
        <script>
            if (localStorage.theme === 'light') {
                document.documentElement.classList.remove('dark');
            } else {
                document.documentElement.classList.add('dark');
            }
        </script>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.googleapis.com">
        <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
        <link href="https://fonts.googleapis.com/css2?family=Space+Grotesk:wght@300;400;500;600;700&display=swap" rel="stylesheet">

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        <!-- SweetAlert2 library and global decorator -->
        <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
        <script>
            window.alert = function(message) {
                const isDark = document.documentElement.classList.contains('dark');
                const msgLower = message.toLowerCase();
                let icon = 'info';
                
                if (msgLower.includes('failed') || msgLower.includes('error')) {
                    icon = 'error';
                } else if (msgLower.includes('success') || msgLower.includes('rescheduled') || msgLower.includes('cancelled') || msgLower.includes('recorded') || msgLower.includes('updated') || msgLower.includes('deleted') || msgLower.includes('created')) {
                    icon = 'success';
                }

                Swal.fire({
                    text: message,
                    icon: icon,
                    background: isDark ? '#12121f' : '#ffffff',
                    color: isDark ? '#f3f4f6' : '#1f2937',
                    customClass: {
                        popup: 'border border-[var(--border)] rounded-2xl shadow-2xl font-sans',
                        confirmButton: 'px-5 py-2.5 text-xs font-bold rounded-xl bg-[var(--pur)] text-white hover:opacity-90 transition-opacity focus:outline-none'
                    },
                    buttonsStyling: false
                });
            };
        </script>
    </head>
    <body x-data="{ sidebarOpen: false }" class="font-sans antialiased text-[var(--text)] transition-colors duration-300 overflow-x-hidden min-h-screen relative">
        <div class="aur aur-a top-[-200px] left-[-200px]"></div>
        <div class="aur aur-b bottom-[-200px] right-[-200px]"></div>
        <div class="min-h-screen flex flex-col relative z-10">
            <!-- Sidebar -->
            @include('layouts.navigation')

            <!-- Main Layout Viewport -->
            <div class="lg:pl-64 flex flex-col flex-1 min-h-screen">
                <!-- Mobile top navigation bar -->
                <header class="flex lg:hidden items-center justify-between h-16 bg-white/80 dark:bg-slate-900/80 backdrop-blur-md border-b border-slate-200/80 dark:border-slate-800 px-5 sticky top-0 z-30 transition-colors duration-300">
                    <button @click="sidebarOpen = true" class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                        </svg>
                    </button>
                    
                    <a href="{{ route('dashboard') }}" class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold shadow-sm">
                            S
                        </div>
                        <span class="font-bold text-slate-900 dark:text-white tracking-tight text-sm">Synapse</span>
                    </a>

                    <!-- Mobile Theme Toggler Button -->
                    <button onclick="toggleTheme()" class="p-1.5 rounded-xl border border-slate-200 dark:border-slate-800 text-slate-600 dark:text-slate-300 hover:bg-slate-50 dark:hover:bg-slate-800 focus:outline-none">
                        <!-- Sun Icon (shows in dark mode) -->
                        <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                        </svg>
                        <!-- Moon Icon (shows in light mode) -->
                        <svg class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                        </svg>
                    </button>
                </header>

                <!-- Page Header -->
                @isset($header)
                    <div class="bg-white/40 dark:bg-slate-900/10 border-b border-slate-100 dark:border-slate-800/60 py-5 px-5 sm:px-8 lg:px-10 backdrop-blur-md transition-colors duration-300">
                        {{ $header }}
                    </div>
                @endisset

                <!-- Main Content Area -->
                <main class="flex-1 px-5 sm:px-8 lg:px-10 py-6">
                    {{ $slot }}
                </main>
            </div>
        </div>

        <!-- Floating Theme Switcher Button -->
        <div class="fixed bottom-6 right-6 z-50">
            <button onclick="toggleTheme()" class="w-12 h-12 rounded-full bg-[var(--surface)]/80 border border-[var(--border)] text-[var(--text)] hover:text-[var(--pur)] shadow-[0_8px_30px_rgba(0,0,0,0.08)] dark:shadow-[0_8px_30px_rgba(0,0,0,0.4)] flex items-center justify-center transition-all hover:scale-110 active:scale-95 focus:outline-none backdrop-blur-md" title="Toggle Light/Dark Mode">
                <!-- Sun Icon (shows in dark mode) -->
                <svg class="w-5 h-5 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                </svg>
                <!-- Moon Icon (shows in light mode) -->
                <svg class="w-5 h-5 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                </svg>
            </button>
        </div>

        <!-- Global Task Alert Modal (Alpine.js) -->
        <div x-data="taskAlertManager()" x-show="open" class="fixed inset-0 z-50 flex items-center justify-center p-4" style="display: none;" x-cloak>
            <!-- Backdrop -->
            <div class="fixed inset-0 bg-slate-955/70 backdrop-blur-sm" @click="closeModal"></div>

            <!-- Modal Content Card -->
            <div class="bg-[var(--bg2)] border border-[var(--border)] rounded-2xl w-full max-w-md shadow-2xl relative z-50 overflow-hidden transform transition-all p-6 flex flex-col gap-4">
                
                <!-- Header with alert icon -->
                <div class="flex items-center gap-3 border-b border-[var(--border)] pb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-500/10 text-rose-400 border border-rose-500/20 flex items-center justify-center animate-pulse shrink-0">
                        <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="font-bold text-sm text-[var(--text)]">Task Deadline Alert!</h3>
                        <p class="text-[10px] text-[var(--text-muted)] font-medium">Task is approaching its scheduled deadline</p>
                    </div>
                </div>

                <!-- Body details -->
                <div class="space-y-3 py-1">
                    <div class="bg-[var(--surface)] border border-[var(--border)] p-4 rounded-xl flex flex-col gap-2">
                        <span class="text-[9px] font-extrabold uppercase tracking-widest text-rose-400" x-text="'Due in ' + activeAlert.minutes_remaining + ' minutes!'"></span>
                        <h4 class="font-bold text-sm text-[var(--text)]" x-text="activeAlert.title"></h4>
                        <span class="text-[10px] text-[var(--text-secondary)] font-semibold" x-text="'Deadline: ' + formatDate(activeAlert.due_at)"></span>
                    </div>

                    <!-- Reschedule Input Picker -->
                    <div x-show="showRescheduleInput" class="space-y-2 mt-2 pt-2 border-t border-[var(--border)]/40" style="display: none;">
                        <label class="block text-[9px] font-bold uppercase tracking-wider text-[var(--text-secondary)]">Select Custom Date & Time</label>
                        <div class="flex gap-2">
                            <input type="datetime-local" x-model="customDueAt" class="auth-input flex-1 !py-1.5 !text-xs" :min="getMinDateTime()">
                            <button @click="submitReschedule" class="px-4 py-1.5 text-xs font-bold rounded-xl bg-[var(--pur)] text-white hover:opacity-90 transition-opacity">
                                Confirm
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Action Button Control Grid -->
                <div class="grid grid-cols-3 gap-2.5 pt-1.5">
                    <!-- Reschedule -->
                    <button @click="showRescheduleInput = !showRescheduleInput" class="px-3 py-2.5 text-[10px] font-bold rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--text)] hover:bg-[var(--bg3)]/50 hover:text-[var(--pur)] transition-colors focus:outline-none flex flex-col items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0 text-[var(--text-secondary)]" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" /></svg>
                        <span>Reschedule</span>
                    </button>

                    <!-- Auto-Reschedule -->
                    <button @click="triggerAutoReschedule" class="px-3 py-2.5 text-[10px] font-bold rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--text)] hover:bg-[var(--bg3)]/50 hover:text-[var(--pur)] transition-colors focus:outline-none flex flex-col items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0 text-emerald-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" /></svg>
                        <span>Auto-Resched</span>
                    </button>

                    <!-- Cancel -->
                    <button @click="triggerCancelTask" class="px-3 py-2.5 text-[10px] font-bold rounded-xl border border-[var(--border)] bg-[var(--surface)] text-[var(--text)] hover:bg-rose-500/10 hover:text-rose-400 transition-colors focus:outline-none flex flex-col items-center justify-center gap-1.5">
                        <svg class="w-4 h-4 shrink-0 text-rose-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                        <span>Cancel Task</span>
                    </button>
                </div>

                <!-- Close / Dismiss x -->
                <button @click="closeModal" class="absolute top-4 right-4 text-[var(--text-secondary)] hover:text-[var(--text)] transition-colors">
                    <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" /></svg>
                </button>
            </div>
        </div>

        <script>
        function taskAlertManager() {
            return {
                open: false,
                activeAlert: {},
                showRescheduleInput: false,
                customDueAt: '',
                pollingInterval: null,
                
                init() {
                    // Check alert on load
                    setTimeout(() => {
                        this.checkAlerts();
                    }, 1000);

                    // Poll every 30 seconds
                    this.pollingInterval = setInterval(() => {
                        this.checkAlerts();
                    }, 30000);
                },

                async checkAlerts() {
                    if (this.open) return;

                    try {
                        const response = await fetch('/assistant/tasks/upcoming-alerts');
                        if (!response.ok) return;

                        const data = await response.json();
                        if (data && data.length > 0) {
                            this.activeAlert = data[0];
                            this.customDueAt = '';
                            this.showRescheduleInput = false;
                            this.open = true;
                        }
                    } catch (error) {
                        console.error('Error fetching upcoming task alerts:', error);
                    }
                },

                closeModal() {
                    this.open = false;
                    this.activeAlert = {};
                },

                getMinDateTime() {
                    const now = new Date();
                    const year = now.getFullYear();
                    const month = String(now.getMonth() + 1).padStart(2, '0');
                    const day = String(now.getDate()).padStart(2, '0');
                    const hours = String(now.getHours()).padStart(2, '0');
                    const minutes = String(now.getMinutes()).padStart(2, '0');
                    return `${year}-${month}-${day}T${hours}:${minutes}`;
                },

                formatDate(dateStr) {
                    if (!dateStr) return '';
                    const date = new Date(dateStr);
                    return date.toLocaleDateString('en-US', { month: 'short', day: 'numeric', year: 'numeric' }) + 
                           ' at ' + 
                           date.toLocaleTimeString('en-US', { hour: 'numeric', minute: '2-digit', hour12: true });
                },

                async triggerAutoReschedule() {
                    if (!this.activeAlert.id) return;
                    try {
                        const response = await fetch(`/assistant/tasks/${this.activeAlert.id}/auto-reschedule`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const res = await response.json();
                        if (res.success) {
                            alert(res.message || 'Task auto-rescheduled successfully!');
                            this.closeModal();
                            window.dispatchEvent(new CustomEvent('task-updated'));
                        } else {
                            alert('Failed to auto-reschedule task.');
                        }
                    } catch (error) {
                        console.error('Error in auto-rescheduling:', error);
                    }
                },

                async triggerCancelTask() {
                    if (!this.activeAlert.id) return;
                    if (!confirm(`Cancel task: "${this.activeAlert.title}"?`)) return;

                    try {
                        const response = await fetch(`/assistant/tasks/${this.activeAlert.id}/cancel`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            }
                        });
                        const res = await response.json();
                        if (res.success) {
                            alert(res.message || 'Task cancelled.');
                            this.closeModal();
                            window.dispatchEvent(new CustomEvent('task-updated'));
                        } else {
                            alert('Failed to cancel task.');
                        }
                    } catch (error) {
                        console.error('Error cancelling task:', error);
                    }
                },

                async submitReschedule() {
                    if (!this.customDueAt) {
                        alert('Please select a date and time.');
                        return;
                    }
                    try {
                        const response = await fetch(`/assistant/tasks/${this.activeAlert.id}/reschedule-to`, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                            },
                            body: JSON.stringify({ due_at: this.customDueAt })
                        });
                        const res = await response.json();
                        if (res.success) {
                            alert(res.message || 'Task rescheduled.');
                            this.closeModal();
                            window.dispatchEvent(new CustomEvent('task-updated'));
                        } else {
                            alert('Failed to reschedule task.');
                        }
                    } catch (error) {
                        console.error('Error rescheduling task:', error);
                    }
                }
            }
        }
        </script>

        <!-- Global Theme Toggle Script -->
        <script>
            function toggleTheme() {
                if (document.documentElement.classList.contains('dark')) {
                    document.documentElement.classList.remove('dark');
                    localStorage.theme = 'light';
                } else {
                    document.documentElement.classList.add('dark');
                    localStorage.theme = 'dark';
                }
                window.dispatchEvent(new Event('theme-changed'));
            }
        </script>
    </body>
</html>
