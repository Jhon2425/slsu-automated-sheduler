<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8 relative overflow-hidden">
        <!-- Enhanced Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-pink-500/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm text-white/80 mb-6">
                <a href="{{ route('faculty.dashboard') }}" class="flex items-center hover:text-violet-400 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
            </nav>

            <!-- Page Header -->
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-8 border border-white/20">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-bold text-white flex items-center gap-3 drop-shadow-lg">
                            <i class="fas fa-tachometer-alt"></i> Faculty Dashboard
                        </h1>
                        <p class="mt-3 text-white/90 text-lg drop-shadow">Welcome, {{ $faculty->name }}!</p>
                        <p class="mt-1 text-white/80 text-sm drop-shadow">
                            {{ $faculty->rank ?? 'Faculty Member' }} | {{ $faculty->employment_status ?? 'Employment Status Not Set' }}
                        </p>
                        <p class="mt-1 text-white/70 text-sm drop-shadow">
                            Academic Year {{ $schoolYear ?? date('Y') . '-' . (date('Y') + 1) }} | {{ $semester ?? '1st' }} Semester
                        </p>
                    </div>
                </div>
            </div>

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">

                <!-- Total Contact Hours -->
                <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20 transition-transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-500/30 backdrop-blur-md p-4 rounded-xl">
                            <i class="fas fa-clock text-3xl text-white"></i>
                        </div>
                        <span class="text-white/40 text-xs uppercase tracking-widest">Weekly</span>
                    </div>
                    <h3 class="text-white/80 text-sm font-medium mb-2">Contact Hours / Week</h3>
                    <p class="text-4xl font-bold text-white">
                        {{ number_format($totalContactHours ?? 0, 1) }}
                        <span class="text-lg font-normal text-white/60">hrs</span>
                    </p>
                    <p class="text-white/60 text-xs mt-2">
                        @if(($totalContactHours ?? 0) > 0)
                            <span class="text-blue-300">
                                <i class="fas fa-check-circle mr-1"></i>
                                {{ number_format(($totalContactHours ?? 0) / 5, 1) }} hrs/day avg
                            </span>
                        @else
                            <span><i class="fas fa-info-circle mr-1"></i>No schedule assigned</span>
                        @endif
                    </p>
                </div>

                <!-- Total Units -->
                <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20 transition-transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-500/30 backdrop-blur-md p-4 rounded-xl">
                            <i class="fas fa-book text-3xl text-white"></i>
                        </div>
                        <span class="text-white/40 text-xs uppercase tracking-widest">Load</span>
                    </div>
                    <h3 class="text-white/80 text-sm font-medium mb-2">Total Units</h3>
                    <p class="text-4xl font-bold text-white">
                        {{ $totalUnits ?? 0 }}
                        <span class="text-lg font-normal text-white/60">units</span>
                    </p>
                    <p class="text-white/60 text-xs mt-2">
                        @if(($totalUnits ?? 0) > 18)
                            <span class="text-yellow-300">
                                <i class="fas fa-exclamation-triangle mr-1"></i>
                                Overload: {{ ($totalUnits ?? 0) - 18 }} units
                            </span>
                        @else
                            <span class="text-green-300">
                                <i class="fas fa-check-circle mr-1"></i>
                                Normal load
                            </span>
                        @endif
                    </p>
                </div>

                <!-- Number of Subjects -->
                <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20 transition-transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-500/30 backdrop-blur-md p-4 rounded-xl">
                            <i class="fas fa-chalkboard-teacher text-3xl text-white"></i>
                        </div>
                        <span class="text-white/40 text-xs uppercase tracking-widest">Courses</span>
                    </div>
                    <h3 class="text-white/80 text-sm font-medium mb-2">Subjects Teaching</h3>
                    <p class="text-4xl font-bold text-white">
                        {{ isset($assignedSubjects) ? $assignedSubjects->count() : 0 }}
                        <span class="text-lg font-normal text-white/60">subj</span>
                    </p>
                    <p class="text-white/60 text-xs mt-2">
                        <span class="text-purple-300">
                            <i class="fas fa-info-circle mr-1"></i>
                            Active courses this semester
                        </span>
                    </p>
                </div>

                <!-- Total Schedules -->
                <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20 transition-transform hover:scale-105">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-pink-500/30 backdrop-blur-md p-4 rounded-xl">
                            <i class="fas fa-calendar-check text-3xl text-white"></i>
                        </div>
                        <span class="text-white/40 text-xs uppercase tracking-widest">Sessions</span>
                    </div>
                    <h3 class="text-white/80 text-sm font-medium mb-2">Class Schedules</h3>
                    <p class="text-4xl font-bold text-white">
                        {{ isset($schedules) ? $schedules->count() : 0 }}
                        <span class="text-lg font-normal text-white/60">rows</span>
                    </p>
                    <p class="text-white/60 text-xs mt-2">
                        <span class="text-pink-300">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            Total class sessions
                        </span>
                    </p>
                </div>

            </div>

            <!-- Quick Access - My Teaching Schedule -->
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-8 border border-white/20">
                <div class="text-center">
                    <div class="bg-gradient-to-br from-blue-500/20 to-purple-500/20 backdrop-blur-md rounded-2xl p-12 border border-white/30">
                        <div class="mb-6">
                            <div class="inline-block bg-white/20 p-6 rounded-full mb-4">
                                <i class="fas fa-calendar-alt text-6xl text-white"></i>
                            </div>
                        </div>
                        <h2 class="text-3xl font-bold text-white mb-4">My Teaching Schedule</h2>
                        <p class="text-white/80 text-lg mb-8 max-w-2xl mx-auto">
                            View your complete teaching load, class schedules, and download your official teaching load document
                        </p>
                        <div class="flex flex-wrap gap-4 justify-center">
                            <a href="{{ route('faculty.teaching-load.index') }}" class="bg-blue-500/40 backdrop-blur-md hover:bg-blue-500/60 text-white px-8 py-4 rounded-xl shadow-lg font-semibold border border-white/30 transition-all inline-flex items-center gap-3 hover:scale-105">
                                <i class="fas fa-eye text-xl"></i>
                                <span>View My Schedule</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 gap-8 mb-8">
                <div class="glass-card rounded-2xl shadow-xl p-8 border border-white/20">
                    <h2 class="text-2xl font-bold text-white flex items-center gap-3 mb-6">
                        <i class="fas fa-calendar-week"></i>
                        This Week's Classes
                    </h2>

                    @if(isset($schedules) && $schedules->isNotEmpty())
                        <div class="space-y-4">
                            @php
                                $today      = date('l');
                                $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                            @endphp

                            @foreach($daysOfWeek as $dayIndex => $day)
                                @php
                                    $dayNumber    = $dayIndex + 1;
                                    $daySchedules = $schedules->where('day', $dayNumber);
                                    $isToday      = $day === $today;
                                @endphp
                                @if($daySchedules->isNotEmpty())
                                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-5 border {{ $isToday ? 'border-yellow-400/50 shadow-lg shadow-yellow-500/20' : 'border-white/20' }}">
                                        <div class="flex items-center justify-between mb-4">
                                            <h3 class="text-white font-bold text-lg flex items-center gap-2">
                                                <span class="bg-white/20 px-4 py-1 rounded-lg">{{ $day }}</span>
                                                @if($isToday)
                                                    <span class="bg-yellow-500/30 px-3 py-1 rounded-lg text-sm">Today</span>
                                                @endif
                                            </h3>
                                            <span class="text-white/60 text-sm">
                                                {{ $daySchedules->count() }} {{ $daySchedules->count() == 1 ? 'class' : 'classes' }}
                                            </span>
                                        </div>
                                        <div class="space-y-3">
                                            @foreach($daySchedules->sortBy('start_time') as $schedule)
                                                <div class="bg-white/5 rounded-lg p-4 hover:bg-white/10 transition-all">
                                                    <div class="flex items-center justify-between">
                                                        <div class="flex-1">
                                                            <p class="text-white font-semibold text-lg">
                                                                {{ $schedule->subject->course_code ?? 'Course Code Unavailable' }}
                                                            </p>
                                                            <p class="text-white/90 text-sm mt-1">
                                                                {{ $schedule->subject->subject_name ?? 'Subject Name Unavailable' }}
                                                            </p>
                                                            <div class="flex items-center gap-4 mt-2 text-white/70 text-sm">
                                                                <span>
                                                                    <i class="fas fa-door-open mr-1"></i>
                                                                    {{ $schedule->classroom->room_name ?? 'Room TBA' }}
                                                                </span>
                                                              <span>
                                                                <i class="fas fa-graduation-cap mr-1"></i>
                                                                {{ $schedule->program->code ?? $schedule->subject->program->code ?? 'Program TBA' }}
                                                                {{ $schedule->year_level ? '— Year ' . $schedule->year_level : '' }}
                                                            </span>
                                                            </div>
                                                        </div>
                                                        <div class="text-right ml-4">
                                                            <p class="text-white font-bold text-lg">
                                                                {{ $schedule->start_time ? date('g:i A', strtotime($schedule->start_time)) : 'TBA' }}
                                                            </p>
                                                            <p class="text-white/60 text-sm">
                                                                {{ $schedule->end_time ? date('g:i A', strtotime($schedule->end_time)) : 'TBA' }}
                                                            </p>
                                                            <p class="text-white/50 text-xs mt-1">
                                                                @if($schedule->start_time && $schedule->end_time)
                                                                    @php
                                                                        $start = strtotime($schedule->start_time);
                                                                        $end   = strtotime($schedule->end_time);
                                                                        $hours = round(($end - $start) / 3600, 1);
                                                                    @endphp
                                                                    {{ $hours }} {{ $hours == 1 ? 'hour' : 'hours' }}
                                                                @else
                                                                    Duration TBA
                                                                @endif
                                                            </p>
                                                        </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-16">
                            <div class="bg-white/10 backdrop-blur-md rounded-2xl p-12 border border-white/20">
                                <i class="fas fa-calendar-times text-6xl text-white/30 mb-4"></i>
                                <p class="text-white/60 text-lg mb-2">No schedule assigned yet</p>
                                <p class="text-white/40 text-sm">Please contact your administrator</p>
                            </div>
                        </div>
                    @endif
                </div>
            </div>

            {{-- ── Teaching Load Gauge ── --}}
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-8 border border-white/20">
                <h2 class="text-xl font-bold text-white flex items-center gap-3 mb-6">
                    <i class="fas fa-tachometer-alt text-blue-300"></i>
                    Teaching Load Gauge
                </h2>

                @php
                    $units     = $totalUnits ?? 0;
                    $maxUnits  = 21;
                    $normalMax = 18;
                    $pct       = min(100, round(($units / $maxUnits) * 100));

                    if ($units > $normalMax) {
                        $gaugeColor = '#f59e0b';
                        $gaugeLabel = 'Overloaded';
                        $gaugeBadge = 'bg-amber-500/30 text-amber-200';
                        $gaugeIcon  = 'fa-exclamation-triangle';
                        $gaugeMsg   = ($units - $normalMax) . ' unit(s) above the standard load.';
                    } elseif ($units >= 12) {
                        $gaugeColor = '#34d399';
                        $gaugeLabel = 'Optimal Load';
                        $gaugeBadge = 'bg-emerald-500/30 text-emerald-200';
                        $gaugeIcon  = 'fa-check-circle';
                        $gaugeMsg   = 'Your load is within the recommended range.';
                    } else {
                        $gaugeColor = '#60a5fa';
                        $gaugeLabel = 'Underloaded';
                        $gaugeBadge = 'bg-blue-500/30 text-blue-200';
                        $gaugeIcon  = 'fa-info-circle';
                        $gaugeMsg   = 'You may be assigned additional units.';
                    }

                    // Semicircle: half-circumference of r=90 ≈ 283
                    $arcFilled = round(($pct / 100) * 283);
                @endphp

                <div class="flex flex-col items-center">

                    {{-- Semicircle SVG --}}
                    <div class="relative w-56 h-28 mb-4">
                        <svg viewBox="0 0 200 105" xmlns="http://www.w3.org/2000/svg" class="w-full h-full">
                            {{-- Track --}}
                            <path d="M 10 100 A 90 90 0 0 1 190 100"
                                  fill="none" stroke="rgba(255,255,255,0.08)"
                                  stroke-width="18" stroke-linecap="round"/>
                            {{-- Filled arc --}}
                            <path d="M 10 100 A 90 90 0 0 1 190 100"
                                  fill="none"
                                  stroke="{{ $gaugeColor }}"
                                  stroke-width="18"
                                  stroke-linecap="round"
                                  stroke-dasharray="{{ $arcFilled }} 283"/>
                            {{-- Threshold tick at ~18 units (86 %) --}}
                            <line x1="172" y1="28" x2="162" y2="40"
                                  stroke="rgba(245,158,11,0.7)" stroke-width="2.5" stroke-linecap="round"/>
                        </svg>
                        {{-- Centre label --}}
                        <div class="absolute inset-x-0 bottom-0 flex flex-col items-center leading-none">
                            <span class="text-4xl font-black text-white">{{ $units }}</span>
                            <span class="text-white/50 text-xs mt-1">/ {{ $maxUnits }} units</span>
                        </div>
                    </div>

                    {{-- Status badge --}}
                    <span class="inline-flex items-center gap-2 px-4 py-1.5 rounded-full text-sm font-semibold {{ $gaugeBadge }}">
                        <i class="fas {{ $gaugeIcon }}"></i> {{ $gaugeLabel }}
                    </span>

                    <p class="text-white/50 text-xs text-center mt-3 max-w-xs">{{ $gaugeMsg }}</p>

                    {{-- Segmented bar --}}
                    <div class="w-full mt-6">
                        <div class="flex justify-between text-xs text-white/40 mb-1">
                            <span>0</span><span>12</span><span>18</span><span>21</span>
                        </div>
                        <div class="relative w-full h-3 bg-white/10 rounded-full overflow-hidden">
                            <div class="absolute left-0 top-0 h-full rounded-full transition-all duration-1000"
                                 style="width:{{ $pct }}%; background:{{ $gaugeColor }};"></div>
                            {{-- Threshold lines --}}
                            <div class="absolute top-0 h-full w-px bg-white/30"       style="left:{{ round((12/21)*100) }}%"></div>
                            <div class="absolute top-0 h-full w-px bg-amber-400/60"   style="left:{{ round((18/21)*100) }}%"></div>
                        </div>
                        <div class="flex gap-5 mt-2 text-xs text-white/40 justify-center">
                            <span><span class="inline-block w-2 h-2 rounded-full bg-blue-400 mr-1"></span>Under 12</span>
                            <span><span class="inline-block w-2 h-2 rounded-full bg-emerald-400 mr-1"></span>12 – 18</span>
                            <span><span class="inline-block w-2 h-2 rounded-full bg-amber-400 mr-1"></span>18 +</span>
                        </div>
                    </div>

                </div>
            </div>

        </div>
    </div>

    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25%       { transform: translate(20px, -50px) scale(1.1); }
            50%       { transform: translate(-20px, 20px) scale(0.9); }
            75%       { transform: translate(50px, 50px) scale(1.05); }
        }
        .animate-blob         { animation: blob 7s infinite; }
        .animation-delay-2000 { animation-delay: 2s; }
        .animation-delay-4000 { animation-delay: 4s; }

        @media print {
            body { background: white !important; }
            .glass-card, nav, button { display: none !important; }
        }
    </style>
</x-app-layout>