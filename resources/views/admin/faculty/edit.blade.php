<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Page Header with Breadcrumb -->
            <div class="mb-8 animate-fade-in">
                <div class="flex items-center text-white/70 text-sm mb-2">
                    <a href="{{ route('admin.dashboard') }}" class="hover:text-blue-400 transition-colors">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <a href="{{ route('admin.faculty.index') }}" class="hover:text-blue-400 transition-colors">
                        Faculty Management
                    </a>
                    <i class="fas fa-chevron-right mx-2 text-xs"></i>
                    <span class="text-white font-semibold">Edit Faculty</span>
                </div>
                <h1 class="text-4xl font-bold text-white flex items-center">
                    <i class="fas fa-user-edit mr-3"></i>Edit Faculty Member
                </h1>
                <p class="mt-2 text-white/90 text-lg">Update information for {{ $faculty->name }}</p>
            </div>

            <!-- Success Message -->
            @if (session('success'))
                <div class="glass-card border-l-4 border-green-400 text-white px-6 py-4 rounded-xl mb-6 animate-slide-down">
                    <div class="flex items-start">
                        <div class="bg-green-500 rounded-full p-3 mr-4">
                            <i class="fas fa-check-circle text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <span class="font-bold block text-sm">{{ session('success') }}</span>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="glass-card border-l-4 border-red-400 text-white px-6 py-4 rounded-xl mb-6 animate-slide-down">
                    <div class="flex items-start">
                        <div class="bg-red-500 rounded-full p-3 mr-4">
                            <i class="fas fa-exclamation-circle text-xl"></i>
                        </div>
                        <div class="flex-1">
                            <span class="font-bold block text-sm mb-2">Please correct the following errors:</span>
                            <ul class="list-disc list-inside text-sm space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Form -->
            <form action="{{ route('admin.faculty.update', $faculty->id) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <!-- Personal Information Section -->
                <div class="glass-card rounded-2xl overflow-hidden shadow-xl animate-fade-in-up">
                    <div class="px-8 py-6" style="background: rgba(109, 151, 115, 0.15);">
                        <h3 class="text-2xl font-bold text-white flex items-center">
                            <div class="rounded-xl p-2 mr-3" style="background: rgba(109, 151, 115, 0.3);">
                                <i class="fas fa-user"></i>
                            </div>
                            Personal Information
                        </h3>
                        <p class="text-sm text-white/80 mt-1">Basic faculty member details</p>
                    </div>

                    <div class="p-8 space-y-6">
                        <!-- Full Name -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-user mr-2"></i>Full Name <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="name" value="{{ old('name', $faculty->name) }}" required
                                   class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Civil Status -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-rings-wedding mr-2"></i>Civil Status <span class="text-red-400">*</span>
                                </label>
                                <select name="civil_status" required
                                        class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                                    <option value="">Select Status</option>
                                    <option value="Single" {{ old('civil_status', $faculty->civil_status) == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option value="Married" {{ old('civil_status', $faculty->civil_status) == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Widowed" {{ old('civil_status', $faculty->civil_status) == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                    <option value="Divorced" {{ old('civil_status', $faculty->civil_status) == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                </select>
                            </div>

                            <!-- Birthdate -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-calendar mr-2"></i>Birthdate <span class="text-red-400">*</span>
                                </label>
                                <input type="date" name="birthdate" value="{{ old('birthdate', $faculty->birthdate) }}" required
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>
                        </div>

                        <!-- Employment Status -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-briefcase mr-2"></i>Employment Status <span class="text-red-400">*</span>
                            </label>
                            <select name="employment_status" required
                                    class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                                <option value="">Select Status</option>
                                <option value="Full-Time" {{ old('employment_status', $faculty->employment_status) == 'Full-Time' ? 'selected' : '' }}>Full-Time</option>
                                <option value="Part-Time" {{ old('employment_status', $faculty->employment_status) == 'Part-Time' ? 'selected' : '' }}>Part-Time</option>
                                <option value="Contractual" {{ old('employment_status', $faculty->employment_status) == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                            </select>
                        </div>

                        <!-- Home Address -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-home mr-2"></i>Home Address <span class="text-red-400">*</span>
                            </label>
                            <textarea name="home_address" rows="3" required
                                      class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">{{ old('home_address', $faculty->home_address) }}</textarea>
                        </div>
                    </div>
                </div>

                <!-- Educational Background Section -->
                <div class="glass-card rounded-2xl overflow-hidden shadow-xl animate-fade-in-up" style="animation-delay: 0.1s;">
                    <div class="px-8 py-6" style="background: rgba(109, 151, 115, 0.15);">
                        <h3 class="text-2xl font-bold text-white flex items-center">
                            <div class="rounded-xl p-2 mr-3" style="background: rgba(109, 151, 115, 0.3);">
                                <i class="fas fa-graduation-cap"></i>
                            </div>
                            Educational Background
                        </h3>
                        <p class="text-sm text-white/80 mt-1">Academic qualifications</p>
                    </div>

                    <div class="p-8 space-y-6">
                        <!-- Degree Earned -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-certificate mr-2"></i>Degree Earned <span class="text-red-400">*</span>
                            </label>
                            <select name="degree_earned" required
                                    class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                                <option value="">Select Degree</option>
                                <option value="Bachelor's Degree" {{ old('degree_earned', $faculty->degree_earned) == "Bachelor's Degree" ? 'selected' : '' }}>Bachelor's Degree</option>
                                <option value="Master's Degree" {{ old('degree_earned', $faculty->degree_earned) == "Master's Degree" ? 'selected' : '' }}>Master's Degree</option>
                                <option value="Doctorate Degree" {{ old('degree_earned', $faculty->degree_earned) == 'Doctorate Degree' ? 'selected' : '' }}>Doctorate Degree</option>
                                <option value="Professional Degree" {{ old('degree_earned', $faculty->degree_earned) == 'Professional Degree' ? 'selected' : '' }}>Professional Degree</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Year Graduated -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-calendar-check mr-2"></i>Year Graduated <span class="text-red-400">*</span>
                                </label>
                                <input type="number" name="year_graduated" value="{{ old('year_graduated', $faculty->year_graduated) }}" 
                                       min="1950" max="{{ date('Y') }}" required
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>

                            <!-- Course -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-book-open mr-2"></i>Course <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="course" value="{{ old('course', $faculty->course) }}" required
                                       placeholder="e.g., Computer Science"
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>
                        </div>

                        <!-- School Graduated -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-university mr-2"></i>School Graduated <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="school_graduated" value="{{ old('school_graduated', $faculty->school_graduated) }}" required
                                   class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                        </div>
                    </div>
                </div>

                <!-- Subjects Assignment Section -->
                <div class="glass-card rounded-2xl overflow-hidden shadow-xl animate-fade-in-up" style="animation-delay: 0.2s;">
                    <div class="px-8 py-6" style="background: rgba(109, 151, 115, 0.15);">
                        <h3 class="text-2xl font-bold text-white flex items-center">
                            <div class="rounded-xl p-2 mr-3" style="background: rgba(109, 151, 115, 0.3);">
                                <i class="fas fa-book"></i>
                            </div>
                            Subject Assignments
                        </h3>
                        <p class="text-sm text-white/80 mt-1">Manage subject assignments for this faculty member</p>
                    </div>

                    <div class="p-8">
                        <div id="subjects-container" class="space-y-4">
                            <!-- Existing subject rows will be added here via JavaScript -->
                        </div>

                        <button type="button" onclick="addSubjectRow()" 
                                class="mt-4 px-6 py-3 rounded-xl text-white font-medium transition-all duration-300 hover:scale-105"
                                style="background: rgba(59, 130, 246, 0.3); border: 1px solid rgba(59, 130, 246, 0.5);">
                            <i class="fas fa-plus-circle mr-2"></i>Add Subject
                        </button>
                    </div>
                </div>

                <!-- Account Credentials Section -->
                <div class="glass-card rounded-2xl overflow-hidden shadow-xl animate-fade-in-up" style="animation-delay: 0.3s;">
                    <div class="px-8 py-6" style="background: rgba(109, 151, 115, 0.15);">
                        <h3 class="text-2xl font-bold text-white flex items-center">
                            <div class="rounded-xl p-2 mr-3" style="background: rgba(109, 151, 115, 0.3);">
                                <i class="fas fa-key"></i>
                            </div>
                            Account Credentials
                        </h3>
                        <p class="text-sm text-white/80 mt-1">Update login credentials (leave password blank to keep current)</p>
                    </div>

                    <div class="p-8 space-y-6">
                        <!-- Email -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email Address <span class="text-red-400">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $faculty->email) }}" required
                                   class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-lock mr-2"></i>New Password <span class="text-white/60">(optional)</span>
                                </label>
                                <input type="password" name="password" placeholder="Leave blank to keep current password"
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-lock mr-2"></i>Confirm New Password <span class="text-white/60">(optional)</span>
                                </label>
                                <input type="password" name="password_confirmation" placeholder="Confirm new password"
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-4 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <a href="{{ route('admin.faculty.index') }}" 
                       class="px-8 py-4 rounded-xl text-white font-medium transition-all duration-300 hover:bg-white/10"
                       style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit" 
                            class="px-8 py-4 rounded-xl text-white font-medium transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105"
                            style="background: linear-gradient(135deg, #6D9773 0%, #5a7d60 100%);">
                        <i class="fas fa-save mr-2"></i>Update Faculty Member
                    </button>
                </div>
            </form>

        </div>
    </div>

    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }

        select, input, textarea {
            color-scheme: dark;
        }

        select option {
            background: #1f2937;
            color: white;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

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

        @keyframes fadeOut {
            from {
                opacity: 1;
                transform: translateX(0);
            }
            to {
                opacity: 0;
                transform: translateX(-20px);
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
    </style>

    <script>
        // Available subjects, programs, and existing faculty subjects data
        const availableSubjects = @json($subjects ?? []);
        const existingSubjects = @json($existingSubjects ?? []);
        const programs = @json($programs ?? []);
        let subjectRowCounter = 0;

        // Load existing subjects on page load
        document.addEventListener('DOMContentLoaded', function() {
            console.log('Existing subjects:', existingSubjects);
            
            if (existingSubjects && existingSubjects.length > 0) {
                existingSubjects.forEach(subject => {
                    addSubjectRow(subject);
                });
            }
        });

        function addSubjectRow(existingData = null) {
            const container = document.getElementById('subjects-container');
            const rowId = `subject-row-${subjectRowCounter}`;
            const rowIndex = subjectRowCounter++;
            
            const row = document.createElement('div');
            row.id = rowId;
            row.className = 'glass-item p-6 rounded-xl space-y-4 animate-fade-in-up';
            row.style.background = 'rgba(255, 255, 255, 0.08)';
            row.style.border = '1px solid rgba(255, 255, 255, 0.1)';
            
            // Build subject options HTML
            const subjectOptions = availableSubjects.map(subject => {
                const selected = existingData && existingData.id === subject.id ? 'selected' : '';
                return `<option value="${subject.id}" 
                        data-code="${subject.course_code}" 
                        data-units="${subject.units || ''}"
                        data-name="${subject.subject_name}"
                        ${selected}>
                    ${subject.subject_name} (${subject.course_code})
                </option>`;
            }).join('');

            // Build program options HTML
            const programOptions = programs.map(program => {
                const selected = existingData && existingData.pivot && existingData.pivot.program_id === program.id ? 'selected' : '';
                return `<option value="${program.id}" ${selected}>${program.program_name}</option>`;
            }).join('');
            
            row.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-white font-semibold">
                        <i class="fas fa-book mr-2"></i>Subject ${rowIndex + 1}
                    </h4>
                    <button type="button" onclick="removeSubjectRow('${rowId}')" 
                            class="text-red-400 hover:text-red-300 transition">
                        <i class="fas fa-times-circle text-xl"></i>
                    </button>
                </div>

                <!-- Subject Selection -->
                <div>
                    <label class="block text-white font-medium mb-2">
                        <i class="fas fa-list mr-2"></i>Select Subject <span class="text-red-400">*</span>
                    </label>
                    <select name="subjects[${rowIndex}][subject_id]" 
                            onchange="updateSubjectDetails(this, '${rowId}')"
                            required
                            class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Choose a subject</option>
                        ${subjectOptions}
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Subject Code (Auto-filled) -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-code mr-2"></i>Subject Code
                        </label>
                        <input type="text" 
                               id="${rowId}-code"
                               value="${existingData?.course_code || ''}"
                               readonly
                               class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>

                    <!-- Lecture Units -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>Lecture Units
                        </label>
                        <input type="number" 
                               name="subjects[${rowIndex}][lecture_units]" 
                               value="${existingData?.pivot?.lecture_units || ''}"
                               min="0" 
                               step="0.5"
                               placeholder="0.0"
                               class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <!-- Laboratory Units -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-flask mr-2"></i>Laboratory Units
                        </label>
                        <input type="number" 
                               name="subjects[${rowIndex}][laboratory_units]" 
                               value="${existingData?.pivot?.laboratory_units || ''}"
                               min="0" 
                               step="0.5"
                               placeholder="0.0"
                               class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>

                <!-- Program Selection -->
                <div>
                    <label class="block text-white font-medium mb-2">
                        <i class="fas fa-graduation-cap mr-2"></i>Program
                    </label>
                    <select name="subjects[${rowIndex}][program_id]"
                            class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select Program (Optional)</option>
                        ${programOptions}
                    </select>
                </div>
            `;
            
            container.appendChild(row);
        }

        function updateSubjectDetails(select, rowId) {
            const selectedOption = select.options[select.selectedIndex];
            const code = selectedOption.getAttribute('data-code');
            
            document.getElementById(`${rowId}-code`).value = code || '';
        }

        function removeSubjectRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                if (confirm('Are you sure you want to remove this subject assignment?')) {
                    row.style.animation = 'fadeOut 0.3s ease-out';
                    setTimeout(() => row.remove(), 300);
                }
            }
        }
    </script>
</x-app-layout>