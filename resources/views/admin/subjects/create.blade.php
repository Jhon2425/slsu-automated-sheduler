<!-- CREATE SUBJECT MODAL -->
<div x-show="openCreateModal"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto"
    aria-labelledby="modal-title"
    role="dialog"
    aria-modal="true">

    <!-- Background overlay -->
    <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
        <div x-show="openCreateModal"
            x-transition:enter="ease-out duration-300"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="ease-in duration-200"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
            @click="openCreateModal = false"
            class="fixed inset-0 bg-gradient-to-br from-black/70 via-black/60 to-black/70 backdrop-blur-md transition-opacity"
            aria-hidden="true">
        </div>

        <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>

        <!-- Modal panel -->
        <div x-show="openCreateModal"
            x-transition:enter="ease-out duration-400"
            x-transition:enter-start="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-90"
            x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave="ease-in duration-300"
            x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
            x-transition:leave-end="opacity-0 translate-y-8 sm:translate-y-0 sm:scale-90"
            class="inline-block align-bottom rounded-2xl text-left overflow-hidden shadow-2xl transform transition-all 
                   sm:my-8 sm:align-middle sm:max-w-2xl sm:w-full 
                   bg-gradient-to-br from-white/25 via-white/20 to-white/25 
                   backdrop-blur-xl border border-white/30
                   hover:shadow-yellow-500/10">

            <!-- Modal Header -->
            <div class="px-6 py-5 border-b border-white/30 bg-gradient-to-r from-white/10 to-white/5">
                <div class="flex items-center justify-between">
                    <h3 class="text-2xl font-bold text-white flex items-center group">
                        <span class="bg-gradient-to-r from-yellow-400 to-yellow-600 p-3 rounded-xl mr-3 
                                     group-hover:scale-110 group-hover:rotate-6 transition-all duration-300
                                     shadow-lg shadow-yellow-500/20">
                            <i class="fas fa-book"></i>
                        </span>
                        <span class="bg-gradient-to-r from-yellow-400 to-yellow-600 bg-clip-text text-transparent">
                            Add New Subject
                        </span>
                    </h3>
                    <button @click="openCreateModal = false" 
                        class="text-white/60 hover:text-white hover:bg-white/10 p-2 rounded-lg
                               transition-all duration-300 hover:rotate-90 hover:scale-110 active:scale-95">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <form method="POST" action="{{ route('admin.subjects.store') }}">
                @csrf

                <div class="px-6 py-6 space-y-5 max-h-[60vh] overflow-y-auto custom-scrollbar">

                    <!-- Program Selection -->
                    <div class="group">
                        <label class="block text-white text-sm font-bold mb-2 flex items-center">
                            <i class="fas fa-university mr-2 text-yellow-400 group-hover:scale-110 transition-transform duration-300"></i>
                            Program <span class="text-red-400 ml-1 animate-pulse">*</span>
                        </label>
                        <div class="relative">
                            <select name="program_id" required
                                class="w-full px-4 py-3 rounded-xl bg-white/15 backdrop-blur-sm border border-white/30 text-white 
                                       focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20
                                       transition-all duration-300 appearance-none cursor-pointer
                                       hover:bg-white/20 hover:border-yellow-400/50">
                                <option value="">Select Program</option>
                                @foreach($programs as $program)
                                    <option value="{{ $program->id }}" class="bg-gray-800">{{ $program->name }}</option>
                                @endforeach
                            </select>
                            <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-yellow-400 pointer-events-none"></i>
                        </div>
                        @error('program_id')
                            <p class="text-red-400 text-xs mt-2 flex items-center animate-shake">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Course Code -->
                    <div class="group">
                        <label class="block text-white text-sm font-bold mb-2 flex items-center">
                            <i class="fas fa-code mr-2 text-yellow-400 group-hover:scale-110 transition-transform duration-300"></i>
                            Course Code <span class="text-red-400 ml-1 animate-pulse">*</span>
                        </label>
                        <input type="text" 
                            name="course_code" 
                            required
                            placeholder="e.g., CS101, MATH201"
                            class="w-full px-4 py-3 rounded-xl bg-white/15 backdrop-blur-sm border border-white/30 text-white placeholder-white/40
                                   focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20
                                   transition-all duration-300 hover:bg-white/20 hover:border-yellow-400/50">
                        @error('course_code')
                            <p class="text-red-400 text-xs mt-2 flex items-center animate-shake">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Subject Name -->
                    <div class="group">
                        <label class="block text-white text-sm font-bold mb-2 flex items-center">
                            <i class="fas fa-book-open mr-2 text-yellow-400 group-hover:scale-110 transition-transform duration-300"></i>
                            Subject Name <span class="text-red-400 ml-1 animate-pulse">*</span>
                        </label>
                        <input type="text" 
                            name="subject_name" 
                            required
                            placeholder="e.g., Introduction to Programming"
                            class="w-full px-4 py-3 rounded-xl bg-white/15 backdrop-blur-sm border border-white/30 text-white placeholder-white/40
                                   focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20
                                   transition-all duration-300 hover:bg-white/20 hover:border-yellow-400/50">
                        @error('subject_name')
                            <p class="text-red-400 text-xs mt-2 flex items-center animate-shake">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <!-- Units -->
                    <div class="group">
                        <label class="block text-white text-sm font-bold mb-2 flex items-center">
                            <i class="fas fa-calculator mr-2 text-yellow-400 group-hover:scale-110 transition-transform duration-300"></i>
                            Units <span class="text-red-400 ml-1 animate-pulse">*</span>
                        </label>
                        <input type="number" 
                            name="units" 
                            required
                            min="1"
                            max="6"
                            step="0.5"
                            placeholder="e.g., 3"
                            class="w-full px-4 py-3 rounded-xl bg-white/15 backdrop-blur-sm border border-white/30 text-white placeholder-white/40
                                   focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20
                                   transition-all duration-300 hover:bg-white/20 hover:border-yellow-400/50">
                        @error('units')
                            <p class="text-red-400 text-xs mt-2 flex items-center animate-shake">
                                <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                            </p>
                        @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <!-- Semester -->
                        <div class="group">
                            <label class="block text-white text-sm font-bold mb-2 flex items-center">
                                <i class="fas fa-calendar-alt mr-2 text-yellow-400 group-hover:scale-110 transition-transform duration-300"></i>
                                Semester <span class="text-red-400 ml-1 animate-pulse">*</span>
                            </label>
                            <div class="relative">
                                <select name="semester" required
                                    class="w-full px-4 py-3 rounded-xl bg-white/15 backdrop-blur-sm border border-white/30 text-white
                                           focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20
                                           transition-all duration-300 appearance-none cursor-pointer
                                           hover:bg-white/20 hover:border-yellow-400/50">
                                    <option value="">Select Semester</option>
                                    <option value="1st Semester" class="bg-gray-800">1st Semester</option>
                                    <option value="2nd Semester" class="bg-gray-800">2nd Semester</option>
                                    <option value="Summer" class="bg-gray-800">Summer</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-yellow-400 pointer-events-none"></i>
                            </div>
                            @error('semester')
                                <p class="text-red-400 text-xs mt-2 flex items-center animate-shake">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>

                        <!-- Year Level -->
                        <div class="group">
                            <label class="block text-white text-sm font-bold mb-2 flex items-center">
                                <i class="fas fa-graduation-cap mr-2 text-yellow-400 group-hover:scale-110 transition-transform duration-300"></i>
                                Year Level <span class="text-red-400 ml-1 animate-pulse">*</span>
                            </label>
                            <div class="relative">
                                <select name="year_level" required
                                    class="w-full px-4 py-3 rounded-xl bg-white/15 backdrop-blur-sm border border-white/30 text-white
                                           focus:outline-none focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20
                                           transition-all duration-300 appearance-none cursor-pointer
                                           hover:bg-white/20 hover:border-yellow-400/50">
                                    <option value="">Select Year</option>
                                    <option value="1" class="bg-gray-800">1st Year</option>
                                    <option value="2" class="bg-gray-800">2nd Year</option>
                                    <option value="3" class="bg-gray-800">3rd Year</option>
                                    <option value="4" class="bg-gray-800">4th Year</option>
                                </select>
                                <i class="fas fa-chevron-down absolute right-4 top-1/2 -translate-y-1/2 text-yellow-400 pointer-events-none"></i>
                            </div>
                            @error('year_level')
                                <p class="text-red-400 text-xs mt-2 flex items-center animate-shake">
                                    <i class="fas fa-exclamation-circle mr-1"></i>{{ $message }}
                                </p>
                            @enderror
                        </div>
                    </div>

                    <!-- Success Message -->
                    <div class="bg-gradient-to-r from-green-500/20 to-emerald-500/20 border border-green-500/30 rounded-xl p-4
                                hover:from-green-500/25 hover:to-emerald-500/25 transition-all duration-300
                                animate-fade-in shadow-lg shadow-green-500/10">
                        <div class="flex items-start">
                            <i class="fas fa-check-circle text-green-300 mt-1 mr-3 animate-pulse"></i>
                            <div class="text-sm text-green-200">
                                <strong class="text-green-100">Ready to create!</strong> Fill in all required fields and click "Create Subject" to add this subject to your program.
                            </div>
                        </div>
                    </div>

                </div>

                <!-- Modal Footer -->
                <div class="px-6 py-4 border-t border-white/30 bg-gradient-to-r from-white/10 to-white/5 flex justify-end space-x-3">
                    <button type="button" 
                        @click="openCreateModal = false"
                        class="px-6 py-3 rounded-xl border border-white/30 text-white 
                               hover:bg-white/10 hover:border-white/40 hover:scale-105 active:scale-95
                               transition-all duration-300 flex items-center">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </button>
                    <button type="submit"
                        class="group px-6 py-3 rounded-xl text-white transition-all duration-300 
                               bg-gradient-to-r from-yellow-500 to-yellow-600
                               hover:from-yellow-600 hover:to-yellow-700 hover:scale-105 hover:shadow-lg hover:shadow-yellow-500/30
                               active:scale-95 flex items-center">
                        <i class="fas fa-save mr-2 group-hover:rotate-12 transition-transform duration-300"></i>
                        Create Subject
                    </button>
                </div>

            </form>

        </div>
    </div>

    <style>
        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            25% { transform: translateX(-5px); }
            75% { transform: translateX(5px); }
        }

        @keyframes fade-in {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .animate-shake {
            animation: shake 0.3s ease-in-out;
        }

        .animate-fade-in {
            animation: fade-in 0.6s ease-out;
        }

        .custom-scrollbar::-webkit-scrollbar {
            width: 8px;
        }

        .custom-scrollbar::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb {
            background: rgba(218, 198, 17, 0.5);
            border-radius: 10px;
        }

        .custom-scrollbar::-webkit-scrollbar-thumb:hover {
            background: rgba(218, 198, 17, 0.7);
        }
    </style>
</div>