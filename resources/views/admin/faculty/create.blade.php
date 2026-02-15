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
                    <span class="text-white font-semibold">Create Faculty</span>
                </div>
                <h1 class="text-4xl font-bold text-white flex items-center">
                    <i class="fas fa-user-plus mr-3"></i>Create New Faculty
                </h1>
                <p class="mt-2 text-white/90 text-lg">Add a new faculty member to the system</p>
            </div>

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
            <form action="{{ route('admin.faculty.store') }}" method="POST" class="space-y-6">
                @csrf

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
                            <input type="text" name="name" value="{{ old('name') }}" required
                                   class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                        </div>

                        <!-- Faculty Code -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-id-card mr-2"></i>Faculty Code <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="faculty_code" value="{{ old('faculty_code') }}" required
                                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            <p class="mt-1 text-sm text-white/60">
                                <i class="fas fa-info-circle mr-1"></i>Enter a unique identifier for this faculty member
                            </p>
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
                                    <option value="Single" {{ old('civil_status') == 'Single' ? 'selected' : '' }}>Single</option>
                                    <option value="Married" {{ old('civil_status') == 'Married' ? 'selected' : '' }}>Married</option>
                                    <option value="Widowed" {{ old('civil_status') == 'Widowed' ? 'selected' : '' }}>Widowed</option>
                                    <option value="Divorced" {{ old('civil_status') == 'Divorced' ? 'selected' : '' }}>Divorced</option>
                                </select>
                            </div>

                            <!-- Birthdate -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-calendar mr-2"></i>Birthdate <span class="text-red-400">*</span>
                                </label>
                                <input type="date" name="birthdate" value="{{ old('birthdate') }}" required
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
                                <option value="Full-Time" {{ old('employment_status') == 'Full-Time' ? 'selected' : '' }}>Full-Time</option>
                                <option value="Part-Time" {{ old('employment_status') == 'Part-Time' ? 'selected' : '' }}>Part-Time</option>
                                <option value="Contractual" {{ old('employment_status') == 'Contractual' ? 'selected' : '' }}>Contractual</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Rank -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-award mr-2"></i>Rank <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="rank" value="{{ old('rank') }}" required
                                       placeholder="Instructor I, Professor II"
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>

                            <!-- Years of Service in SLSU -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-hourglass-half mr-2"></i>Years of Service in SLSU <span class="text-red-400">*</span>
                                </label>
                                <input type="number" name="years_of_service" value="{{ old('years_of_service') }}" required
                                       min="0" max="50" step="0.5"
                                       placeholder="e.g., 5"
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                                <p class="mt-1 text-sm text-white/60">
                                    <i class="fas fa-info-circle mr-1"></i>Enter number of years (e.g., 5.5 for 5 years and 6 months)
                                </p>
                            </div>
                        </div>

                        <!-- Home Address -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-home mr-2"></i>Home Address <span class="text-red-400">*</span>
                            </label>
                            <textarea name="home_address" rows="3" required
                                      class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">{{ old('home_address') }}</textarea>
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
                        <p class="text-sm text-white/80 mt-1">Add academic qualifications (at least one required)</p>
                    </div>

                    <div class="p-8">
                        <div id="education-container" class="space-y-4">
                            <!-- Education rows will be added here -->
                        </div>

                        <button type="button" onclick="addEducationRow()" 
                                class="mt-4 px-6 py-3 rounded-xl text-white font-medium transition-all duration-300 hover:scale-105"
                                style="background: rgba(109, 151, 115, 0.3); border: 1px solid rgba(109, 151, 115, 0.5);">
                            <i class="fas fa-plus-circle mr-2"></i>Add Educational Background
                        </button>
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
                        <p class="text-sm text-white/80 mt-1">Assign subjects to this faculty member (optional)</p>
                    </div>

                    <div class="p-8">
                        <div id="subjects-container" class="space-y-4">
                            <!-- Subject rows will be added here -->
                        </div>

                        <button type="button" onclick="addSubjectRow()" 
                                class="mt-4 px-6 py-3 rounded-xl text-white font-medium transition-all duration-300 hover:scale-105"
                                style="background: rgba(59, 130, 246, 0.3); border: 1px solid rgba(59, 130, 246, 0.5);">
                            <i class="fas fa-plus-circle mr-2"></i>Add Subject
                        </button>
                    </div>
                </div>

                <!-- Unavailability Schedule Section -->
                <div class="glass-card rounded-2xl overflow-hidden shadow-xl animate-fade-in-up" style="animation-delay: 0.25s;">
                    <div class="px-8 py-6" style="background: rgba(220, 38, 38, 0.15);">
                        <h3 class="text-2xl font-bold text-white flex items-center">
                            <div class="rounded-xl p-2 mr-3" style="background: rgba(220, 38, 38, 0.3);">
                                <i class="fas fa-calendar-times"></i>
                            </div>
                            Unavailability Schedule
                        </h3>
                        <p class="text-sm text-white/80 mt-1">Set times when this faculty member is not available (optional)</p>
                    </div>

                    <div class="p-8">
                        <div id="unavailabilities-container" class="space-y-4">
                            <!-- Unavailability rows will be added here -->
                        </div>

                        <button type="button" onclick="addUnavailabilityRow()" 
                                class="mt-4 px-6 py-3 rounded-xl text-white font-medium transition-all duration-300 hover:scale-105"
                                style="background: rgba(220, 38, 38, 0.3); border: 1px solid rgba(220, 38, 38, 0.5);">
                            <i class="fas fa-plus-circle mr-2"></i>Add Unavailability
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
                        <p class="text-sm text-white/80 mt-1">Login credentials for faculty member</p>
                    </div>

                    <div class="p-8 space-y-6">
                        <!-- Email -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email Address <span class="text-red-400">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email') }}" required
                                   class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-lock mr-2"></i>Password <span class="text-red-400">*</span>
                                </label>
                                <input type="password" name="password" required
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-lock mr-2"></i>Confirm Password <span class="text-red-400">*</span>
                                </label>
                                <input type="password" name="password_confirmation" required
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="flex justify-end gap-4 animate-fade-in-up" style="animation-delay: 0.4s;">
                    <a href="{{ route('admin.faculty.index') }}" 
                       class="px-8 py-4 rounded-xl text-white font-medium transition-all duration-300"
                       style="background: rgba(255, 255, 255, 0.1); border: 1px solid rgba(255, 255, 255, 0.2);">
                        <i class="fas fa-times mr-2"></i>Cancel
                    </a>
                    <button type="submit" 
                            class="px-8 py-4 rounded-xl text-white font-medium transition-all duration-300 shadow-lg hover:shadow-xl hover:scale-105"
                            style="background: linear-gradient(135deg, #6D9773 0%, #5a7d60 100%);">
                        <i class="fas fa-save mr-2"></i>Create Faculty Member
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

        .glass-item {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Make selects match your inputs */
        .glass-card select,
        select {
            background: rgba(255, 255, 255, 0.10);
            border: 1px solid rgba(255, 255, 255, 0.20);
            color: #ffffff;
        }

        /* Keep focus consistent */
        .glass-card select:focus,
        select:focus {
            outline: none;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35);
            border-color: transparent;
        }

                /* ✅ Force green focus for ALL inputs (removes blue focus line) */
        input:focus,
        textarea:focus,
        select:focus {
            outline: none !important;
            border-color: transparent !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35) !important;
        }

        /* ✅ Specifically catch number inputs (some browsers are stubborn) */
        input[type="number"]:focus {
            outline: none !important;
            border-color: transparent !important;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.35) !important;
        }

        /* Dropdown list colors */
        /* Make dropdown list clean white */
        select option {
            background-color: #ffffff;
            color: #111827; /* dark text */
        }

        /* ✅ Remove number input arrows (Chrome, Edge, Safari) */
        input[type="number"]::-webkit-inner-spin-button,
        input[type="number"]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }

        /* ✅ Remove number input arrows (Firefox) */
        input[type="number"] {
            -moz-appearance: textfield;
        }

        select, input, textarea {
            color-scheme: dark;
        }

        select option:hover {
            background-color: rgba(156, 163, 175, 0.2);
            color: #000000;
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

        /* Read-only fields styling */
        input[readonly] {
            cursor: not-allowed;
            opacity: 0.7;
        }
    </style>

    <script>
        const availableSubjects = @json($subjects ?? []);
        const availablePrograms = @json($programs ?? []);
        let subjectRowCounter = 0;
        let unavailabilityRowCounter = 0;
        let educationRowCounter = 0;

        // Time slots from 7:30 AM to 7:00 PM in 30-minute intervals
        const timeSlots = [
            '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', 
            '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00',
            '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00'
        ];

        const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        // Add initial education row on page load
        document.addEventListener('DOMContentLoaded', function() {
            addEducationRow();
        });

        function addEducationRow() {
            const container = document.getElementById('education-container');
            const rowIndex = educationRowCounter++;
            const rowId = `education-row-${rowIndex}`;

            const row = document.createElement('div');
            row.id = rowId;
            row.className = 'glass-item p-6 rounded-xl space-y-4';

            row.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-white font-semibold">
                        <i class="fas fa-graduation-cap mr-2"></i>Education ${rowIndex + 1}
                    </h4>
                    ${rowIndex > 0 ? `
                    <button type="button" onclick="removeEducationRow('${rowId}')"
                            class="text-red-400 hover:text-red-300 transition">
                        <i class="fas fa-times-circle text-xl"></i>
                    </button>
                    ` : ''}
                </div>

                <!-- Degree Earned -->
                <div>
                    <label class="block text-white font-medium mb-2">
                        <i class="fas fa-certificate mr-2"></i>Degree Earned <span class="text-red-400">*</span>
                    </label>
                    <select name="education[${rowIndex}][degree_earned]" required
                            class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Select Degree</option>
                        <option value="Bachelor Degree">Bachelor's Degree</option>
                        <option value="Master Degree">Master's Degree</option>
                        <option value="Doctorate Degree">Doctorate Degree</option>
                        <option value="Professional Degree">Professional Degree</option>
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Year Graduated -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-calendar-check mr-2"></i>Year Graduated <span class="text-red-400">*</span>
                        </label>
                        <input type="number" name="education[${rowIndex}][year_graduated]" 
                               min="1950" max="${new Date().getFullYear()}" required
                               placeholder="e.g., 2020"
                               class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <!-- Course -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-book-open mr-2"></i>Course <span class="text-red-400">*</span>
                        </label>
                        <input type="text" name="education[${rowIndex}][course]" required
                               placeholder="e.g., Computer Science"
                               class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>
                </div>

                <!-- School Graduated -->
                <div>
                    <label class="block text-white font-medium mb-2">
                        <i class="fas fa-university mr-2"></i>School Graduated <span class="text-red-400">*</span>
                    </label>
                    <input type="text" name="education[${rowIndex}][school_graduated]" required
                           placeholder="e.g., University of the Philippines"
                           class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500">
                </div>
            `;

            container.appendChild(row);
        }

        function removeEducationRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => row.remove(), 300);
            }
        }

        function addSubjectRow() {
            const container = document.getElementById('subjects-container');
            const rowIndex = subjectRowCounter++;
            const rowId = `subject-row-${rowIndex}`;

            const row = document.createElement('div');
            row.id = rowId;
            row.className = 'glass-item p-6 rounded-xl space-y-4';

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
                        <i class="fas fa-list mr-2"></i>Select Subject
                    </label>
                    <select name="subjects[${rowIndex}][subject_id]"
                            onchange="updateSubjectDetails(this, '${rowId}', ${rowIndex})"
                            required
                            class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Choose a subject</option>
                        ${availableSubjects.map(subject => `
                            <option value="${subject.id}"
                                    data-code="${subject.course_code || ''}"
                                    data-year="${subject.year_level || ''}"
                                    data-lecture="${subject.lec || 0}"
                                    data-laboratory="${subject.lab || 0}"
                                    data-semester="${subject.semester || ''}"
                                    data-prereq="${subject.pre_req || ''}">
                                ${subject.subject_name} (${subject.course_code})
                            </option>
                        `).join('')}
                    </select>
                </div>

                <!-- Auto-filled fields (Read-only) -->
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-code mr-2"></i>Subject Code
                        </label>
                        <input type="text"
                            id="${rowId}-code"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-layer-group mr-2"></i>Year Level
                        </label>
                        <input type="number"
                            id="${rowId}-year"
                            name="subjects[${rowIndex}][year_level]"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-calendar-alt mr-2"></i>Semester
                        </label>
                        <input type="text"
                            id="${rowId}-semester"
                            name="subjects[${rowIndex}][semester]"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>Lecture Units
                        </label>
                        <input type="number"
                            id="${rowId}-lecture"
                            name="subjects[${rowIndex}][lecture_units]"
                            step="0.5"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>

                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-flask mr-2"></i>Laboratory Units
                        </label>
                        <input type="number"
                            id="${rowId}-laboratory"
                            name="subjects[${rowIndex}][laboratory_units]"
                            step="0.5"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>
                </div>

                <!-- Class Size -->
                <div>
                    <label class="block text-white font-medium mb-2">
                        <i class="fas fa-users mr-2"></i>Class Size <span class="text-red-400">*</span>
                    </label>
                    <input type="number"
                        name="subjects[${rowIndex}][class_size]"
                        min="1"
                        max="100"
                        placeholder="e.g., 40"
                        required
                        class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500">
                    <p class="mt-1 text-sm text-white/60">
                        <i class="fas fa-info-circle mr-1"></i>Expected number of students in this class
                    </p>
                </div>

                <!-- Hidden program ID field -->
                <input type="hidden" id="${rowId}-program" name="subjects[${rowIndex}][program_id]">
            `;

            container.appendChild(row);
        }

        function updateSubjectDetails(select, rowId, rowIndex) {
            const selectedOption = select.options[select.selectedIndex];

            if (!selectedOption.value) {
                document.getElementById(`${rowId}-code`).value = '';
                document.getElementById(`${rowId}-year`).value = '';
                document.getElementById(`${rowId}-semester`).value = '';
                document.getElementById(`${rowId}-lecture`).value = '';
                document.getElementById(`${rowId}-laboratory`).value = '';
                document.getElementById(`${rowId}-program`).value = '';
                return;
            }

            const code = selectedOption.getAttribute('data-code');
            const year = selectedOption.getAttribute('data-year');
            let semester = selectedOption.getAttribute('data-semester');
            const lecture = selectedOption.getAttribute('data-lecture');
            const laboratory = selectedOption.getAttribute('data-laboratory');
            const program = selectedOption.getAttribute('data-program');

            if (semester) {
                if (semester === '1') {
                    semester = '1st Semester';
                } else if (semester === '2') {
                    semester = '2nd Semester';
                }
            }

            document.getElementById(`${rowId}-code`).value = code || '';
            document.getElementById(`${rowId}-year`).value = year || '';
            document.getElementById(`${rowId}-semester`).value = semester || '';
            document.getElementById(`${rowId}-lecture`).value = lecture || '0';
            document.getElementById(`${rowId}-laboratory`).value = laboratory || '0';
            document.getElementById(`${rowId}-program`).value = program || '';
        }

        function removeSubjectRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => row.remove(), 300);
            }
        }

        function addUnavailabilityRow() {
            const container = document.getElementById('unavailabilities-container');
            const rowIndex = unavailabilityRowCounter++;
            const rowId = `unavail-row-${rowIndex}`;

            const row = document.createElement('div');
            row.id = rowId;
            row.className = 'glass-item p-6 rounded-xl space-y-4';
            row.style.background = 'rgba(220, 38, 38, 0.08)';
            row.style.border = '1px solid rgba(220, 38, 38, 0.2)';

            row.innerHTML = `
                <div class="flex justify-between items-center mb-4">
                    <h4 class="text-white font-semibold">
                        <i class="fas fa-calendar-times mr-2"></i>Unavailability ${rowIndex + 1}
                    </h4>
                    <button type="button" onclick="removeUnavailabilityRow('${rowId}')"
                            class="text-red-400 hover:text-red-300 transition">
                        <i class="fas fa-times-circle text-xl"></i>
                    </button>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Day Selection -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-calendar-day mr-2"></i>Day
                        </label>
                        <select name="unavailabilities[${rowIndex}][day]"
                                required
                                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Select Day</option>
                            ${daysOfWeek.map(day => `<option value="${day}">${day}</option>`).join('')}
                        </select>
                    </div>

                    <!-- Time From -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-clock mr-2"></i>From
                        </label>
                        <select name="unavailabilities[${rowIndex}][time_from]"
                                id="${rowId}-time-from"
                                onchange="updateTimeToOptions('${rowId}', ${rowIndex})"
                                required
                                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Select Time</option>
                            ${timeSlots.map(time => `<option value="${time}">${formatTime(time)}</option>`).join('')}
                        </select>
                    </div>

                    <!-- Time To -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-clock mr-2"></i>To
                        </label>
                        <select name="unavailabilities[${rowIndex}][time_to]"
                                id="${rowId}-time-to"
                                required
                                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                            <option value="">Select Time</option>
                            ${timeSlots.map(time => `<option value="${time}">${formatTime(time)}</option>`).join('')}
                        </select>
                    </div>
                </div>

                <!-- Reason (Optional) -->
                <div>
                    <label class="block text-white font-medium mb-2">
                        <i class="fas fa-comment mr-2"></i>Reason (Optional)
                    </label>
                    <textarea name="unavailabilities[${rowIndex}][reason]"
                              rows="2"
                              placeholder="e.g., Teaching at another institution, Personal appointment"
                              class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-red-500"></textarea>
                </div>
            `;

            container.appendChild(row);
        }

        function removeUnavailabilityRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => row.remove(), 300);
            }
        }

        function formatTime(time) {
            const [hours, minutes] = time.split(':');
            const hour = parseInt(hours);
            const period = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour > 12 ? hour - 12 : (hour === 0 ? 12 : hour);
            return `${displayHour}:${minutes} ${period}`;
        }

        function updateTimeToOptions(rowId, rowIndex) {
            const timeFromSelect = document.getElementById(`${rowId}-time-from`);
            const timeToSelect = document.getElementById(`${rowId}-time-to`);
            
            if (!timeFromSelect.value) {
                return;
            }

            const selectedFromIndex = timeSlots.indexOf(timeFromSelect.value);
            const currentToValue = timeToSelect.value;

            timeToSelect.innerHTML = '<option value="">Select Time</option>';
            
            timeSlots.forEach((time, index) => {
                if (index > selectedFromIndex) {
                    const option = document.createElement('option');
                    option.value = time;
                    option.textContent = formatTime(time);
                    if (time === currentToValue) {
                        option.selected = true;
                    }
                    timeToSelect.appendChild(option);
                }
            });
        }

        // Make functions globally available
        window.addEducationRow = addEducationRow;
        window.removeEducationRow = removeEducationRow;
        window.addSubjectRow = addSubjectRow;
        window.updateSubjectDetails = updateSubjectDetails;
        window.removeSubjectRow = removeSubjectRow;
        window.addUnavailabilityRow = addUnavailabilityRow;
        window.removeUnavailabilityRow = removeUnavailabilityRow;
        window.updateTimeToOptions = updateTimeToOptions;
    </script>

</x-app-layout>