<!-- resources/views/layouts/navigation.blade.php -->

@if(auth()->check()) {{-- Prevent errors when user is not logged in --}}
<nav class="bg-[#0C3B2E] px-4 py-3 flex items-center justify-between">

    <!-- Logo -->
        <div class="flex items-center space-x-2">
            <img src="{{ asset('slsu-logo.png') }}" alt="Logo" class="h-8 w-8 rounded-full">
            <div class="flex flex-col leading-tight">
             <p class="font-bold text-lg mb-0" style="color: #fae209ee;"> South Luzon State University -</p>
             <p class="font-semibold text-sm mb-0" style="color: #e7d954a3;"> Tiaong Campus</p>

            </div>
        </div>


    <!-- User Dropdown -->
    <div class="relative" x-data="{ open: false }">
        <button @click="open = !open"
        class="flex items-center space-x-3 px-4 py-2 rounded-xl shadow-md border border-white/10 bg-white/10 backdrop-blur-md transition hover:bg-white/20 focus:outline-none">

        <div class="text-centered hidden md:block">
            <p class="text-white text-sm font-semibold leading-tight">
                {{ auth()->user()->name }}
            </p>
            <p class="text-[#cfe5d0] text-xs font-medium">
                {{ auth()->user()->email }}
            </p>
        </div>
    </button>

        <!-- Dropdown Menu -->
        <div x-show="open" @click.outside="open = false"
             class="absolute right-0 mt-2 w-60 bg-[#0C3B2E] border border-[#6D9773]/30 rounded-lg shadow-lg z-50">

            <!-- User Info -->
            <div class="p-4 border-b border-[#6D9773]/20">
                <p class="text-white font-semibold text-base">{{ auth()->user()->name }}</p>
                <p class="text-white/70 text-sm">{{ auth()->user()->email }}</p>
            </div>

            <!-- Menu Actions -->
            <div class="flex flex-col p-2">
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('admin.dashboard') }}" class="px-3 py-2 text-sm text-white hover:bg-[#6D9773]/20 rounded">Admin Dashboard</a>
                    <a href="{{ route('admin.faculties.index') }}" class="px-3 py-2 text-sm text-white hover:bg-[#6D9773]/20 rounded">Manage Faculties</a>
                    <a href="{{ route('admin.courses.index') }}" class="px-3 py-2 text-sm text-white hover:bg-[#6D9773]/20 rounded">Manage Courses</a>
                @elseif(auth()->user()->role === 'faculty')
                    <a href="{{ route('faculty.dashboard') }}" class="px-3 py-2 text-sm text-white hover:bg-[#6D9773]/20 rounded">Faculty Dashboard</a>
                    <a href="{{ route('faculty.schedule.index') }}" class="px-3 py-2 text-sm text-white hover:bg-[#6D9773]/20 rounded">My Schedule</a>
                @endif

                <a href="{{ route('profile.edit') }}" class="px-3 py-2 text-sm text-white hover:bg-[#6D9773]/20 rounded">Profile</a>

                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full text-left px-3 py-2 text-sm text-white hover:bg-[#6D9773]/20 rounded">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</nav>
@endif
