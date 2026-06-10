@php
    $unreadCount = auth()->check() ? auth()->user()->unreadNotifications->count() : 0;

    $navGroups = [
        'general' => [
            'label' => 'General',
            'items' => [
                [
                    'name' => 'Dashboard',
                    'route' => 'dashboard',
                    'active' => request()->routeIs('dashboard'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2H6a2 2 0 01-2-2v-4zM14 16a2 2 0 012-2h2a2 2 0 012 2v4a2 2 0 01-2 2h-2a2 2 0 01-2-2v-4z" />'
                ],
                [
                    'name' => 'Calendar',
                    'route' => 'calendar.index',
                    'active' => request()->routeIs('calendar.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />'
                ],
                [
                    'name' => 'Notifications',
                    'route' => 'notifications.index',
                    'active' => request()->routeIs('notifications.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />',
                    'badge' => true
                ]
            ]
        ],
        'assistant' => [
            'label' => 'AI Assistant',
            'items' => [
                [
                    'name' => 'AI Chat Workspace',
                    'route' => 'assistant.chat',
                    'active' => request()->routeIs('assistant.chat'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />'
                ],
                [
                    'name' => 'Tasks List',
                    'route' => 'assistant.tasks.index',
                    'active' => request()->routeIs('assistant.tasks.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />'
                ]
            ]
        ],
        'accounting' => [
            'label' => 'Financials',
            'items' => [
                [
                    'name' => 'Transactions',
                    'route' => 'accounting.transactions.index',
                    'active' => request()->routeIs('accounting.transactions.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />'
                ],
                [
                    'name' => 'Budgets Tracker',
                    'route' => 'accounting.budgets.index',
                    'active' => request()->routeIs('accounting.budgets.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />'
                ],
                [
                    'name' => 'Financial Reports',
                    'route' => 'accounting.reports.index',
                    'active' => request()->routeIs('accounting.reports.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H3a2 2 0 01-2-2V5a2 2 0 012-2h14a2 2 0 012 2v14a2 2 0 01-2 2z" />'
                ]
            ]
        ],
        'distribution' => [
            'label' => 'Content & Distribution',
            'items' => [
                [
                    'name' => 'Media Library',
                    'route' => 'distribution.media.index',
                    'active' => request()->routeIs('distribution.media.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />'
                ],
                [
                    'name' => 'Publish Queue',
                    'route' => 'distribution.publish.index',
                    'active' => request()->routeIs('distribution.publish.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />'
                ]
            ]
        ]
    ];

    if (auth()->user()->hasRole('admin')) {
        $navGroups['admin'] = [
            'label' => 'Administration',
            'items' => [
                [
                    'name' => 'Manage Users',
                    'route' => 'admin.users.index',
                    'active' => request()->routeIs('admin.users.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'
                ],
                [
                    'name' => 'Access Roles',
                    'route' => 'admin.roles.index',
                    'active' => request()->routeIs('admin.roles.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />'
                ]
            ]
        ];
    }

    if (auth()->user()->hasRole('superadmin')) {
        $navGroups['superadmin'] = [
            'label' => 'Super Admin',
            'items' => [
                [
                    'name' => 'Super Dashboard',
                    'route' => 'superadmin.dashboard',
                    'active' => request()->routeIs('superadmin.dashboard'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z" />'
                ],
                [
                    'name' => 'All Users',
                    'route' => 'superadmin.users.index',
                    'active' => request()->routeIs('superadmin.users.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />'
                ],
                [
                    'name' => 'API Integrations',
                    'route' => 'superadmin.apis.index',
                    'active' => request()->routeIs('superadmin.apis.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 7v10c0 2.21 3.582 4 8 4s8-1.79 8-4V7M4 7c0 2.21 3.582 4 8 4s8-1.79 8-4M4 7c0-2.21 3.582-4 8-4s8 1.79 8 4" />'
                ],
                [
                    'name' => 'Services Status',
                    'route' => 'superadmin.services.index',
                    'active' => request()->routeIs('superadmin.services.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />'
                ],
                [
                    'name' => 'Training Export',
                    'route' => 'superadmin.training.index',
                    'active' => request()->routeIs('superadmin.training.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'
                ],
                [
                    'name' => 'System Logs',
                    'route' => 'superadmin.logs.index',
                    'active' => request()->routeIs('superadmin.logs.*'),
                    'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />'
                ]
            ]
        ];
    }
@endphp

<!-- DESKTOP FIXED SIDEBAR -->
<aside class="hidden lg:flex lg:w-64 lg:flex-col lg:fixed lg:bottom-0 lg:top-0 lg:left-0 bg-[var(--bg2)] border-r border-[var(--border)] z-40 transition-colors duration-300">
    <!-- Brand Header -->
    <div class="flex items-center justify-between h-16 px-6 border-b border-[var(--border)]">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="logo-i">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <span class="font-bold text-[var(--text)] text-lg tracking-tight">Synapse</span>
        </a>
    </div>

    <!-- Navigation Groups -->
    <div class="flex-1 overflow-y-auto px-4 py-6 space-y-7">
        @foreach ($navGroups as $group)
            <div class="space-y-2">
                <span class="px-4 text-[10px] font-bold uppercase tracking-widest text-[var(--text-secondary)] opacity-80">{{ $group['label'] }}</span>
                <nav class="space-y-1">
                    @foreach ($group['items'] as $item)
                        <a href="{{ route($item['route']) }}" class="{{ $item['active'] ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {!! $item['icon'] !!}
                            </svg>
                            <span class="flex-1">{{ $item['name'] }}</span>
                            @if(isset($item['badge']) && $item['badge'] && $unreadCount > 0)
                                <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full bg-rose-500 text-white animate-pulse">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
        @endforeach
    </div>

    <!-- User Profile & Footer Actions -->
    <div class="p-4 border-t border-[var(--border)] bg-[var(--bg3)]/30">
        <div class="flex items-center justify-between gap-3 p-2 rounded-2xl hover:bg-[var(--bg3)]/50 transition-all">
            <a href="{{ route('profile.edit') }}" class="flex items-center gap-2.5 min-w-0">
                <div class="w-9 h-9 rounded-xl bg-[var(--pur)]/20 text-[var(--pur)] flex items-center justify-center font-bold text-sm border border-[var(--pur)]/20">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-[var(--text)] truncate">{{ Auth::user()->name }}</p>
                    <p class="text-[10px] text-[var(--text-muted)] truncate">{{ Auth::user()->email }}</p>
                </div>
            </a>

            <div class="flex items-center gap-1.5 shrink-0">
                <!-- Theme Toggler -->
                <button onclick="toggleTheme()" class="p-2 rounded-xl text-[var(--text-secondary)] hover:text-[var(--pur)] hover:bg-[var(--pur)]/10 transition-colors" title="Toggle Light/Dark Theme">
                    <svg class="w-4 h-4 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                    </svg>
                    <svg class="w-4 h-4 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <!-- Logout button -->
                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-2 rounded-xl text-[var(--text-secondary)] hover:text-rose-400 hover:bg-rose-500/10 transition-colors" title="Log Out">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</aside>

<!-- MOBILE DRAWER DRAWER SIDEBAR -->
<div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 z-40 bg-slate-950/60 backdrop-blur-sm lg:hidden" style="display: none;"></div>

<div x-show="sidebarOpen" 
     @click.away="sidebarOpen = false"
     x-transition:enter="transition ease-in-out duration-300 transform" 
     x-transition:enter-start="-translate-x-full" 
     x-transition:enter-end="translate-x-0" 
     x-transition:leave="transition ease-in-out duration-300 transform" 
     x-transition:leave-start="translate-x-0" 
     x-transition:leave-end="-translate-x-full" 
     class="fixed inset-y-0 left-0 z-50 w-72 bg-[var(--bg2)] border-r border-[var(--border)] flex flex-col p-6 shadow-2xl lg:hidden transition-colors duration-300" style="display: none;">
     
    <!-- Mobile Sidebar Close and Brand Header -->
    <div class="flex items-center justify-between border-b border-[var(--border)] pb-5 mb-5">
        <a href="{{ route('dashboard') }}" class="flex items-center gap-3">
            <div class="logo-i">
                <i class="fa-solid fa-layer-group"></i>
            </div>
            <span class="font-bold text-[var(--text)] text-base tracking-tight">Synapse</span>
        </a>
        <button @click="sidebarOpen = false" class="p-1.5 rounded-xl border border-[var(--border)] text-[var(--text-secondary)] hover:text-[var(--text)] hover:bg-[var(--bg3)]/50 focus:outline-none">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
            </svg>
        </button>
    </div>

    <!-- Navigation List for Mobile -->
    <div class="flex-1 overflow-y-auto space-y-7 pr-1">
        @foreach ($navGroups as $group)
            <div class="space-y-2">
                <span class="text-[9px] font-bold uppercase tracking-widest text-[var(--text-secondary)] opacity-80 px-3">{{ $group['label'] }}</span>
                <nav class="space-y-1">
                    @foreach ($group['items'] as $item)
                        <a href="{{ route($item['route']) }}" @click="sidebarOpen = false" class="{{ $item['active'] ? 'sidebar-link-active' : 'sidebar-link' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                {!! $item['icon'] !!}
                            </svg>
                            <span class="flex-1">{{ $item['name'] }}</span>
                            @if(isset($item['badge']) && $item['badge'] && $unreadCount > 0)
                                <span class="px-2 py-0.5 text-[9px] font-extrabold rounded-full bg-rose-500 text-white animate-pulse">
                                    {{ $unreadCount }}
                                </span>
                            @endif
                        </a>
                    @endforeach
                </nav>
            </div>
        @endforeach
    </div>

    <!-- Mobile profile card -->
    <div class="mt-auto pt-5 border-t border-[var(--border)]">
        <div class="flex items-center justify-between gap-3 bg-[var(--bg3)]/30 p-2.5 rounded-2xl">
            <a href="{{ route('profile.edit') }}" @click="sidebarOpen = false" class="flex items-center gap-2.5 min-w-0">
                <div class="w-8 h-8 rounded-lg bg-[var(--pur)]/20 text-[var(--pur)] flex items-center justify-center font-bold text-xs border border-[var(--pur)]/20">
                    {{ substr(Auth::user()->name, 0, 2) }}
                </div>
                <div class="min-w-0">
                    <p class="text-xs font-semibold text-[var(--text)] truncate">{{ Auth::user()->name }}</p>
                </div>
            </a>
            
            <div class="flex items-center gap-1.5 shrink-0">
                <!-- Theme Toggler -->
                <button onclick="toggleTheme()" class="p-1.5 rounded-lg text-[var(--text-secondary)] hover:text-[var(--pur)] hover:bg-[var(--bg3)]/50 transition-colors" title="Toggle Light/Dark Theme">
                    <svg class="w-4 h-4 hidden dark:block" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364-6.364l-.707.707M6.343 17.657l-.707.707m12.728 0l-.707-.707M6.343 6.343l-.707-.707M12 7a5 5 0 100 10 5 5 0 000-10z" />
                    </svg>
                    <svg class="w-4 h-4 block dark:hidden" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z" />
                    </svg>
                </button>

                <form method="POST" action="{{ route('logout') }}" class="inline">
                    @csrf
                    <button type="submit" class="p-1.5 rounded-lg text-[var(--text-secondary)] hover:text-rose-400 hover:bg-rose-500/10 transition-colors">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1" />
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
