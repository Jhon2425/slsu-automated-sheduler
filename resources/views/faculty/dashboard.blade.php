<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8 relative overflow-hidden">
        <!-- Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-green-500/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <!-- Page Header with Time/Date -->
            <div class="mb-8 animate-fade-in flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div class="animate-slide-in-left">
                    <h1 class="text-5xl font-bold text-white flex items-center mb-2">
                        <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-purple-600 flex items-center justify-center mr-4 shadow-2xl shadow-blue-500/50 floating">
                            <i class="fas fa-tachometer-alt text-2xl animate-pulse-gentle"></i>
                        </div>
                        <span class="animate-text-shimmer">Faculty Dashboard</span>
                    </h1>
                    <p class="text-white/80 text-lg mt-2">Welcome back, <span class="font-semibold text-white">{{ Auth::user()->name }}</span>!</p>
                </div>

                <!-- Manila Time and Date Card -->
                <div class="glass-card-modern px-6 py-4 rounded-2xl border border-white/20 hover:scale-105 transition-all duration-300 animate-slide-in-right">
                    <div class="flex items-center gap-4">
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-orange-500 to-red-600 flex items-center justify-center shadow-lg">
                            <i class="fas fa-clock text-white text-xl"></i>
                        </div>
                        <div>
                            <div class="text-xs text-white/70 font-semibold uppercase tracking-wider mb-1">Manila Time</div>
                            <div class="text-2xl font-bold text-white" id="manila-time">--:--:--</div>
                            <div class="text-xs text-white/80 font-medium" id="manila-date">Loading...</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="glass-card-modern border-l-4 border-green-500 text-white px-6 py-5 rounded-2xl mb-6 flex items-center shadow-2xl animate-slide-down backdrop-blur-xl">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-green-500 to-green-600 flex items-center justify-center mr-4 shadow-lg animate-scale-bounce">
                        <i class="fas fa-check-circle text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="font-bold block text-lg mb-1">Success!</span>
                        <span class="text-sm text-white/90">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.classList.add('animate-fade-out')" class="ml-4 w-10 h-10 rounded-xl hover:bg-white/20 flex items-center justify-center transition-all duration-300 hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="glass-card-modern border-l-4 border-red-500 text-white px-6 py-5 rounded-2xl mb-6 flex items-center shadow-2xl animate-slide-down animate-shake backdrop-blur-xl">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-red-500 to-red-600 flex items-center justify-center mr-4 shadow-lg animate-scale-bounce">
                        <i class="fas fa-exclamation-circle text-white text-2xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="font-bold block text-lg mb-1">Error!</span>
                        <span class="text-sm text-white/90">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.classList.add('animate-fade-out')" class="ml-4 w-10 h-10 rounded-xl hover:bg-white/20 flex items-center justify-center transition-all duration-300 hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Available Programs -->
                <div class="stat-card-enhanced animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="glass-card-modern rounded-2xl p-6 h-full border border-white/20 hover:border-blue-500/50 transition-all duration-500 group overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2 flex items-center">
                                        <span class="w-2 h-2 bg-blue-500 rounded-full mr-2 animate-pulse"></span>
                                        Available Programs
                                    </p>
                                    <p class="text-5xl font-bold text-white mb-2 number-counter">{{ $availablePrograms->count() }}</p>
                                    <div class="flex items-center text-xs text-blue-400">
                                        <i class="fas fa-arrow-up mr-1"></i>
                                        <span>Ready to enroll</span>
                                    </div>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                                    <i class="fas fa-graduation-cap text-white text-2xl group-hover:animate-bounce"></i>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-blue-500 to-blue-700 rounded-full animate-progress-fill" style="width: 100%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- My Enrollments -->
                <div class="stat-card-enhanced animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="glass-card-modern rounded-2xl p-6 h-full border border-white/20 hover:border-green-500/50 transition-all duration-500 group overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-green-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2 flex items-center">
                                        <span class="w-2 h-2 bg-green-500 rounded-full mr-2 animate-pulse"></span>
                                        My Enrollments
                                    </p>
                                    <p class="text-5xl font-bold text-white mb-2 number-counter">{{ $enrolledPrograms->count() }}</p>
                                    <div class="flex items-center text-xs text-green-400">
                                        <i class="fas fa-check-circle mr-1"></i>
                                        <span>Enrolled programs</span>
                                    </div>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                                    <i class="fas fa-clipboard-check text-white text-2xl group-hover:animate-bounce"></i>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-green-500 to-emerald-700 rounded-full animate-progress-fill" style="width: 85%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Active Schedules -->
                <div class="stat-card-enhanced animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="glass-card-modern rounded-2xl p-6 h-full border border-white/20 hover:border-purple-500/50 transition-all duration-500 group overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2 flex items-center">
                                        <span class="w-2 h-2 bg-purple-500 rounded-full mr-2 animate-pulse"></span>
                                        Active Schedules
                                    </p>
                                    <p class="text-5xl font-bold text-white mb-2 number-counter">{{ $enrolledPrograms->where('schedules.count', '>', 0)->count() }}</p>
                                    <div class="flex items-center text-xs text-purple-400">
                                        <i class="fas fa-calendar-check mr-1"></i>
                                        <span>Classes assigned</span>
                                    </div>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-purple-500 to-purple-700 flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500">
                                    <i class="fas fa-calendar-alt text-white text-2xl group-hover:animate-bounce"></i>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-purple-500 to-purple-700 rounded-full animate-progress-fill" style="width: 70%;"></div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pending Approval -->
                <div class="stat-card-enhanced animate-fade-in-up" style="animation-delay: 0.4s;">
                    <div class="glass-card-modern rounded-2xl p-6 h-full border border-white/20 hover:border-yellow-500/50 transition-all duration-500 group overflow-hidden relative">
                        <div class="absolute inset-0 bg-gradient-to-br from-yellow-500/10 to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
                        <div class="relative z-10">
                            <div class="flex items-start justify-between mb-4">
                                <div>
                                    <p class="text-white/60 text-xs font-bold uppercase tracking-wider mb-2 flex items-center">
                                        <span class="w-2 h-2 bg-yellow-500 rounded-full mr-2 animate-pulse"></span>
                                        Pending Approval
                                    </p>
                                    <p class="text-5xl font-bold text-white mb-2 number-counter">{{ $enrolledPrograms->where('enrollment_status', 'pending')->count() }}</p>
                                    <div class="flex items-center text-xs text-yellow-400">
                                        <i class="fas fa-hourglass-half mr-1"></i>
                                        <span>Awaiting review</span>
                                    </div>
                                </div>
                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-yellow-500 to-orange-600 flex items-center justify-center shadow-xl group-hover:scale-110 group-hover:rotate-6 transition-all duration-500 animate-pulse-gentle">
                                    <i class="fas fa-clock text-white text-2xl"></i>
                                </div>
                            </div>
                            <div class="w-full h-2 bg-white/10 rounded-full overflow-hidden">
                                <div class="h-full bg-gradient-to-r from-yellow-500 to-orange-600 rounded-full animate-progress-fill" style="width: 60%;"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Available Programs Section -->
            <div class="mb-8 animate-fade-in-up" style="animation-delay: 0.5s;">
                <div class="glass-card-modern rounded-2xl p-6 border border-white/20 mb-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-3xl font-bold text-white flex items-center">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center mr-3 shadow-lg">
                                <i class="fas fa-graduation-cap text-white text-lg"></i>
                            </div>
                            Available Programs
                        </h2>
                        <span class="glass-mini-card px-4 py-2 rounded-xl text-white font-semibold">
                            {{ $availablePrograms->count() }} programs
                        </span>
                    </div>
                </div>

                @if($availablePrograms->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($availablePrograms as $index => $program)
                            <div class="glass-card-modern rounded-2xl overflow-hidden hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 border border-white/20 group animate-fade-in-up" style="animation-delay: {{ 0.6 + ($index * 0.1) }}s;">
                                <div class="bg-gradient-to-r from-blue-500/30 to-blue-600/30 p-5 backdrop-blur-xl border-b border-white/20">
                                    <h3 class="text-xl font-bold text-white mb-1 group-hover:text-blue-300 transition-colors">{{ $program->name }}</h3>
                                    <p class="text-blue-200 text-sm font-semibold">{{ $program->code }}</p>
                                </div>
                                
                                <div class="p-6">
                                    <div class="space-y-3 mb-5">
                                        <div class="flex items-center text-white">
                                            <div class="w-8 h-8 rounded-lg bg-blue-500/20 flex items-center justify-center mr-3">
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
                                                class="w-full bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-700 hover:to-blue-800 text-white font-bold py-3 px-4 rounded-xl transition-all duration-300 flex items-center justify-center shadow-lg hover:shadow-blue-500/50 hover:scale-105">
                                            <i class="fas fa-user-plus mr-2"></i>
                                            Enroll in Program
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="glass-card-modern border-l-4 border-yellow-500 p-6 rounded-2xl backdrop-blur-xl">
                        <div class="flex items-center">
                            <div class="w-14 h-14 rounded-xl bg-yellow-500/20 flex items-center justify-center mr-4">
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
                <div class="glass-card-modern rounded-2xl p-6 border border-white/20 mb-6">
                    <div class="flex justify-between items-center">
                        <h2 class="text-3xl font-bold text-white flex items-center">
                            <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center mr-3 shadow-lg">
                                <i class="fas fa-clipboard-check text-white text-lg"></i>
                            </div>
                            My Enrolled Programs
                        </h2>
                        <span class="glass-mini-card px-4 py-2 rounded-xl text-white font-semibold">
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
                            <div class="glass-card-modern rounded-2xl overflow-hidden hover:shadow-2xl transition-all duration-500 border border-white/20 group animate-fade-in-up" style="animation-delay: {{ 0.8 + ($index * 0.1) }}s;">
                                <div class="p-6">
                                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                                        <div class="flex-1">
                                            <div class="flex items-start mb-4">
                                                <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-green-500 to-emerald-700 flex items-center justify-center mr-4 shadow-lg group-hover:scale-110 group-hover:rotate-3 transition-all duration-500">
                                                    <i class="fas fa-graduation-cap text-white text-2xl"></i>
                                                </div>
                                                <div class="flex-1">
                                                    <h3 class="text-2xl font-bold text-white group-hover:text-green-300 transition-colors">{{ $program->name }}</h3>
                                                    <p class="text-white/80 text-sm font-medium mt-1">{{ $program->code }}</p>
                                                    <div class="flex flex-wrap gap-2 mt-3">
                                                        <span class="glass-badge badge-blue">
                                                            <i class="fas fa-calendar-alt mr-1 text-xs"></i>
                                                            {{ $program->semester }}
                                                        </span>
                                                        <span class="glass-badge badge-purple">
                                                            <i class="fas fa-book mr-1 text-xs"></i>
                                                            {{ $program->academic_year }}
                                                        </span>
                                                        <span class="glass-badge {{ $enrollment->enrollment_status === 'pending' ? 'badge-yellow' : 'badge-green' }}">
                                                            <i class="fas {{ $enrollment->enrollment_status === 'pending' ? 'fa-clock' : 'fa-check-circle' }} mr-1 text-xs"></i>
                                                            {{ ucfirst($enrollment->enrollment_status) }}
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>

                                            <!-- Schedule Stats -->
                                            @if($hasSchedule)
                                                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mt-5 p-5 glass-card-modern rounded-xl border border-white/20">
                                                    <div class="text-center">
                                                        <p class="text-white/70 text-xs font-bold uppercase mb-1">Classes</p>
                                                        <p class="text-2xl font-bold text-white">{{ $enrollment->schedules->count() }}</p>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-white/70 text-xs font-bold uppercase mb-1">Subject</p>
                                                        <p class="text-sm font-bold text-white">{{ $enrollment->course_subject ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-white/70 text-xs font-bold uppercase mb-1">Section</p>
                                                        <p class="text-sm font-bold text-white">{{ $enrollment->year_section ?? 'N/A' }}</p>
                                                    </div>
                                                    <div class="text-center">
                                                        <p class="text-white/70 text-xs font-bold uppercase mb-1">Hours/Week</p>
                                                        <p class="text-2xl font-bold text-white">{{ $enrollment->no_of_hours ?? 0 }}</p>
                                                    </div>
                                                </div>
                                            @else
                                                <div class="mt-5 p-4 glass-card-modern border-l-4 border-yellow-500 rounded-xl">
                                                    <p class="text-white text-sm font-medium flex items-center">
                                                        <i class="fas fa-clock mr-2 text-yellow-400"></i>
                                                        Schedule pending - waiting for admin to assign
                                                    </p>
                                                </div>
                                            @endif
                                        </div>

                                        <!-- Action Buttons -->
                                        <div class="flex flex-col gap-3 lg:min-w-[200px]">
                                            @if($hasSchedule)
                                                <a href="{{ route('faculty.schedule.view', $enrollment->id) }}" 
                                                   class="action-btn action-btn-primary">
                                                    <i class="fas fa-calendar-alt mr-2"></i>View Schedule
                                                </a>
                                                <a href="{{ route('faculty.schedule.download', $enrollment->id) }}" 
                                                   class="action-btn action-btn-danger">
                                                    <i class="fas fa-file-pdf mr-2"></i>Download PDF
                                                </a>
                                            @else
                                                <button disabled 
                                                        class="action-btn action-btn-disabled">
                                                    <i class="fas fa-calendar-alt mr-2"></i>No Schedule Yet
                                                </button>
                                            @endif
                                            
                                            <form action="{{ route('faculty.programs.unenroll', $enrollment->id) }}" 
                                                  method="POST" 
                                                  onsubmit="return confirm('Are you sure you want to unenroll from this program?');">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" 
                                                        class="action-btn action-btn-secondary w-full">
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
                    <div class="glass-card-modern border-l-4 border-blue-500 p-6 rounded-2xl backdrop-blur-xl">
                        <div class="flex items-center">
                            <div class="w-14 h-14 rounded-xl bg-blue-500/20 flex items-center justify-center mr-4">
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

    <script>
        // Manila Time and Date Display
        function updateManilaTime() {
            const now = new Date();
            
            // Convert to Manila time (UTC+8)
            const manilaTime = new Date(now.toLocaleString('en-US', { timeZone: 'Asia/Manila' }));
            
            // Format time
            const hours = String(manilaTime.getHours()).padStart(2, '0');
            const minutes = String(manilaTime.getMinutes()).padStart(2, '0');
            const seconds = String(manilaTime.getSeconds()).padStart(2, '0');
            const timeString = `${hours}:${minutes}:${seconds}`;
            
            // Format date
            const options = { 
                weekday: 'long', 
                year: 'numeric', 
                month: 'long', 
                day: 'numeric',
                timeZone: 'Asia/Manila'
            };
            const dateString = manilaTime.toLocaleDateString('en-US', options);
            
            // Update DOM
            document.getElementById('manila-time').textContent = timeString;
            document.getElementById('manila-date').textContent = dateString;
        }
        
        // Update immediately and then every second
        updateManilaTime();
        setInterval(updateManilaTime, 1000);
    </script>

    <style>
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

        /* Glass Badges */
        .glass-badge {
            padding: 0.5rem 1rem;
            border-radius: 0.75rem;
            font-size: 0.75rem;
            font-weight: 700;
            backdrop-filter: blur(10px);
            border: 1px solid;
            display: inline-flex;
            align-items: center;
        }

        .badge-blue {
            background: rgba(59, 130, 246, 0.2);
            color: #93c5fd;
            border-color: rgba(59, 130, 246, 0.3);
        }

        .badge-purple {
            background: rgba(168, 85, 247, 0.2);
            color: #c4b5fd;
            border-color: rgba(168, 85, 247, 0.3);
        }

        .badge-green {
            background: rgba(34, 197, 94, 0.2);
            color: #86efac;
            border-color: rgba(34, 197, 94, 0.3);
        }

        .badge-yellow {
            background: rgba(234, 179, 8, 0.2);
            color: #fde047;
            border-color: rgba(234, 179, 8, 0.3);
        }

        /* Action Buttons */
        .action-btn {
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            font-weight: 700;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            white-space: nowrap;
        }

        .action-btn-primary {
            background: linear-gradient(135deg, #3b82f6, #2563eb);
            color: white;
            box-shadow: 0 4px 15px rgba(59, 130, 246, 0.3);
        }

        .action-btn-primary:hover {
            background: linear-gradient(135deg, #2563eb, #1d4ed8);
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(59, 130, 246, 0.5);
        }

        .action-btn-danger {
            background: linear-gradient(135deg, #ef4444, #dc2626);
            color: white;
            box-shadow: 0 4px 15px rgba(239, 68, 68, 0.3);
        }

        .action-btn-danger:hover {
            background: linear-gradient(135deg, #dc2626, #b91c1c);
            transform: translateY(-2px);
            box-shadow: 0 6px 25px rgba(239, 68, 68, 0.5);
        }

        .action-btn-secondary {
            background: rgba(255, 255, 255, 0.1);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.2);
            backdrop-filter: blur(10px);
        }

        .action-btn-secondary:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.2);
        }

        .action-btn-disabled {
            background: rgba(107, 114, 128, 0.3);
            color: rgba(255, 255, 255, 0.5);
            cursor: not-allowed;
            backdrop-filter: blur(10px);
        }

        .action-btn:active:not(.action-btn-disabled) {
            transform: translateY(0) scale(0.98);
        }

        /* Animated Background Blobs */
        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(30px, -50px) scale(1.1); }
            50% { transform: translate(-20px, 30px) scale(0.9); }
            75% { transform: translate(50px, 50px) scale(1.05); }
        }

        .animate-blob {
            animation: blob 10s infinite ease-in-out;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        /* Animations */
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
            from { width: 0; }
        }

        @keyframes textShimmer {
            0% { background-position: -1000px 0; }
            100% { background-position: 1000px 0; }
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

        .animate-fade-in {
            animation: fadeIn 0.8s ease-out;
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
            animation: progressFill 2s ease-out;
        }

        .animate-text-shimmer {
            background: linear-gradient(90deg, #ffffff 0%, #3b82f6 50%, #ffffff 100%);
            background-size: 200% 100%;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            animation: textShimmer 3s linear infinite;
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
            transform: translateY(-8px);
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
    </style>
</x-app-layout>