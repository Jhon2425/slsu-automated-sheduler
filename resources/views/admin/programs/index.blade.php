<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header with Breadcrumb -->
            <div class="mb-8 animate-fade-in flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
                <div>
                    <div class="flex items-center text-white/70 text-sm mb-2">
                        <a href="{{ route('admin.dashboard') }}" class="hover:text-white transition-colors hover:scale-105 inline-flex items-center">
                            <i class="fas fa-home mr-2"></i>Dashboard
                        </a>
                        <i class="fas fa-chevron-right mx-2 text-xs animate-pulse-slow"></i>
                        <span class="text-white font-medium">Faculty</span>
                    </div>
                    <h1 class="text-4xl font-bold text-white flex items-center">
                        <i class="fas fa-user-graduate mr-3 floating"></i>Faculty Management
                    </h1>
                    <p class="mt-2 text-white/90 text-lg">Monitor faculty enrollments and manage schedules</p>
                </div>

                <!-- Quick Stats Mini Cards -->
                <div class="flex gap-3">
                    <div class="glass-mini-card px-4 py-3 rounded-xl">
                        <div class="text-xs text-white/70 mb-1">Total Faculty</div>
                        <div class="text-2xl font-bold text-white">{{ $faculties->count() }}</div>
                    </div>
                    <div class="glass-mini-card px-4 py-3 rounded-xl">
                        <div class="text-xs text-white/70 mb-1">Pending</div>
                        <div class="text-2xl font-bold text-yellow-400">{{ $faculties->sum('pending_count') }}</div>
                    </div>
                </div>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="glass-card border-l-4 border-green-400 text-white px-6 py-4 rounded-xl mb-6 flex items-center shadow-xl animate-slide-down">
                    <div class="bg-green-500 rounded-full p-3 mr-4 animate-bounce-once">
                        <i class="fas fa-check-circle text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="font-bold block text-sm mb-1">Success!</span>
                        <span class="text-sm">{{ session('success') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="ml-4 hover:bg-white/20 p-2 rounded-lg transition hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Error Message -->
            @if(session('error'))
                <div class="glass-card border-l-4 border-red-400 text-white px-6 py-4 rounded-xl mb-6 flex items-center shadow-xl animate-slide-down animate-shake">
                    <div class="bg-red-500 rounded-full p-3 mr-4 animate-bounce-once">
                        <i class="fas fa-exclamation-circle text-xl"></i>
                    </div>
                    <div class="flex-1">
                        <span class="font-bold block text-sm mb-1">Error!</span>
                        <span class="text-sm">{{ session('error') }}</span>
                    </div>
                    <button onclick="this.parentElement.remove()" class="ml-4 hover:bg-white/20 p-2 rounded-lg transition hover:rotate-90">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @endif

            <!-- Statistics Cards -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
                <!-- Total Faculty -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Total Faculty</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $faculties->count() }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-users text-[#6D9773] mr-1"></i>
                                Registered members
                            </p>
                        </div>
                        <div class="icon-container rounded-2xl p-4 shadow-lg" style="background: linear-gradient(135deg, #6D9773 0%, #5a7d60 100%);">
                            <i class="fas fa-user-graduate text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>

                <!-- Total Enrollments -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.15s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Enrollments</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $faculties->sum('enrollments_count') }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-clipboard-list text-blue-400 mr-1"></i>
                                Total enrolled
                            </p>
                        </div>
                        <div class="icon-container bg-gradient-to-br from-blue-400 to-blue-600 rounded-2xl p-4 shadow-lg">
                            <i class="fas fa-clipboard-list text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>

                <!-- Pending Actions -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Pending</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $faculties->sum('pending_count') }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-clock text-yellow-400 mr-1"></i>
                                Needs attention
                            </p>
                        </div>
                        <div class="icon-container bg-gradient-to-br from-yellow-400 to-yellow-600 rounded-2xl p-4 shadow-lg animate-pulse-slow">
                            <i class="fas fa-clock text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>

                <!-- Active Enrollments -->
                <div class="stat-card glass-card rounded-2xl shadow-xl p-6 hover-lift animate-fade-in-up" style="animation-delay: 0.25s;">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-white/80 text-sm font-medium mb-2">Active</p>
                            <p class="text-4xl font-bold text-white number-animate">{{ $faculties->sum('active_count') }}</p>
                            <p class="text-xs text-white/60 mt-2 flex items-center">
                                <i class="fas fa-check-circle text-[#6D9773] mr-1"></i>
                                Currently active
                            </p>
                        </div>
                        <div class="icon-container rounded-2xl p-4 shadow-lg" style="background: linear-gradient(135deg, #6D9773 0%, #5a7d60 100%);">
                            <i class="fas fa-check-circle text-white text-3xl"></i>
                        </div>
                    </div>
                    <div class="progress-bar mt-4"></div>
                </div>
            </div>

            <!-- Faculty Table -->
            <div class="glass-card rounded-2xl overflow-hidden shadow-xl animate-fade-in-up" style="animation-delay: 0.3s;">
                <div class="px-8 py-6" style="background: rgba(109, 151, 115, 0.15); backdrop-filter: blur(10px);">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-2xl font-bold text-white flex items-center">
                                <div class="rounded-xl p-2 mr-3" style="background: rgba(109, 151, 115, 0.3);">
                                    <i class="fas fa-list text-white"></i>
                                </div>
                                Faculty Members
                            </h3>
                            <p class="text-sm text-white/80 mt-1">Manage enrollments and assign schedules</p>
                        </div>
                        <div class="text-white/70 text-sm">
                            <i class="fas fa-users mr-2"></i>
                            {{ $faculties->count() }} {{ Str::plural('member', $faculties->count()) }}
                        </div>
                    </div>
                </div>

                <div class="overflow-x-auto">
                    @if($faculties->count() > 0)
                        <table class="w-full">
                            <thead style="background: rgba(109, 151, 115, 0.1); backdrop-filter: blur(5px);">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        <i class="fas fa-hashtag mr-2"></i>Faculty ID
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-white uppercase tracking-wider">
                                        <i class="fas fa-user mr-2"></i>Faculty Name
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        <i class="fas fa-clipboard-list mr-2"></i>Enrollments
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        <i class="fas fa-clock mr-2"></i>Pending
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        <i class="fas fa-check-circle mr-2"></i>Active
                                    </th>
                                    <th class="px-6 py-4 text-center text-xs font-semibold text-white uppercase tracking-wider">
                                        <i class="fas fa-cog mr-2"></i>Actions
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-white/10">
                                @foreach($faculties as $index => $faculty)
                                    <tr class="table-row hover:bg-white/5 transition-all duration-300" style="animation: tableRowFadeIn 0.5s ease-out {{ $index * 0.05 }}s both;">
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="text-sm font-bold text-white/90 px-3 py-1 rounded-full inline-flex items-center" style="background: rgba(109, 151, 115, 0.2);">
                                                <i class="fas fa-id-badge mr-2 text-xs"></i> 
                                                {{-- show the faculty_id saved on users table; fallback to numeric id --}}
                                                {{ $faculty->faculty_id ?? '#'.$faculty->id }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <div class="flex items-center">
                                                <div class="rounded-full p-3 mr-3 shadow-lg" style="background: linear-gradient(135deg, #6D9773 0%, #5a7d60 100%);">
                                                    <i class="fas fa-user text-white"></i>
                                                </div>
                                                <div>
                                                    <div class="text-sm font-bold text-white">{{ $faculty->name ?? 'No Name' }}</div>
                                                    <div class="text-xs text-white/60">
                                                        <i class="fas fa-envelope mr-1"></i>{{ $faculty->email ?? 'No email' }}
                                                    </div>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-bold" style="background: rgba(59, 130, 246, 0.2); color: #93c5fd; border: 1px solid rgba(59, 130, 246, 0.3);">
                                                <i class="fas fa-clipboard-list mr-1 text-xs"></i>
                                                {{ $faculty->enrollments_count }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($faculty->pending_count > 0)
                                                <span class="badge-warning animate-pulse-slow inline-flex items-center">
                                                    <i class="fas fa-exclamation-triangle mr-1 text-xs"></i>
                                                    {{ $faculty->pending_count }}
                                                </span>
                                            @else
                                                <span class="text-sm text-white/40 inline-flex items-center">
                                                    <i class="fas fa-check mr-1 text-xs"></i>0
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-center">
                                            @if($faculty->active_count > 0)
                                                <span class="badge-success-custom inline-flex items-center">
                                                    <i class="fas fa-check-circle mr-1 text-xs"></i>
                                                    {{ $faculty->active_count }}
                                                </span>
                                            @else
                                                <span class="text-sm text-white/40 inline-flex items-center">
                                                    <i class="fas fa-minus mr-1 text-xs"></i>0
                                                </span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4">
                                            @if($faculty->enrollments->where('enrollment_status', 'pending')->count() > 0)
                                                <div class="flex justify-center gap-2 flex-wrap">
                                                    @foreach($faculty->enrollments->where('enrollment_status', 'pending') as $enrollment)
                                                        <div class="flex gap-2 items-center p-2 rounded-lg glass-item">
                                                            <!-- Accept Enrollment -->
                                                            <form action="{{ route('admin.enrollments.accept', $enrollment->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" 
                                                                        class="action-button bg-gradient-to-br from-green-500 to-green-600 hover:from-green-600 hover:to-green-700 shadow-lg" 
                                                                        title="Accept Enrollment"
                                                                        onclick="return confirm('Accept this enrollment?');">
                                                                    <i class="fas fa-check"></i>
                                                                </button>
                                                            </form>

                                                            <!-- Decline Enrollment -->
                                                            <form action="{{ route('admin.enrollments.decline', $enrollment->id) }}" method="POST" class="inline">
                                                                @csrf
                                                                <button type="submit" 
                                                                        class="action-button bg-gradient-to-br from-red-500 to-red-600 hover:from-red-600 hover:to-red-700 shadow-lg" 
                                                                        title="Decline Enrollment"
                                                                        onclick="return confirm('Are you sure you want to decline this enrollment?');">
                                                                    <i class="fas fa-times"></i>
                                                                </button>
                                                            </form>

                                                            <!-- Assign Schedule -->
                                                            <a href="{{ route('admin.enrollments.assign-schedule', $enrollment->id) }}" 
                                                               class="action-button bg-gradient-to-br from-blue-500 to-blue-600 hover:from-blue-600 hover:to-blue-700 shadow-lg" 
                                                               title="Assign Schedule">
                                                                <i class="fas fa-calendar-alt"></i>
                                                            </a>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @else
                                                <div class="text-center">
                                                    <span class="text-xs text-white/50 italic">
                                                        <i class="fas fa-check-circle mr-1"></i>No pending actions
                                                    </span>
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="px-8 py-20 text-center">
                            <div class="inline-flex items-center justify-center w-24 h-24 rounded-full mb-6 animate-bounce-slow" style="background: rgba(109, 151, 115, 0.15);">
                                <i class="fas fa-user-graduate text-6xl" style="color: #6D9773;"></i>
                            </div>
                            <h3 class="text-2xl font-bold text-white mb-3">No Faculty Found</h3>
                            <p class="text-white/70 mb-8 max-w-md mx-auto">
                                Faculty members will appear here once they are registered in the system.
                            </p>
                        </div>
                    @endif
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

        .glass-mini-card {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            transition: all 0.3s ease;
        }

        .glass-mini-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
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
            background: linear-gradient(90deg, #6D9773, #5a7d60);
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

        /* Action Button */
        .action-button {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            padding: 0.65rem;
            border-radius: 0.75rem;
            color: white;
            transition: all 0.3s ease;
            font-size: 0.95rem;
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        .action-button:hover {
            transform: scale(1.15) rotate(5deg);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.4);
        }

        .action-button:active {
            transform: scale(0.95);
        }

        /* Hover Lift */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        /* Table Row Animation */
        .table-row {
            transition: all 0.3s ease;
        }

        .table-row:hover {
            transform: scale(1.005);
            box-shadow: 0 5px 20px rgba(109, 151, 115, 0.2);
        }

        @keyframes tableRowFadeIn {
            from {
                opacity: 0;
                transform: translateX(-20px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        /* Badge Success Custom */
        .badge-success-custom {
            background: rgba(109, 151, 115, 0.25);
            color: #a8d5b0;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid rgba(109, 151, 115, 0.4);
            box-shadow: 0 2px 8px rgba(109, 151, 115, 0.3);
        }

        /* Badge Warning */
        .badge-warning {
            background: rgba(234, 179, 8, 0.2);
            color: #fde047;
            padding: 0.35rem 0.85rem;
            border-radius: 9999px;
            font-size: 0.75rem;
            font-weight: 700;
            border: 1px solid rgba(234, 179, 8, 0.3);
            box-shadow: 0 2px 8px rgba(234, 179, 8, 0.3);
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
                transform: translateY(-10px);
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
    </style>
</x-app-layout>