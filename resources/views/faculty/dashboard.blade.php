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
                            {{ $faculty->rank ?? 'Faculty Member' }} | {{ $faculty->employment_status ?? 'Not Set' }}
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
                <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20 transition-transform">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-blue-500/30 backdrop-blur-md p-4 rounded-xl">
                            <i class="fas fa-clock text-3xl text-white"></i>
                        </div>
                    </div>
                    <h3 class="text-white/80 text-sm font-medium mb-2">Contact Hours/Week</h3>
                    <p class="text-4xl font-bold text-white">{{ number_format($totalContactHours ?? 0, 1) }}</p>
                    <p class="text-white/60 text-xs mt-2">Total weekly teaching hours</p>
                </div>

                <!-- Total Units -->
                <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20 transition-transform">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-green-500/30 backdrop-blur-md p-4 rounded-xl">
                            <i class="fas fa-book text-3xl text-white"></i>
                        </div>
                    </div>
                    <h3 class="text-white/80 text-sm font-medium mb-2">Total Units</h3>
                    <p class="text-4xl font-bold text-white">{{ $totalUnits ?? 0 }}</p>
                    <p class="text-white/60 text-xs mt-2">
                        @if(($totalUnits ?? 0) > 18)
                            <span class="text-yellow-300"><i class="fas fa-exclamation-triangle mr-1"></i>Overload: {{ ($totalUnits ?? 0) - 18 }} units</span>
                        @else
                            <span class="text-green-300"><i class="fas fa-check-circle mr-1"></i>Normal load</span>
                        @endif
                    </p>
                </div>

                <!-- Number of Subjects -->
                <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20 transition-transform">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-purple-500/30 backdrop-blur-md p-4 rounded-xl">
                            <i class="fas fa-chalkboard-teacher text-3xl text-white"></i>
                        </div>
                    </div>
                    <h3 class="text-white/80 text-sm font-medium mb-2">Subjects Teaching</h3>
                    <p class="text-4xl font-bold text-white">{{ $assignedSubjects->count() ?? 0 }}</p>
                    <p class="text-white/60 text-xs mt-2">Active courses this semester</p>
                </div>

                <!-- Total Schedules -->
                <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20 transition-transform">
                    <div class="flex items-center justify-between mb-4">
                        <div class="bg-pink-500/30 backdrop-blur-md p-4 rounded-xl">
                            <i class="fas fa-calendar-check text-3xl text-white"></i>
                        </div>
                    </div>
                    <h3 class="text-white/80 text-sm font-medium mb-2">Class Schedules</h3>
                    <p class="text-4xl font-bold text-white">{{ $schedules->count() ?? 0 }}</p>
                    <p class="text-white/60 text-xs mt-2">Total class sessions</p>
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
                            <button onclick="window.print()" class="bg-green-500/40 backdrop-blur-md hover:bg-green-500/60 text-white px-8 py-4 rounded-xl shadow-lg font-semibold border border-white/30 transition-all inline-flex items-center gap-3 hover:scale-105">
                                <i class="fas fa-print text-xl"></i>
                                <span>Print Schedule</span>
                            </button>
                            <a href="{{ route('faculty.teaching-load.download-pdf') }}" class="bg-red-500/40 backdrop-blur-md hover:bg-red-500/60 text-white px-8 py-4 rounded-xl shadow-lg font-semibold border border-white/30 transition-all inline-flex items-center gap-3 hover:scale-105">
                                <i class="fas fa-download text-xl"></i>
                                <span>Download PDF</span>
                            </a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Main Content Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                
                <!-- Left Column - Schedule Preview -->
                <div class="lg:col-span-2">
                    <div class="glass-card rounded-2xl shadow-xl p-8 border border-white/20">
                        <h2 class="text-2xl font-bold text-white flex items-center gap-3 mb-6">
                            <i class="fas fa-calendar-week"></i>
                            This Week's Classes
                        </h2>
                        
                        @if(isset($schedules) && $schedules->isNotEmpty())
                            <div class="space-y-4">
                                @php
                                    $today = date('l'); // Get current day name
                                    $daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                    
                                    // Helper function to convert day number to name
                                    function getDayName($dayNumber) {
                                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                                        return $days[$dayNumber - 1] ?? 'Unknown';
                                    }
                                @endphp
                                
                                @foreach($daysOfWeek as $dayIndex => $day)
                                    @php
                                        // Filter schedules by day number (1 = Monday, 2 = Tuesday, etc.)
                                        $dayNumber = $dayIndex + 1;
                                        $daySchedules = $schedules->where('day', $dayNumber);
                                        $isToday = $day === $today;
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
                                                <span class="text-white/60 text-sm">{{ $daySchedules->count() }} {{ $daySchedules->count() == 1 ? 'class' : 'classes' }}</span>
                                            </div>
                                            <div class="space-y-3">
                                                @foreach($daySchedules->sortBy('start_time') as $schedule)
                                                    <div class="bg-white/5 rounded-lg p-4 hover:bg-white/10 transition-all">
                                                        <div class="flex items-center justify-between">
                                                            <div class="flex-1">
                                                                <p class="text-white font-semibold text-lg">
                                                                    {{ $schedule->subject->course_code ?? 'N/A' }}
                                                                </p>
                                                                <p class="text-white/90 text-sm mt-1">
                                                                    {{ $schedule->subject->subject_name ?? 'N/A' }}
                                                                </p>
                                                                <div class="flex items-center gap-4 mt-2 text-white/70 text-sm">
                                                                    <span><i class="fas fa-door-open mr-1"></i>{{ $schedule->classroom->room_name ?? 'TBA' }}</span>
                                                                    <span><i class="fas fa-graduation-cap mr-1"></i>{{ $schedule->subject->program->code ?? 'N/A' }} {{ $schedule->year_level ?? '' }}</span>
                                                                </div>
                                                            </div>
                                                            <div class="text-right ml-4">
                                                                <p class="text-white font-bold text-lg">
                                                                    {{ date('g:i A', strtotime($schedule->start_time)) }}
                                                                </p>
                                                                <p class="text-white/60 text-sm">
                                                                    {{ date('g:i A', strtotime($schedule->end_time)) }}
                                                                </p>
                                                                <p class="text-white/50 text-xs mt-1">
                                                                    @php
                                                                        $start = strtotime($schedule->start_time);
                                                                        $end = strtotime($schedule->end_time);
                                                                        $hours = ($end - $start) / 3600;
                                                                    @endphp
                                                                    {{ $hours }} {{ $hours == 1 ? 'hour' : 'hours' }}
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

                <!-- Right Column - Profile & Info -->
                <div class="space-y-6">
                    
                    <!-- Profile Summary -->
                    <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2 mb-4">
                            <i class="fas fa-user-circle"></i>
                            Profile Information
                        </h2>
                        <div class="space-y-3">
                            <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
                                <p class="text-white/60 text-xs mb-1">Full Name</p>
                                <p class="text-white font-semibold">{{ $faculty->name }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
                                <p class="text-white/60 text-xs mb-1">Email Address</p>
                                <p class="text-white font-semibold text-sm">{{ $faculty->email }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
                                <p class="text-white/60 text-xs mb-1">Rank</p>
                                <p class="text-white font-semibold">{{ $faculty->rank ?? 'Not Set' }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
                                <p class="text-white/60 text-xs mb-1">Employment Status</p>
                                <p class="text-white font-semibold">{{ $faculty->employment_status ?? 'Not Set' }}</p>
                            </div>
                            <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
                                <p class="text-white/60 text-xs mb-1">Years of Service</p>
                                <p class="text-white font-semibold">{{ $faculty->years_of_service ?? '0 year' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('profile.edit') }}" class="block mt-4 text-center bg-white/10 hover:bg-white/20 backdrop-blur-md text-white py-3 rounded-xl border border-white/20 transition-all font-semibold">
                            <i class="fas fa-edit mr-2"></i>Edit Profile
                        </a>
                    </div>

                    <!-- Educational Background -->
                    <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2 mb-4">
                            <i class="fas fa-user-graduate"></i>
                            Educational Background
                        </h2>
                        @if(isset($educationalQualifications) && $educationalQualifications->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($educationalQualifications as $education)
                                    <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
                                        <p class="text-white font-semibold">{{ $education->degree ?? 'N/A' }}</p>
                                        <p class="text-white/70 text-sm mt-1">{{ $education->institution ?? 'N/A' }}</p>
                                        <p class="text-white/60 text-xs mt-1">
                                            <i class="fas fa-calendar mr-1"></i>
                                            {{ $education->year_graduated ?? 'N/A' }}
                                        </p>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-graduation-cap text-5xl text-white/20 mb-3"></i>
                                <p class="text-white/50 text-sm">No educational background added</p>
                            </div>
                        @endif
                    </div>

                    <!-- Assigned Subjects -->
                    <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2 mb-4">
                            <i class="fas fa-book-reader"></i>
                            Assigned Subjects
                        </h2>
                        @if(isset($assignedSubjects) && $assignedSubjects->isNotEmpty())
                            <div class="space-y-3">
                                @foreach($assignedSubjects as $assignment)
                                    <div class="bg-white/10 backdrop-blur-md rounded-lg p-4 border border-white/20">
                                        <p class="text-white font-semibold">{{ $assignment->subject->course_code ?? 'N/A' }}</p>
                                        <p class="text-white/70 text-sm mt-1">{{ $assignment->subject->subject_name ?? 'N/A' }}</p>
                                        <div class="flex items-center gap-2 mt-2">
                                            <span class="text-xs px-2 py-1 rounded-full bg-blue-500/30 text-blue-200 border border-blue-400/30">
                                                <i class="fas fa-graduation-cap mr-1"></i>
                                                {{ $assignment->subject->program->code ?? 'N/A' }}
                                            </span>
                                            <span class="text-xs px-2 py-1 rounded-full bg-purple-500/30 text-purple-200 border border-purple-400/30">
                                                <i class="fas fa-book mr-1"></i>
                                                {{ (($assignment->subject->lecture_units ?? 0) + ($assignment->subject->laboratory_units ?? 0)) }} units
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="text-center py-8">
                                <i class="fas fa-book-open text-5xl text-white/20 mb-3"></i>
                                <p class="text-white/50 text-sm">No subjects assigned</p>
                            </div>
                        @endif
                    </div>

                    <!-- Quick Links -->
                    <div class="glass-card rounded-2xl shadow-xl p-6 border border-white/20">
                        <h2 class="text-xl font-bold text-white flex items-center gap-2 mb-4">
                            <i class="fas fa-link"></i>
                            Quick Links
                        </h2>
                        <div class="space-y-3">
                            <a href="{{ route('settings') }}" class="block bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-lg p-3 border border-white/20 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="bg-gray-500/30 p-2 rounded-lg">
                                        <i class="fas fa-cog text-white"></i>
                                    </div>
                                    <span class="text-white font-semibold">Settings</span>
                                </div>
                            </a>
                            <a href="{{ route('profile.edit') }}" class="block bg-white/10 hover:bg-white/20 backdrop-blur-md rounded-lg p-3 border border-white/20 transition-all">
                                <div class="flex items-center gap-3">
                                    <div class="bg-blue-500/30 p-2 rounded-lg">
                                        <i class="fas fa-user-edit text-white"></i>
                                    </div>
                                    <span class="text-white font-semibold">Update Profile</span>
                                </div>
                            </a>
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
            25% { transform: translate(20px, -50px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.9); }
            75% { transform: translate(50px, 50px) scale(1.05); }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        @media print {
            body {
                background: white !important;
            }
            .glass-card, nav, button {
                display: none !important;
            }
        }
    </style>
</x-app-layout>