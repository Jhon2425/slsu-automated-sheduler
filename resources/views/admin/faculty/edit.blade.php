<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- COMPREHENSIVE DEBUG SECTION -->
            @if(config('app.debug'))
                <div class="mb-6 p-6 bg-yellow-500/30 border-2 border-yellow-500 rounded-xl text-white">
                    <h3 class="text-xl font-bold mb-4">🐛 DEBUG INFORMATION</h3>
                    
                    <div class="space-y-3 text-sm">
                        <div class="bg-black/30 p-3 rounded">
                            <strong>User Object:</strong><br>
                            ID: {{ $user->id ?? 'NULL' }}<br>
                            Name: {{ $user->name ?? 'NULL' }}
                        </div>
                        
                        <div class="bg-black/30 p-3 rounded">
                            <strong>Faculty Object:</strong><br>
                            Type: {{ gettype($faculty) }}<br>
                            ID: {{ $faculty->id ?? 'NULL' }}<br>
                            Name: {{ $faculty->name ?? 'NULL' }}<br>
                            Birthdate (raw): {{ $faculty->birthdate ?? 'NULL' }}<br>
                            Birthdate (type): {{ gettype($faculty->birthdate ?? null) }}<br>
                            Birthdate (formatted): {{ $faculty->birthdate ? $faculty->birthdate->format('Y-m-d') : 'NULL' }}
                        </div>
                        
                        <div class="bg-black/30 p-3 rounded">
                            <strong>Unavailabilities Check:</strong><br>
                            Has 'unavailabilities' property: {{ isset($faculty->unavailabilities) ? 'YES' : 'NO' }}<br>
                            Type: {{ isset($faculty->unavailabilities) ? gettype($faculty->unavailabilities) : 'N/A' }}<br>
                            Count: {{ isset($faculty->unavailabilities) ? count($faculty->unavailabilities) : '0' }}<br>
                            Is Array: {{ is_array($faculty->unavailabilities ?? null) ? 'YES' : 'NO' }}<br>
                            Is Collection: {{ is_object($faculty->unavailabilities ?? null) && method_exists($faculty->unavailabilities ?? null, 'count') ? 'YES' : 'NO' }}
                        </div>
                        
                        @if(isset($faculty->unavailabilities) && count($faculty->unavailabilities) > 0)
                            <div class="bg-black/30 p-3 rounded">
                                <strong>Unavailability Data:</strong><br>
                                @foreach($faculty->unavailabilities as $idx => $unav)
                                    [{{ $idx }}] 
                                    Day: {{ $unav->day ?? 'NULL' }} | 
                                    From: {{ $unav->time_from ?? 'NULL' }} | 
                                    To: {{ $unav->time_to ?? 'NULL' }} | 
                                    Reason: {{ $unav->reason ?? 'NULL' }}<br>
                                @endforeach
                            </div>
                        @else
                            <div class="bg-red-500/50 p-3 rounded">
                                ⚠️ NO UNAVAILABILITIES FOUND - Check database or relationship loading
                            </div>
                        @endif
                        
                        <div class="bg-black/30 p-3 rounded">
                            <strong>All Faculty Properties:</strong><br>
                            @php
                                $props = get_object_vars($faculty);
                                echo implode(', ', array_keys($props));
                            @endphp
                        </div>
                    </div>
                </div>
            @endif

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
                <p class="mt-2 text-white/90 text-lg">Update faculty member information</p>
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
            <form action="{{ route('admin.faculty.update', $user->id) }}" method="POST" class="space-y-6">
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
                                <input type="date" name="birthdate" 
                                       value="{{ old('birthdate', $faculty->birthdate ? $faculty->birthdate->format('Y-m-d') : '') }}" 
                                       required
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
                                <option value="Bachelor Degree" {{ old('degree_earned', $faculty->degree_earned) == "Bachelor Degree" ? 'selected' : '' }}>Bachelor's Degree</option>
                                <option value="Master Degree" {{ old('degree_earned', $faculty->degree_earned) == "Master Degree" ? 'selected' : '' }}>Master's Degree</option>
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
                        <p class="text-sm text-white/80 mt-1">Assign subjects to this faculty member (optional)</p>
                    </div>

                    <div class="p-8">
                        <div id="subjects-container" class="space-y-4">
                            <!-- Existing subject rows will be loaded here -->
                            @foreach($faculty->subjects ?? [] as $index => $subject)
                                <div id="subject-row-existing-{{ $index }}" class="glass-item p-6 rounded-xl space-y-4">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-white font-semibold">
                                            <i class="fas fa-book mr-2"></i>Subject {{ $index + 1 }}
                                        </h4>
                                        <button type="button" onclick="removeSubjectRow('subject-row-existing-{{ $index }}')"
                                                class="text-red-400 hover:text-red-300 transition">
                                            <i class="fas fa-times-circle text-xl"></i>
                                        </button>
                                    </div>

                                    <!-- Subject Selection -->
                                    <div>
                                        <label class="block text-white font-medium mb-2">
                                            <i class="fas fa-list mr-2"></i>Select Subject
                                        </label>
                                        <select name="subjects[{{ $index }}][subject_id]"
                                                onchange="updateSubjectDetails(this, 'subject-row-existing-{{ $index }}', {{ $index }})"
                                                required
                                                class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-green-500">
                                            <option value="">Choose a subject</option>
                                            @foreach($subjects ?? [] as $availableSubject)
                                                <option value="{{ $availableSubject->id }}"
                                                        data-code="{{ $availableSubject->course_code ?? '' }}"
                                                        data-year="{{ $availableSubject->year_level ?? '' }}"
                                                        data-lecture="{{ $availableSubject->lec ?? 0 }}"
                                                        data-laboratory="{{ $availableSubject->lab ?? 0 }}"
                                                        data-semester="{{ $availableSubject->semester ?? '' }}"
                                                        data-prereq="{{ $availableSubject->pre_req ?? '' }}"
                                                        {{ $subject->id == $availableSubject->id ? 'selected' : '' }}>
                                                    {{ $availableSubject->subject_name }} ({{ $availableSubject->course_code }})
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <!-- Auto-filled fields (Read-only) -->
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                        <div>
                                            <label class="block text-white font-medium mb-2">
                                                <i class="fas fa-code mr-2"></i>Subject Code
                                            </label>
                                            <input type="text"
                                                id="subject-row-existing-{{ $index }}-code"
                                                value="{{ $subject->course_code ?? '' }}"
                                                readonly
                                                class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                                        </div>

                                        <div>
                                            <label class="block text-white font-medium mb-2">
                                                <i class="fas fa-layer-group mr-2"></i>Year Level
                                            </label>
                                            <input type="number"
                                                id="subject-row-existing-{{ $index }}-year"
                                                name="subjects[{{ $index }}][year_level]"
                                                value="{{ $subject->year_level ?? '' }}"
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
                                                id="subject-row-existing-{{ $index }}-semester"
                                                name="subjects[{{ $index }}][semester]"
                                                value="{{ $subject->semester == 1 ? '1st Semester' : ($subject->semester == 2 ? '2nd Semester' : $subject->semester) }}"
                                                readonly
                                                class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                                        </div>

                                        <div>
                                            <label class="block text-white font-medium mb-2">
                                                <i class="fas fa-chalkboard-teacher mr-2"></i>Lecture Units
                                            </label>
                                            <input type="number"
                                                id="subject-row-existing-{{ $index }}-lecture"
                                                name="subjects[{{ $index }}][lecture_units]"
                                                value="{{ $subject->lec ?? 0 }}"
                                                step="0.5"
                                                readonly
                                                class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                                        </div>

                                        <div>
                                            <label class="block text-white font-medium mb-2">
                                                <i class="fas fa-flask mr-2"></i>Laboratory Units
                                            </label>
                                            <input type="number"
                                                id="subject-row-existing-{{ $index }}-laboratory"
                                                name="subjects[{{ $index }}][laboratory_units]"
                                                value="{{ $subject->lab ?? 0 }}"
                                                step="0.5"
                                                readonly
                                                class="w-full px-4 py-3 rounded-xl bg-white/5 border border-white/10 text-white/70 cursor-not-allowed">
                                        </div>
                                    </div>

                                    <!-- Hidden program ID field -->
                                    <input type="hidden" id="subject-row-existing-{{ $index }}-program" name="subjects[{{ $index }}][program_id]" value="{{ $subject->program_id ?? '' }}">
                                </div>
                            @endforeach
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
                            <!-- Existing unavailability rows will be loaded here -->
                            @foreach($faculty->unavailabilities ?? [] as $index => $unavailability)
                                <div id="unavail-row-existing-{{ $index }}" class="glass-item p-6 rounded-xl space-y-4" style="background: rgba(220, 38, 38, 0.08); border: 1px solid rgba(220, 38, 38, 0.2);">
                                    <div class="flex justify-between items-center mb-4">
                                        <h4 class="text-white font-semibold">
                                            <i class="fas fa-calendar-times mr-2"></i>Unavailability {{ $index + 1 }}
                                        </h4>
                                        <button type="button" onclick="removeUnavailabilityRow('unavail-row-existing-{{ $index }}')"
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
                                            <select name="unavailabilities[{{ $index }}][day]"
                                                    required
                                                    class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                                                <option value="">Select Day</option>
                                                @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                                    <option value="{{ $day }}" {{ $unavailability->day == $day ? 'selected' : '' }}>{{ $day }}</option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Time From -->
                                        <div>
                                            <label class="block text-white font-medium mb-2">
                                                <i class="fas fa-clock mr-2"></i>From
                                            </label>
                                            <select name="unavailabilities[{{ $index }}][time_from]"
                                                    id="unavail-row-existing-{{ $index }}-time-from"
                                                    onchange="updateTimeToOptions('unavail-row-existing-{{ $index }}', {{ $index }})"
                                                    required
                                                    class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                                                <option value="">Select Time</option>
                                                @php
                                                    $timeSlots = ['07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00', '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00'];
                                                    // Convert database time format to HH:MM format
                                                    $rawTimeFrom = $unavailability->time_from ?? '';
                                                    $savedTimeFrom = '';
                                                    if ($rawTimeFrom) {
                                                        // Handle different time formats
                                                        if (strlen($rawTimeFrom) > 5) {
                                                            $savedTimeFrom = substr($rawTimeFrom, 0, 5); // "07:30:00" -> "07:30"
                                                        } else {
                                                            $savedTimeFrom = $rawTimeFrom; // Already "07:30"
                                                        }
                                                    }
                                                @endphp
                                                @if(config('app.debug'))
                                                    <!-- Debug: Raw={{ $rawTimeFrom }}, Processed={{ $savedTimeFrom }} -->
                                                @endif
                                                @foreach($timeSlots as $time)
                                                    <option value="{{ $time }}" {{ $savedTimeFrom == $time ? 'selected' : '' }}>
                                                        {{ date('g:i A', strtotime($time)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>

                                        <!-- Time To -->
                                        <div>
                                            <label class="block text-white font-medium mb-2">
                                                <i class="fas fa-clock mr-2"></i>To
                                            </label>
                                            <select name="unavailabilities[{{ $index }}][time_to]"
                                                    id="unavail-row-existing-{{ $index }}-time-to"
                                                    required
                                                    class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white focus:outline-none focus:ring-2 focus:ring-red-500">
                                                <option value="">Select Time</option>
                                                @php
                                                    // Convert database time format to HH:MM format
                                                    $rawTimeTo = $unavailability->time_to ?? '';
                                                    $savedTimeTo = '';
                                                    if ($rawTimeTo) {
                                                        // Handle different time formats
                                                        if (strlen($rawTimeTo) > 5) {
                                                            $savedTimeTo = substr($rawTimeTo, 0, 5); // "19:00:00" -> "19:00"
                                                        } else {
                                                            $savedTimeTo = $rawTimeTo; // Already "19:00"
                                                        }
                                                    }
                                                @endphp
                                                @if(config('app.debug'))
                                                    <!-- Debug: Raw={{ $rawTimeTo }}, Processed={{ $savedTimeTo }} -->
                                                @endif
                                                @foreach($timeSlots as $time)
                                                    <option value="{{ $time }}" {{ $savedTimeTo == $time ? 'selected' : '' }}>
                                                        {{ date('g:i A', strtotime($time)) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                        </div>
                                    </div>

                                    <!-- Reason (Optional) -->
                                    <div>
                                        <label class="block text-white font-medium mb-2">
                                            <i class="fas fa-comment mr-2"></i>Reason (Optional)
                                        </label>
                                        <textarea name="unavailabilities[{{ $index }}][reason]"
                                                  rows="2"
                                                  placeholder="e.g., Teaching at another institution, Personal appointment"
                                                  class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-red-500">{{ $unavailability->reason ?? '' }}</textarea>
                                    </div>
                                </div>
                            @endforeach
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
                        <p class="text-sm text-white/80 mt-1">Update login credentials (leave password fields empty to keep current password)</p>
                    </div>

                    <div class="p-8 space-y-6">
                        <!-- Email -->
                        <div>
                            <label class="block text-white font-semibold mb-2">
                                <i class="fas fa-envelope mr-2"></i>Email Address <span class="text-red-400">*</span>
                            </label>
                            <input type="email" name="email" value="{{ old('email', $user->email) }}" required
                                   class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <!-- Password -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-lock mr-2"></i>New Password <span class="text-white/60">(Optional)</span>
                                </label>
                                <input type="password" name="password"
                                       placeholder="Leave blank to keep current password"
                                       class="w-full px-4 py-3 rounded-xl bg-white/10 border border-white/20 text-white placeholder-white/50 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent transition">
                            </div>

                            <!-- Confirm Password -->
                            <div>
                                <label class="block text-white font-semibold mb-2">
                                    <i class="fas fa-lock mr-2"></i>Confirm New Password
                                </label>
                                <input type="password" name="password_confirmation"
                                       placeholder="Confirm new password"
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

        .glass-item {
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
        }

        .glass-card select {
            background-color: rgba(255, 255, 255, 0.85);
            color: #000000;
            border: 2px solid #10b981;
        }

        select, input, textarea {
            color-scheme: dark;
        }

        select {
            background-color: rgba(255, 255, 255, 0.85);
            color: #000000;
            border: 2px solid #10b981;
            border-radius: 0.75rem;
            padding: 0.75rem 1rem;
            outline: none;
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            transition: border-color 0.2s, box-shadow 0.2s;
        }

        select:focus {
            border-color: #10b981;
            box-shadow: 0 0 0 2px rgba(16, 185, 129, 0.3);
            background-color: rgba(255, 255, 255, 0.85);
            color: #000000;
        }

        select option {
            background-color: #ffffff;
            color: #000000;
            border: none;
            box-shadow: none;
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
        let subjectRowCounter = {{ count($faculty->subjects ?? []) }};
        let unavailabilityRowCounter = {{ count($faculty->unavailabilities ?? []) }};

        // Debug: Log available subjects to verify data structure
        console.log('Available subjects:', availableSubjects);
        if (availableSubjects.length > 0) {
            console.log('Sample subject data:', availableSubjects[0]);
        }

        // Time slots from 7:30 AM to 7:00 PM in 30-minute intervals
        const timeSlots = [
            '07:30', '08:00', '08:30', '09:00', '09:30', '10:00', '10:30', '11:00', 
            '11:30', '12:00', '12:30', '13:00', '13:30', '14:00', '14:30', '15:00',
            '15:30', '16:00', '16:30', '17:00', '17:30', '18:00', '18:30', '19:00'
        ];

        const daysOfWeek = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

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

                <!-- Hidden program ID field -->
                <input type="hidden" id="${rowId}-program" name="subjects[${rowIndex}][program_id]">
            `;

            container.appendChild(row);
        }

        function updateSubjectDetails(select, rowId, rowIndex) {
            const selectedOption = select.options[select.selectedIndex];

            if (!selectedOption.value) {
                // Clear all fields if no subject selected
                document.getElementById(`${rowId}-code`).value = '';
                document.getElementById(`${rowId}-year`).value = '';
                document.getElementById(`${rowId}-semester`).value = '';
                document.getElementById(`${rowId}-lecture`).value = '';
                document.getElementById(`${rowId}-laboratory`).value = '';
                document.getElementById(`${rowId}-program`).value = '';
                return;
            }

            // Get the full subject object for detailed debugging
            const selectedSubject = availableSubjects.find(s => s.id == selectedOption.value);
            console.log('Selected subject full object:', selectedSubject);

            // Auto-fill from subject data
            const code = selectedOption.getAttribute('data-code');
            const year = selectedOption.getAttribute('data-year');
            let semester = selectedOption.getAttribute('data-semester');
            const lecture = selectedOption.getAttribute('data-lecture');
            const laboratory = selectedOption.getAttribute('data-laboratory');
            const program = selectedOption.getAttribute('data-program');

            // Debug logging with more detail
            console.log('Extracted data attributes:', {
                code, 
                year, 
                semester, 
                lecture: `"${lecture}" (type: ${typeof lecture})`,
                laboratory: `"${laboratory}" (type: ${typeof laboratory})`,
                program
            });

            // Format semester display (convert numeric to text if needed)
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

            // Verify all fields were set
            console.log('Field values after update:', {
                code: document.getElementById(`${rowId}-code`).value,
                year: document.getElementById(`${rowId}-year`).value,
                semester: document.getElementById(`${rowId}-semester`).value,
                lecture: document.getElementById(`${rowId}-lecture`).value,
                laboratory: document.getElementById(`${rowId}-laboratory`).value
            });
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

            // Clear and rebuild time_to options
            timeToSelect.innerHTML = '<option value="">Select Time</option>';
            
            // Only show times after the selected "from" time
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
        window.addSubjectRow = addSubjectRow;
        window.updateSubjectDetails = updateSubjectDetails;
        window.removeSubjectRow = removeSubjectRow;
        window.addUnavailabilityRow = addUnavailabilityRow;
        window.removeUnavailabilityRow = removeUnavailabilityRow;
        window.updateTimeToOptions = updateTimeToOptions;
    </script>

</x-app-layout>