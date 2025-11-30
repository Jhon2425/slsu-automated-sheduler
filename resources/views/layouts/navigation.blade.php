<!-- resources/views/layouts/navigation.blade.php -->

@if(auth()->check()) {{-- Prevent errors when user is not logged in --}}
<nav x-data="{ mobileOpen: false, userOpen: false }" class="bg-[#0C3B2E] px-4 py-3 shadow-lg relative">
    <div class="max-w-7xl mx-auto flex items-center justify-between gap-4">

        <!-- Left: Logo -->
        <div class="flex items-center space-x-3">
            <a href="{{ route('admin.dashboard') }}" class="flex items-center space-x-3 group">
                <div class="relative">
                    <img src="{{ asset('slsu-logo.png') }}" alt="Logo" class="h-10 w-10 rounded-full transition-transform duration-300 group-hover:scale-110 group-hover:rotate-6">
                    <div class="absolute inset-0 rounded-full bg-[#fae209] opacity-0 group-hover:opacity-20 blur-md transition-opacity duration-300"></div>
                </div>
                <div class="flex flex-col leading-tight">
                    <p class="font-bold text-lg mb-0 transition-colors duration-300" style="color: #fae209ee;"> 
                        South Luzon State University -
                    </p>
                    <p class="font-semibold text-sm mb-0 transition-colors duration-300" style="color: #e7d954a3;"> 
                        Tiaong Campus
                    </p>
                </div>
            </a>
        </div>

        <!-- Center: Tabs (desktop) -->
        <div class="hidden md:flex md:items-center md:space-x-2 flex-1 justify-center relative">
            <a href="{{ route('admin.faculties.index') }}"
               class="relative px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 overflow-hidden group
                      {{ request()->routeIs('admin.faculties.*') ? 'bg-white/10 text-white shadow-md' : 'text-white/80 hover:text-white' }}">
                <span class="relative z-10">Manage Faculties</span>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                @if(request()->routeIs('admin.faculties.*'))
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#fae209]"></div>
                @endif
            </a>

            <a href="{{ route('admin.schedules.index') }}"
               class="relative px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 overflow-hidden group
                      {{ request()->routeIs('admin.schedules.index') || request()->routeIs('admin.schedules.*') ? 'bg-white/10 text-white shadow-md' : 'text-white/80 hover:text-white' }}">
                <span class="relative z-10">View Schedules</span>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                @if(request()->routeIs('admin.schedules.index') || request()->routeIs('admin.schedules.*'))
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#fae209]"></div>
                @endif
            </a>

            <a href="{{ route('admin.schedules.generate') }}"
               class="relative px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 overflow-hidden group
                      {{ request()->routeIs('admin.schedules.generate') ? 'bg-white/10 text-white shadow-md' : 'text-white/80 hover:text-white' }}">
                <span class="relative z-10">Generate Schedule</span>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                @if(request()->routeIs('admin.schedules.generate'))
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#fae209]"></div>
                @endif
            </a>

            <a href="{{ route('admin.schedules.download') }}"
               class="relative px-4 py-2.5 rounded-lg text-sm font-medium transition-all duration-300 overflow-hidden group
                      {{ request()->routeIs('admin.schedules.download') ? 'bg-white/10 text-white shadow-md' : 'text-white/80 hover:text-white' }}">
                <span class="relative z-10">Download Schedule</span>
                <div class="absolute inset-0 bg-gradient-to-r from-white/0 via-white/10 to-white/0 translate-x-[-100%] group-hover:translate-x-[100%] transition-transform duration-700"></div>
                @if(request()->routeIs('admin.schedules.download'))
                    <div class="absolute bottom-0 left-0 right-0 h-0.5 bg-[#fae209]"></div>
                @endif
            </a>
        </div>

        <!-- Right: User dropdown + Mobile button -->
        <div class="flex items-center space-x-3">
            <!-- User Dropdown (desktop & mobile) -->
            <div class="relative" @keydown.escape="userOpen = false">
                <button @click="userOpen = !userOpen"
                        class="flex items-center space-x-3 px-4 py-2.5 rounded-xl shadow-lg border border-white/10 bg-white/10 backdrop-blur-md transition-all duration-300 hover:bg-white/20 hover:shadow-xl hover:scale-105 focus:outline-none focus:ring-2 focus:ring-white/20">
                    <div class="text-centered hidden md:block">
                        <p class="text-white text-sm font-semibold leading-tight">
                            {{ auth()->user()->name }}
                        </p>
                        <p class="text-[#cfe5d0] text-xs font-medium">
                            {{ auth()->user()->email }}
                        </p>
                    </div>

                    <!-- small avatar / initials for mobile -->
                    <div class="md:hidden h-8 w-8 rounded-full bg-[#fae209]/20 flex items-center justify-center text-[#fae209] font-bold text-sm">
                        {{ Str::limit(auth()->user()->name, 1, '') }}
                    </div>
                    
                    <!-- Chevron icon -->
                    <svg class="w-4 h-4 text-white/70 transition-transform duration-300" :class="{ 'rotate-180': userOpen }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>

                <!-- Dropdown Menu -->
                <div x-show="userOpen" 
                     x-cloak 
                     @click.outside="userOpen = false"
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 scale-95 -translate-y-2"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 scale-100 translate-y-0"
                     x-transition:leave-end="opacity-0 scale-95 -translate-y-2"
                     class="absolute right-0 mt-2 w-64 bg-[#0C3B2E] border border-[#6D9773]/30 rounded-xl shadow-2xl z-50 overflow-hidden backdrop-blur-md">
                    <!-- User Info -->
                    <div class="p-4 border-b border-[#6D9773]/20 bg-white/5">
                        <p class="text-white font-semibold text-base">{{ auth()->user()->name }}</p>
                        <p class="text-white/70 text-sm">{{ auth()->user()->email }}</p>
                    </div>

                    <!-- Menu Actions -->
                    <div class="flex flex-col p-2">
                        @if(auth()->user()->role === 'admin')
                            <a href="{{ route('admin.dashboard') }}" class="px-4 py-2.5 text-sm text-white hover:bg-[#6D9773]/20 rounded-lg transition-all duration-200 hover:translate-x-1 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Admin Dashboard</span>
                            </a>
                            <a href="{{ route('admin.faculties.index') }}" class="px-4 py-2.5 text-sm text-white hover:bg-[#6D9773]/20 rounded-lg transition-all duration-200 hover:translate-x-1 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                                </svg>
                                <span>Manage Faculties</span>
                            </a>
                            <a href="{{ route('admin.courses.index') }}" class="px-4 py-2.5 text-sm text-white hover:bg-[#6D9773]/20 rounded-lg transition-all duration-200 hover:translate-x-1 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                                <span>Manage Courses</span>
                            </a>
                        @elseif(auth()->user()->role === 'faculty')
                            <a href="{{ route('faculty.dashboard') }}" class="px-4 py-2.5 text-sm text-white hover:bg-[#6D9773]/20 rounded-lg transition-all duration-200 hover:translate-x-1 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                                </svg>
                                <span>Faculty Dashboard</span>
                            </a>
                            <a href="{{ route('faculty.schedule.index') }}" class="px-4 py-2.5 text-sm text-white hover:bg-[#6D9773]/20 rounded-lg transition-all duration-200 hover:translate-x-1 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <span>My Schedule</span>
                            </a>
                        @endif

                        <a href="{{ route('profile.edit') }}" class="px-4 py-2.5 text-sm text-white hover:bg-[#6D9773]/20 rounded-lg transition-all duration-200 hover:translate-x-1 flex items-center space-x-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            <span>Profile</span>
                        </a>

                        <div class="border-t border-[#6D9773]/20 my-2"></div>

                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" class="w-full text-left px-4 py-2.5 text-sm text-white hover:bg-red-500/20 rounded-lg transition-all duration-200 hover:translate-x-1 flex items-center space-x-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                <span>Logout</span>
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Mobile menu button -->
            <div class="md:hidden">
                <button @click="mobileOpen = !mobileOpen" aria-label="Toggle menu"
                        class="inline-flex items-center justify-center p-2 rounded-lg text-white/90 hover:bg-white/10 focus:outline-none transition-all duration-300 hover:scale-110">
                    <svg x-show="!mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform duration-300" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                   d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <svg x-show="mobileOpen" xmlns="http://www.w3.org/2000/svg" class="h-6 w-6 transition-transform duration-300 rotate-90" fill="none" viewBox="0 0 24 24"
                         stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                   d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Mobile menu -->
    <div x-show="mobileOpen" 
         x-cloak
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 -translate-y-4"
         class="md:hidden mt-4">
        <div class="px-3 pb-3 space-y-2 bg-white/5 rounded-xl backdrop-blur-sm">
            <a href="{{ route('admin.faculties.index') }}"
               class="relative block px-4 py-3 rounded-lg text-base font-medium overflow-hidden group
                      {{ request()->routeIs('admin.faculties.*') ? 'bg-white/10 text-white shadow-md' : 'text-white/80 hover:text-white hover:bg-white/5' }}">
                <span class="relative z-10">Manage Faculties</span>
                @if(request()->routeIs('admin.faculties.*'))
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#fae209]"></div>
                @endif
            </a>

            <a href="{{ route('admin.schedules.index') }}"
               class="relative block px-4 py-3 rounded-lg text-base font-medium overflow-hidden group
                      {{ request()->routeIs('admin.schedules.index') || request()->routeIs('admin.schedules.*') ? 'bg-white/10 text-white shadow-md' : 'text-white/80 hover:text-white hover:bg-white/5' }}">
                <span class="relative z-10">View Schedules</span>
                @if(request()->routeIs('admin.schedules.index') || request()->routeIs('admin.schedules.*'))
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#fae209]"></div>
                @endif
            </a>

            <a href="{{ route('admin.schedules.generate') }}"
               class="relative block px-4 py-3 rounded-lg text-base font-medium overflow-hidden group
                      {{ request()->routeIs('admin.schedules.generate') ? 'bg-white/10 text-white shadow-md' : 'text-white/80 hover:text-white hover:bg-white/5' }}">
                <span class="relative z-10">Generate Schedule</span>
                @if(request()->routeIs('admin.schedules.generate'))
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#fae209]"></div>
                @endif
            </a>

            <a href="{{ route('admin.schedules.download') }}"
               class="relative block px-4 py-3 rounded-lg text-base font-medium overflow-hidden group
                      {{ request()->routeIs('admin.schedules.download') ? 'bg-white/10 text-white shadow-md' : 'text-white/80 hover:text-white hover:bg-white/5' }}">
                <span class="relative z-10">Download Schedule</span>
                @if(request()->routeIs('admin.schedules.download'))
                    <div class="absolute left-0 top-0 bottom-0 w-1 bg-[#fae209]"></div>
                @endif
            </a>

            <!-- Repeated user quick links for mobile -->
            <div class="pt-2 mt-2 border-t border-white/10 space-y-2">
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="block px-4 py-3 rounded-lg text-base font-medium text-white/80 hover:bg-white/5 hover:text-white transition-all duration-200">Admin Dashboard</a>
                @elseif(auth()->user()->role === 'faculty')
                    <a href="{{ route('faculty.dashboard') }}" class="block px-4 py-3 rounded-lg text-base font-medium text-white/80 hover:bg-white/5 hover:text-white transition-all duration-200">Faculty Dashboard</a>
                @endif

                <a href="{{ route('profile.edit') }}" class="block px-4 py-3 rounded-lg text-base font-medium text-white/80 hover:bg-white/5 hover:text-white transition-all duration-200">Profile</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-4 py-3 text-base text-white/80 hover:bg-red-500/20 hover:text-white rounded-lg transition-all duration-200">Logout</button>
                </form>
            </div>
        </div>
    </div>
</nav>
@endif