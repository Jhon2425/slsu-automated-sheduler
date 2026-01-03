<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8 relative overflow-hidden">
        <!-- Enhanced Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-green-500/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
            <div class="absolute top-1/2 left-1/4 w-64 h-64 bg-pink-500/10 rounded-full blur-3xl animate-blob animation-delay-3000"></div>
            <div class="absolute bottom-1/4 right-1/4 w-72 h-72 bg-cyan-500/10 rounded-full blur-3xl animate-blob animation-delay-5000"></div>
        </div>

        <!-- Particle Effect -->
        <div class="particles-container absolute inset-0 pointer-events-none"></div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <!-- Page Header with Time/Date -->
            <div class="mb-8 animate-fade-in flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="animate-slide-in-left">
                    <h1 class="text-5xl font-bold text-white flex items-center mb-2">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 via-blue-600 to-purple-600 flex items-center justify-center mr-4 shadow-2xl shadow-blue-500/50 floating relative overflow-hidden group">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                            <i class="fas fa-tachometer-alt text-2xl animate-pulse-gentle relative z-10"></i>
                        </div>
                        <span class="animate-text-shimmer bg-clip-text text-transparent bg-gradient-to-r from-white via-blue-200 to-purple-300">Faculty Dashboard</span>
                    </h1>
                    <p class="text-white/80 text-lg mt-2 animate-fade-in-delayed">Welcome back, <span class="font-semibold text-white animate-glow">{{ Auth::user()->name }}</span>!</p>
                </div>

                <!-- Manila Time and Date Card -->
                <div class="glass-card-modern px-6 py-4 rounded-2xl border border-white/20 hover:scale-105 transition-all duration-300 animate-slide-in-right hover:border-orange-500/50 group">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center shadow-lg group-hover:rotate-12 transition-transform duration-300 relative overflow-hidden">
                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent shimmer-slow"></div>
                            <i class="fas fa-clock text-white text-xl animate-tick relative z-10"></i>
                        </div>
                        <div>
                            <div class="text-xs text-white/70 font-semibold uppercase tracking-wider mb-1">Manila Time</div>
                            <div class="text-2xl font-bold text-white tabular-nums" id="manila-time">--:--:--</div>
                            <div class="text-xs text-white/80 font-medium" id="manila-date">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="glass-card-modern border-l-4 border-green-500 text-white px-6 py-5 rounded-2xl mb-6 flex items-center shadow-2xl animate-slide-down backdrop-blur-xl hover:scale-[1.02] transition-transform duration-300">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center mr-4 shadow-lg animate-scale-bounce relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent shimmer"></div>
                        <i class="fas fa-check-circle text-white text-2xl relative z-10"></i>
                    </div>
                    <div class="flex-1">
                        <span class="font-bold block text-lg mb-1">Success!</span>
                        <span class="text-sm text-white/90">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.classList.add('animate-fade-out')" class="ml-4 w-10 h-10 rounded-xl hover:bg-white/20 flex items-center justify-center transition-all duration-300 hover:rotate-90 hover:scale-110">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="glass-card-modern border-l-4 border-red-500 text-white px-6 py-5 rounded-2xl mb-6 flex items-center shadow-2xl animate-slide-down animate-shake backdrop-blur-xl hover:scale-[1.02] transition-transform duration-300">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center mr-4 shadow-lg animate-scale-bounce relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent shimmer"></div>
                        <i class="fas fa-exclamation-circle text-white text-2xl relative z-10"></i>
                    </div>
                    <div class="flex-1">
                        <span class="font-bold block text-lg mb-1">Error!</span>
                        <span class="text-sm text-white/90">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.classList.add('animate-fade-out')" class="ml-4 w-10 h-10 rounded-xl hover:bg-white/20 flex items-center justify-center transition-all duration-300 hover:rotate-90 hover:scale-110">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
                <!-- Available Programs -->
                <div class="stat-card-enhanced animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="glass-card-modern rounded-2xl p-6 h-full border border-white/20 hover:border-blue-500/50 transition-all duration-500 group overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-blue-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2 flex items-center">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
                                        Available Programs
                                    </p>
                                    <p class="text-5xl font-bold text-white mb-2 number-counter group-hover:scale-110 transition-transform duration-300">{{ $availablePrograms->count() }}</p>
                                    <div class="flex items-center text-xs text-blue-400">
                                        <i class="fas fa-arrow-up mr-1 animate-bounce-subtle"></i>
                                        <span>Ready to enroll</span>
                                    </div>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                                    <i class="fas fa-graduation-cap text-white text-2xl group-hover:animate-bounce relative z-10"></i>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 via-blue-600 to-blue-700 rounded-full animate-progress-fill shadow-lg shadow-blue-500/50" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Enrollments -->
                <div class="stat-card-enhanced animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="glass-card-modern rounded-2xl p-6 h-full border border-white/20 hover:border-green-500/50 transition-all duration-500 group overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-green-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2 flex items-center">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                        My Enrollments
                                    </p>
                                    <p class="text-5xl font-bold text-white mb-2 number-counter group-hover:scale-110 transition-transform duration-300">{{ $enrolledPrograms->count() }}</p>
                                    <div class="flex items-center text-xs text-green-400">
                                        <i class="fas fa-check-circle mr-1 animate-pulse-gentle"></i>
                                        <span>Enrolled programs</span>
                                    </div>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                                    <i class="fas fa-clipboard-check text-white text-2xl group-hover:animate-bounce relative z-10"></i>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-green-500 via-green-600 to-emerald-700 rounded-full animate-progress-fill shadow-lg shadow-green-500/50" style="width: 85%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Assigned Subjects - WITH MODAL TRIGGER -->
                <div class="stat-card-enhanced animate-fade-in-up cursor-pointer" style="animation-delay: 0.3s;" onclick="openSubjectsModal()">
                    <div class="glass-card-modern rounded-2xl p-6 h-full border border-white/20 hover:border-purple-500/50 transition-all duration-500 group overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-purple-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2 flex items-center">
                                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 animate-pulse"></span>
                                        Assigned Subjects
                                    </p>
                                    <p class="text-5xl font-bold text-white mb-2 number-counter group-hover:scale-110 transition-transform duration-300">{{ $assignedSubjects->count() }}</p>
                                    <div class="flex items-center text-xs text-purple-400">
                                        <i class="fas fa-book mr-1 animate-pulse-gentle"></i>
                                        <span>Click to view details</span>
                                    </div>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                                    <i class="fas fa-chalkboard-teacher text-white text-2xl group-hover:animate-bounce relative z-10"></i>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-500 via-purple-600 to-purple-700 rounded-full animate-progress-fill shadow-lg shadow-purple-500/50" style="width: 70%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approval -->
                <div class="stat-card-enhanced animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="glass-card-modern rounded-2xl p-6 h-full border border-white/20 hover:border-yellow-500/50 transition-all duration-500 group overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="absolute top-0 right-0 w-32 h-32 bg-yellow-500/10 rounded-full blur-2xl group-hover:scale-150 transition-transform duration-700"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2 flex items-center">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></span>
                                        Pending Approval
                                    </p>
                                    <p class="text-5xl font-bold text-white mb-2 number-counter group-hover:scale-110 transition-transform duration-300">{{ $enrolledPrograms->where('enrollment_status', 'pending')->count() }}</p>
                                    <div class="flex items-center text-xs text-yellow-400">
                                        <i class="fas fa-hourglass-half mr-1 animate-spin-slow"></i>
                                        <span>Awaiting review</span>
                                    </div>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 animate-pulse-gentle relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                                    <i class="fas fa-clock text-white text-2xl relative z-10"></i>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-yellow-500 via-yellow-600 to-orange-600 rounded-full animate-progress-fill shadow-lg shadow-yellow-500/50" style="width: 60%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Programs Section -->
            <div class="mb-8 animate-fade-in-up" style="animation-delay: 0.5s;">
                <div class="glass-card-modern rounded-2xl p-6 border border-white/20 mb-6 hover:border-blue-500/30 transition-all duration-300 group">
                    <div class="flex justify-between items-center">
                        <h2 class="text-3xl font-bold text-white flex items-center">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center mr-3 shadow-lg group-hover:rotate-12 transition-transform duration-300 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                                <i class="fas fa-graduation-cap text-white text-lg relative z-10"></i>
                            </div>
                            <span class="group-hover:text-blue-300 transition-colors duration-300">Available Programs</span>
                        </h2>
                        <span class="glass-mini-card px-4 py-2 rounded-xl text-white font-semibold animate-pulse-gentle">
                            {{ $availablePrograms->count() }} programs
                        </span>
                    </div>
                </div>

                @if($availablePrograms->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($availablePrograms as $index => $program)
                            <div class="glass-card-modern rounded-2xl overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 border border-white/20 group animate-fade-in-up hover:border-blue-500/50 relative" style="animation-delay: {{ 0.6 + ($index * 0.1) }}s;">
                                <div class="absolute inset-0 bg-gradient-to-br from-blue-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                
                                <div class="bg-gradient-to-r from-blue-500/30 to-blue-600/30 p-5 backdrop-blur-xl border-b border-white/20 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent shimmer-slow"></div>
                                    <h3 class="text-xl font-bold text-white mb-1 group-hover:text-blue-200 transition-colors duration-300 relative z-10">{{ $program->name }}</h3>
                                    <p class="text-blue-200 text-sm font-semibold relative z-10">{{ $program->code }}</p>
                                </div>
                                
                                <div class="p-6 relative z-10">
                                    <div class="space-y-3 mb-5">
                                        <div class="flex items-center text-white group/item">
                                            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300">
                                                <i class="fas fa-calendar text-blue-400 text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium">{{ $program->semester }} - {{ $program->academic_year }}</span>
                                        </div>
                                        @if($program->description)
                                            <p class="text-white/80 text-sm mt-3 line-clamp-2 pl-11">{{ $program->description }}</p>
                                        @endif
                                    </div>

                                    <form action="{{ route('faculty.programs.enroll', $program->id) }}" method="POST">
                                        @csrf
                                        <button type="submit" 
                                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 flex items-center justify-center shadow-lg hover:shadow-blue-500/50 hover:scale-105 group/btn relative overflow-hidden">
                                            <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer opacity-0 group-hover/btn:opacity-100"></div>
                                            <i class="fas fa-user-plus mr-2 group-hover/btn:animate-bounce relative z-10"></i>
                                            <span class="relative z-10">Enroll in Program</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="glass-card-modern border-l-4 border-yellow-500 p-6 rounded-2xl backdrop-blur-xl hover:scale-[1.02] transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="w-14 h-14 rounded-xl bg-yellow-500/20 flex items-center justify-center mr-4 animate-pulse-gentle">
                                <i class="fas fa-info-circle text-yellow-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1">No Available Programs</h3>
                                <p class="text-white/80">There are currently no programs available for enrollment.</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Enrolled Programs Section -->
            <div class="animate-fade-in-up" style="animation-delay: 0.7s;">
                <div class="glass-card-modern rounded-2xl p-6 border border-white/20 mb-6 hover:border-green-500/30 transition-all duration-300 group">
                    <div class="flex justify-between items-center">
                        <h2 class="text-3xl font-bold text-white flex items-center">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center mr-3 shadow-lg group-hover:rotate-12 transition-transform duration-300 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                                <i class="fas fa-clipboard-check text-white text-lg relative z-10"></i>
                            </div>
                            <span class="group-hover:text-green-300 transition-colors duration-300">My Enrolled Programs</span>
                        </h2>
                        <span class="glass-mini-card px-4 py-2 rounded-xl text-white font-semibold animate-pulse-gentle">
                            {{ $enrolledPrograms->count() }} enrolled
                        </span>
                    </div>
                </div>

                @if($enrolledPrograms->count() > 0)
                    <div class="space-y-6">
                        @foreach($enrolledPrograms as $index => $enrollment)
                            @php
                                $program = $enrollment->program;
                                $hasSchedule = $enrollment->schedules->count() > 0;
                            @endphp
                            <div class="glass-card-modern rounded-2xl overflow-hidden hover:shadow-2xl transition-all duration-500 border border-white/20 group animate-fade-in-up hover:border-green-500/50 relative" style="animation-delay: {{ 0.8 + ($index * 0.1) }}s;">
                                <div class="absolute inset-0 bg-gradient-to-br from-green-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                
                                <!-- Program Header -->
                                <div class="bg-gradient-to-r from-green-500/30 to-emerald-600/30 p-6 backdrop-blur-xl border-b border-white/20 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent shimmer-slow"></div>
                                    <div class="flex items-start justify-between relative z-10">
                                        <div class="flex items-start flex-1">
                                            <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center mr-4 shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 relative overflow-hidden flex-shrink-0">
                                                <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/30 to-transparent shimmer"></div>
                                                <i class="fas fa-graduation-cap text-white text-2xl relative z-10"></i>
                                            </div>
                                            <div class="flex-1">
                                                <h3 class="text-2xl font-bold text-white mb-1 group-hover:text-green-200 transition-colors">{{ $program->name }}</h3>
                                                <p class="text-green-100 text-sm font-semibold mb-3">{{ $program->code }}</p>
                                                <div class="flex flex-wrap gap-2">
                                                    <span class="glass-badge badge-blue">
                                                        <i class="fas fa-calendar-alt mr-1 text-xs"></i>
                                                        {{ $program->semester }}
                                                    </span>
                                                    <span class="glass-badge badge-purple">
                                                        <i class="fas fa-book mr-1 text-xs"></i>
                                                        {{ $program->academic_year }}
                                                    </span>
                                                    <span class="glass-badge text-white {{ $enrollment->enrollment_status === 'pending' ? 'badge-yellow' : 'badge-green' }}">
                                                        <i class="fas {{ $enrollment->enrollment_status === 'pending' ? 'fa-clock' : 'fa-check-circle' }} mr-1 text-xs"></i>
                                                        {{ ucfirst($enrollment->enrollment_status) }}
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <!-- Program Body -->
                                <div class="p-6 relative z-10">
                                    @if($hasSchedule)
                                        <!-- Schedule Stats -->
                                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
                                            <div class="glass-card-modern rounded-xl p-4 border border-white/20 text-center hover:scale-105 transition-all duration-300 group/stat">
                                                <div class="w-10 h-10 rounded-lg bg-green-500/20 flex items-center justify-center mx-auto mb-2 group-hover/stat:scale-110 transition-transform">
                                                    <i class="fas fa-chalkboard-teacher text-green-400 text-lg"></i>
                                                </div>
                                                <p class="text-white/70 text-xs font-bold uppercase mb-1">Classes</p>
                                                <p class="text-2xl font-bold text-white">{{ $enrollment->schedules->count() }}</p>
                                            </div>
                                            <div class="glass-card-modern rounded-xl p-4 border border-white/20 text-center hover:scale-105 transition-all duration-300 group/stat">
                                                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center mx-auto mb-2 group-hover/stat:scale-110 transition-transform">
                                                    <i class="fas fa-book-open text-blue-400 text-lg"></i>
                                                </div>
                                                <p class="text-white/70 text-xs font-bold uppercase mb-1">Subject</p>
                                                <p class="text-sm font-bold text-white truncate">{{ $enrollment->course_subject ?? 'N/A' }}</p>
                                            </div>
                                            <div class="glass-card-modern rounded-xl p-4 border border-white/20 text-center hover:scale-105 transition-all duration-300 group/stat">
                                                <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center mx-auto mb-2 group-hover/stat:scale-110 transition-transform">
                                                    <i class="fas fa-users text-purple-400 text-lg"></i>
                                                </div>
                                                <p class="text-white/70 text-xs font-bold uppercase mb-1">Section</p>
                                                <p class="text-sm font-bold text-white truncate">{{ $enrollment->year_section ?? 'N/A' }}</p>
                                            </div>
                                            <div class="glass-card-modern rounded-xl p-4 border border-white/20 text-center hover:scale-105 transition-all duration-300 group/stat">
                                                <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center mx-auto mb-2 group-hover/stat:scale-110 transition-transform">
                                                    <i class="fas fa-clock text-orange-400 text-lg"></i>
                                                </div>
                                                <p class="text-white/70 text-xs font-bold uppercase mb-1">Hours/Week</p>
                                                <p class="text-2xl font-bold text-white">{{ $enrollment->no_of_hours ?? 0 }}</p>
                                            </div>
                                        </div>

                                        <!-- Action Buttons - With Schedule -->
                                        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
                                            <a href="{{ route('faculty.schedule.view', $enrollment->id) }}" 
                                               class="glass-btn glass-btn-primary group/btn">
                                                <div class="glass-btn-glow glass-btn-glow-blue"></div>
                                                <i class="fas fa-calendar-alt mr-2 group-hover/btn:scale-110 group-hover/btn:rotate-12 transition-all duration-300"></i>
                                                <span>View Schedule</span>
                                            </a>
                                            <a href="{{ route('faculty.schedule.download', $enrollment->id) }}" 
                                               class="glass-btn glass-btn-info group/btn">
                                                <div class="glass-btn-glow glass-btn-glow-cyan"></div>
                                                <i class="fas fa-file-pdf mr-2 group-hover/btn:scale-110 group-hover/btn:rotate-12 transition-all duration-300"></i>
                                                <span>Download PDF</span>
                                            </a>
                                            <button onclick="confirmUnenroll({{ $enrollment->id }}, '{{ addslashes($program->name) }}')" 
                                                    class="glass-btn glass-btn-danger group/btn">
                                                <div class="glass-btn-glow glass-btn-glow-red"></div>
                                                <i class="fas fa-sign-out-alt mr-2 group-hover/btn:scale-110 group-hover/btn:rotate-12 transition-all duration-300"></i>
                                                <span>Unenroll</span>
                                            </button>
                                        </div>
                                    @else
                                        <!-- No Schedule Yet State -->
                                        <div class="glass-card-modern border-l-4 border-yellow-500 rounded-2xl p-6 mb-6 relative overflow-hidden group/alert">
                                            <div class="absolute inset-0 bg-gradient-to-r from-yellow-500/10 to-transparent opacity-0 group-hover/alert:opacity-100 transition-opacity duration-500"></div>
                                            <div class="flex items-start relative z-10">
                                                <div class="flex-shrink-0">
                                                    <div class="w-14 h-14 rounded-xl bg-yellow-500/20 flex items-center justify-center mr-4 animate-pulse-gentle">
                                                        <i class="fas fa-hourglass-half text-yellow-400 text-2xl"></i>
                                                    </div>
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="text-lg font-bold text-white mb-2 flex items-center">
                                                        <span>Schedule Pending</span>
                                                        <span class="ml-2 px-3 py-1 bg-yellow-500/20 rounded-lg text-xs font-semibold text-yellow-300">Awaiting Admin</span>
                                                    </h4>
                                                    <p class="text-white/80 text-sm mb-4">Your schedule is being prepared by the administration. You'll be notified once it's ready.</p>
                                                    <div class="flex items-center gap-2 text-xs text-yellow-300">
                                                        <div class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></div>
                                                        <span class="font-semibold">This usually takes 1-3 business days</span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <!-- Action Buttons - No Schedule -->
                                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                            <button disabled 
                                                    class="glass-btn glass-btn-disabled group/btn cursor-not-allowed text-white">
                                                <div class="glass-btn-glow glass-btn-glow-gray"></div>
                                                <i class="fas fa-calendar-times mr-2 text-white"></i>
                                                <span>Schedule Not Available</span>
                                            </button>
                                            <button onclick="confirmUnenroll({{ $enrollment->id }}, '{{ addslashes($program->name) }}')" 
                                                    class="glass-btn glass-btn-danger group/btn text-white">
                                                <div class="glass-btn-glow glass-btn-glow-red"></div>
                                                <i class="fas fa-sign-out-alt mr-2 group-hover/btn:scale-110 group-hover/btn:rotate-12 transition-all duration-300 text-white"></i>
                                                <span>Unenroll from Program</span>
                                            </button>
                                        </div>
                                    @endif

                                    <!-- Hidden form for unenrollment -->
                                    <form id="unenroll-form-{{ $enrollment->id }}" 
                                          action="{{ route('faculty.programs.unenroll', $enrollment->id) }}" 
                                          method="POST" 
                                          class="hidden">
                                        @csrf
                                        @method('DELETE')
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="glass-card-modern border-l-4 border-blue-500 p-6 rounded-2xl backdrop-blur-xl hover:scale-[1.02] transition-transform duration-300">
                        <div class="flex items-center">
                            <div class="w-14 h-14 rounded-xl bg-blue-500/20 flex items-center justify-center mr-4 animate-pulse-gentle">
                                <i class="fas fa-graduation-cap text-blue-400 text-2xl"></i>
                            </div>
                            <div>
                                <h3 class="text-lg font-bold text-white mb-1">Not Enrolled Yet</h3>
                                <p class="text-white/80">You haven't enrolled in any programs yet. Browse available programs above to get started!</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <!-- Assigned Subjects Modal -->
    <div id="subjectsModal" class="fixed inset-0 bg-black/75 backdrop-blur-sm z-[9999] hidden items-center justify-center p-4">
        <div class="glass-card-modern rounded-3xl overflow-hidden border border-white/30 shadow-2xl max-w-6xl w-full max-h-[90vh] flex flex-col animate-modal-in">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-purple-500/40 to-purple-600/40 p-6 backdrop-blur-xl border-b border-white/20 flex justify-between items-center flex-shrink-0">
                <div class="flex items-center">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center mr-4 shadow-lg relative overflow-hidden">
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent shimmer"></div>
                        <i class="fas fa-book-open text-white text-2xl relative z-10"></i>
                    </div>
                    <div>
                        <h3 class="text-3xl font-bold text-white">My Assigned Subjects</h3>
                        <p class="text-purple-200 text-sm font-medium mt-1">{{ $assignedSubjects->count() }} subjects total</p>
                    </div>
                </div>
                <button onclick="closeSubjectsModal()" class="w-12 h-12 rounded-xl bg-white/10 hover:bg-white/20 flex items-center justify-center transition-all duration-300 hover:rotate-90 hover:scale-110 group flex-shrink-0">
                    <i class="fas fa-times text-white text-xl group-hover:text-purple-300"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="p-6 overflow-y-auto flex-1">
                @if($assignedSubjects->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($assignedSubjects as $subject)
                            <div class="glass-card-modern rounded-2xl overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 border border-white/20 group hover:border-purple-500/50 relative">
                                <div class="absolute inset-0 bg-gradient-to-br from-purple-500/5 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                                
                                <div class="bg-gradient-to-r from-purple-500/30 to-purple-600/30 p-5 backdrop-blur-xl border-b border-white/20 relative overflow-hidden">
                                    <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/10 to-transparent shimmer-slow"></div>
                                    <h3 class="text-xl font-bold text-white mb-1 group-hover:text-purple-200 transition-colors duration-300 relative z-10">{{ $subject->subject_code }}</h3>
                                    <p class="text-purple-200 text-sm font-semibold relative z-10">{{ $subject->subject_name }}</p>
                                </div>
                                
                                <div class="p-6 relative z-10">
                                    <div class="space-y-3">
                                        <div class="flex items-center text-white group/item">
                                            <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300">
                                                <i class="fas fa-graduation-cap text-purple-400 text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium">{{ $subject->program->name ?? 'N/A' }}</span>
                                        </div>
                                        <div class="flex items-center text-white group/item">
                                            <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300">
                                                <i class="fas fa-clock text-purple-400 text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium">{{ $subject->units }} Units</span>
                                        </div>
                                         <div class="flex items-center text-white group/item">
                                            <div class="w-8 h-8 rounded-lg bg-purple-500/20 flex items-center justify-center mr-3 group-hover/item:scale-110 transition-transform duration-300">
                                                <i class="fas fa-person text-purple-400 text-sm"></i>
                                            </div>
                                            <span class="text-sm font-medium">{{ $subject->enrolled_student }} Students Enrolled</span>
                                        </div>
                                        @if($subject->description)
                                            <div class="mt-4 pt-4 border-t border-white/10">
                                                <p class="text-white/70 text-xs line-clamp-3">{{ $subject->description }}</p>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="glass-card-modern border-l-4 border-purple-500 p-8 rounded-2xl backdrop-blur-xl text-center">
                        <div class="w-20 h-20 rounded-2xl bg-purple-500/20 flex items-center justify-center mx-auto mb-4">
                            <i class="fas fa-book-open text-purple-400 text-3xl"></i>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-2">No Subjects Assigned</h3>
                        <p class="text-white/70">You don't have any assigned subjects at the moment.</p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script>
        // Manila Time and Date Display
        function updateManilaTime() {
            const now = new Date();
            const manilaTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Manila' }));
            
            const hours = String(manilaTime.getHours()).padStart(2, '0');
            const minutes = String(manilaTime.getMinutes()).padStart(2, '0');
            const seconds = String(manilaTime.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;
            
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                timeZone: 'Asia/Manila'
            };
            const dateString = manilaTime.toLocaleDateString('en-US', options);
            
            document.getElementById('manila-time').textContent = timeString;
            document.getElementById('manila-date').textContent = dateString;
        }
        
        updateManilaTime();
        setInterval(updateManilaTime, 1000);

        // Modal Functions
        function openSubjectsModal() {
            const modal = document.getElementById('subjectsModal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeSubjectsModal() {
            const modal = document.getElementById('subjectsModal');
            modal.classList.add('hidden');
            modal.classList.remove('flex');
            document.body.style.overflow = 'auto';
        }

        // Close modal on outside click
        document.getElementById('subjectsModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeSubjectsModal();
            }
        });

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                const modal = document.getElementById('subjectsModal');
                if (!modal.classList.contains('hidden')) {
                    closeSubjectsModal();
                }
            }
        });

        // Unenroll Confirmation Function
        function confirmUnenroll(enrollmentId, programName) {
            // Create custom confirmation modal
            const confirmModal = document.createElement('div');
            confirmModal.className = 'fixed inset-0 bg-black/80 backdrop-blur-sm z-[10000] flex items-center justify-center p-4 animate-fade-in';
            confirmModal.innerHTML = `
                <div class="glass-card-modern rounded-3xl max-w-md w-full p-8 border border-red-500/30 animate-scale-in shadow-2xl">
                    <div class="text-center mb-6">
                        <div class="w-20 h-20 rounded-full bg-red-500/20 flex items-center justify-center mx-auto mb-4 animate-pulse-gentle">
                            <i class="fas fa-exclamation-triangle text-red-400 text-4xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-2">Confirm Unenrollment</h3>
                        <p class="text-white/80 text-sm">Are you sure you want to unenroll from:</p>
                        <p class="text-white font-bold text-lg mt-2">${programName}</p>
                    </div>
                    
                    <div class="glass-card-modern border-l-4 border-yellow-500 p-4 rounded-xl mb-6">
                        <div class="flex items-start">
                            <i class="fas fa-info-circle text-yellow-400 text-lg mr-3 mt-1"></i>
                            <div class="text-sm text-white/90">
                                <p class="font-semibold mb-1">Important Notice:</p>
                                <ul class="list-disc list-inside space-y-1 text-xs">
                                    <li>Your schedule will be removed</li>
                                    <li>You'll need to re-enroll to access this program again</li>
                                    <li>This action cannot be undone</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-2 gap-3">
                        <button onclick="this.closest('.fixed').remove(); document.body.style.overflow = 'auto';" 
                                class="glass-btn glass-btn-secondary group/btn">
                            <div class="glass-btn-glow glass-btn-glow-gray"></div>
                            <i class="fas fa-times mr-2 group-hover/btn:rotate-90 transition-all duration-300"></i>
                            <span>Cancel</span>
                        </button>
                        <button onclick="document.getElementById('unenroll-form-${enrollmentId}').submit();" 
                                class="glass-btn glass-btn-danger-solid group/btn">
                            <div class="glass-btn-glow glass-btn-glow-red"></div>
                            <i class="fas fa-sign-out-alt mr-2 group-hover/btn:scale-110 group-hover/btn:rotate-12 transition-all duration-300"></i>
                            <span>Unenroll</span>
                        </button>
                    </div>
                </div>
            `;
            
            document.body.appendChild(confirmModal);
            document.body.style.overflow = 'hidden';
            
            // Close on outside click
            confirmModal.addEventListener('click', function(e) {
                if (e.target === confirmModal) {
                    confirmModal.remove();
                    document.body.style.overflow = 'auto';
                }
            });
        }

        // Particle Effect
        function createParticles() {
            const container = document.querySelector('.particles-container');
            if (!container) return;
            
            for (let i = 0; i < 30; i++) {
                const particle = document.createElement('div');
                particle.className = 'particle';
                particle.style.left = Math.random() * 100 + '%';
                particle.style.animationDelay = Math.random() * 15 + 's';
                particle.style.animationDuration = (Math.random() * 10 + 15) + 's';
                container.appendChild(particle);
            }
        }
        
        document.addEventListener('DOMContentLoaded', createParticles);
    </script>

    <style>
        /* Modal Styles */
        .modal-overlay {
            position: fixed;
            inset: 0;
            background: rgba(0, 0, 0, 0.75);
            backdrop-filter: blur(8px);
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }

        .modal-container {
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        @keyframes modalIn {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(-20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        .animate-modal-in {
            animation: modalIn 0.3s cubic-bezier(0.4, 0, 0.2, 1) forwards;
        }

        /* Modern Glass Card Effect */
        .glass-card-modern {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        .glass-mini-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        /* Particle Effect */
        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: rgba(255, 255, 255, 0.5);
            border-radius: 50%;
            animation: particleFloat 20s infinite linear;
            box-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
        }

        @keyframes particleFloat {
            0% {
                transform: translateY(100vh) translateX(0) scale(0);
                opacity: 0;
            }
            10% {
                opacity: 1;
            }
            90% {
                opacity: 1;
            }
            100% {
                transform: translateY(-100vh) translateX(100px) scale(1);
                opacity: 0;
            }
        }

        /* Shimmer Effect */
        .shimmer {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
            animation: shimmer 3s infinite;
        }

        .shimmer-slow {
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            animation: shimmer 5s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        /* Enhanced Animations */
        @keyframes blob {
            0%, 100% { 
                transform: translate(0, 0) scale(1) rotate(0deg); 
            }
            25% { 
                transform: translate(30px, -50px) scale(1.1) rotate(90deg); 
            }
            50% { 
                transform: translate(-20px, 30px) scale(0.9) rotate(180deg); 
            }
            75% { 
                transform: translate(50px, 50px) scale(1.05) rotate(270deg); 
            }
        }

        .animate-blob {
            animation: blob 15s infinite ease-in-out;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-3000 {
            animation-delay: 3s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        .animation-delay-5000 {
            animation-delay: 5s;
        }

        /* Glow Animation */
        @keyframes glow {
            0%, 100% {
                text-shadow: 0 0 10px rgba(255, 255, 255, 0.5),
                             0 0 20px rgba(255, 255, 255, 0.3),
                             0 0 30px rgba(255, 255, 255, 0.2);
            }
            50% {
                text-shadow: 0 0 20px rgba(255, 255, 255, 0.8),
                             0 0 30px rgba(255, 255, 255, 0.5),
                             0 0 40px rgba(255, 255, 255, 0.3);
            }
        }

        .animate-glow {
            animation: glow 3s ease-in-out infinite;
        }

        /* Clock Tick Animation */
        @keyframes tick {
            0%, 100% { transform: rotate(0deg); }
            50% { transform: rotate(360deg); }
        }

        .animate-tick {
            animation: tick 2s linear infinite;
        }

        /* Bounce Subtle */
        @keyframes bounceSubtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }

        .animate-bounce-subtle {
            animation: bounceSubtle 2s ease-in-out infinite;
        }

        /* Spin Slow */
        @keyframes spinSlow {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }

        .animate-spin-slow {
            animation: spinSlow 3s linear infinite;
        }

        /* Base Animations */
        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(50px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInLeft {
            from {
                opacity: 0;
                transform: translateX(-60px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(60px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes fadeOut {
            to {
                opacity: 0;
                transform: translateY(-20px);
                height: 0;
                margin: 0;
                padding: 0;
            }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            10%, 30%, 50%, 70%, 90% { transform: translateX(-8px); }
            20%, 40%, 60%, 80% { transform: translateX(8px); }
        }

        @keyframes scaleBounce {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }

        @keyframes progressFill {
            from { 
                width: 0;
                opacity: 0.5;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes textShimmer {
            0% { background-position: -200% center; }
            100% { background-position: 200% center; }
        }

        @keyframes numberCount {
            0% {
                transform: scale(0.5) translateY(-20px);
                opacity: 0;
            }
            60% {
                transform: scale(1.15);
            }
            100% {
                transform: scale(1) translateY(0);
                opacity: 1;
            }
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-12px); }
        }

        @keyframes pulse {
            0%, 100% { opacity: 1; }
            50% { opacity: 0.6; }
        }

        /* Animation Classes */
        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
        }

        .animate-fade-in-delayed {
            animation: fadeIn 1s ease-out 0.3s backwards;
        }

        .animate-fade-in-up {
            animation: fadeInUp 0.9s ease-out;
            animation-fill-mode: both;
        }

        .animate-slide-in-left {
            animation: slideInLeft 0.8s ease-out;
        }

        .animate-slide-in-right {
            animation: slideInRight 0.8s ease-out;
        }

        .animate-slide-down {
            animation: slideDown 0.6s ease-out;
        }

        .animate-fade-out {
            animation: fadeOut 0.5s ease-out forwards;
        }

        .animate-shake {
            animation: shake 0.6s ease-out;
        }

        .animate-scale-bounce {
            animation: scaleBounce 0.8s ease-out;
        }

        .animate-progress-fill {
            animation: progressFill 2.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .animate-text-shimmer {
            background: linear-gradient(90deg, #ffffff 0%, #60a5fa 25%, #a78bfa 50%, #60a5fa 75%, #ffffff 100%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: textShimmer 4s linear infinite;
        }

        .animate-pulse-gentle {
            animation: pulse 4s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        .number-counter {
            animation: numberCount 1s cubic-bezier(0.34, 1.56, 0.64, 1);
        }

        /* Enhanced Stat Card */
        .stat-card-enhanced {
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card-enhanced:hover {
            transform: translateY(-8px) scale(1.02);
        }

        /* Smooth transitions */
        * {
            scroll-behavior: smooth;
        }

        /* Line Clamp Utility */
        .line-clamp-2 {
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .line-clamp-3 {
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        /* Tabular Numbers */
        .tabular-nums {
            font-variant-numeric: tabular-nums;
        }

        /* Hover Effects */
        .group:hover .group-hover\:animate-bounce {
            animation: bounceSubtle 1s ease-in-out infinite;
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            transition: background 0.3s;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.3);
        }

        /* Focus States */
        button:focus,
        a:focus {
            outline: 2px solid rgba(59, 130, 246, 0.5);
            outline-offset: 2px;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .stat-card-enhanced:hover {
                transform: translateY(-4px) scale(1.01);
            }
        }

        .glass-badge {
            color: white;
            /* for soft contrast
            color: rgba(255, 255, 255, 0.9)
             */
        }

        .glass-badge i {
            color: white;
        }
    </style>
</x-app-layout>