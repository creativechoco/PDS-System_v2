    <nav x-data="{ open: false }" class="bg-white border-b border-gray-100 sticky top-0 z-50 shadow-sm relative">
    <!-- Primary Navigation Menu -->
    <div class="max-w-20xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-20">
            <div class="flex">
                <!-- Logo -->
                <div class="shrink-0 flex items-center gap-2 sm:gap-3 pl-2 sm:pl-4">
                    <img src="{{ asset('images/ph-logo.png') }}" class="h-10 w-auto sm:h-14"/>
                    <img src="{{ asset('images/Bfar logo.png') }}" class="h-10 w-auto sm:h-14"/>
                    <img src="{{ asset('images/gad-logo.jpg') }}" class="h-10 w-auto sm:h-14"/>
                </div>

                <!-- Navigation Links  -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-6 sm:flex">
                    <x-nav-link :href="route('employee.dashboard')" :active="request()->routeIs('employee.dashboard')" class="font-semibold text-xs md:text-base">
                        {{ __('Dashboard') }}
                    </x-nav-link>
                </div>
                
                <!-- PDS form screen -->
                <div class="hidden space-x-8 sm:-my-px sm:ms-6 sm:flex">
                    @if($hasRejectedPds)
                        <x-nav-link :href="route('pds.form1')" :active="request()->routeIs('pds.form*')" class="font-semibold text-xs md:text-base">
                            {{ __('PDS Form') }}
                        </x-nav-link>
                    @elseif($hasSubmittedPds)
                        <x-nav-link :href="route('pds.view')" :active="request()->routeIs('pds.view')" class="font-semibold text-xs md:text-base">
                            {{ __('View PDS') }}
                        </x-nav-link>
                    @else
                        <x-nav-link :href="route('pds.form1')" :active="request()->routeIs('pds.form*')" class="font-semibold text-xs md:text-base">
                            {{ __('PDS Form') }}
                        </x-nav-link>
                    @endif
                </div>

            </div>

            <!-- Settings Dropdown + Notifications -->
            <div class="flex items-center sm:ms-6 gap-2 sm:gap-3">
                @php
                    $unreadCount = Auth::user()->unreadNotifications()->count();
                    $recentNotifications = Auth::user()->notifications()->latest()->take(20)->get();
                @endphp

@php
    $currentUserId = Auth::id();
@endphp
<script>
    window.currentUserId = {{ $currentUserId ?? 'null' }};
</script>

                <!-- Desktop dropdowns -->
                <div class="hidden sm:flex sm:items-center gap-3">
                    <x-dropdown align="right" width="0">
                        <x-slot name="trigger">
                            <button type="button" class="relative inline-flex h-10 w-10 items-center justify-center rounded-full p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                </svg>
                                <span id="notification-badge" data-count="{{ $unreadCount }}" class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-semibold px-1.5 py-0.5 min-w-[18px] {{ $unreadCount > 0 ? '' : 'hidden' }}">{{ $unreadCount }}</span>
                                <span class="sr-only">Notifications</span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="w-[400px] max-w-[95vw] bg-white rounded-xl shadow-lg overflow-hidden max-h-[70vh] flex flex-col">
                                <div class="px-5 py-3 flex items-center justify-between gap-3 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" /><path d="M13.73 21a2 2 0 0 1-3.46 0" /></svg>
                                        <div class="text-base font-semibold text-gray-900 leading-none">Notifications</div>
                                    </div>
                                    @if($unreadCount > 0)
                                        <form method="POST" action="{{ route('notifications.readAll') }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">Mark All as Read</button>
                                        </form>
                                    @endif
                                </div>

                            <div id="notification-list" class="max-h-[55vh] overflow-y-auto" x-on:click.stop>
                            @forelse($recentNotifications as $notification)
                                @php
                                    $data = $notification->data ?? [];
                                    $title = $data['title'] ?? 'Notification';
                                    $message = $data['message'] ?? '';
                                    $link = $data['link'] ?? null;
                                    $isUnread = is_null($notification->read_at);
                                    $pill = $isUnread ? 'bg-indigo-600' : 'bg-gray-300';
                                    $initial = strtoupper(mb_substr($data['name'] ?? $title ?? 'N', 0, 1));
                                @endphp
                                    <div class="px-6 py-5 {{ $isUnread ? 'bg-indigo-50' : 'bg-white' }} hover:bg-indigo-50/70 transition" data-notification-id="{{ $notification->id }}">
                                        <div class="flex items-start gap-4">
                                            <div class="flex-1 min-w-0">
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="text-sm font-semibold text-gray-900 leading-snug">{{ $title }}</div>
                                                    <span class="text-[11px] text-gray-400 shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                                                </div>
                                                <div class="flex items-start justify-between gap-3">
                                                    <div class="text-xs text-gray-600 mt-1 leading-relaxed">{!! nl2br(e($message)) !!}</div>
                                                    <div class="mt-3 flex items-center gap-4 text-xs font-semibold">
                                                        @if($link)
                                                            <a href="{{ $link }}" onclick="return viewNotification(event, '{{ $notification->id }}', '{{ $link }}')" class="text-indigo-600 hover:text-indigo-800">View</a>
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @empty
                                    <div class="px-6 py-8 text-sm text-gray-500 text-center">No notifications yet.</div>
                                @endforelse
                                </div>
                            </div>
                        </x-slot>
                    </x-dropdown>

                    <x-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <button class="inline-flex h-10 items-center gap-2 px-3 border-transparent text-md leading-4 font-medium rounded-md text-gray-500 bg-white hover:text-gray-700 focus:outline-none transition ease-in-out duration-150">
                                <span class="truncate max-w-[160px]">{{ Auth::user()->name }}</span>
                                <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                </svg>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <x-dropdown-link :href="route('profile.edit')">
                                {{ __('Profile') }}
                            </x-dropdown-link>

                            <!-- Authentication -->
                            <form method="POST" action="{{ route('logout', [], false) }}">
                                @csrf

                                <x-dropdown-link :href="route('logout', [], false)"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Log Out') }}
                                </x-dropdown-link>
                            </form>
                        </x-slot>
                    </x-dropdown>
                </div>

                <!-- Mobile notification + hamburger + user name -->
                <div class="flex items-center gap-2 sm:hidden">
                    <span class="max-w-[140px] truncate text-sm font-semibold text-gray-700">{{ Auth::user()->name }}</span>
                    <x-dropdown align="right" width="0">
                        <x-slot name="trigger">
                            <button type="button" class="relative inline-flex items-center justify-center rounded-full p-2 text-gray-500 hover:text-indigo-600 hover:bg-indigo-50 focus:outline-none focus:ring-2 focus:ring-indigo-200">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                    <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" />
                                    <path d="M13.73 21a2 2 0 0 1-3.46 0" />
                                </svg>
                                <span id="notification-badge-mobile" data-count="{{ $unreadCount }}" class="absolute -top-1 -right-1 inline-flex items-center justify-center rounded-full bg-red-500 text-white text-[10px] font-semibold px-1.5 py-0.5 min-w-[18px] {{ $unreadCount > 0 ? '' : 'hidden' }}">{{ $unreadCount }}</span>
                                <span class="sr-only">Notifications</span>
                            </button>
                        </x-slot>

                        <x-slot name="content">
                            <div class="w-[80vw] max-w-[96vw] sm:w-full sm:max-w-[92vw] bg-white rounded-xl shadow-lg overflow-hidden max-h-[70vh] flex flex-col">
                                <div class="px-4 py-3 flex items-center justify-between gap-3 border-b border-gray-100">
                                    <div class="flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 text-indigo-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9" /><path d="M13.73 21a2 2 0 0 1-3.46 0" /></svg>
                                        <div class="text-base font-semibold text-gray-900">Notifications</div>
                                    </div>
                                    @if($unreadCount > 0)
                                        <form method="POST" action="{{ route('notifications.readAll') }}">
                                            @csrf
                                            <button type="submit" class="text-xs text-indigo-600 hover:text-indigo-800 font-semibold">Mark All as Read</button>
                                        </form>
                                    @endif
                                </div>

                                <div id="notification-list-mobile" class="max-h-[55vh] sm:max-h-[60vh] overflow-y-auto" x-on:click.stop>
                                @forelse($recentNotifications as $notification)
                                    @php
                                        $data = $notification->data ?? [];
                                        $title = $data['title'] ?? 'Notification';
                                        $message = $data['message'] ?? '';
                                        $link = $data['link'] ?? null;
                                        $isUnread = is_null($notification->read_at);
                                    @endphp
                                        <div class="px-5 py-4 {{ $isUnread ? 'bg-indigo-50' : 'bg-white' }} hover:bg-indigo-50/70 transition" data-notification-id="{{ $notification->id }}">
                                            <div class="flex items-start gap-3">
                                                <div class="flex-1 min-w-0">
                                                    <div class="flex items-start justify-between gap-2">
                                                        <div class="text-sm font-semibold text-gray-900 leading-snug">{{ $title }}</div>
                                                        <span class="text-[11px] text-gray-400 shrink-0">{{ $notification->created_at->diffForHumans() }}</span>
                                                    </div>
                                                    <div class="flex items-start justify-between gap-3">
                                                        <div class="text-xs text-gray-600 mt-1 leading-relaxed">{{ $message }}</div>
                                                        <div class="mt-2 flex items-center gap-3 text-xs font-semibold">
                                                            @if($link)
                                                                <a href="{{ $link }}" onclick="return viewNotification(event, '{{ $notification->id }}', '{{ $link }}')" class="text-indigo-600 hover:text-indigo-800">View</a>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @empty
                                    <div class="px-5 py-6 text-sm text-gray-500 text-center">No notifications yet.</div>
                                @endforelse
                                </div>
                            </div>
                        </x-slot>
                    </x-dropdown>

                    <!-- Hamburger -->
                    <div class="-me-2 flex items-center">
                        <button @click="open = ! open" class="inline-flex items-center justify-center p-2 rounded-md text-gray-400 hover:text-gray-500 hover:bg-gray-100 focus:outline-none focus:bg-gray-100 focus:text-gray-500 transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': open, 'inline-flex': ! open }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! open, 'inline-flex': open }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div x-cloak x-show="open" x-transition.origin.top.right @click.outside="open = false" class="sm:hidden absolute right-3 top-16 w-64 bg-white shadow-xl rounded-2xl border border-gray-100 overflow-hidden">
        <div class="pt-2 pb-3 space-y-1">
            <x-responsive-nav-link :href="route('employee.dashboard')" :active="request()->routeIs('employee.dashboard')">
                {{ __('Dashboard') }}
            </x-responsive-nav-link>
            @if($hasRejectedPds)
                <x-responsive-nav-link :href="route('pds.form1')" :active="request()->routeIs('pds.form*')">
                    {{ __('PDS Form') }}
                </x-responsive-nav-link>
            @elseif($hasSubmittedPds)
                <x-responsive-nav-link :href="route('pds.view')" :active="request()->routeIs('pds.view')">
                    {{ __('View PDS') }}
                </x-responsive-nav-link>
            @else
                <x-responsive-nav-link :href="route('pds.form1')" :active="request()->routeIs('pds.form*')">
                    {{ __('PDS Form') }}
                </x-responsive-nav-link>
            @endif
        </div>

        <!-- Responsive Settings Options -->
        <div class="pt-1 pb-1 border-t border-gray-200">
            <div class="mt-1 space-y-1">
                <x-responsive-nav-link :href="route('profile.edit')">
                    {{ __('Profile') }}
                </x-responsive-nav-link>

                <!-- Authentication -->
                <form method="POST" action="{{ route('logout') }}">
                    @csrf

                    <x-responsive-nav-link :href="route('logout')"
                            onclick="event.preventDefault();
                                        this.closest('form').submit();">
                        {{ __('Log Out') }}
                    </x-responsive-nav-link>
                </form>
            </div>
        </div>
    </div>
</nav>
