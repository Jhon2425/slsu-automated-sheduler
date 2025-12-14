<x-app-layout>
    <x-slot name="header"></x-slot>

    <div 
        x-data="{
            openCreateModal: false,
            openEditModal: false,
            editSubject: {},

            startEdit(data) {
                this.editSubject = data;
                this.openEditModal = true;
            }
        }"
        class="py-12 relative"
    >
        <!-- Animated Background Elements -->
        <div class="fixed inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-20 left-10 w-72 h-72 bg-yellow-500/10 rounded-full blur-3xl animate-pulse"></div>
            <div class="absolute bottom-20 right-10 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 1s;"></div>
            <div class="absolute top-1/2 left-1/2 w-64 h-64 bg-blue-500/10 rounded-full blur-3xl animate-pulse" style="animation-delay: 2s;"></div>
        </div>

        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 relative z-10">
            
        <!-- Breadcrumb -->
        <nav class="flex items-center text-sm text-white/70 mb-6 animate-fade-in">
            <a href="{{ route('admin.dashboard') }}"
            class="flex items-center hover:text-yellow-400 transition-colors">
                <i class="fas fa-home mr-2"></i>
                Dashboard
            </a>

            <i class="fas fa-chevron-right mx-3 text-xs text-white/40"></i>

            <span class="font-semibold text-white">
                Subject Management
            </span>
        </nav>

            <!-- Page Title + Add Button -->
            <div class="flex justify-between items-center mb-8 animate-fade-in">
                <div class="flex items-center space-x-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-yellow-400/20 to-yellow-600/20 backdrop-blur-sm border border-yellow-400/30 flex items-center justify-center shadow-lg shadow-yellow-500/20">
                        <i class="fas fa-book text-2xl text-yellow-400"></i>
                    </div>
                    <div>
                        <h1 class="text-4xl font-bold text-white flex items-center">
                            <span class="bg-gradient-to-r from-yellow-400 via-yellow-500 to-yellow-600 bg-clip-text text-transparent">
                                Subject List
                            </span>
                        </h1>
                        <p class="text-white/60 text-sm mt-1">Manage and organize your academic subjects</p>
                    </div>
                </div>

                <button @click="openCreateModal = true"
                    class="group relative px-8 py-4 rounded-xl text-white transition-all duration-300 overflow-hidden
                           hover:scale-105 hover:shadow-2xl hover:shadow-yellow-500/30 active:scale-95 border border-yellow-400/30">
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-500 to-yellow-600"></div>
                    <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 to-yellow-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <span class="relative flex items-center font-semibold">
                        <i class="fas fa-plus-circle mr-2 group-hover:rotate-90 transition-transform duration-300 text-lg"></i>
                        Add Subject
                    </span>
                    <div class="absolute inset-0 -z-10 bg-gradient-to-r from-yellow-600 to-yellow-700 blur-xl opacity-50 group-hover:opacity-75 transition-opacity"></div>
                </button>
            </div>

            <!-- Success Message -->
            @if(session('success'))
                <div class="relative overflow-hidden bg-gradient-to-r from-green-500/20 via-emerald-500/20 to-teal-500/20 border border-green-400/40 text-white px-6 py-4 rounded-2xl mb-8 backdrop-blur-md
                            animate-slide-down shadow-xl shadow-green-500/20">
                    <div class="absolute inset-0 bg-gradient-to-r from-green-400/10 to-transparent"></div>
                    <div class="relative flex items-center">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-green-500/30 flex items-center justify-center mr-4 border border-green-400/40">
                            <i class="fas fa-check-circle text-green-300 text-xl"></i>
                        </div>
                        <div class="flex-grow">
                            <p class="font-semibold text-green-100">Success!</p>
                            <p class="text-green-200 text-sm">{{ session('success') }}</p>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Filter Section -->
            <div class="mb-8 animate-fade-in" style="animation-delay: 0.1s;">
                <div class="glass-card rounded-2xl p-6 border border-white/30 backdrop-blur-xl bg-white/10 shadow-2xl">
                    <div class="flex items-center mb-5">
                        <div class="w-10 h-10 rounded-lg bg-gradient-to-br from-blue-400/20 to-blue-600/20 flex items-center justify-center mr-3 border border-blue-400/30">
                            <i class="fas fa-filter text-blue-300"></i>
                        </div>
                        <h3 class="text-lg font-bold text-white">Filter Options</h3>
                    </div>
                    
                    <form method="GET" action="{{ route('admin.subjects.index') }}" class="grid grid-cols-1 md:grid-cols-4 gap-5">
                        
                        <!-- Program Filter -->
                        <div class="filter-input-group">
                            <label class="block text-white text-sm font-bold mb-2 flex items-center">
                                <i class="fas fa-university mr-2 text-yellow-400"></i>
                                Program
                            </label>
                            <div class="relative group">
                                <select name="program_id" 
                                    class="w-full px-4 py-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-white 
                                           focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/40 focus:bg-white/15
                                           transition-all duration-300 cursor-pointer hover:bg-white/15 hover:border-white/30 appearance-none
                                           shadow-lg group-hover:shadow-xl">
                                    <option value="" selected hidden>All Programs</option>
                                    @foreach($programs as $program)
                                        <option value="{{ $program->id }}" {{ request('program_id') == $program->id ? 'selected' : '' }}>
                                            {{ $program->name }}
                                        </option>
                                    @endforeach
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-yellow-400 pointer-events-none text-sm group-hover:text-yellow-300 transition-colors"></i>
                            </div>
                        </div>

                        <!-- Year Level Filter -->
                        <div class="filter-input-group">
                            <label class="block text-white text-sm font-bold mb-2 flex items-center">
                                <i class="fas fa-graduation-cap mr-2 text-yellow-400"></i>
                                Year Level
                            </label>
                            <div class="relative group">
                                <select name="year_level" 
                                    class="w-full px-4 py-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-white 
                                           focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/40 focus:bg-white/15
                                           transition-all duration-300 cursor-pointer hover:bg-white/15 hover:border-white/30 appearance-none
                                           shadow-lg group-hover:shadow-xl">
                                    <option value="" selected hidden>All Year</option>
                                    <option value="1" {{ request('year_level') == '1' ? 'selected' : '' }}>1st Year</option>
                                    <option value="2" {{ request('year_level') == '2' ? 'selected' : '' }}>2nd Year</option>
                                    <option value="3" {{ request('year_level') == '3' ? 'selected' : '' }}>3rd Year</option>
                                    <option value="4" {{ request('year_level') == '4' ? 'selected' : '' }}>4th Year</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-yellow-400 pointer-events-none text-sm group-hover:text-yellow-300 transition-colors"></i>
                            </div>
                        </div>

                        <!-- Semester Filter -->
                        <div class="filter-input-group">
                            <label class="block text-white text-sm font-bold mb-2 flex items-center">
                                <i class="fas fa-calendar-alt mr-2 text-yellow-400"></i>
                                Semester
                            </label>
                            <div class="relative group">
                                <select name="semester" 
                                    class="w-full px-4 py-3 rounded-xl bg-white/10 backdrop-blur-md border border-white/20 text-white 
                                           focus:outline-none focus:border-yellow-400 focus:ring-2 focus:ring-yellow-400/40 focus:bg-white/15
                                           transition-all duration-300 cursor-pointer hover:bg-white/15 hover:border-white/30 appearance-none
                                           shadow-lg group-hover:shadow-xl">
                                    <option value="" selected hidden>All Semester</option>
                                    <option value="1st Semester" {{ request('semester') == '1st Semester' ? 'selected' : '' }}>1st Semester</option>
                                    <option value="2nd Semester" {{ request('semester') == '2nd Semester' ? 'selected' : '' }}>2nd Semester</option>
                                    <option value="Summer" {{ request('semester') == 'Summer' ? 'selected' : '' }}>Summer</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-yellow-400 pointer-events-none text-sm group-hover:text-yellow-300 transition-colors"></i>
                            </div>
                        </div>

                        <!-- Filter Button -->
                        <div class="flex items-end">
                            <button type="submit" 
                                class="group/btn w-full px-6 py-3 rounded-xl bg-gradient-to-r from-yellow-500 to-yellow-600 text-white 
                                       hover:from-yellow-600 hover:to-yellow-700 transition-all duration-300
                                       hover:scale-105 hover:shadow-2xl hover:shadow-yellow-500/40 active:scale-95
                                       flex items-center justify-center font-semibold border border-yellow-400/30 relative overflow-hidden">
                                <div class="absolute inset-0 bg-gradient-to-r from-yellow-400 to-yellow-500 opacity-0 group-hover/btn:opacity-100 transition-opacity duration-300"></div>
                                <span class="relative flex items-center">
                                    <i class="fas fa-search mr-2 group-hover/btn:rotate-90 transition-transform duration-300"></i>
                                    Apply Filters
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- TABLE -->
            <div class="glass-card overflow-hidden shadow-2xl rounded-2xl backdrop-blur-xl bg-white/10 border border-white/30
                        animate-fade-in hover:shadow-3xl transition-all duration-500" 
                 style="animation-delay: 0.2s;">
                <div class="p-6 overflow-x-auto custom-scrollbar">

                    <table class="min-w-full divide-y divide-white/20">
                        <thead class="bg-gradient-to-r from-white/20 to-white/10 backdrop-blur-md">
                            <tr>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider transition-all duration-300 hover:bg-white/20 rounded-tl-xl group" 
                                    style="color: #f6de0cf2;">
                                    <div class="flex items-center">
                                        <i class="fas fa-code mr-2 group-hover:scale-110 transition-transform"></i>
                                        Course Code
                                    </div>
                                </th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider transition-all duration-300 hover:bg-white/20 group" 
                                    style="color: #f6de0cf2;">
                                    <div class="flex items-center">
                                        <i class="fas fa-book-open mr-2 group-hover:scale-110 transition-transform"></i>
                                        Subject Name
                                    </div>
                                </th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider transition-all duration-300 hover:bg-white/20 group" 
                                    style="color: #f6de0cf2;">
                                    <div class="flex items-center">
                                        <i class="fas fa-calculator mr-2 group-hover:scale-110 transition-transform"></i>
                                        Units
                                    </div>
                                </th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider transition-all duration-300 hover:bg-white/20 group" 
                                    style="color: #f6de0cf2;">
                                    <div class="flex items-center">
                                        <i class="fas fa-users mr-2 group-hover:scale-110 transition-transform"></i>
                                        Students
                                    </div>
                                </th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider transition-all duration-300 hover:bg-white/20 group" 
                                    style="color: #f6de0cf2;">
                                    <div class="flex items-center">
                                        <i class="fas fa-calendar-alt mr-2 group-hover:scale-110 transition-transform"></i>
                                        Semester
                                    </div>
                                </th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider transition-all duration-300 hover:bg-white/20 group" 
                                    style="color: #f6de0cf2;">
                                    <div class="flex items-center">
                                        <i class="fas fa-graduation-cap mr-2 group-hover:scale-110 transition-transform"></i>
                                        Year Level
                                    </div>
                                </th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider transition-all duration-300 hover:bg-white/20 group" 
                                    style="color: #f6de0cf2;">
                                    <div class="flex items-center">
                                        <i class="fas fa-university mr-2 group-hover:scale-110 transition-transform"></i>
                                        Program
                                    </div>
                                </th>
                                <th class="px-6 py-5 text-left text-xs font-bold uppercase tracking-wider transition-all duration-300 hover:bg-white/20 rounded-tr-xl group" 
                                    style="color: #f6de0cf2;">
                                    <div class="flex items-center">
                                        <i class="fas fa-cog mr-2 group-hover:rotate-180 transition-transform duration-500"></i>
                                        Actions
                                    </div>
                                </th>
                            </tr>
                        </thead>

                        <tbody class="divide-y divide-white/10">
                            @forelse($subjects as $subject)

                                @php
                                    $subjectJson = json_encode([
                                        'id' => $subject->id,
                                        'course_code' => $subject->course_code,
                                        'subject_name' => $subject->subject_name,
                                        'units' => $subject->units,
                                        'students_enrolled' => $subject->students_enrolled ?? 0,
                                        'semester' => $subject->semester,
                                        'year_level' => $subject->year_level,
                                        'program_id' => $subject->program_id,
                                    ]);
                                @endphp

                                <tr class="bg-white/5 backdrop-blur-sm cursor-pointer 
                                          hover:bg-gradient-to-r hover:from-white/15 hover:to-white/10 hover:scale-[1.01] hover:shadow-xl
                                          transition-all duration-300 group/row border-l-4 border-transparent hover:border-yellow-400"
                                    @click="startEdit({{ $subjectJson }})">
                                    <td class="px-6 py-5 text-white font-semibold">
                                        <span class="inline-flex items-center px-4 py-2 rounded-xl bg-gradient-to-r from-purple-500/30 to-pink-500/30 
                                                     border border-purple-400/40 group-hover/row:from-purple-500/40 group-hover/row:to-pink-500/40
                                                     group-hover/row:shadow-lg group-hover/row:shadow-purple-500/30 group-hover/row:scale-105
                                                     transition-all duration-300 font-mono text-sm backdrop-blur-sm">
                                            <i class="fas fa-hashtag text-purple-300 mr-2 text-xs"></i>
                                            {{ $subject->course_code }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-white group-hover/row:text-yellow-300 transition-colors duration-300 font-medium">
                                        <div class="flex items-center">
                                            <i class="fas fa-book text-white/40 mr-2 group-hover/row:text-yellow-400 transition-colors"></i>
                                            {{ $subject->subject_name }}
                                        </div>
                                    </td>
                                    <td class="px-6 py-5 text-white">
                                        <span class="inline-flex items-center justify-center w-12 h-12 rounded-xl 
                                                     bg-gradient-to-br from-yellow-400/30 to-orange-500/30 
                                                     border border-yellow-400/40 font-bold text-yellow-200
                                                     group-hover/row:scale-110 group-hover/row:rotate-6 group-hover/row:shadow-lg group-hover/row:shadow-yellow-500/30
                                                     transition-all duration-300 backdrop-blur-sm">
                                            {{ $subject->units }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-white">
                                        <span class="inline-flex items-center px-4 py-2 text-sm font-semibold rounded-xl bg-gradient-to-r from-cyan-500/30 to-teal-500/30 
                                                     text-cyan-200 border border-cyan-400/40
                                                     group-hover/row:from-cyan-500/40 group-hover/row:to-teal-500/40 group-hover/row:shadow-lg group-hover/row:shadow-cyan-500/30
                                                     transition-all duration-300 backdrop-blur-sm">
                                            <i class="fas fa-user-friends mr-2"></i>{{ $subject->enrolled_student ?? 0 }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-white">
                                        <span class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-gradient-to-r from-blue-500/30 to-cyan-500/30 
                                                     text-blue-200 border border-blue-400/40
                                                     group-hover/row:from-blue-500/40 group-hover/row:to-cyan-500/40 group-hover/row:shadow-lg group-hover/row:shadow-blue-500/30
                                                     transition-all duration-300 backdrop-blur-sm">
                                            <i class="fas fa-calendar mr-2"></i>{{ $subject->semester }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-white">
                                        <span class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-gradient-to-r from-green-500/30 to-emerald-500/30 
                                                     text-green-200 border border-green-400/40
                                                     group-hover/row:from-green-500/40 group-hover/row:to-emerald-500/40 group-hover/row:shadow-lg group-hover/row:shadow-green-500/30
                                                     transition-all duration-300 backdrop-blur-sm">
                                            <i class="fas fa-user-graduate mr-2"></i>Year {{ $subject->year_level }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 text-white">
                                        <span class="inline-flex items-center px-4 py-2 text-xs font-semibold rounded-xl bg-gradient-to-r from-purple-500/30 to-indigo-500/30 
                                                     text-purple-200 border border-purple-400/40
                                                     group-hover/row:from-purple-500/40 group-hover/row:to-indigo-500/40 group-hover/row:shadow-lg group-hover/row:shadow-purple-500/30
                                                     transition-all duration-300 backdrop-blur-sm">
                                            <i class="fas fa-building mr-2"></i>{{ $subject->program->name ?? 'N/A' }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-5 whitespace-nowrap" @click.stop>
                                        <!-- DELETE -->
                                        <form method="POST" action="{{ route('admin.subjects.destroy', $subject) }}" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                class="group/btn relative inline-flex items-center justify-center w-11 h-11 text-red-300 hover:text-white transition-all duration-300
                                                       hover:scale-110 active:scale-95 rounded-xl border border-red-400/30 hover:border-red-400/60
                                                       bg-red-500/10 hover:bg-red-500/30 backdrop-blur-sm
                                                       hover:shadow-lg hover:shadow-red-500/30 overflow-hidden"
                                                onclick="return confirm('Are you sure you want to delete this subject?')">
                                                <div class="absolute inset-0 bg-gradient-to-r from-red-500/20 to-red-600/20 opacity-0 group-hover/btn:opacity-100 transition-opacity"></div>
                                                <i class="fas fa-trash relative z-10 group-hover/btn:animate-pulse"></i>
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                            @empty
                                <tr class="animate-fade-in">
                                    <td colspan="8" class="px-6 py-20 text-center text-white/60">
                                        <div class="flex flex-col items-center space-y-6">
                                            <div class="relative">
                                                <div class="w-24 h-24 rounded-2xl bg-gradient-to-br from-white/10 to-white/5 flex items-center justify-center backdrop-blur-sm border border-white/20">
                                                    <i class="fas fa-book text-5xl opacity-30"></i>
                                                </div>
                                                <div class="absolute -top-2 -right-2 w-10 h-10 bg-yellow-500/20 rounded-full flex items-center justify-center border border-yellow-400/30 backdrop-blur-sm">
                                                    <i class="fas fa-search text-yellow-400 text-sm"></i>
                                                </div>
                                            </div>
                                            <div>
                                                <p class="text-xl font-bold text-white/80 mb-2">No subjects found</p>
                                                <p class="text-sm text-white/50">Click "Add Subject" button to create your first subject</p>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>

                </div>
            </div>

            <div class="mt-8 text-white animate-fade-in" style="animation-delay: 0.3s;">
                {{ $subjects->links() }}
            </div>

        </div>

        <!-- CREATE MODAL -->
        @include('admin.subjects.create')
        
        <!-- EDIT MODAL -->
        @include('admin.subjects.edit')
    </div>

    <style>
        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes slide-down {
            from {
                opacity: 0;
                transform: translateY(-30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-fade-in {
            animation: fade-in 0.6s ease-out forwards;
        }

        .animate-slide-down {
            animation: slide-down 0.6s ease-out forwards;
        }

        /* Glass Card Effect */
        .glass-card {
            background: linear-gradient(135deg, rgba(255, 255, 255, 0.1) 0%, rgba(255, 255, 255, 0.05) 100%);
            backdrop-filter: blur(20px);
            -webkit-backdrop-filter: blur(20px);
        }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar {
            height: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.05);
            border-radius: 10px;
            margin: 0 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: linear-gradient(90deg, rgba(250, 204, 21, 0.4), rgba(234, 179, 8, 0.6));
            border-radius: 10px;
            border: 2px solid rgba(255, 255, 255, 0.1);
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: linear-gradient(90deg, rgba(250, 204, 21, 0.6), rgba(234, 179, 8, 0.8));
        }

        /* Select dropdown styling */
        select option {
            background-color: rgba(30, 30, 40, 0.95);
            color: white;
            padding: 10px;
        }

        select option:hover {
            background-color: rgba(250, 204, 21, 0.2);
        }

        /* Filter input group animation */
        .filter-input-group {
            animation: fade-in 0.5s ease-out forwards;
        }

        .filter-input-group:nth-child(2) {
            animation-delay: 0.05s;
        }

        .filter-input-group:nth-child(3) {
            animation-delay: 0.1s;
        }

        .filter-input-group:nth-child(4) {
            animation-delay: 0.15s;
        }

        /* Table row stagger animation */
        tbody tr {
            animation: fade-in 0.5s ease-out forwards;
        }

        tbody tr:nth-child(1) { animation-delay: 0s; }
        tbody tr:nth-child(2) { animation-delay: 0.05s; }
        tbody tr:nth-child(3) { animation-delay: 0.1s; }
        tbody tr:nth-child(4) { animation-delay: 0.15s; }
        tbody tr:nth-child(5) { animation-delay: 0.2s; }
        tbody tr:nth-child(6) { animation-delay: 0.25s; }
        tbody tr:nth-child(7) { animation-delay: 0.3s; }
        tbody tr:nth-child(8) { animation-delay: 0.35s; }
        tbody tr:nth-child(9) { animation-delay: 0.4s; }
        tbody tr:nth-child(10) { animation-delay: 0.45s; }
    </style>
</x-app-layout>