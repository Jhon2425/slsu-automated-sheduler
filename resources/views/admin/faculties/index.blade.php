<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-white leading-tight">
            <i class="fas fa-users mr-3"></i>{{ __('Faculty Management') }}
        </h2>
    </x-slot>

    <div 
        x-data="{
            openFacultyModal: false,
            openEditModal: false,
            editFaculty: {},

            startEdit(data) {
                this.editFaculty = data;
                this.openEditModal = true;
            }
        }"
        class="py-12"
    >

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Page Title + Add Button -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-white">
                    Faculty List
                </h1>

                <button @click="openFacultyModal = true"
                    class="px-6 py-3 rounded-lg text-white transition-colors duration-300 border border-white/30"
                    style="background-color: transparent;"
                    onmouseover="this.style.backgroundColor='#dac611a0'"
                    onmouseout="this.style.backgroundColor='transparent'">
                    <i class="fas fa-plus mr-2"></i>Add Faculty
                </button>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="bg-white/20 border border-white/30 text-white px-4 py-3 rounded mb-6 backdrop-blur-sm">
                    {{ session('success') }}
                </div>
            @endif

            <!-- TABLE -->
            <div class="glass overflow-hidden shadow-sm sm:rounded-lg bg-white/20 backdrop-blur-md border border-white/30">
                <div class="p-6 overflow-x-auto">

                    <table class="min-w-full divide-y divide-white/30">
                        <thead class="bg-white/30 backdrop-blur-sm">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase" style="color: #f6de0cf2;">Name</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase" style="color: #f6de0cf2;">Subject</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase" style="color: #f6de0cf2;">Hours</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase" style="color: #f6de0cf2;">Units</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase" style="color: #f6de0cf2;">Students</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase" style="color: #f6de0cf2;">Section</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase" style="color: #f6de0cf2;">Type</th>
                                <th class="px-6 py-3 text-left text-xs font-bold uppercase" style="color: #f6de0cf2;">Actions</th>

                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/30">
                            @foreach($faculties as $faculty)

                                @php
                                    $facultyJson = json_encode([
                                        'id' => $faculty->id,
                                        'name' => $faculty->name,
                                        'course_subject' => $faculty->course_subject,
                                        'no_of_hours' => $faculty->no_of_hours,
                                        'units' => $faculty->units,
                                        'no_of_students' => $faculty->no_of_students,
                                        'year_section' => $faculty->year_section,
                                        'action_type' => $faculty->action_type,
                                    ]);
                                @endphp

                                <tr class="bg-white/10 backdrop-blur-sm">
                                    <td class="px-6 py-4 text-white">{{ $faculty->name }}</td>
                                    <td class="px-6 py-4 text-white">{{ $faculty->course_subject }}</td>
                                    <td class="px-6 py-4 text-white">{{ $faculty->no_of_hours }}</td>
                                    <td class="px-6 py-4 text-white">{{ $faculty->units }}</td>
                                    <td class="px-6 py-4 text-white">{{ $faculty->no_of_students }}</td>
                                    <td class="px-6 py-4 text-white">{{ $faculty->year_section }}</td>

                                    <td class="px-6 py-4">
                                        <span class="px-2 py-1 text-xs rounded-full 
                                            @if($faculty->action_type == 'Laboratory') bg-blue-100/30 text-blue-800
                                            @elseif($faculty->action_type == 'Lecture') bg-green-100/30 text-green-800
                                            @else bg-yellow-100/30 text-yellow-800 @endif">
                                            {{ $faculty->action_type }}
                                        </span>
                                    </td>

                                    <td class="px-6 py-4 whitespace-nowrap space-x-3">
                                        <!-- EDIT BUTTON -->
                                        <button 
                                            @click="startEdit({!! $facultyJson !!})"
                                            class="text-blue-300 hover:text-blue-500"
                                        >
                                            <i class="fas fa-edit"></i>
                                        </button>

                                        <!-- DELETE -->
                                        <form method="POST" action="{{ route('admin.faculties.destroy', $faculty) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-red-300 hover:text-red-500"
                                                onclick="return confirm('Are you sure?')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                            @endforeach
                        </tbody>
                    </table>

                </div>
            </div>

            <div class="mt-6 text-white">
                {{ $faculties->links() }}
            </div>

        </div>

        <!-- CREATE MODAL -->
        @include('admin.faculties.create') {{-- Make modal glassy --}}
        <!-- EDIT MODAL -->
        @include('admin.faculties.edit') {{-- Make modal glassy --}}
    </div>
</x-app-layout>
