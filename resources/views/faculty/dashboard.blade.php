<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header -->
            <div class="mb-8 animate-fade-in">
                <h1 class="text-3xl font-bold text-white flex items-center">
                    <i class="fas fa-tachometer-alt mr-3 animate-pulse-slow"></i>Faculty Dashboard
                </h1>
                <p class="mt-2 text-white/80">Welcome back, {{ Auth::user()->name }}!</p>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-white/20 border border-white/30 text-white px-4 py-3 rounded-lg mb-6 flex items-center backdrop-blur-sm animate-slide-down">
                    <i class="fas fa-check-circle mr-3 text-xl animate-bounce-once"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-white/20 border border-white/30 text-white px-4 py-3 rounded-lg mb-6 flex items-center backdrop-blur-sm animate-slide-down animate-shake">
                    <i class="fas fa-exclamation-circle mr-3 text-xl animate-bounce-once"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">Available Programs</p>
                            <p class="text-3xl font-bold text-white">{{ $availablePrograms->count() }}</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4">
                            <i class="fas fa-graduation-cap text-blue-500 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">My Enrollments</p>
                            <p class="text-3xl font-bold text-white">{{ $enrolledPrograms->count() }}</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4">
                            <i class="fas fa-clipboard-check text-green-500 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">Active Schedules</p>
                            <p class="text-3xl font-bold text-white">{{ $enrolledPrograms->where('schedules.count', '>', 0)->count() }}</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4">
                            <i class="fas fa-calendar-alt text-purple-500 text-3xl"></i>
                        </div>
                    </div>
                </div>

                <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover:shadow-2xl transition-all duration-300 hover:-translate-y-2 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white text-sm font-medium mb-1">Pending Approval</p>
                            <p class="text-3xl font-bold text-white">{{ $enrolledPrograms->where('enrollment_status', 'pending')->count() }}</p>
                        </div>
                        <div class="bg-white/30 rounded-full p-4">
                            <i class="fas fa-clock text-yellow-500 text-3xl"></i>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Programs Section -->
            <div class="mb-8 animate-fade-in-up" style="animation-delay: 0.5s;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-graduation-cap mr-3 text-blue-500"></i>
                        Available Programs
                    </h2>
                    <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-semibold backdrop-blur-sm">
                        {{ $availablePrograms->count() }} programs
                    </span>
                </div>

                @if($availablePrograms->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($availablePrograms as $program)
                            <div class="bg-white/20 rounded-lg shadow-md overflow-hidden hover:shadow-xl transition-all duration-300 hover:-translate-y-1 border border-white/30 backdrop-blur-md">
                                <div class="bg-gradient-to-r from-blue-500/80 to-blue-600/80 p-4 backdrop-blur-sm">
                                    <h3 class="text-xl font-bold text-white mb-1">{{ $program->name }}</h3>
                                    <p class="text-blue-100 text-sm">{{ $program->code }}</p>
                                </div>
                                
                                <div class="p-5">
                                    <div class="space-y-3 mb-4">
                                        <div class="flex items-center text-white">
                                            <i class="fas fa-calendar text-blue-400 w-5"></i>
                                            <span class="ml-2 text-sm">{{ $program->semester }} - {{ $program->academic_year }}</span>
                                        </div>
                                        @if($program->description)
                                            <p class="text-white/80 text-sm mt-2 line-clamp-2">{{ $program->description }}</p>
                                        @endif
                                    </div>

                                    <form action="{{ route('faculty.programs.enroll', $program->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-4 rounded-lg transition duration-200 flex items-center justify-center">
                                            <i class="fas fa-user-plus mr-2"></i>
                                            Enroll in Program
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white/10 border-l-4 border-yellow-400 p-6 rounded-lg shadow-md backdrop-blur-sm">
                        <div class="flex items-center">
                            <i class="fas fa-info-circle text-yellow-400 text-3xl mr-4"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">No Available Programs</h3>
                                <p class="text-white/80">There are currently no programs available for enrollment.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Enrolled Programs Section -->
            <div class="animate-fade-in-up" style="animation-delay: 0.6s;">
                <div class="flex justify-between items-center mb-4">
                    <h2 class="text-2xl font-bold text-white flex items-center">
                        <i class="fas fa-clipboard-check mr-3 text-green-500"></i>
                        My Enrolled Programs
                    </h2>
                    <span class="bg-white/20 text-white px-3 py-1 rounded-full text-sm font-semibold backdrop-blur-sm">
                        {{ $enrolledPrograms->count() }} enrolled
                    </span>
                </div>

                @if($enrolledPrograms->count() > 0)
                    <div class="space-y-4">
                        @foreach($enrolledPrograms as $enrollment)
                            @php
                                $program = $enrollment->program;
                                $hasSchedule = $enrollment->schedules->count() > 0;
                            @endphp
                            <div class="bg-white/20 rounded-lg shadow-md overflow-hidden hover:shadow-lg transition-all duration-300 border border-white/30 backdrop-blur-md">
                                <div class="p-6">
                                    <div class="flex flex-col md:flex-row md:items-center md:justify-between">
                                        <div class="flex-1">
                                            <div class="flex items-start mb-3">
                                                <div class="bg-white/30 rounded-full p-3 mr-4 backdrop-blur-sm">
                                                    <i class="fas fa-graduation-cap text-green-400 text-xl"></i>
                                                </div>
                                                <div>
                                                    <h3 class="text-xl font-bold text-white">{{ $program->name }}</h3>
                                                    <p class="text-white/80 text-sm">{{ $program->code }}</p>
                                                    <div class="flex flex-wrap gap-2 mt-2">
                                                        <span class="bg-blue-500/30 text-white px-2 py-1 rounded text-xs font-semibold backdrop-blur-sm">
                                                            {{ $program->semester }}
                                                        </span>
                                                        <span class="bg-purple-500/30 text-white px-2 py-1 rounded text-xs font-semibold backdrop-blur-sm">
                                                            {{ $program->academic_year }}
                                                        </span>
                                                        <span class="px-2 py-1 rounded text-xs backdrop-blur-sm
                                                            {{ $enrollment->enrollment_status === 'pending' ? 'bg-yellow-500/30 text-white' : 'bg-green-500/30 text-white' }}">
                                                            {{ ucfirst($enrollment->enrollment_status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Schedule Stats -->
                                            @if($hasSchedule)
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 mt-4 p-4 bg-white/10 rounded-lg backdrop-blur-sm">
                                                    <div class="text-center">
                                                        <p class="text-white/80 text-xs font-medium">Classes</p>
                                                        <p class="text-lg font-bold text-white">{{ $enrollment->schedules->count() }}</p>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-white/80 text-xs font-medium">Subject</p>
                                                        <p class="text-sm font-bold text-white">{{ $enrollment->course_subject ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-white/80 text-xs font-medium">Section</p>
                                                        <p class="text-sm font-bold text-white">{{ $enrollment->year_section ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-white/80 text-xs font-medium">Hours/Week</p>
                                                        <p class="text-lg font-bold text-white">{{ $enrollment->no_of_hours ?? 0 }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-4 p-3 bg-yellow-500/20 border border-yellow-400/30 rounded-lg backdrop-blur-sm">
                                                    <p class="text-white text-sm flex items-center">
                                                        <i class="fas fa-clock mr-2"></i>
                                                        Schedule pending - waiting for admin to assign
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="mt-4 md:mt-0 md:ml-6 flex flex-col gap-2">
                                            @if($hasSchedule)
                                                <a href="{{ route('faculty.schedule.view', $enrollment->id) }}" 
                                                   class="bg-blue-600 hover:bg-blue-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 text-center whitespace-nowrap">
                                                    <i class="fas fa-calendar-alt mr-2"></i>View Schedule
                                                </a>
                                                <a href="{{ route('faculty.schedule.download', $enrollment->id) }}" 
                                                   class="bg-red-600 hover:bg-red-700 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 text-center whitespace-nowrap">
                                                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                                                </a>
                                            @else
                                                <button disabled 
                                                        class="bg-gray-500/50 text-white/70 font-semibold py-2 px-6 rounded-lg cursor-not-allowed backdrop-blur-sm">
                                                    <i class="fas fa-calendar-alt mr-2"></i>No Schedule Yet
                                                </button>
                                            @endif
                                            
                                            <form action="{{ route('faculty.programs.unenroll', $enrollment->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to unenroll from this program?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="w-full bg-white/20 hover:bg-white/30 text-white font-semibold py-2 px-6 rounded-lg transition duration-200 backdrop-blur-sm">
                                                    <i class="fas fa-sign-out-alt mr-2"></i>Unenroll
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="bg-white/10 border-l-4 border-blue-400 p-6 rounded-lg shadow-md backdrop-blur-sm">
                        <div class="flex items-center">
                            <i class="fas fa-graduation-cap text-blue-400 text-3xl mr-4"></i>
                            <div>
                                <h3 class="text-lg font-semibold text-white mb-1">Not Enrolled Yet</h3>
                                <p class="text-white/80">You haven't enrolled in any programs yet. Browse available programs above to get started!</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <style>
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

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

        @keyframes bounceOnce {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-5px); }
            20%, 40%, 60%, 80% { transform: translateX(5px); }
        }

        @keyframes pulseSlow {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        .animate-fade-in { animation: fadeIn 0.6s ease-out; }
        .animate-fade-in-up {
            animation: fadeInUp 0.6s ease-out;
            animation-fill-mode: both;
        }
        .animate-slide-down { animation: slideDown 0.4s ease-out; }
        .animate-bounce-once { animation: bounceOnce 0.6s ease-out; }
        .animate-shake { animation: shake 0.5s ease-out; }
        .animate-pulse-slow { animation: pulseSlow 3s ease-in-out infinite; }
    </style>
</x-app-layout>