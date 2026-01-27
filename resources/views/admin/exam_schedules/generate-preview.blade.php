<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-white/20 backdrop-blur-md rounded-lg p-6 shadow-md border border-white/30">
                <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
                    <!-- Page Header -->
                    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4">
                        <div>
                            <h1 class="text-3xl font-bold text-gray-800">
                                <i class="fas fa-file-alt mr-3"></i>Examination Schedules
                            </h1>
                            <p class="mt-2 text-gray-600">View and manage all examination schedules</p>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            <button onclick="generateExamSchedule()"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-6 py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-magic mr-2"></i>Generate Exam Schedule
                            </button>
                            <a href="{{ route('admin.examinations.download-pdf') }}" 
                               class="bg-red-600 hover:bg-red-700 text-white px-6 py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105 inline-block">
                                <i class="fas fa-file-pdf mr-2"></i>Download PDF
                            </a>
                            @php
                                $examinationsCollection = isset($examinations)
                                    ? ($examinations instanceof \Illuminate\Pagination\LengthAwarePaginator
                                        ? $examinations->getCollection()
                                        : $examinations)
                                    : collect();
                                $totalExaminations = isset($examinations) && method_exists($examinations, 'total')
                                    ? $examinations->total()
                                    : $examinationsCollection->count();
                            @endphp

                            @if($examinationsCollection->count() > 0)
                                <button onclick="window.print()" 
                                    class="bg-gray-600 hover:bg-gray-700 text-white px-6 py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                                    <i class="fas fa-print mr-2"></i>Print
                                </button>
                                <form action="{{ route('admin.examinations.clear') }}" method="POST" 
                                      onsubmit="return confirm('Are you sure you want to clear all examination schedules?');" class="inline">
                                    @csrf
                                    <button type="submit"
                                        class="bg-orange-600 hover:bg-orange-700 text-white px-6 py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                                        <i class="fas fa-trash mr-2"></i>Clear All
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    @if(session('success'))
                        <div class="bg-white/20 border border-white/30 text-white px-4 py-3 rounded-lg mb-6 flex items-center backdrop-blur-sm">
                            <i class="fas fa-check-circle mr-3 text-xl"></i>
                            <span>{{ session('success') }}</span>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center">
                            <i class="fas fa-exclamation-circle mr-3 text-xl"></i>
                            <span>{{ session('error') }}</span>
                        </div>
                    @endif

                    <!-- Statistics Cards -->
                    @if($examinationsCollection->count() > 0)
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                            <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm font-medium">Total Exams</p>
                                        <p class="text-3xl font-bold text-gray-800">{{ $totalExaminations }}</p>
                                    </div>
                                    <i class="fas fa-file-alt text-blue-500 text-3xl"></i>
                                </div>
                            </div>

                            <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm font-medium">Unique Subjects</p>
                                        <p class="text-3xl font-bold text-gray-800">{{ $examinationsCollection->pluck('subject_id')->unique()->count() }}</p>
                                    </div>
                                    <i class="fas fa-book text-green-500 text-3xl"></i>
                                </div>
                            </div>

                            <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm font-medium">Exam Rooms</p>
                                        <p class="text-3xl font-bold text-gray-800">{{ $examinationsCollection->pluck('classroom_id')->unique()->count() }}</p>
                                    </div>
                                    <i class="fas fa-door-open text-purple-500 text-3xl"></i>
                                </div>
                            </div>

                            <div class="bg-white/20 rounded-lg shadow-md p-6 border border-white/30 backdrop-blur-md">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <p class="text-gray-600 text-sm font-medium">Upcoming</p>
                                        @php
                                            $upcomingCount = $examinationsCollection->filter(function($exam) {
                                                try {
                                                    $date = $exam->exam_date instanceof \Illuminate\Support\Carbon
                                                        ? $exam->exam_date
                                                        : \Illuminate\Support\Carbon::parse($exam->exam_date);
                                                    return $date->isFuture();
                                                } catch (\Exception $e) {
                                                    return false;
                                                }
                                            })->count();
                                        @endphp
                                        <p class="text-3xl font-bold text-gray-800">{{ $upcomingCount }}</p>
                                    </div>
                                    <i class="fas fa-calendar-check text-orange-500 text-3xl"></i>
                                </div>
                            </div>
                        </div>
                    @endif

                    <!-- Filter Section -->
                    <div class="bg-white/20 rounded-lg shadow-md p-6 mb-6 border border-white/30 backdrop-blur-md">
                        <form method="GET" action="{{ route('admin.examinations.index') }}" class="grid grid-cols-1 md:grid-cols-5 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Search Subject</label>
                                <input type="text" name="search" value="{{ request('search') }}" 
                                       placeholder="Subject name..." 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Exam Type</label>
                                <select name="exam_type" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Types</option>
                                    <option value="Midterm" {{ request('exam_type') == 'Midterm' ? 'selected' : '' }}>Midterm</option>
                                    <option value="Final" {{ request('exam_type') == 'Final' ? 'selected' : '' }}>Final</option>
                                    <option value="Quiz" {{ request('exam_type') == 'Quiz' ? 'selected' : '' }}>Quiz</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Classroom</label>
                                <select name="classroom" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                    <option value="">All Classrooms</option>
                                    @if(isset($classrooms))
                                        @foreach($classrooms as $classroom)
                                            <option value="{{ $classroom->id }}" {{ request('classroom') == $classroom->id ? 'selected' : '' }}>
                                                {{ $classroom->room_name }}
                                            </option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Date From</label>
                                <input type="date" name="date_from" value="{{ request('date_from') }}" 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                            </div>

                            <div class="flex items-end gap-2">
                                <button type="submit" 
                                        class="flex-1 bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg transition">
                                    <i class="fas fa-filter mr-2"></i>Filter
                                </button>
                                <a href="{{ route('admin.examinations.index') }}" 
                                   class="bg-gray-300 hover:bg-gray-400 text-gray-800 px-4 py-2 rounded-lg transition">
                                    <i class="fas fa-redo"></i>
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Examinations Table -->
                    @if($examinationsCollection->count() > 0)
                        <div class="bg-white/20 rounded-lg shadow-md overflow-hidden border border-white/30 backdrop-blur-md">
                            <div class="overflow-x-auto">
                                <table class="min-w-full divide-y divide-gray-200">
                                    <thead class="bg-white/50 rounded-lg shadow-md overflow-hidden border border-white/30 backdrop-blur-md">
                                        <tr>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-hashtag mr-2"></i>ID
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-book mr-2"></i>Subject
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-user-tie mr-2"></i>Faculty
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-tag mr-2"></i>Type
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-calendar mr-2"></i>Date
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-clock mr-2"></i>Time
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-door-open mr-2"></i>Room
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-users mr-2"></i>Section
                                            </th>
                                            <th class="px-6 py-4 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                <i class="fas fa-cog mr-2"></i>Actions
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white/20 rounded-lg shadow-md overflow-hidden border border-white/30 backdrop-blur-md">
                                        @foreach($examinationsCollection as $exam)
                                        <tr class="hover:bg-gray-50 transition duration-150">
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                #{{ $exam->id }}
                                            </td>
                                            <td class="px-6 py-4">
                                                <div class="text-sm font-medium text-gray-900">{{ optional($exam->subject)->subject_name }}</div>
                                                <div class="text-xs text-gray-500">{{ optional($exam->subject)->course_code }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="flex items-center">
                                                    <div class="bg-blue-100 rounded-full p-2 mr-3">
                                                        <i class="fas fa-user-tie text-blue-600 text-sm"></i>
                                                    </div>
                                                    <div class="text-sm font-medium text-gray-900">{{ optional($exam->faculty)->name }}</div>
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold
                                                    @if($exam->exam_type == 'Midterm') bg-yellow-100 text-yellow-800
                                                    @elseif($exam->exam_type == 'Final') bg-red-100 text-red-800
                                                    @else bg-blue-100 text-blue-800 @endif">
                                                    {{ $exam->exam_type }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    @php
                                                        try {
                                                            $date = $exam->exam_date instanceof \Illuminate\Support\Carbon
                                                                ? $exam->exam_date
                                                                : \Illuminate\Support\Carbon::parse($exam->exam_date);
                                                            $dateFormatted = $date->format('M d, Y');
                                                            $dayName = $date->format('l');
                                                        } catch (\Exception $e) {
                                                            $dateFormatted = $exam->exam_date;
                                                            $dayName = $exam->day_name ?? '';
                                                        }
                                                    @endphp
                                                    {{ $dateFormatted }}
                                                </div>
                                                <div class="text-xs text-gray-500">{{ $dayName }}</div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <div class="text-sm font-medium text-gray-900">
                                                    {{ date('g:i A', strtotime($exam->start_time)) }}
                                                </div>
                                                <div class="text-xs text-gray-500">
                                                    to {{ date('g:i A', strtotime($exam->end_time)) }}
                                                </div>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                                    <i class="fas fa-door-open mr-1"></i>
                                                    {{ optional($exam->classroom)->room_name }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                    {{ $exam->year_section ?? 'N/A' }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                <button onclick="viewExamDetails({{ $exam->id }})" 
                                                        class="text-blue-600 hover:text-blue-800 transition" 
                                                        title="View Details">
                                                    <i class="fas fa-eye"></i>
                                                </button>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pagination -->
                        @if(isset($examinations) && method_exists($examinations, 'links'))
                            <div class="mt-6">
                                {{ $examinations->links() }}
                            </div>
                        @endif
                    @else
                        <!-- No Examinations Message -->
                        <div class="bg-white/20 rounded-lg shadow-md p-12 text-center border border-white/30 backdrop-blur-md">
                            <i class="fas fa-file-alt text-gray-300 text-6xl mb-4"></i>
                            <h3 class="text-xl font-semibold text-gray-700 mb-2">No Examination Schedules Found</h3>
                            <p class="text-gray-500 mb-6">There are no examination schedules available. Generate exam schedules to get started.</p>
                            <button onclick="generateExamSchedule()"
                                class="bg-purple-600 hover:bg-purple-700 text-white px-8 py-3 rounded-lg transition duration-200 shadow-md hover:shadow-lg transform hover:scale-105">
                                <i class="fas fa-magic mr-2"></i>Generate Exam Schedule Now
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Exam Preview Modal -->
    <div id="examPreviewModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-8 border border-white/30 w-11/12 max-w-6xl shadow-2xl rounded-2xl bg-white/10 backdrop-blur-xl mb-10">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-white/20">
                <h3 class="text-3xl font-bold text-white flex items-center gap-3 drop-shadow-lg">
                    <div class="bg-gradient-to-r from-red-500/30 to-orange-500/30 backdrop-blur-sm rounded-xl p-3 border border-white/30">
                        <i class="fas fa-file-alt text-white"></i>
                    </div>
                    <span>Examination Schedule Preview</span>
                </h3>
                <button onclick="closeExamPreviewModal()" class="text-white/70 hover:text-white transition p-2 hover:bg-white/10 rounded-lg backdrop-blur-sm">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div id="examLoadingIndicator" class="hidden text-center py-12">
                <i class="fas fa-spinner fa-spin text-white text-6xl mb-4"></i>
                <p class="text-white text-xl">Generating examination schedule...</p>
            </div>

            <div id="examConflictsAlert" class="hidden bg-yellow-500/20 backdrop-blur-md border border-yellow-500/50 text-white px-6 py-4 rounded-xl mb-6">
                <h4 class="font-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Scheduling Conflicts</h4>
                <div id="examConflictsList"></div>
            </div>

            <div id="examPreviewContent" class="overflow-x-auto"></div>
            
            <div class="mt-6 flex justify-end gap-4 pt-4 border-t border-white/20">
                <button onclick="closeExamPreviewModal()" class="bg-gray-500/30 backdrop-blur-md hover:bg-gray-500/40 text-white px-8 py-3 rounded-xl font-semibold border border-white/30 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button onclick="confirmExamSchedule()" id="confirmExamBtn" class="bg-green-500/30 backdrop-blur-md hover:bg-green-500/40 text-white px-8 py-3 rounded-xl font-semibold border border-white/30 transition-all">
                    <i class="fas fa-check mr-2"></i>Confirm & Save
                </button>
            </div>
        </div>
    </div>

    <!-- Exam Details Modal -->
    <div id="examModal" class="hidden fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-lg bg-white">
            <div class="mt-3">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-bold text-gray-900">Examination Details</h3>
                    <button onclick="closeExamModal()" class="text-gray-400 hover:text-gray-600">
                        <i class="fas fa-times text-xl"></i>
                    </button>
                </div>
                <div id="examModalContent" class="text-sm text-gray-700">
                    <!-- Content will be loaded dynamically -->
                </div>
            </div>
        </div>
    </div>

    <script>
        let generatedExaminations = null;
        let examResponseData = null;

        async function generateExamSchedule() {
            const modal = document.getElementById('examPreviewModal');
            const loading = document.getElementById('examLoadingIndicator');
            const content = document.getElementById('examPreviewContent');
            const conflictsAlert = document.getElementById('examConflictsAlert');
            
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            content.innerHTML = '';
            conflictsAlert.classList.add('hidden');

            try {
                const res = await fetch('{{ route("admin.examinations.generate-preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        schedule_type: 'examination'
                    })
                });
                
                const data = await res.json();
                loading.classList.add('hidden');

                console.log('Exam Response data:', data);

                if(data.success){
                    examResponseData = data;
                    generatedExaminations = data.examinations;
                    
                    if (data.conflicts && data.conflicts.length > 0) {
                        displayExamConflicts(data.conflicts);
                    }
                    
                    displayExaminationTable(data.examinations);
                } else {
                    alert(data.message || 'Failed to generate examination schedule');
                    closeExamPreviewModal();
                }
            } catch(e) {
                console.error('Error generating examination schedule:', e);
                loading.classList.add('hidden');
                alert('Error generating examination schedule: ' + e.message);
                closeExamPreviewModal();
            }
        }

        function displayExamConflicts(conflicts) {
            const alert = document.getElementById('examConflictsAlert');
            const list = document.getElementById('examConflictsList');
            
            let html = '<ul class="list-disc list-inside space-y-1">';
            conflicts.forEach(conflict => {
                html += `<li><strong>${conflict.faculty}</strong> - ${conflict.subject}: ${conflict.reason}</li>`;
            });
            html += '</ul>';
            
            list.innerHTML = html;
            alert.classList.remove('hidden');
        }

        function displayExaminationTable(examinations) {
            const container = document.getElementById('examPreviewContent');
            
            if (!examinations || examinations.length === 0) {
                container.innerHTML = '<div class="text-center text-white py-8">No examinations to display</div>';
                return;
            }

            // Group by date
            const examsByDate = {};
            examinations.forEach(exam => {
                const date = exam.exam_date;
                if (!examsByDate[date]) {
                    examsByDate[date] = [];
                }
                examsByDate[date].push(exam);
            });

            let html = '<div class="space-y-6">';
            
            Object.keys(examsByDate).sort().forEach(date => {
                const exams = examsByDate[date];
                html += `
                    <div class="bg-white/10 backdrop-blur-md rounded-xl p-6 border border-white/20">
                        <h4 class="text-xl font-bold text-white mb-4">
                            <i class="fas fa-calendar-day mr-2"></i>${formatDate(date)}
                        </h4>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-white/20">
                                <thead>
                                    <tr class="bg-white/10">
                                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Time</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Subject</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Faculty</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Room</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Type</th>
                                        <th class="px-4 py-3 text-left text-xs font-medium text-white uppercase">Section</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/10">`;
                
                exams.forEach(exam => {
                    html += `
                        <tr class="hover:bg-white/5">
                            <td class="px-4 py-3 text-white text-sm">
                                ${formatTimeSimple(exam.start_time)} - ${formatTimeSimple(exam.end_time)}
                            </td>
                            <td class="px-4 py-3 text-white text-sm">
                                <div class="font-semibold">${exam.course_code || 'N/A'}</div>
                                <div class="text-xs text-white/70">${exam.course_subject || 'N/A'}</div>
                            </td>
                            <td class="px-4 py-3 text-white text-sm">${exam.faculty_name || 'N/A'}</td>
                            <td class="px-4 py-3 text-white text-sm">${exam.classroom_name || 'N/A'}</td>
                            <td class="px-4 py-3 text-white text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold bg-red-500/30 text-white">
                                    ${exam.exam_type || 'Final'}
                                </span>
                            </td>
                            <td class="px-4 py-3 text-white text-sm">${exam.year_section || 'N/A'}</td>
                        </tr>`;
                });
                
                html += `
                                </tbody>
                            </table>
                        </div>
                    </div>`;
            });
            
            html += '</div>';
            container.innerHTML = html;
        }

        function formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleDateString('en-US', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        }

        function formatTimeSimple(time) {
            if (!time) return 'N/A';
            const [h, m] = time.split(':');
            const hour = parseInt(h);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${m} ${ampm}`;
        }

        async function confirmExamSchedule() {
            if(!examResponseData || !generatedExaminations) {
                alert('No examination schedules to save');
                return;
            }

            const confirmBtn = document.getElementById('confirmExamBtn');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

            try {
                const payload = {
                    schedule_type: 'examination',
                    examinations: generatedExaminations
                };

                console.log('Sending exam payload:', {
                    schedule_type: payload.schedule_type,
                    exam_count: payload.examinations.length
                });

                const res = await fetch('{{ route("admin.examinations.confirm") }}', {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });

                console.log('Exam response status:', res.status);

                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await res.text();
                    console.error('Server returned non-JSON response:');
                    console.error(text);
                    throw new Error('Server error: Expected JSON response but got HTML. Check browser console for full error.');
                }

                const data = await res.json();
                console.log('Exam confirmation response:', data);

                if(data.success) {
                    const message = data.message || 'Examination schedule saved successfully!';
                    if (data.errors && data.errors.length > 0) {
                        console.warn('Some items had errors:', data.errors);
                        alert(message + '\n\nNote: Some items had errors (see console for details)');
                    } else {
                        alert(message);
                    }
                    window.location.reload();
                } else {
                    const errorMsg = data.message || 'Failed to save examination schedules';
                    console.error('Save failed:', data);
                    alert('Error: ' + errorMsg);
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirm & Save';
                }
            } catch(e) {
                console.error('Error saving examination schedule:', e);
                alert('Error saving examination schedule: ' + e.message + '\n\nCheck the browser console (F12) for more details.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirm & Save';
            }
        }

        function closeExamPreviewModal() {
            document.getElementById('examPreviewModal').classList.add('hidden');
            examResponseData = null;
            generatedExaminations = null;
        }

        function viewExamDetails(examId) {
            document.getElementById('examModal').classList.remove('hidden');
            document.getElementById('examModalContent').innerHTML = `
                <p class="mb-2"><strong>Examination ID:</strong> ${examId}</p>
                <p class="text-gray-600">Loading details...</p>
            `;
        }

        function closeExamModal() {
            document.getElementById('examModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('examModal');
            if (event.target == modal) {
                modal.classList.add('hidden');
            }
            const previewModal = document.getElementById('examPreviewModal');
            if (event.target == previewModal) {
                closeExamPreviewModal();
            }
        }
    </script>
</x-app-layout>