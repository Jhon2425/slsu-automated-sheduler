<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Schedule Details') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-4xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-2xl font-bold text-gray-800">Schedule Information</h3>
                        <a href="{{ route('admin.schedules.index') }}" 
                           class="bg-gray-600 hover:bg-gray-700 text-white px-4 py-2 rounded-lg">
                            <i class="fas fa-arrow-left mr-2"></i>Back
                        </a>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Faculty</h4>
                            <p class="text-lg font-semibold text-gray-900">{{ $schedule->faculty->name ?? 'N/A' }}</p>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Subject</h4>
                            <p class="text-lg font-semibold text-gray-900">{{ $schedule->subject->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ $schedule->subject->code ?? '' }}</p>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Room</h4>
                            <p class="text-lg font-semibold text-gray-900">{{ $schedule->room->room_number ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ $schedule->room->type ?? '' }}</p>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Program</h4>
                            <p class="text-lg font-semibold text-gray-900">{{ $schedule->program->name ?? 'N/A' }}</p>
                            <p class="text-sm text-gray-600">{{ $schedule->program->code ?? '' }}</p>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Day</h4>
                            <p class="text-lg font-semibold text-gray-900">{{ $schedule->day }}</p>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Time</h4>
                            <p class="text-lg font-semibold text-gray-900">
                                {{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}
                            </p>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Class Type</h4>
                            <span class="px-3 py-1 text-sm rounded-full {{ $schedule->class_type == 'lecture' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                                {{ ucfirst($schedule->class_type) }}
                            </span>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Semester</h4>
                            <p class="text-lg font-semibold text-gray-900">{{ $schedule->semester }}</p>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Academic Year</h4>
                            <p class="text-lg font-semibold text-gray-900">{{ $schedule->academic_year }}</p>
                        </div>

                        <div class="border-b pb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Type</h4>
                            <span class="px-3 py-1 text-sm rounded-full bg-blue-100 text-blue-800">
                                {{ ucfirst($schedule->type) }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
