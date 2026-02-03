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
                                <option value="Bachelor Degree" {{ old('degree_earned') == "Bachelor Degree" ? 'selected' : '' }}>Bachelor's Degree</option>
                                <option value="Master Degree" {{ old('degree_earned') == "Master Degree" ? 'selected' : '' }}>Master's Degree</option>
                                <option value="Doctorate Degree" {{ old('degree_earned') == 'Doctorate Degree' ? 'selected' : '' }}>Doctorate Degree</option>
                                <option value="Professional Degree" {{ old('degree_earned') == 'Professional Degree' ? 'selected' : '' }}>Professional Degree</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Year Graduated -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-calendar-check mr-2"></i>Year Graduated <span class="text-red-400">*</span>
                                </label>
                                <input type="number" name="year_graduated" value="{{ old('year_graduated') }}" 
                                       min="1950" max="{{ date('Y') }}" required
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>

                            <!-- Course -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-book-open mr-2"></i>Course <span class="text-red-400">*</span>
                                </label>
                                <input type="text" name="course" value="{{ old('course') }}" required
                                       placeholder="e.g., Computer Science"
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>
                        </div>

                        <!-- School Graduated -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-university mr-2"></i>School Graduated <span class="text-red-400">*</span>
                            </label>
                            <input type="text" name="school_graduated" value="{{ old('school_graduated') }}" required
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

        select, input, textarea {
            color-scheme: dark;
        }

/* Main select box */
select {
    background-color: rgba(255, 255, 255, 0.85); /* white background */
    color: #000000; /* black text */
    border: 2px solid #10b981; /* green border maintained */
    border-radius: 0.75rem;
    padding: 0.75rem 1rem;
    outline: none;
    -webkit-appearance: none;
    -moz-appearance: none;
    appearance: none;
    transition: border-color 0.2s, box-shadow 0.2s;
}

/* Keep green border on focus */
select:focus {
    border-color: #10b981;
    box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
    background-color: rgba(255, 255, 255, 0.85);
    color: #000000;
}

/* Dropdown list options - completely neutral */
select option {
    background-color: #ffffff; /* plain white background */
    color: #000000; /* black text */
    border: none; /* remove any border */
    box-shadow: none; /* remove any shadow or “select status” */
}

/* Optional: simple hover effect */
select option:hover {
    background-color: rgba(156, 163, 175, 0.2); /* very light gray hover */
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
    </style>

    <script>
        const availableSubjects = @json($subjects ?? []);
        let subjectRowCounter = 0;

        function addSubjectRow() {
            const container = document.getElementById('subjects-container');

            // ✅ FIX: stable index for this row
            const rowIndex = subjectRowCounter++;
            const rowId = `subject-row-${rowIndex}`;

            const row = document.createElement('div');
            row.id = rowId;
            row.className = 'glass-item p-6 rounded-xl space-y-4';
            row.style.background = 'rgba(255, 255, 255, 0.08)';
            row.style.border = '1px solid rgba(255, 255, 255, 0.1)';

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
                            onchange="updateSubjectDetails(this, '${rowId}')"
                            class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Choose a subject</option>
                        ${availableSubjects.map(subject => `
                            <option value="${subject.id}"
                                    data-code="${subject.course_code || ''}"
                                    data-year="${subject.year_level || subject.year || ''}"
                                    data-semester="${subject.semester || ''}"
                                    data-lecture="${subject.lec || subject.lecture_units || ''}"
                                    data-laboratory="${subject.lab || subject.laboratory_units || ''}">
                                ${subject.subject_name} (${subject.course_code})
                            </option>
                        `).join('')}
                    </select>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <!-- Subject Code -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-code mr-2"></i>Subject Code
                        </label>
                        <input type="text"
                            id="${rowId}-code"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>

                    <!-- Year Level -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-layer-group mr-2"></i>Year Level
                        </label>
                        <input type="text"
                            id="${rowId}-year"
                            name="subjects[${rowIndex}][year_level]"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <!-- Semester -->
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

                    <!-- Lecture Units -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-chalkboard-teacher mr-2"></i>Lecture Units
                        </label>
                        <input type="number"
                            id="${rowId}-lecture"
                            name="subjects[${rowIndex}][lecture_units]"
                            min="0"
                            step="0.5"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>

                    <!-- Laboratory Units -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-flask mr-2"></i>Laboratory Units
                        </label>
                        <input type="number"
                            id="${rowId}-laboratory"
                            name="subjects[${rowIndex}][laboratory_units]"
                            min="0"
                            step="0.5"
                            readonly
                            class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                    </div>
                </div>

                <!-- ✅ Availability + Date + Time (EQUAL WIDTHS, ONE LINE) -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-end">
                    <!-- Availability -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-user-check mr-2"></i>Availability
                        </label>
                        <select name="subjects[${rowIndex}][availability]"
                                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select Day</option>
                            <option value="Monday">Monday</option>
                            <option value="Tuesday">Tuesday</option>
                            <option value="Wednesday">Wednesday</option>
                            <option value="Thursday">Thursday</option>
                            <option value="Friday">Friday</option>
                            <option value="Saturday">Saturday</option>
                            <option value="Sunday">Sunday</option>
                        </select>
                    </div>

                    <!-- Date -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-calendar-day mr-2"></i>Date
                        </label>
                        <input type="date"
                            name="subjects[${rowIndex}][date]"
                            class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                    </div>

                    <!-- Time Slot -->
                    <div>
                        <label class="block text-white font-medium mb-2">
                            <i class="fas fa-clock mr-2"></i>Time Slot
                        </label>
                        <select name="subjects[${rowIndex}][time_slot]"
                                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                            <option value="">Select Time</option>

                            <option value="07:30-08:30">7:30 AM - 8:30 AM</option>
                            <option value="08:30-09:30">8:30 AM - 9:30 AM</option>
                            <option value="09:30-10:30">9:30 AM - 10:30 AM</option>
                            <option value="10:30-11:30">10:30 AM - 11:30 AM</option>
                            <option value="11:30-12:30">11:30 AM - 12:30 PM</option>
                            <option value="12:30-13:30">12:30 PM - 1:30 PM</option>
                            <option value="13:30-14:30">1:30 PM - 2:30 PM</option>
                            <option value="14:30-15:30">2:30 PM - 3:30 PM</option>
                            <option value="15:30-16:30">3:30 PM - 4:30 PM</option>
                            <option value="16:30-17:30">4:30 PM - 5:30 PM</option>
                            <option value="17:30-18:30">5:30 PM - 6:30 PM</option>
                            <option value="18:30-19:30">6:30 PM - 7:30 PM</option>
                            <option value="19:30-20:30">7:30 PM - 8:30 PM</option>
                            <option value="20:30-21:30">8:30 PM - 9:30 PM</option>
                            <option value="21:30-22:30">9:30 PM - 10:30 PM</option>
                        </select>
                    </div>
                </div>
            `;

            container.appendChild(row);
        }

        function updateSubjectDetails(select, rowId) {
            const selectedOption = select.options[select.selectedIndex];

            const code = selectedOption.getAttribute('data-code');
            const year = selectedOption.getAttribute('data-year');
            const semester = selectedOption.getAttribute('data-semester');
            const lecture = selectedOption.getAttribute('data-lecture');
            const laboratory = selectedOption.getAttribute('data-laboratory');

            document.getElementById(`${rowId}-code`).value = code || '';
            document.getElementById(`${rowId}-year`).value = year || '';
            document.getElementById(`${rowId}-semester`).value = semester || '';
            document.getElementById(`${rowId}-lecture`).value = lecture || '';
            document.getElementById(`${rowId}-laboratory`).value = laboratory || '';
        }

        function removeSubjectRow(rowId) {
            const row = document.getElementById(rowId);
            if (row) {
                row.style.animation = 'fadeOut 0.3s ease-out';
                setTimeout(() => row.remove(), 300);
            }
        }

        // ✅ IMPORTANT: inline onclick needs these functions in global scope
        window.addSubjectRow = addSubjectRow;
        window.updateSubjectDetails = updateSubjectDetails;
        window.removeSubjectRow = removeSubjectRow;
    </script>

</x-app-layout>