<x-app-layout>
    <div class="min-h-screen bg-gray-100">
        <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h1 class="text-3xl font-bold text-gray-800">
                        <i class="fas fa-calendar-check mr-3"></i>My Schedule
                    </h1>
                    <p class="mt-2 text-gray-600">View your teaching schedule</p>
                </div>
                @if(isset($schedules) && $schedules->count() > 0)
                    <a href="{{ route('faculty.schedule.download') }}" 
                       class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                        <i class="fas fa-file-pdf mr-2"></i>Download PDF
                    </a>
                @endif
            </div>

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-check-circle mr-3 text-xl"></i>
                    <span>{{ session('success') }}</span>
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                    <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                    <span>{{ session('error') }}</span>
                </div>
            @endif

            <!-- No Schedule Message -->
            @if(!isset($schedules) || $schedules->count() == 0)
                <div class="bg-yellow-50 border-l-4 border-yellow-400 p-6 rounded-lg shadow-md">
                    <div class="flex items-center">
                        <div class="flex-shrink-0">
                            <i class="fas fa-exclamation-triangle text-yellow-400 text-4xl"></i>
                        </div>
                        <div class="ml-4">
                            <h3 class="text-lg font-semibold text-yellow-800 mb-2">No Schedule Assigned</h3>
                            <p class="text-yellow-700">
                                No schedule has been assigned to you yet. Please contact the administrator for more information.
                            </p>
                        </div>
                    </div>
                </div>
            @else
                <!-- Faculty Information Card -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden mb-6">
                    <div class="bg-gradient-to-r from-blue-600 to-blue-700 text-white p-6">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-2xl font-bold mb-2">{{ $faculty->name }}</h2>
                                <div class="flex flex-wrap gap-4 text-blue-100">
                                    <span class="flex items-center">
                                        <i class="fas fa-book mr-2"></i>
                                        {{ $faculty->course_subject }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-users mr-2"></i>
                                        {{ $faculty->year_section }}
                                    </span>
                                    <span class="flex items-center">
                                        <i class="fas fa-clock mr-2"></i>
                                        {{ $faculty->no_of_hours }} hours/week
                                    </span>
                                </div>
                            </div>
                            <div class="bg-white bg-opacity-20 rounded-full p-4">
                                <i class="fas fa-chalkboard-teacher text-5xl"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Stats -->
                    <div class="grid grid-cols-1 md:grid-cols-4 gap-4 p-6 bg-gray-50">
                        <div class="text-center">
                            <p class="text-gray-600 text-sm font-medium">Total Classes</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $schedules->count() }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-600 text-sm font-medium">Students</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $faculty->no_of_students }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-600 text-sm font-medium">Units</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $faculty->units }}</p>
                        </div>
                        <div class="text-center">
                            <p class="text-gray-600 text-sm font-medium">Class Type</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $faculty->action_type }}</p>
                        </div>
                    </div>
                </div>

                <!-- Schedule Table -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-calendar-day mr-2"></i>Day
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-clock mr-2"></i>Time
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-door-open mr-2"></i>Classroom
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-calendar-alt mr-2"></i>Date
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        <i class="fas fa-info-circle mr-2"></i>Duration
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($schedules as $schedule)
                                <tr class="hover:bg-gray-50 transition duration-150">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-semibold
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
                                        <div class="text-sm text-gray-500">
                                            to {{ date('g:i A', strtotime($schedule->end_time)) }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="flex items-center">
                                            <div class="bg-purple-100 rounded-full p-2 mr-3">
                                                <i class="fas fa-door-open text-purple-600"></i>
                                            </div>
                                            <span class="text-sm font-medium text-gray-900">
                                                {{ $schedule->classroom->room_name }}
                                            </span>
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ $schedule->schedule_date->format('M d, Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @php
                                            $start = \Carbon\Carbon::parse($schedule->start_time);
                                            $end = \Carbon\Carbon::parse($schedule->end_time);
                                            $duration = $start->diffInMinutes($end);
                                            $hours = floor($duration / 60);
                                            $minutes = $duration % 60;
                                        @endphp
                                        <span class="text-sm text-gray-600">
                                            @if($hours > 0)
                                                {{ $hours }}h
                                            @endif
                                            @if($minutes > 0)
                                                {{ $minutes }}m
                                            @endif
                                        </span>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Weekly Overview -->
                <div class="mt-6 bg-white rounded-lg shadow-md p-6">
                    <h3 class="text-lg font-bold text-gray-800 mb-4 flex items-center">
                        <i class="fas fa-chart-pie mr-3 text-blue-600"></i>
                        Weekly Overview
                    </h3>
                    <div class="grid grid-cols-2 md:grid-cols-7 gap-3">
                        @php
                            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
                            $schedulesByDay = $schedules->groupBy('day');
                        @endphp
                        @foreach($days as $day)
                            @php
                                $daySchedules = $schedulesByDay->get($day, collect());
                                $hasClasses = $daySchedules->count() > 0;
                            @endphp
                            <div class="text-center p-3 rounded-lg {{ $hasClasses ? 'bg-green-50 border-2 border-green-200' : 'bg-gray-50 border-2 border-gray-200' }}">
                                <p class="text-xs font-semibold text-gray-700 mb-1">{{ substr($day, 0, 3) }}</p>
                                <p class="text-lg font-bold {{ $hasClasses ? 'text-green-600' : 'text-gray-400' }}">
                                    {{ $daySchedules->count() }}
                                </p>
                                <p class="text-xs text-gray-500">
                                    {{ $hasClasses ? 'class' . ($daySchedules->count() > 1 ? 'es' : '') : 'free' }}
                                </p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>