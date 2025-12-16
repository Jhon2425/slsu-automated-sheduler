<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header with Date/Time -->
            <div class="mb-8 animate-fade-in flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="animate-slide-in-left">
                    <h1 class="text-5xl font-bold text-white flex items-center mb-2">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 via-blue-600 to-purple-600 flex items-center justify-center mr-4 shadow-2xl shadow-blue-500/50 floating relative overflow-hidden group">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                            <i class="fas fa-tachometer-alt text-2xl animate-pulse-gentle relative z-10"></i>
                        </div>

                        <span class="animate-text-shimmer bg-clip-text text-transparent bg-gradient-to-r from-white via-blue-200 to-purple-300">
                            Admin Dashboard
                        </span>
                    </h1>

                    <p class="text-white/80 text-lg mt-2 animate-fade-in-delayed">
                        Welcome back,
                        <span class="font-semibold text-white animate-glow">
                            {{ auth()->user()->name }}
                        </span>!
                    </p>
                </div>
                
                <!-- Glass Date/Time Display -->
                <div class="glass-card px-6 py-4 rounded-2xl shadow-xl hover-lift">
                    <div class="flex items-center space-x-4">
                        <div class="text-right">
                            <div class="flex items-center text-white/90 text-sm font-medium mb-1">
                                <i class="fas fa-calendar-day mr-2 text-blue-400"></i>
                                <span id="currentDate">Loading...</span>
                            </div>
                            <div class="flex items-center text-white font-bold text-xl">
                                <i class="fas fa-clock mr-2 text-yellow-400 animate-pulse-slow"></i>
                                <span id="manilaTime">--:--:--</span>
                            </div>
                            <div class="text-white/70 text-xs mt-1">Manila, Philippines</div>
                        </div>
                        <div class="bg-white/20 rounded-full p-3 animate-pulse-slow">
                            <i class="fas fa-map-marker-alt text-red-400 text-2xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="glass-card border-l-4 border-green-400 text-white px-6 py-4 rounded-lg mb-6 flex items-center shadow-xl animate-slide-down">
                    <div class="bg-green-500 rounded-full p-2 mr-4 animate-bounce-once">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <span class="font-medium">{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="glass-card border-l-4 border-red-400 text-white px-6 py-4 rounded-lg mb-6 flex items-center shadow-xl animate-slide-down animate-shake">
                    <div class="bg-red-500 rounded-full p-2 mr-4 animate-bounce-once">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                    </div>
                    <span class="font-medium">{{ session('error') }}</span>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <!-- Total Accepted Faculties Card -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Accepted Faculties</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $acceptedFaculties ?? 0 }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-check-circle text-green-400 mr-1"></i>
                                Active members
                            </p>
                        </div>
                        <div class="icon-container bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl p-4 shadow-lg">
                            <i class="fas fa-chalkboard-teacher text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>

                <!-- Total Programs Card -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.15s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Active Programs</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $totalPrograms ?? 0 }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-check-circle text-green-400 mr-1"></i>
                                Current programs
                            </p>
                        </div>
                        <div class="icon-container bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-2xl p-4 shadow-lg">
                            <i class="fas fa-graduation-cap text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>

                <!-- Total Schedules Card -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Total Schedules</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $totalSchedules ?? 0 }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-calendar-check text-green-400 mr-1"></i>
                                Generated schedules
                            </p>
                        </div>
                        <div class="icon-container bg-gradient-to-br from-green-400 to-green-600 rounded-2xl p-4 shadow-lg">
                            <i class="fas fa-calendar-alt text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>

                <!-- Total Classrooms Card -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Classrooms</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $totalClassrooms ?? 0 }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-door-open text-purple-400 mr-1"></i>
                                Available rooms
                            </p>
                        </div>
                        <div class="icon-container bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl p-4 shadow-lg">
                            <i class="fas fa-door-open text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>

                <!-- Enrollments Card -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Enrollments</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $totalEnrollments ?? 0 }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-user-check text-yellow-400 mr-1"></i>
                                Faculty enrolled
                            </p>
                        </div>
                        <div class="icon-container bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl p-4 shadow-lg">
                            <i class="fas fa-user-check text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-8 animate-fade-in-up" style="animation-delay: 0.5s;">
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <div class="bg-gradient-to-br from-yellow-400 to-orange-500 rounded-xl p-2 mr-3 animate-pulse-glow">
                        <i class="fas fa-bolt text-white"></i>
                    </div>
                    Quick Actions
                </h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Manage Programs -->
                    <a href="{{ route('admin.programs.index') }}" 
                      class="action-card glass-card rounded-xl p-6 text-center group flex flex-col h-full min-h-[220px]">
                        <div class="icon-wrapper bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-2xl p-4 mx-auto mb-4 w-16 h-16 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-graduation-cap text-white text-3xl"></i>
                        </div>
                        <div class="flex-grow flex flex-col justify-center">
                            <p class="font-bold text-white text-lg mb-2">Manage Programs</p>
                            <p class="text-xs text-white/70 leading-relaxed">View programs and manage enrollments</p>
                        </div>
                    </a>

                    <!-- Manage Subjects -->
                    <a href="{{ route('admin.subjects.index') }}" 
                      class="action-card glass-card rounded-xl p-6 text-center group flex flex-col h-full min-h-[220px]">
                        <div class="icon-wrapper bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl p-4 mx-auto mb-4 w-16 h-16 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-book text-white text-3xl"></i>
                        </div>
                        <div class="flex-grow flex flex-col justify-center">
                            <p class="font-bold text-white text-lg mb-2">Manage Subjects</p>
                            <p class="text-xs text-white/70 leading-relaxed">Add, edit, or remove subjects</p>
                        </div>
                    </a>

                    <!-- View Schedules -->
                    <a href="{{ route('admin.schedules.index') }}" 
                      class="action-card glass-card rounded-xl p-6 text-center group flex flex-col h-full min-h-[220px]">
                        <div class="icon-wrapper bg-gradient-to-br from-purple-400 to-purple-600 rounded-2xl p-4 mx-auto mb-4 w-16 h-16 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-calendar text-white text-3xl"></i>
                        </div>
                        <div class="flex-grow flex flex-col justify-center">
                            <p class="font-bold text-white text-lg mb-2">View Schedules</p>
                            <p class="text-xs text-white/70 leading-relaxed">Browse all schedules</p>
                        </div>
                    </a>

                    <!-- Generate Schedule -->
                    <form method="POST" action="{{ route('admin.schedules.generate') }}" class="m-0 h-full">
                        @csrf
                        <button type="submit" 
                                class="w-full h-full action-card glass-card rounded-xl p-6 text-center group flex flex-col min-h-[220px]">
                            <div class="icon-wrapper bg-gradient-to-br from-red-400 to-red-600 rounded-2xl p-4 mx-auto mb-4 w-16 h-16 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-magic text-white text-3xl"></i>
                            </div>
                            <div class="flex-grow flex flex-col justify-center">
                                <p class="font-bold text-white text-lg mb-2">Generate Schedule</p>
                                <p class="text-xs text-white/70 leading-relaxed">Auto-generate schedules</p>
                            </div>
                        </button>
                    </form>

                    <!-- Download Reports -->
                    <a href="{{ route('admin.schedules.download') }}" 
                       class="action-card glass-card rounded-xl p-6 text-center group flex flex-col h-full min-h-[220px]">
                        <div class="icon-wrapper bg-gradient-to-br from-orange-400 to-orange-600 rounded-2xl p-4 mx-auto mb-4 w-16 h-16 flex items-center justify-center flex-shrink-0">
                            <i class="fas fa-file-pdf text-white text-3xl"></i>
                        </div>
                        <div class="flex-grow flex flex-col justify-center">
                            <p class="font-bold text-white text-lg mb-2">Download Report</p>
                            <p class="text-xs text-white/70 leading-relaxed">Export as PDF</p>
                        </div>
                    </a>
                </div>
            </div>

            <!-- Recent Activity & Programs Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Recent Programs -->
                <div class="glass-card rounded-2xl shadow-xl p-6 animate-fade-in-up" style="animation-delay: 0.6s;">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                        <div class="bg-gradient-to-br from-indigo-400 to-indigo-600 rounded-lg p-2 mr-3">
                            <i class="fas fa-graduation-cap text-white"></i>
                        </div>
                        Recent Programs
                    </h2>
                    @if(isset($recentPrograms) && $recentPrograms->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentPrograms as $program)
                                <div class="glass-item rounded-xl p-4 hover-slide">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center flex-1 min-w-0">
                                            <div class="bg-indigo-500/30 rounded-full p-2 mr-3 flex-shrink-0">
                                                <i class="fas fa-book text-indigo-300"></i>
                                            </div>
                                            <div class="min-w-0 flex-1">
                                                <p class="font-semibold text-white truncate">{{ $program->name }}</p>
                                                <p class="text-xs text-white/70">{{ $program->code }}</p>
                                            </div>
                                        </div>
                                        <span class="badge-success ml-2 flex-shrink-0">
                                            {{ $program->enrollments_count ?? 0 }} enrolled
                                        </span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('admin.programs.index') }}" 
                           class="block mt-4 text-center text-white/80 hover:text-white text-sm transition-colors font-medium">
                            View all programs
                        </a>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-inbox text-white/30 text-5xl mb-3"></i>
                            <p class="text-white/70 mb-4">No programs created yet</p>
                        </div>
                    @endif
                </div>

                <!-- Recent Faculties -->
                <div class="glass-card rounded-2xl shadow-xl p-6 animate-fade-in-up" style="animation-delay: 0.7s;">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                        <div class="bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg p-2 mr-3">
                            <i class="fas fa-history text-white"></i>
                        </div>
                        Recent Faculties
                    </h2>
                    @if(isset($recentFaculties) && $recentFaculties->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentFaculties as $faculty)
                                <div class="glass-item rounded-xl p-4 hover-slide">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center">
                                            <div class="bg-blue-500/30 rounded-full p-2 mr-3">
                                                <i class="fas fa-user text-blue-300"></i>
                                            </div>
                                            <div>
                                                <p class="font-semibold text-white">{{ $faculty->name }}</p>
                                                <p class="text-xs text-white/70">{{ $faculty->course_subject ?? 'No subject' }}</p>
                                            </div>
                                        </div>
                                        <span class="text-xs text-white/60">{{ $faculty->created_at->diffForHumans() }}</span>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-user-slash text-white/30 text-5xl mb-3"></i>
                            <p class="text-white/70">No recent faculties added</p>
                        </div>
                    @endif
                </div>

                <!-- Pending Enrollments -->
                <div class="glass-card rounded-2xl shadow-xl p-6 animate-fade-in-up" style="animation-delay: 0.8s;">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                        <div class="bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-lg p-2 mr-3 animate-bounce-slow">
                            <i class="fas fa-clock text-white"></i>
                        </div>
                        Pending Enrollments
                    </h2>
                    @if(isset($pendingEnrollments) && $pendingEnrollments->count() > 0)
                        <div class="space-y-3">
                            @foreach($pendingEnrollments as $enrollment)
                                <div class="glass-item rounded-xl p-4">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-semibold text-white text-sm">{{ $enrollment->faculty->name }}</p>
                                        <span class="badge-warning">Pending</span>
                                    </div>
                                    <p class="text-xs text-white/70 mb-3">{{ $enrollment->program->name }}</p>
                                    <a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" 
                                       class="block text-center bg-blue-500 hover:bg-blue-600 text-white py-2 px-3 rounded-lg text-xs transition-all transform hover:scale-105 font-medium">
                                        <i class="fas fa-edit mr-1"></i>Assign Schedule
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('admin.programs.index') }}" 
                           class="block mt-4 text-center text-white/80 hover:text-white text-sm transition-colors font-medium">
                            View all enrollments →
                        </a>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-green-400 text-5xl mb-3 animate-pulse-slow"></i>
                            <p class="text-white font-semibold">All caught up!</p>
                            <p class="text-white/60 text-xs mt-1">No pending enrollments</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- System Statistics -->
            <div class="glass-card rounded-2xl shadow-xl p-6 animate-fade-in-up" style="animation-delay: 0.9s;">
                <h2 class="text-2xl font-bold text-white mb-6 flex items-center">
                    <div class="bg-gradient-to-br from-green-400 to-green-600 rounded-xl p-2 mr-3">
                        <i class="fas fa-chart-bar text-white"></i>
                    </div>
                    System Statistics
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="glass-item rounded-xl p-4 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-users text-blue-400 text-2xl"></i>
                            <span class="badge-info">AVG</span>
                        </div>
                        <p class="text-2xl font-bold text-white mb-1">{{ $avgClassSize ?? 'N/A' }}</p>
                        <p class="text-white/70 text-xs">Average Class Size</p>
                    </div>
                    <div class="glass-item rounded-xl p-4 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-clock text-purple-400 text-2xl"></i>
                            <span class="badge-info">TOTAL</span>
                        </div>
                        <p class="text-2xl font-bold text-white mb-1">{{ $totalHours ?? 'N/A' }}h</p>
                        <p class="text-white/70 text-xs">Scheduled Hours</p>
                    </div>
                    <div class="glass-item rounded-xl p-4 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-flask text-green-400 text-2xl"></i>
                            <span class="badge-success">{{ $labCount ?? 0 }}</span>
                        </div>
                        <p class="text-2xl font-bold text-white mb-1">Lab</p>
                        <p class="text-white/70 text-xs">Laboratory Classes</p>
                    </div>
                    <div class="glass-item rounded-xl p-4 hover-lift">
                        <div class="flex items-center justify-between mb-2">
                            <i class="fas fa-book-open text-yellow-400 text-2xl"></i>
                            <span class="badge-warning">{{ $lectureCount ?? 0 }}</span>
                        </div>
                        <p class="text-2xl font-bold text-white mb-1">Lecture</p>
                        <p class="text-white/70 text-xs">Lecture Classes</p>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
        /* Glass Card Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .glass-item {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .glass-item:hover {
            background: rgba(255, 255, 255, 0.15);
            border-color: rgba(255, 255, 255, 0.3);
            transform: translateY(-2px);
        }

        /* Stat Card Animation */
        .stat-card {
            position: relative;
            overflow: hidden;
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.2), transparent);
            transition: left 0.7s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        /* Progress Bar */
        .progress-bar {
            height: 3px;
            background: rgba(255, 255, 255, 0.2);
            border-radius: 2px;
            overflow: hidden;
            position: relative;
        }

        .progress-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 70%;
            background: linear-gradient(90deg, #60a5fa, #a78bfa);
            border-radius: 2px;
            animation: progressSlide 2s ease-out;
        } 

        @keyframes progressSlide {
            from { width: 0; }
            to { width: 70%; }
        }

        /* Icon Container Animation */
        .icon-container {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover .icon-container {
            transform: rotate(10deg) scale(1.1);
        }

        /* Action Card - Equal Sizes with Flexbox */
        .action-card {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.1);
            transform: translate(-50%, -50%);
            transition: width 0.6s, height 0.6s;
        }

        .action-card:hover::before {
            width: 300px;
            height: 300px;
        }

        .action-card:hover {
            transform: translateY(-10px) scale(1.02);
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.4);
            border-color: rgba(255, 255, 255, 0.4);
        }

        .action-card .icon-wrapper {
            transition: all 0.5s cubic-bezier(0.68, -0.55, 0.265, 1.55);
        }

        .action-card:hover .icon-wrapper {
            transform: rotateY(360deg) scale(1.15);
        }

        /* Pulse Glow Animation */
        @keyframes pulseGlow {
            0%, 100% {
                box-shadow: 0 0 10px rgba(251, 191, 36, 0.5);
            }
            50% {
                box-shadow: 0 0 20px rgba(251, 191, 36, 0.8), 0 0 30px rgba(251, 191, 36, 0.4);
            }
        }

        .animate-pulse-glow {
            animation: pulseGlow 2s ease-in-out infinite;
        }

        /* Hover Lift */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        /* Hover Slide */
        .hover-slide {
            transition: all 0.3s ease;
        }

        .hover-slide:hover {
            transform: translateX(8px);
            box-shadow: -5px 0 15px rgba(0, 0, 0, 0.2);
        }

        /* Badges */
        .badge-success {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(34, 197, 94, 0.3);
        }

        .badge-warning {
            background: rgba(234, 179, 8, 0.2);
            color: #fde047;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(234, 179, 8, 0.3);
        }

        .badge-info {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 600;
            border: 1px solid rgba(59, 130, 246, 0.3);
        }

        /* Number Animation */
        .number-animate {
            display: inline-block;
            animation: numberPop 0.6s ease-out;
        }

        @keyframes numberPop {
            0% {
                transform: scale(0.5);
                opacity: 0;
            }
            50% {
                transform: scale(1.1);
            }
            100% {
                transform: scale(1);
                opacity: 1;
            }
        }

        /* Floating Animation */
        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% {
                transform: translateY(0px);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Fade In Animation */
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        /* Fade In Up Animation */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Slide Down Animation */
        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Bounce Once Animation */
        @keyframes bounceOnce {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-10px);
            }
        }

        /* Shake Animation */
        @keyframes shake {
            0%, 100% {
                transform: translateX(0);
            }
            10%, 30%, 50%, 70%, 90% {
                transform: translateX(-5px);
            }
            20%, 40%, 60%, 80% {
                transform: translateX(5px);
            }
        }

        /* Slow Pulse Animation */
        @keyframes pulseSlow {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: 0.6;
            }
        }

        /* Slow Bounce Animation */
        @keyframes bounceSlow {
            0%, 100% {
                transform: translateY(0);
            }
            50% {
                transform: translateY(-5px);
            }
        }

        .animate-fade-in {
            animation: fadeIn 0.6s ease-out;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }

        .animate-slide-down {
            animation: slideDown 0.4s ease-out;
        }

        .animate-bounce-once {
            animation: bounceOnce 0.6s ease-out;
        }

        .animate-shake {
            animation: shake 0.5s ease-out;
        }

        .animate-pulse-slow {
            animation: pulseSlow 3s ease-in-out infinite;
        }

        .animate-bounce-slow {
            animation: bounceSlow 2s ease-in-out infinite;
        }

        /* Responsive adjustments */
        @media (max-width: 640px) {
            .action-card {
                min-h: 200px;
            }
        }
    </style>

    <script>
        // Manila Time Clock - Real server time
        function updateManilaTime() {
            // Get current time from browser which reflects actual system/server time
            const now = new Date();

            // Format time in 12-hour format with AM/PM
            const timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true,
                timeZone: 'Asia/Manila'
            };
            const timeString = now.toLocaleTimeString('en-US', timeOptions);

            document.getElementById('manilaTime').textContent = timeString;

            // Format date
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: 'Asia/Manila'
            };
            const dateString = now.toLocaleDateString('en-US', dateOptions);
            document.getElementById('currentDate').textContent = dateString;
        }

        // Update time immediately and then every second to show real time
        updateManilaTime();
        setInterval(updateManilaTime, 1000);

        // Add entrance animations to stat cards
        document.addEventListener('DOMContentLoaded', function() {
            const statCards = document.querySelectorAll('.stat-card');
            statCards.forEach((card, index) => {
                card.style.opacity = '0';
                setTimeout(() => {
                    card.style.opacity = '1';
                }, index * 100);
            });

            // Add stagger animation to action cards
            const actionCards = document.querySelectorAll('.action-card');
            actionCards.forEach((card, index) => {
                card.style.animationDelay = `${0.5 + (index * 0.1)}s`;
            });
        });
    </script>
</x-app-layout>