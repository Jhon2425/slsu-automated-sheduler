<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Schedule Management') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <!-- Page Header -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center">
                        <div>
                            <h3 class="text-2xl font-bold text-gray-800">All Schedules</h3>
                            <p class="text-gray-600 mt-1">Manage and generate class schedules</p>
                        </div>
                        <div class="flex space-x-3">
                            <button onclick="openGenerateModal()" 
                                    class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg transition">
                                <i class="fas fa-magic mr-2"></i>Generate Schedule
                            </button>
                            @if(isset($schedules) && $schedules->count() > 0)
                            <a href="{{ route('admin.schedules.download') }}" 
                               class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg transition">
                                <i class="fas fa-file-pdf mr-2"></i>Download PDF
                            </a>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <!-- Messages -->
            @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
            @endif

            <!-- Statistics -->
            @if(isset($schedules) && $schedules->count() > 0)
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Total Schedules</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $schedules->total() }}</p>
                        </div>
                        <i class="fas fa-calendar-check text-blue-500 text-3xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Programs</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $schedules->pluck('program_id')->unique()->count() }}</p>
                        </div>
                        <i class="fas fa-graduation-cap text-green-500 text-3xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Faculties</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $schedules->pluck('faculty_id')->unique()->count() }}</p>
                        </div>
                        <i class="fas fa-user-tie text-purple-500 text-3xl"></i>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-600 text-sm">Rooms Used</p>
                            <p class="text-2xl font-bold text-gray-800">{{ $schedules->pluck('room_id')->unique()->count() }}</p>
                        </div>
                        <i class="fas fa-door-open text-orange-500 text-3xl"></i>
                    </div>
                </div>
            </div>
            @endif

            <!-- Schedules Table -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    @if(isset($schedules) && $schedules->count() > 0)
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Faculty</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Subject</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Room</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Program</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Day</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Time</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Type</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Actions</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($schedules as $schedule)
                                <tr class="hover:bg-gray-50">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">
                                            {{ $schedule->faculty->name ?? 'N/A' }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $schedule->subject->name ?? 'N/A' }}</div>
                                        <div class="text-xs text-gray-500">{{ $schedule->subject->code ?? '' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-purple-100 text-purple-800">
                                            {{ $schedule->room->room_number ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900">{{ $schedule->program->name ?? 'N/A' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full bg-blue-100 text-blue-800">
                                            {{ $schedule->day }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="px-2 py-1 text-xs rounded-full {{ $schedule->class_type == 'lecture' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                            {{ ucfirst($schedule->class_type) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        <a href="{{ route('admin.schedules.show', $schedule->id) }}" 
                                           class="text-blue-600 hover:text-blue-800 mr-3">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $schedules->links() }}
                    </div>
                    @else
                    <div class="text-center py-12">
                        <i class="fas fa-calendar-times text-gray-300 text-6xl mb-4"></i>
                        <h3 class="text-xl font-semibold text-gray-700 mb-2">No Schedules Found</h3>
                        <p class="text-gray-500 mb-6">Generate schedules to get started.</p>
                        <button onclick="openGenerateModal()" 
                                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg">
                            <i class="fas fa-magic mr-2"></i>Generate Schedule Now
                        </button>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Generate Schedule Modal -->
    <div id="generateModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-bold text-gray-900 mb-4">Generate Schedule</h3>
                <form method="POST" action="{{ route('admin.schedules.generate') }}">
                    @csrf
                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Program</label>
                        <select name="program_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Program</option>
                            @foreach($programs as $program)
                            <option value="{{ $program->id }}">{{ $program->name }} ({{ $program->code }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Semester</label>
                        <select name="semester" required class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="">Select Semester</option>
                            <option value="1st Semester">1st Semester</option>
                            <option value="2nd Semester">2nd Semester</option>
                            <option value="Summer">Summer</option>
                        </select>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Academic Year</label>
                        <input type="text" name="academic_year" required placeholder="e.g., 2024-2025" 
                               class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-medium text-gray-700 mb-2">Type</label>
                        <select name="type" class="w-full px-3 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-purple-500">
                            <option value="regular">Regular</option>
                            <option value="make_up">Make-up</option>
                            <option value="special">Special</option>
                        </select>
                    </div>

                    <div class="flex justify-end space-x-3">
                        <button type="button" onclick="closeGenerateModal()" 
                                class="px-4 py-2 bg-gray-300 text-gray-800 rounded-lg hover:bg-gray-400">
                            Cancel
                        </button>
                        <button type="submit" 
                                class="px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700">
                            Generate
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function openGenerateModal() {
            document.getElementById('generateModal').classList.remove('hidden');
        }

        function closeGenerateModal() {
            document.getElementById('generateModal').classList.add('hidden');
        }

        window.onclick = function(event) {
            const modal = document.getElementById('generateModal');
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
        }
    </script>
</x-app-layout>