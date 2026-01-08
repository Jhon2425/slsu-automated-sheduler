<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm text-white/80 mb-6">
                <a href="{{ route('admin.dashboard') }}" class="flex items-center hover:text-violet-400 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
                <i class="fas fa-chevron-right mx-3 text-xs text-white/50"></i>
                <span class="font-semibold text-white">View Saved Schedules</span>
            </nav>

            <!-- Page Header -->
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-8 border border-white/20">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-bold text-white flex items-center gap-3 drop-shadow-lg">
                            <i class="fas fa-archive"></i> View Saved Schedules
                        </h1>
                        <p class="mt-3 text-white/90 text-lg drop-shadow">View and manage previously generated schedules</p>
                    </div>
                </div>
            </div>

            <!-- Tabs -->
            <div class="glass-card rounded-2xl shadow-xl border border-white/20 mb-6">
                <div class="flex border-b border-white/20">
                    <button onclick="switchTab('class')" id="classTab" class="flex-1 px-6 py-4 text-white font-semibold transition-all duration-300 tab-button active-tab">
                        <i class="fas fa-calendar-alt mr-2"></i>
                        Class Schedules
                        <span class="ml-2 bg-white/20 px-3 py-1 rounded-full text-sm">{{ $classScheduleCount }}</span>
                    </button>
                    <button onclick="switchTab('exam')" id="examTab" class="flex-1 px-6 py-4 text-white/70 font-semibold transition-all duration-300 tab-button">
                        <i class="fas fa-file-alt mr-2"></i>
                        Examination Schedules
                        <span class="ml-2 bg-white/10 px-3 py-1 rounded-full text-sm">{{ $examScheduleCount }}</span>
                    </button>
                </div>
            </div>

            <!-- Filters -->
            <div class="glass-card rounded-2xl shadow-xl p-6 mb-6 border border-white/20">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-white/80 text-sm font-semibold mb-2">
                            <i class="fas fa-calendar mr-1"></i> From Date
                        </label>
                        <input type="date" id="fromDate" class="w-full bg-white/10 border border-white/30 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-white/80 text-sm font-semibold mb-2">
                            <i class="fas fa-calendar mr-1"></i> To Date
                        </label>
                        <input type="date" id="toDate" class="w-full bg-white/10 border border-white/30 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                    </div>
                    <div>
                        <label class="block text-white/80 text-sm font-semibold mb-2">
                            <i class="fas fa-sort mr-1"></i> Sort By
                        </label>
                        <select id="sortBy" class="w-full bg-white/10 border border-white/30 rounded-lg px-4 py-2 text-white focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <option value="newest">Newest First</option>
                            <option value="oldest">Oldest First</option>
                        </select>
                    </div>
                    <div class="flex items-end gap-2">
                        <button onclick="applyFilters()" class="flex-1 bg-blue-500/30 backdrop-blur-md hover:bg-blue-500/40 text-white px-6 py-2 rounded-lg font-semibold border border-white/30 transition-all">
                            <i class="fas fa-filter mr-2"></i>Filter
                        </button>
                        <button onclick="resetFilters()" class="bg-gray-500/30 backdrop-blur-md hover:bg-gray-500/40 text-white px-4 py-2 rounded-lg font-semibold border border-white/30 transition-all">
                            <i class="fas fa-redo"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Class Schedules Tab Content -->
            <div id="classContent" class="tab-content">
                @if($classSchedules->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($classSchedules as $schedule)
                            <div class="glass-card rounded-2xl shadow-xl border border-white/20 p-6 hover-lift schedule-card" data-date="{{ $schedule->created_at->format('Y-m-d') }}">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <div class="inline-block bg-green-500/20 backdrop-blur-sm px-3 py-1 rounded-lg border border-green-500/30 mb-3">
                                            <span class="text-green-300 text-xs font-semibold">CLASS SCHEDULE</span>
                                        </div>
                                        <h3 class="text-xl font-bold text-white mb-2">
                                            {{ $schedule->subject->course_code ?? 'N/A' }} - {{ $schedule->subject->subject_name ?? 'Schedule' }}
                                        </h3>
                                        <p class="text-white/70 text-sm">
                                            <i class="fas fa-calendar-day mr-1"></i>
                                            Generated: {{ $schedule->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-chalkboard-teacher w-5 text-blue-400"></i>
                                        <span>{{ $schedule->faculty->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-door-open w-5 text-yellow-400"></i>
                                        <span>{{ $schedule->classroom->room_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-clock w-5 text-purple-400"></i>
                                        <span>{{ $schedule->day_name ?? 'N/A' }}, {{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</span>
                                    </div>
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-book w-5 text-green-400"></i>
                                        <span>{{ $schedule->class_type ?? 'Lecture' }} | Year {{ $schedule->year_level ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <div class="flex gap-2 pt-4 border-t border-white/10">
                                    <button onclick="viewScheduleDetails({{ $schedule->id }}, 'class')" class="flex-1 bg-blue-500/30 hover:bg-blue-500/40 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all border border-white/30">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </button>
                                    <button onclick="downloadSchedule({{ $schedule->id }}, 'class')" class="flex-1 bg-green-500/30 hover:bg-green-500/40 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all border border-white/30">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination for Class Schedules -->
                    <div class="mt-6">
                        {{ $classSchedules->appends(['exam_page' => request('exam_page')])->links() }}
                    </div>
                @else
                    <div class="glass-card rounded-2xl shadow-xl p-16 text-center border border-white/20">
                        <div class="bg-white/20 backdrop-blur-sm rounded-full w-32 h-32 mx-auto flex items-center justify-center mb-6 border border-white/30">
                            <i class="fas fa-calendar-times text-white/60 text-6xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3 drop-shadow-lg">No Class Schedules Found</h3>
                        <p class="text-white/80 mb-8 text-lg drop-shadow">No generated class schedules in the archive.</p>
                    </div>
                @endif
            </div>

            <!-- Exam Schedules Tab Content -->
            <div id="examContent" class="tab-content hidden">
                @if($examSchedules->count() > 0)
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        @foreach($examSchedules as $exam)
                            <div class="glass-card rounded-2xl shadow-xl border border-white/20 p-6 hover-lift schedule-card" data-date="{{ $exam->created_at->format('Y-m-d') }}">
                                <div class="flex items-start justify-between mb-4">
                                    <div>
                                        <div class="inline-block bg-orange-500/20 backdrop-blur-sm px-3 py-1 rounded-lg border border-orange-500/30 mb-3">
                                            <span class="text-orange-300 text-xs font-semibold">{{ $exam->exam_type ?? 'FINAL' }} EXAM</span>
                                        </div>
                                        <h3 class="text-xl font-bold text-white mb-2">
                                            {{ $exam->subject->course_code ?? 'N/A' }} - {{ $exam->subject->subject_name ?? 'Examination' }}
                                        </h3>
                                        <p class="text-white/70 text-sm">
                                            <i class="fas fa-calendar-day mr-1"></i>
                                            Generated: {{ $exam->created_at->format('M d, Y') }}
                                        </p>
                                    </div>
                                </div>

                                <div class="space-y-2 mb-4">
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-calendar-check w-5 text-orange-400"></i>
                                        <span>Exam Date: {{ $exam->exam_date ? date('M d, Y', strtotime($exam->exam_date)) : 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-chalkboard-teacher w-5 text-blue-400"></i>
                                        <span>{{ $exam->faculty->name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-door-open w-5 text-yellow-400"></i>
                                        <span>{{ $exam->classroom->room_name ?? 'N/A' }}</span>
                                    </div>
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-clock w-5 text-purple-400"></i>
                                        <span>{{ $exam->day_name ?? 'N/A' }}, {{ date('g:i A', strtotime($exam->start_time)) }} - {{ date('g:i A', strtotime($exam->end_time)) }}</span>
                                    </div>
                                    <div class="flex items-center text-white/80 text-sm">
                                        <i class="fas fa-users w-5 text-green-400"></i>
                                        <span>Section: {{ $exam->year_section ?? 'N/A' }}</span>
                                    </div>
                                </div>

                                <div class="flex gap-2 pt-4 border-t border-white/10">
                                    <button onclick="viewScheduleDetails({{ $exam->id }}, 'exam')" class="flex-1 bg-blue-500/30 hover:bg-blue-500/40 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all border border-white/30">
                                        <i class="fas fa-eye mr-1"></i> View
                                    </button>
                                    <button onclick="downloadSchedule({{ $exam->id }}, 'exam')" class="flex-1 bg-green-500/30 hover:bg-green-500/40 text-white px-4 py-2 rounded-lg text-sm font-semibold transition-all border border-white/30">
                                        <i class="fas fa-download mr-1"></i> Download
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Pagination for Exam Schedules -->
                    <div class="mt-6">
                        {{ $examSchedules->appends(['page' => request('page')])->links() }}
                    </div>
                @else
                    <div class="glass-card rounded-2xl shadow-xl p-16 text-center border border-white/20">
                        <div class="bg-white/20 backdrop-blur-sm rounded-full w-32 h-32 mx-auto flex items-center justify-center mb-6 border border-white/30">
                            <i class="fas fa-file-times text-white/60 text-6xl"></i>
                        </div>
                        <h3 class="text-2xl font-bold text-white mb-3 drop-shadow-lg">No Exam Schedules Found</h3>
                        <p class="text-white/80 mb-8 text-lg drop-shadow">No generated examination schedules in the archive.</p>
                    </div>
                @endif
            </div>

        </div>
    </div>

    <style>
        /* Glass Card Effect */
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        /* Tab Styling */
        .tab-button {
            border-bottom: 3px solid transparent;
        }

        .active-tab {
            background: rgba(255, 255, 255, 0.1);
            border-bottom-color: #60a5fa;
        }

        /* Hover Effects */
        .hover-lift {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .hover-lift:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.3);
        }

        /* Hide/Show Tab Content */
        .tab-content {
            animation: fadeIn 0.4s ease-in-out;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: rgba(255, 255, 255, 0.1);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb {
            background: rgba(255, 255, 255, 0.3);
            border-radius: 10px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: rgba(255, 255, 255, 0.5);
        }
    </style>

    <script>
        function switchTab(tab) {
            const classTab = document.getElementById('classTab');
            const examTab = document.getElementById('examTab');
            const classContent = document.getElementById('classContent');
            const examContent = document.getElementById('examContent');

            if (tab === 'class') {
                classTab.classList.add('active-tab');
                classTab.classList.remove('text-white/70');
                examTab.classList.remove('active-tab');
                examTab.classList.add('text-white/70');
                classContent.classList.remove('hidden');
                examContent.classList.add('hidden');
            } else {
                examTab.classList.add('active-tab');
                examTab.classList.remove('text-white/70');
                classTab.classList.remove('active-tab');
                classTab.classList.add('text-white/70');
                examContent.classList.remove('hidden');
                classContent.classList.add('hidden');
            }
        }

        function applyFilters() {
            const fromDate = document.getElementById('fromDate').value;
            const toDate = document.getElementById('toDate').value;
            const sortBy = document.getElementById('sortBy').value;

            const cards = document.querySelectorAll('.schedule-card');
            let cardsArray = Array.from(cards);

            // Filter by date range
            cardsArray.forEach(card => {
                const cardDate = card.getAttribute('data-date');
                let show = true;

                if (fromDate && cardDate < fromDate) show = false;
                if (toDate && cardDate > toDate) show = false;

                card.style.display = show ? 'block' : 'none';
            });

            // Sort
            const visibleCards = cardsArray.filter(card => card.style.display !== 'none');
            const parent = visibleCards[0]?.parentElement;

            if (parent) {
                visibleCards.sort((a, b) => {
                    const dateA = a.getAttribute('data-date');
                    const dateB = b.getAttribute('data-date');
                    return sortBy === 'newest' ? dateB.localeCompare(dateA) : dateA.localeCompare(dateB);
                });

                visibleCards.forEach(card => parent.appendChild(card));
            }
        }

        function resetFilters() {
            document.getElementById('fromDate').value = '';
            document.getElementById('toDate').value = '';
            document.getElementById('sortBy').value = 'newest';

            const cards = document.querySelectorAll('.schedule-card');
            cards.forEach(card => card.style.display = 'block');
        }

        function viewScheduleDetails(id, type) {
            if (type === 'class') {
                window.open(`{{ url('admin/schedules') }}/${id}`, '_blank');
            } else {
                window.open(`{{ url('admin/examinations') }}/${id}`, '_blank');
            }
        }

        function downloadSchedule(id, type) {
            if (type === 'class') {
                window.open(`{{ route('admin.schedules.download-pdf') }}?id=${id}`, '_blank');
            } else {
                window.open(`{{ route('admin.examinations.download-pdf') }}?id=${id}`, '_blank');
            }
        }
    </script>
</x-app-layout>