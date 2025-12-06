<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header -->
            <div class="mb-8 animate-fade-in">
                <h1 class="text-3xl font-bold text-white flex items-center">
                    <i class="fas fa-tachometer-alt mr-3 animate-pulse-slow"></i>Admin Dashboard
                </h1>
                <p class="mt-2 text-white/80">Welcome back, {{ auth()->user()->name }}!</p>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-white/20 border border-white/30 text-white px-4 py-3 rounded-lg mb-6 flex items-center backdrop-blur-sm animate-slide-down">
                    <i class="fas fa-check-circle mr-3 text-xl animate-bounce-once"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="bg-white/20 border border-white/30 text-white px-4 py-3 rounded-lg mb-6 flex items-center backdrop-blur-sm animate-slide-down animate-shake">
                    <i class="fas fa-exclamation-circle mr-3 text-xl animate-bounce-once"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-6 mb-8">
                <!-- Total Faculties Card -->
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-105 animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">Total Faculties</p>
                            <p class="text-3xl font-bold text-white transition-all duration-500 hover:scale-110">{{ $totalFaculties ?? 0 }}</p>
                            <p class="text-xs text-white/70 mt-1">Registered faculty members</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4 transition-all duration-300 hover:rotate-12 hover:scale-110">
                            <i class="fas fa-chalkboard-teacher text-blue-500 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Programs Card -->
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-105 animate-fade-in-up" style="animation-delay: 0.15s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">Active Programs</p>
                            <p class="text-3xl font-bold text-white transition-all duration-500 hover:scale-110">{{ $totalPrograms ?? 0 }}</p>
                            <p class="text-xs text-white/70 mt-1">Current programs</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4 transition-all duration-300 hover:rotate-12 hover:scale-110">
                            <i class="fas fa-graduation-cap text-indigo-500 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Schedules Card -->
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-105 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">Total Schedules</p>
                            <p class="text-3xl font-bold text-white transition-all duration-500 hover:scale-110">{{ $totalSchedules ?? 0 }}</p>
                            <p class="text-xs text-white/70 mt-1">Generated schedules</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4 transition-all duration-300 hover:rotate-12 hover:scale-110">
                            <i class="fas fa-calendar-alt text-green-500 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Total Classrooms Card -->
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-105 animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">Classrooms</p>
                            <p class="text-3xl font-bold text-white transition-all duration-500 hover:scale-110">{{ $totalClassrooms ?? 0 }}</p>
                            <p class="text-xs text-white/70 mt-1">Available rooms</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4 transition-all duration-300 hover:rotate-12 hover:scale-110">
                            <i class="fas fa-door-open text-purple-500 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <!-- Enrollments Card -->
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 hover:scale-105 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">Enrollments</p>
                            <p class="text-3xl font-bold text-white transition-all duration-500 hover:scale-110">{{ $totalEnrollments ?? 0 }}</p>
                            <p class="text-xs text-white/70 mt-1">Faculty enrolled</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4 transition-all duration-300 hover:rotate-12 hover:scale-110">
                            <i class="fas fa-user-check text-yellow-500 text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Quick Actions Section -->
            <div class="bg-white/20 rounded-lg shadow-md p-6 mb-8 border border-white/30 backdrop-blur-md animate-fade-in-up" style="animation-delay: 0.5s;">
                <h2 class="text-xl font-bold text-white mb-6 flex items-center">
                    <i class="fas fa-bolt mr-3 text-yellow-500 animate-pulse-slow"></i>
                    Quick Actions
                </h2>
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-4">
                    <!-- Manage Programs -->
                    <a href="{{ route('admin.programs.index') }}" 
                      class="bg-white/20 hover:bg-indigo-700 text-white p-6 rounded-lg text-center transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 hover:shadow-2xl shadow-md backdrop-blur-sm group">
                        <i class="fas fa-graduation-cap text-4xl mb-3 transition-all duration-300 group-hover:scale-125 group-hover:rotate-6"></i>
                        <p class="font-semibold text-lg">Manage Programs</p>
                        <p class="text-xs text-white/70 mt-2">Create and manage programs</p>
                    </a>

                    <!-- Manage Faculties -->
                    <a href="{{ route('admin.faculties.index') }}" 
                      class="bg-white/20 hover:bg-green-700 text-white p-6 rounded-lg text-center transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 hover:shadow-2xl shadow-md backdrop-blur-sm group">
                        <i class="fas fa-users text-4xl mb-3 transition-all duration-300 group-hover:scale-125 group-hover:rotate-6"></i>
                        <p class="font-semibold text-lg">Manage Faculties</p>
                        <p class="text-xs text-white/70 mt-2">Add, edit, or remove faculty members</p>
                    </a>

                    <!-- View Schedules -->
                    <a href="{{ route('admin.schedules.index') }}" 
                      class="bg-white/20 hover:bg-purple-600 text-white p-6 rounded-lg text-center transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 hover:shadow-2xl shadow-md backdrop-blur-sm group">
                        <i class="fas fa-calendar text-4xl mb-3 transition-all duration-300 group-hover:scale-125 group-hover:rotate-6"></i>
                        <p class="font-semibold text-lg">View Schedules</p>
                        <p class="text-xs text-white/70 mt-2">Browse all generated schedules</p>
                    </a>

                    <!-- Generate Schedule -->
                    <form method="POST" action="{{ route('admin.schedules.generate') }}" class="m-0">
                        @csrf
                        <button type="submit" 
                                class="w-full bg-white/20 hover:bg-red-700 text-white p-6 rounded-lg text-center transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 hover:shadow-2xl shadow-md backdrop-blur-sm group">
                            <i class="fas fa-magic text-4xl mb-3 transition-all duration-300 group-hover:scale-125 group-hover:rotate-12"></i>
                            <p class="font-semibold text-lg">Generate Schedule</p>
                            <p class="text-xs text-white/70 mt-2">Auto-generate new schedules</p>
                        </button>
                    </form>

                    <!-- Download Reports -->
                    <a href="{{ route('admin.schedules.download') }}" 
                       class="bg-white/20 hover:bg-orange-700 text-white p-6 rounded-lg text-center transition-all duration-300 transform hover:scale-110 hover:-translate-y-1 hover:shadow-2xl shadow-md backdrop-blur-sm group">
                        <i class="fas fa-file-pdf text-4xl mb-3 transition-all duration-300 group-hover:scale-125 group-hover:-rotate-6"></i>
                        <p class="font-semibold text-lg">Download Report</p>
                        <p class="text-xs text-white/70 mt-2">Export schedules as PDF</p>
                    </a>
                </div>
            </div>

            <!-- Recent Activity & Programs Overview -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                <!-- Recent Programs -->
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md animate-fade-in-up" style="animation-delay: 0.6s;">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                        <i class="fas fa-graduation-cap mr-3 text-indigo-500 animate-pulse-slow"></i>
                        Recent Programs
                    </h2>
                    @if(isset($recentPrograms) && $recentPrograms->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentPrograms as $program)
                                <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all duration-300 backdrop-blur-sm hover:translate-x-2 hover:shadow-lg">
                                    <div class="flex items-center flex-1 min-w-0">
                                        <div class="bg-white/20 rounded-full p-2 mr-3 transition-all duration-300 hover:rotate-12 hover:scale-110 flex-shrink-0">
                                            <i class="fas fa-book text-indigo-500"></i>
                                        </div>
                                        <div class="min-w-0 flex-1">
                                            <p class="font-semibold text-white truncate">{{ $program->name }}</p>
                                            <p class="text-xs text-white/70">{{ $program->code }}</p>
                                        </div>
                                    </div>
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold ml-2 flex-shrink-0">
                                        {{ $program->enrollments_count ?? 0 }} enrolled
                                    </span>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('admin.programs.index') }}" 
                           class="block mt-4 text-center text-white/80 hover:text-white text-sm transition-colors">
                            View all programs →
                        </a>
                    @else
                        <p class="text-white/70 text-center py-8">No programs created yet</p>
                        <a href="{{ route('admin.programs.create') }}" 
                           class="block mt-4 bg-white/20 hover:bg-white/30 text-white py-2 px-4 rounded-lg text-center transition-all">
                            <i class="fas fa-plus mr-2"></i>Create First Program
                        </a>
                    @endif
                </div>

                <!-- Recent Faculties -->
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md animate-fade-in-up" style="animation-delay: 0.7s;">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                        <i class="fas fa-history mr-3 text-blue-500 animate-spin-slow"></i>
                        Recent Faculties
                    </h2>
                    @if(isset($recentFaculties) && $recentFaculties->count() > 0)
                        <div class="space-y-3">
                            @foreach($recentFaculties as $faculty)
                                <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all duration-300 backdrop-blur-sm hover:translate-x-2 hover:shadow-lg">
                                    <div class="flex items-center">
                                        <div class="bg-white/20 rounded-full p-2 mr-3 transition-all duration-300 hover:rotate-12 hover:scale-110">
                                            <i class="fas fa-user text-blue-500"></i>
                                        </div>
                                        <div>
                                            <p class="font-semibold text-white">{{ $faculty->name }}</p>
                                            <p class="text-xs text-white/70">{{ $faculty->course_subject ?? 'No subject assigned' }}</p>
                                        </div>
                                    </div>
                                    <span class="text-xs text-white/70">{{ $faculty->created_at->diffForHumans() }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <p class="text-white/70 text-center py-8">No recent faculties added</p>
                    @endif
                </div>

                <!-- Pending Enrollments -->
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md animate-fade-in-up" style="animation-delay: 0.8s;">
                    <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                        <i class="fas fa-clock mr-3 text-yellow-500 animate-bounce-slow"></i>
                        Pending Enrollments
                    </h2>
                    @if(isset($pendingEnrollments) && $pendingEnrollments->count() > 0)
                        <div class="space-y-3">
                            @foreach($pendingEnrollments as $enrollment)
                                <div class="p-3 bg-white/10 rounded-lg hover:bg-white/20 transition-all duration-300 backdrop-blur-sm hover:translate-x-2 hover:shadow-lg">
                                    <div class="flex items-center justify-between mb-2">
                                        <p class="font-semibold text-white text-sm">{{ $enrollment->faculty->name }}</p>
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-0.5 rounded text-xs font-semibold">Pending</span>
                                    </div>
                                    <p class="text-xs text-white/70 mb-2">{{ $enrollment->program->name }}</p>
                                    <a href="{{ route('admin.enrollments.edit', $enrollment->id) }}" 
                                       class="block text-center bg-blue-500 hover:bg-blue-600 text-white py-1 px-3 rounded text-xs transition-colors">
                                        <i class="fas fa-edit mr-1"></i>Assign Schedule
                                    </a>
                                </div>
                            @endforeach
                        </div>
                        <a href="{{ route('admin.programs.index') }}" 
                           class="block mt-4 text-center text-white/80 hover:text-white text-sm transition-colors">
                            View all enrollments →
                        </a>
                    @else
                        <div class="text-center py-8">
                            <i class="fas fa-check-circle text-green-400 text-4xl mb-2"></i>
                            <p class="text-white/70 text-sm">All caught up!</p>
                            <p class="text-white/50 text-xs mt-1">No pending enrollments</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- System Statistics -->
            <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md animate-fade-in-up" style="animation-delay: 0.9s;">
                <h2 class="text-xl font-bold text-white mb-4 flex items-center">
                    <i class="fas fa-chart-bar mr-3 text-green-500 animate-bounce-slow"></i>
                    System Statistics
                </h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg backdrop-blur-sm transition-all duration-300 hover:bg-white/20 hover:translate-x-2 hover:shadow-lg">
                        <span class="text-white/80 text-sm">Average Class Size</span>
                        <span class="font-bold text-white transition-all duration-300 hover:scale-110">{{ $avgClassSize ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg backdrop-blur-sm transition-all duration-300 hover:bg-white/20 hover:translate-x-2 hover:shadow-lg">
                        <span class="text-white/80 text-sm">Scheduled Hours</span>
                        <span class="font-bold text-white transition-all duration-300 hover:scale-110">{{ $totalHours ?? 'N/A' }}h</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg backdrop-blur-sm transition-all duration-300 hover:bg-white/20 hover:translate-x-2 hover:shadow-lg">
                        <span class="text-white/80 text-sm">Lab Classes</span>
                        <span class="font-bold text-white transition-all duration-300 hover:scale-110">{{ $labCount ?? 'N/A' }}</span>
                    </div>
                    <div class="flex items-center justify-between p-3 bg-white/10 rounded-lg backdrop-blur-sm transition-all duration-300 hover:bg-white/20 hover:translate-x-2 hover:shadow-lg">
                        <span class="text-white/80 text-sm">Lecture Classes</span>
                        <span class="font-bold text-white transition-all duration-300 hover:scale-110">{{ $lectureCount ?? 'N/A' }}</span>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <style>
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

        /* Slow Spin Animation */
        @keyframes spinSlow {
            from {
                transform: rotate(0deg);
            }
            to {
                transform: rotate(360deg);
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

        .animate-spin-slow {
            animation: spinSlow 8s linear infinite;
        }

        .animate-bounce-slow {
            animation: bounceSlow 2s ease-in-out infinite;
        }
    </style>
</x-app-layout>