<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="font-semibold text-xl text-gray-800 leading-tight">
                    {{ __('Automated Scheduling System') }}
                </h2>
                <p class="text-sm text-gray-600 mt-1">Scheduling Dashboard</p>
            </div>
            <div class="flex items-center gap-4">
                <button class="px-4 py-2 bg-[#FFBA00] text-[#0C3B2E] rounded-lg font-semibold hover:bg-[#e5a800] transition">
                    <svg class="inline w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12" />
                    </svg>
                    Import Data
                </button>
            </div>
        </div>
    </x-slot>

    <!-- Navigation Tabs -->
    <div class="bg-white border-b border-gray-200" x-data="{ activeTab: 'overview' }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex gap-8">
                <button @click="activeTab = 'overview'" 
                    :class="activeTab === 'overview' ? 'border-[#0C3B2E] text-[#0C3B2E]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-2 border-b-2 font-medium text-sm transition">
                    Overview
                </button>
                <button @click="activeTab = 'faculty'" 
                    :class="activeTab === 'faculty' ? 'border-[#0C3B2E] text-[#0C3B2E]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-2 border-b-2 font-medium text-sm transition">
                    Faculty
                </button>
                <button @click="activeTab = 'schedules'" 
                    :class="activeTab === 'schedules' ? 'border-[#0C3B2E] text-[#0C3B2E]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-2 border-b-2 font-medium text-sm transition">
                    Schedules
                </button>
                <button @click="activeTab = 'conflicts'" 
                    :class="activeTab === 'conflicts' ? 'border-[#0C3B2E] text-[#0C3B2E]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-2 border-b-2 font-medium text-sm transition">
                    Conflicts
                </button>
                <button @click="activeTab = 'reports'" 
                    :class="activeTab === 'reports' ? 'border-[#0C3B2E] text-[#0C3B2E]' : 'border-transparent text-gray-500 hover:text-gray-700'"
                    class="py-4 px-2 border-b-2 font-medium text-sm transition">
                    Reports
                </button>
            </div>
        </div>

        <div class="py-8">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
                
                <!-- Overview Tab -->
                <div x-show="activeTab === 'overview'" class="space-y-6">
                    <!-- Stats Grid -->
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Total Faculty</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $totalFaculty ?? 48 }}</p>
                                </div>
                                <div class="bg-[#6D9773] p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Active Schedules</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $activeSchedules ?? 156 }}</p>
                                </div>
                                <div class="bg-[#0C3B2E] p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Conflicts Resolved</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $conflictsResolved ?? 23 }}</p>
                                </div>
                                <div class="bg-[#B46617] p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="text-gray-500 text-sm">Classes Scheduled</p>
                                    <p class="text-3xl font-bold text-gray-800 mt-2">{{ $classesScheduled ?? 234 }}</p>
                                </div>
                                <div class="bg-[#FFBA00] p-3 rounded-lg">
                                    <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                                    </svg>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Quick Actions -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Quick Actions</h2>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                            <a href="{{ route('faculty.create') }}" class="flex items-center gap-3 p-4 border-2 border-[#6D9773] rounded-lg hover:bg-[#6D9773] hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                <span class="font-medium">Add Faculty Member</span>
                            </a>
                            <a href="{{ route('schedules.generate') }}" class="flex items-center gap-3 p-4 border-2 border-[#B46617] rounded-lg hover:bg-[#B46617] hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span class="font-medium">Generate Schedule</span>
                            </a>
                            <a href="{{ route('reports.index') }}" class="flex items-center gap-3 p-4 border-2 border-[#FFBA00] rounded-lg hover:bg-[#FFBA00] hover:text-[#0C3B2E] transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z" />
                                </svg>
                                <span class="font-medium">View Analytics</span>
                            </a>
                        </div>
                    </div>

                    <!-- System Performance -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">System Performance (ISO 25010)</h2>
                        <div class="space-y-4">
                            @php
                                $metrics = [
                                    ['name' => 'Functional Suitability', 'score' => 95, 'color' => '#6D9773'],
                                    ['name' => 'Reliability', 'score' => 92, 'color' => '#0C3B2E'],
                                    ['name' => 'Performance Efficiency', 'score' => 88, 'color' => '#B46617'],
                                    ['name' => 'Usability', 'score' => 90, 'color' => '#FFBA00'],
                                    ['name' => 'Security', 'score' => 94, 'color' => '#6D9773'],
                                ];
                            @endphp

                            @foreach($metrics as $metric)
                            <div>
                                <div class="flex justify-between mb-1">
                                    <span class="text-sm font-medium text-gray-700">{{ $metric['name'] }}</span>
                                    <span class="text-sm font-bold text-gray-800">{{ $metric['score'] }}%</span>
                                </div>
                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="h-2 rounded-full" style="width: {{ $metric['score'] }}%; background-color: {{ $metric['color'] }};"></div>
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Faculty Tab -->
                <div x-show="activeTab === 'faculty'" class="space-y-6">
                    <!-- Search and Filter -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <form method="GET" action="{{ route('faculty.index') }}" class="flex flex-col md:flex-row gap-4">
                            <div class="flex-1 relative">
                                <svg class="absolute left-3 top-1/2 transform -translate-y-1/2 text-gray-400 w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
                                </svg>
                                <input type="text" name="search" placeholder="Search faculty members..." class="w-full pl-10 pr-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-[#0C3B2E]" value="{{ request('search') }}">
                            </div>
                            <a href="{{ route('faculty.create') }}" class="px-6 py-2 bg-[#0C3B2E] text-white rounded-lg hover:bg-[#0a2f23] transition flex items-center gap-2 justify-center">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                </svg>
                                Add Faculty
                            </a>
                        </form>
                    </div>

                    <!-- Faculty Table -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-[#0C3B2E] text-white">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-sm font-semibold">Name</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold">Course Subject</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold">No. of Hours</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold">Units</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold">No. of Students</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold">Year & Section</th>
                                        <th class="px-6 py-3 text-left text-sm font-semibold">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    @forelse($facultyMembers ?? [] as $faculty)
                                    <tr class="hover:bg-gray-50 transition">
                                        <td class="px-6 py-4">
                                            <div class="flex items-center gap-3">
                                                <div class="w-10 h-10 bg-[#6D9773] rounded-full flex items-center justify-center text-white font-semibold">
                                                    {{ strtoupper(substr($faculty->name, 0, 1)) }}
                                                </div>
                                                <span class="font-medium text-gray-800">{{ $faculty->name }}</span>
                                            </div>
                                        </td>
                                        <td class="px-6 py-4 text-gray-600">{{ $faculty->course_subject }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $faculty->hours }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $faculty->units }}</td>
                                        <td class="px-6 py-4 text-gray-600">{{ $faculty->num_students }}</td>
                                        <td class="px-6 py-4">
                                            <span class="px-3 py-1 bg-[#FFBA00] bg-opacity-20 text-[#B46617] rounded-full text-sm font-medium">
                                                {{ $faculty->year_section }}
                                            </span>
                                        </td>
                                        <td class="px-6 py-4">
                                            <a href="{{ route('faculty.edit', $faculty->id) }}" class="text-[#0C3B2E] hover:text-[#6D9773] font-medium text-sm">
                                                Edit
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="7" class="px-6 py-8 text-center text-gray-500">
                                            No faculty members found.
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Schedules Tab -->
                <div x-show="activeTab === 'schedules'">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div class="flex justify-between items-center mb-6">
                            <h2 class="text-xl font-bold text-gray-800">Class Schedules</h2>
                            <a href="{{ route('schedules.generate') }}" class="px-6 py-2 bg-[#FFBA00] text-[#0C3B2E] rounded-lg font-semibold hover:bg-[#e5a800] transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                Generate New Schedule
                            </a>
                        </div>
                        <div class="grid grid-cols-7 gap-2">
                            @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'] as $day)
                            <div>
                                <div class="bg-[#0C3B2E] text-white py-2 rounded-t-lg font-semibold text-sm text-center">
                                    {{ $day }}
                                </div>
                                <div class="border border-t-0 rounded-b-lg p-2 min-h-[200px] space-y-2">
                                    @if($day !== 'Sunday')
                                        <div class="bg-[#6D9773] bg-opacity-20 p-2 rounded text-xs">
                                            <p class="font-semibold text-[#0C3B2E]">8:00-11:00</p>
                                            <p class="text-gray-700">CS 101</p>
                                        </div>
                                        <div class="bg-[#B46617] bg-opacity-20 p-2 rounded text-xs">
                                            <p class="font-semibold text-[#0C3B2E]">1:00-4:00</p>
                                            <p class="text-gray-700">Math 201</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Conflicts Tab -->
                <div x-show="activeTab === 'conflicts'" class="space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-4">Scheduling Conflicts</h2>
                        <div class="space-y-4">
                            @forelse($conflicts ?? [] as $conflict)
                            <div class="flex items-start gap-4 p-4 bg-red-50 border-l-4 border-red-500 rounded">
                                <svg class="w-6 h-6 text-red-500 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800">{{ $conflict->title }}</h3>
                                    <p class="text-sm text-gray-600 mt-1">{{ $conflict->description }}</p>
                                    <a href="{{ route('conflicts.resolve', $conflict->id) }}" class="mt-2 inline-block px-4 py-1 bg-red-500 text-white rounded text-sm hover:bg-red-600 transition">
                                        Resolve
                                    </a>
                                </div>
                            </div>
                            @empty
                            <div class="flex items-start gap-4 p-4 bg-green-50 border-l-4 border-green-500 rounded">
                                <svg class="w-6 h-6 text-green-500 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <div class="flex-1">
                                    <h3 class="font-semibold text-gray-800">All Systems Clear</h3>
                                    <p class="text-sm text-gray-600 mt-1">No scheduling conflicts detected</p>
                                </div>
                            </div>
                            @endforelse
                        </div>
                    </div>
                </div>

                <!-- Reports Tab -->
                <div x-show="activeTab === 'reports'">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 class="text-xl font-bold text-gray-800 mb-6">System Reports & Analytics</h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div class="border rounded-lg p-4">
                                <h3 class="font-semibold text-gray-800 mb-2">Workload Distribution</h3>
                                <div class="h-48 flex items-end justify-around gap-2">
                                    @foreach([65, 80, 75, 90, 70] as $idx => $height)
                                    <div class="flex-1 flex flex-col items-center">
                                        <div class="w-full bg-[#6D9773] rounded-t" style="height: {{ $height }}%"></div>
                                        <span class="text-xs text-gray-600 mt-2">F{{ $idx + 1 }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                            <div class="border rounded-lg p-4">
                                <h3 class="font-semibold text-gray-800 mb-2">User Satisfaction Score</h3>
                                <div class="flex items-center justify-center h-48">
                                    <div class="text-center">
                                        <div class="text-6xl font-bold text-[#0C3B2E]">4.7</div>
                                        <div class="text-gray-600 mt-2">out of 5.0</div>
                                        <div class="flex gap-1 justify-center mt-3">
                                            @for($i = 1; $i <= 5; $i++)
                                            <span class="text-[#FFBA00] text-2xl">★</span>
                                            @endfor
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="mt-6">
                            <a href="{{ route('reports.export') }}" class="w-full inline-flex justify-center items-center px-6 py-3 border-2 border-[#0C3B2E] text-[#0C3B2E] rounded-lg font-semibold hover:bg-[#0C3B2E] hover:text-white transition gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                                </svg>
                                Export Report
                            </a>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    @push('scripts')
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    @endpush
</x-app-layout>