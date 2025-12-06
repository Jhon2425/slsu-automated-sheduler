<x-app-layout>
    <style>
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slideInRight {
            from {
                opacity: 0;
                transform: translateX(30px);
            }
            to {
                opacity: 1;
                transform: translateX(0);
            }
        }

        @keyframes pulse {
            0%, 100% {
                opacity: 1;
            }
            50% {
                opacity: .7;
            }
        }

        @keyframes shimmer {
            0% {
                background-position: -1000px 0;
            }
            100% {
                background-position: 1000px 0;
            }
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.6s ease-out forwards;
        }

        .animate-slideInRight {
            animation: slideInRight 0.6s ease-out forwards;
        }

        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        }

        .shimmer {
            background: linear-gradient(90deg, 
                rgba(255, 255, 255, 0) 0%, 
                rgba(255, 255, 255, 0.2) 50%, 
                rgba(255, 255, 255, 0) 100%);
            background-size: 1000px 100%;
            animation: shimmer 2s infinite;
        }

        .table-row-hover {
            transition: all 0.3s ease;
        }

        .table-row-hover:hover {
            background: rgba(255, 255, 255, 0.5) !important;
            transform: scale(1.01);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }

        .modal-overlay {
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        .modal-content {
            animation: slideUp 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(50px) scale(0.9);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

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
            background: linear-gradient(90deg, transparent, rgba(255,255,255,0.3), transparent);
            transition: left 0.5s;
        }

        .stat-card:hover::before {
            left: 100%;
        }

        .badge-pulse {
            animation: pulse 2s cubic-bezier(0.4, 0, 0.6, 1) infinite;
        }

        .floating {
            animation: floating 3s ease-in-out infinite;
        }

        @keyframes floating {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
    </style>

    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/20 backdrop-blur-md rounded-lg p-6 shadow-md border border-white/30">
                <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                    <!-- Page Header -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 animate-fadeInUp">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-calendar-alt mr-3 floating"></i>All Schedules
                            </h1>
                            <p class="mt-2 text-gray-600">View and manage all generated schedules</p>
                        </div>
                        <div class="flex flex-wrap gap-3 animate-slideInRight">
                            <form method="POST" action="{{ route('admin.schedules.generate') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="group relative bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105 overflow-hidden">
                                    <span class="relative z-10 flex items-center">
                                        <i class="fas fa-magic mr-2 group-hover:rotate-12 transition-transform"></i>Generate Schedule
                                    </span>
                                    <div class="absolute inset-0 shimmer"></div>
                                </button>
                            </form>
                            <a href="{{ route('admin.schedules.download') }}" 
                               class="group bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105 inline-block">
                                <i class="fas fa-file-pdf mr-2 group-hover:rotate-12 transition-transform"></i>Download PDF
                            </a>
                            @php
                                $schedulesCollection = isset($schedules)
                                    ? ($schedules instanceof \Illuminate\Pagination\LengthAwarePaginator
                                        ? $schedules->getCollection()
                                        : $schedules)
                                    : collect();
                                $totalSchedules = isset($schedules) && method_exists($schedules, 'total')
                                    ? $schedules->total()
                                    : $schedulesCollection->count();
                            @endphp

                            @if($schedulesCollection->count() > 0)
                                <button onclick="window.print()" 
                                        class="group bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105">
                                    <i class="fas fa-print mr-2 group-hover:scale-110 transition-transform"></i>Print
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="bg-white/20 border border-white/30 text-white px-4 py-3 rounded-lg mb-6 flex items-center backdrop-blur-sm shadow-lg animate-fadeInUp">
                            <div class="bg-green-500 rounded-full p-2 mr-3">
                                <i class="fas fa-check-circle text-xl"></i>
                            </div>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center shadow-lg animate-fadeInUp">
                            <div class="bg-red-500 rounded-full p-2 mr-3">
                                <i class="fas fa-exclamation-circle text-white text-xl"></i>
                            </div>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <!-- Statistics Cards -->
                    @if($schedulesCollection->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div class="stat-card bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover-lift" style="animation-delay: 0.1s;">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm font-medium">Total Schedules</p>
                                        <p class="text-3xl font-bold text-gray-800">{{ $totalSchedules }}</p>
                                    </div>
                                    <i class="fas fa-calendar-check text-blue-500 text-3xl"></i>
                                </div>
                            </div>

                            <div class="stat-card bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover-lift" style="animation-delay: 0.2s;">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm font-medium">Unique Faculties</p>
                                        <p class="text-3xl font-bold text-gray-800">{{ $schedulesCollection->pluck('faculty_id')->unique()->count() }}</p>
                                    </div>
                                    <i class="fas fa-user-tie text-green-500 text-3xl"></i>
                                </div>
                            </div>

                            <div class="stat-card bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover-lift" style="animation-delay: 0.3s;">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm font-medium">Classrooms Used</p>
                                        <p class="text-3xl font-bold text-gray-800">{{ $schedulesCollection->pluck('classroom_id')->unique()->count() }}</p>
                                    </div>
                                    <i class="fas fa-door-open text-purple-500 text-3xl"></i>
                                </div>
                            </div>

                            <div class="stat-card bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md hover-lift" style="animation-delay: 0.4s;">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm font-medium">This Week</p>
                                        @php
                                            $thisWeekCount = $schedulesCollection->filter(function($s) {
                                                try {
                                                    $date = $s->schedule_date instanceof \Illuminate\Support\Carbon
                                                        ? $s->schedule_date
                                                        : \Illuminate\Support\Carbon::parse($s->schedule_date);
                                                    return $date->isCurrentWeek();
                                                } catch (\Exception $e) {
                                                    return false;
                                                }
                                            })->count();
                                        @endphp
                                        <p class="text-3xl font-bold text-gray-800">{{ $thisWeekCount }}</p>
                                    </div>
                                    <i class="fas fa-calendar-week text-orange-500 text-3xl badge-pulse"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="bg-white/20 rounded-lg shadow-md p-6 mb-6 border border-white/30 backdrop-blur-md animate-fadeInUp">
                        <form method="GET" action="{{ route('admin.schedules.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Faculty</label>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Faculty name..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Day</label>
                                <select name="day" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300">
                                    <option value="">All Days</option>
                                    <option value="Monday" {{ request('day') == 'Monday' ? 'selected' : '' }}>Monday</option>
                                    <option value="Tuesday" {{ request('day') == 'Tuesday' ? 'selected' : '' }}>Tuesday</option>
                                    <option value="Wednesday" {{ request('day') == 'Wednesday' ? 'selected' : '' }}>Wednesday</option>
                                    <option value="Thursday" {{ request('day') == 'Thursday' ? 'selected' : '' }}>Thursday</option>
                                    <option value="Friday" {{ request('day') == 'Friday' ? 'selected' : '' }}>Friday</option>
                                    <option value="Saturday" {{ request('day') == 'Saturday' ? 'selected' : '' }}>Saturday</option>
                                    <option value="Sunday" {{ request('day') == 'Sunday' ? 'selected' : '' }}>Sunday</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Classroom</label>
                                <select name="classroom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300">
                                    <option value="">All Classrooms</option>
                                    @if(isset($classrooms))
                                        @foreach($classrooms as $classroom)
                                            <option value="{{ $classroom->id }}" {{ request('classroom') == $classroom->id ? 'selected' : '' }}>
                                                {{ $classroom->room_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Section</label>
                                <input type="text" name="section" value="{{ request('section') }}" 
                                       placeholder="e.g., 1A, 2B..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 transition-all duration-300">
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit" 
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition-all duration-300 transform hover:scale-105">
                                    <i class="fas fa-filter mr-2"></i>Filter
                                </button>
                                <a href="{{ route('admin.schedules.index') }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition-all duration-300 transform hover:scale-105">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Schedules Table -->
                    @if($schedulesCollection->count() > 0)
                        <div class="bg-white/20 rounded-lg shadow-md overflow-hidden border border-white/30 backdrop-blur-md animate-fadeInUp">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-white/50 rounded-lg shadow-md overflow-hidden border border-white/30 backdrop-blur-md">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-hashtag mr-2"></i>ID
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-user mr-2"></i>Faculty
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-book mr-2"></i>Subject
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-door-open mr-2"></i>Classroom
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-calendar-day mr-2"></i>Day
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-clock mr-2"></i>Time
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-users mr-2"></i>Section
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-calendar-alt mr-2"></i>Date
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-cog mr-2"></i>Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white/20 rounded-lg shadow-md overflow-hidden border border-white/30 backdrop-blur-md">
                                        @foreach($schedulesCollection as $index => $schedule)
                                        <tr class="table-row-hover hover:bg-gray-50 transition duration-150" style="animation: fadeInUp 0.5s ease-out {{ $index * 0.05 }}s both;">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                #{{ $schedule->id }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="bg-blue-100 rounded-full p-2 mr-3 transform hover:scale-110 transition-transform">
                                                        <i class="fas fa-user-tie text-blue-600 text-sm"></i>
                                                    </div>
                                                    <div>
                                                        <div class="text-sm font-medium text-gray-900">{{ optional($schedule->faculty)->name }}</div>
                                                        <div class="text-xs text-gray-500">{{ optional($schedule->faculty)->action_type }}</div>
                                                    </div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ optional($schedule->faculty)->course_subject }}</div>
                                                <div class="text-xs text-gray-500">{{ optional($schedule->faculty)->units }} units</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800 transform hover:scale-105 transition-transform">
                                                    <i class="fas fa-door-open mr-1"></i>
                                                    {{ optional($schedule->classroom)->room_name }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold transform hover:scale-105 transition-transform
                                                    @if($schedule->day == 'Monday') bg-blue-100 text-blue-800
                                                    @elseif($schedule->day == 'Tuesday') bg-green-100 text-green-800
                                                    @elseif($schedule->day == 'Wednesday') bg-yellow-100 text-yellow-800
                                                    @elseif($schedule->day == 'Thursday') bg-purple-100 text-purple-800
                                                    @elseif($schedule->day == 'Friday') bg-pink-100 text-pink-800
                                                    @elseif($schedule->day == 'Saturday') bg-indigo-100 text-indigo-800
                                                    @else bg-gray-100 text-gray-800 @endif">
                                                    {{ $schedule->day }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ date('g:i A', strtotime($schedule->start_time)) }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    to {{ date('g:i A', strtotime($schedule->end_time)) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ optional($schedule->faculty)->year_section }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                @php
                                                    try {
                                                        $date = $schedule->schedule_date instanceof \Illuminate\Support\Carbon
                                                            ? $schedule->schedule_date
                                                            : \Illuminate\Support\Carbon::parse($schedule->schedule_date);
                                                        $dateFormatted = $date->format('M d, Y');
                                                    } catch (\Exception $e) {
                                                        $dateFormatted = $schedule->schedule_date;
                                                    }
                                                @endphp
                                                {{ $dateFormatted }}
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <button onclick="viewScheduleDetails({{ $schedule->id }})" 
                                                        class="text-blue-600 hover:text-blue-800 transition-all transform hover:scale-125" 
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        @if(isset($schedules) && method_exists($schedules, 'links'))
                            <div class="mt-6">
                                {{ $schedules->links() }}
                            </div>
                        @endif
                    @else
                        <!-- No Schedules Message -->
                        <div class="bg-white/20 rounded-lg shadow-md p-12 text-center border border-white/30 backdrop-blur-md animate-fadeInUp">
                            <div class="floating">
                                <i class="fas fa-calendar-times text-gray-300 text-6xl mb-4"></i>
                            </div>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Schedules Found</h3>
                            <p class="text-gray-500 mb-6">There are no schedules available. Generate schedules to get started.</p>
                            <form method="POST" action="{{ route('admin.schedules.generate') }}" class="inline">
                                @csrf
                                <button type="submit" 
                                        class="group bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg transition-all duration-300 shadow-md hover:shadow-lg transform hover:scale-105">
                                    <i class="fas fa-magic mr-2 group-hover:rotate-12 transition-transform"></i>Generate Schedule Now
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Schedule Details Modal -->
    <div id="scheduleModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50 modal-overlay">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white modal-content">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Schedule Details</h3>
                    <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-all transform hover:rotate-90 hover:scale-110">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="modalContent" class="text-sm text-gray-700">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script>
        function viewScheduleDetails(scheduleId) {
            const modal = document.getElementById('scheduleModal');
            const content = document.getElementById('modalContent');
            
            modal.classList.remove('hidden');
            content.innerHTML = `
                <p class="mb-2"><strong>Schedule ID:</strong> ${scheduleId}</p>
                <p class="text-gray-600">Loading details...</p>
            `;
        }

        function closeModal() {
            document.getElementById('scheduleModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('scheduleModal');
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        }

        // Add staggered animation to cards on load
        document.addEventListener('DOMContentLoaded', function() {
            const cards = document.querySelectorAll('.stat-card');
            cards.forEach((card, index) => {
                card.style.opacity = '0';
                setTimeout(() => {
                    card.classList.add('animate-fadeInUp');
                }, index * 100);
            });
        });
    </script>
</x-app-layout>