<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Success/Error Messages -->
            @if(session('success'))
                <div class="bg-green-500/20 backdrop-blur-md border border-green-500/50 text-white px-6 py-4 rounded-xl mb-6">
                    <i class="fas fa-check-circle mr-2"></i>{{ session('success') }}
                </div>
            @endif

            @if(session('error'))
                <div class="bg-red-500/20 backdrop-blur-md border border-red-500/50 text-white px-6 py-4 rounded-xl mb-6">
                    <i class="fas fa-exclamation-circle mr-2"></i>{{ session('error') }}
                </div>
            @endif

            <!-- Page Header -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl p-8 mb-8 border border-white/20">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-bold text-white flex items-center gap-3 drop-shadow-lg">
                            <i class="fas fa-calendar-alt"></i> All Schedules
                        </h1>
                        <p class="mt-3 text-white/90 text-lg drop-shadow">View and manage all generated schedules</p>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <button onclick="openScheduleTypeModal()"
                            class="bg-purple-500/30 backdrop-blur-md hover:bg-purple-500/40 text-white px-6 py-3 rounded-xl shadow-lg font-semibold border border-white/30 transition-all">
                            <i class="fas fa-magic mr-2"></i>Generate Schedule
                        </button>
                        <a href="{{ route('admin.schedules.download-pdf') }}"
                            class="bg-red-500/30 backdrop-blur-md hover:bg-red-500/40 text-white px-6 py-3 rounded-xl shadow-lg font-semibold border border-white/30 transition-all">
                            <i class="fas fa-file-pdf mr-2"></i>Download PDF
                        </a>
                        <a href="{{ route('admin.schedules.download-excel') }}"
                            class="bg-green-500/30 backdrop-blur-md hover:bg-green-500/40 text-white px-6 py-3 rounded-xl shadow-lg font-semibold border border-white/30 transition-all">
                            <i class="fas fa-file-excel mr-2"></i>Download Excel
                        </a>
                        <form action="{{ route('admin.schedules.clear') }}" method="POST" onsubmit="return confirm('Are you sure you want to clear all schedules?');" class="inline">
                            @csrf
                            <button type="submit"
                                class="bg-orange-500/30 backdrop-blur-md hover:bg-orange-500/40 text-white px-6 py-3 rounded-xl shadow-lg font-semibold border border-white/30 transition-all">
                                <i class="fas fa-trash mr-2"></i>Clear All
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            @if(isset($schedules) && $schedules->count() > 0)
                <!-- Timetable View -->
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl border border-white/20 p-6">
                    <div class="timetable-wrapper overflow-x-auto">
                        <div class="timetable-container bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/20" style="min-width: max-content;">
                            <table class="border-collapse" style="width: 100%;">
                                <thead>
                                    <tr>
                                        <th class="bg-white/20 backdrop-blur-sm text-white font-bold py-4 px-4 border border-white/20 sticky left-0 z-20" style="width: 120px; min-width: 120px;">Time</th>
                                        @foreach(['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'] as $day)
                                            <th class="bg-white/20 backdrop-blur-sm text-white font-bold py-4 px-4 border border-white/20" style="width: 220px; min-width: 220px;">{{ $day }}</th>
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];
                                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                                        
                                        $schedulesByDayAndTime = [];
                                        foreach($days as $day) {
                                            $schedulesByDayAndTime[$day] = [];
                                            foreach($timeSlots as $time) {
                                                $schedulesByDayAndTime[$day][$time] = [];
                                            }
                                        }
                                        
                                        $occupiedCells = [];
                                        
                                        foreach($schedules as $schedule) {
                                            $day = $schedule->day_name;
                                            $startTime = substr($schedule->start_time, 0, 5);
                                            $endTime = substr($schedule->end_time, 0, 5);
                                            
                                            $startHour = (int)substr($startTime, 0, 2);
                                            $startMin = (int)substr($startTime, 3, 2);
                                            $endHour = (int)substr($endTime, 0, 2);
                                            $endMin = (int)substr($endTime, 3, 2);
                                            
                                            // Calculate duration in hours (including partial hours)
                                            $duration = $endHour - $startHour;
                                            if ($endMin > $startMin) {
                                                $duration += 1;
                                            }
                                            
                                            if(isset($schedulesByDayAndTime[$day][$startTime])) {
                                                $schedule->calculated_rowspan = max(1, $duration);
                                                $schedulesByDayAndTime[$day][$startTime][] = $schedule;
                                                
                                                for($i = 1; $i < $duration; $i++) {
                                                    $nextTimeIndex = array_search($startTime, $timeSlots) + $i;
                                                    if($nextTimeIndex < count($timeSlots)) {
                                                        $nextTime = $timeSlots[$nextTimeIndex];
                                                        if(!isset($occupiedCells[$day])) {
                                                            $occupiedCells[$day] = [];
                                                        }
                                                        $occupiedCells[$day][$nextTime] = true;
                                                    }
                                                }
                                            }
                                        }
                                        
                                        $colors = ['pink', 'blue', 'green', 'yellow', 'purple', 'red', 'indigo', 'teal', 'orange', 'cyan', 'lime', 'fuchsia'];
                                        $subjectColors = [];
                                        $colorIndex = 0;
                                    @endphp
                                    
                                    @foreach($timeSlots as $time)
                                        <tr>
                                            <td class="bg-white/10 text-white text-center font-semibold border border-white/20 time-slot align-top sticky left-0 z-10">
                                                <div class="py-3">
                                                    @php
                                                        $hour = (int)substr($time, 0, 2);
                                                        $ampm = $hour >= 12 ? 'PM' : 'AM';
                                                        $displayHour = $hour % 12 ?: 12;
                                                    @endphp
                                                    {{ $displayHour }}:{{ substr($time, 3, 2) }} {{ $ampm }}
                                                </div>
                                            </td>
                                            
                                            @foreach($days as $day)
                                                @php
                                                    if(isset($occupiedCells[$day][$time])) {
                                                        continue;
                                                    }
                                                    
                                                    $daySchedules = $schedulesByDayAndTime[$day][$time];
                                                @endphp
                                                
                                                <td class="bg-white/5 border border-white/20 time-slot timetable-cell align-top" 
                                                    @if(count($daySchedules) > 0 && isset($daySchedules[0]->calculated_rowspan))
                                                        rowspan="{{ $daySchedules[0]->calculated_rowspan }}"
                                                    @endif>
                                                    @if(count($daySchedules) > 0)
                                                        <div style="display: flex; gap: 4px; height: 100%; padding: 4px;">
                                                            @foreach($daySchedules as $schedule)
                                                                @php
                                                                    if(!isset($subjectColors[$schedule->subject_id])) {
                                                                        $subjectColors[$schedule->subject_id] = $colors[$colorIndex % count($colors)];
                                                                        $colorIndex++;
                                                                    }
                                                                    $color = $subjectColors[$schedule->subject_id];
                                                                    $rowspan = $schedule->calculated_rowspan ?? 1;
                                                                    $blockHeight = ($rowspan * 150) - 8;
                                                                @endphp
                                                                
                                                                <div class="schedule-block schedule-block-{{ $color }}" 
                                                                     style="flex: 1; min-width: 0; height: {{ $blockHeight }}px;">
                                                                    <div class="schedule-text font-bold text-xs truncate">{{ $schedule->subject->course_code ?? 'N/A' }}</div>
                                                                    <div class="schedule-text text-xs truncate">{{ $schedule->subject->subject_name ?? 'N/A' }}</div>
                                                                    <div class="schedule-text text-xs truncate">{{ $schedule->classroom->room_name ?? 'N/A' }}</div>
                                                                    <div class="schedule-text text-xs truncate">{{ $schedule->faculty->name ?? 'N/A' }}</div>
                                                                    <div class="schedule-text text-xs">{{ $schedule->class_type === 'Laboratory' ? 'Lab' : 'Lec' }}</div>
                                                                    <div class="schedule-text text-xs">Yr {{ $schedule->year_level }}</div>
                                                                    <div class="schedule-text text-xs opacity-80">
                                                                        {{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}
                                                                    </div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @endif
                                                </td>
                                            @endforeach
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Pagination -->
                <div class="mt-6">
                    {{ $schedules->links() }}
                </div>
            @else
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl p-16 text-center border border-white/20">
                    <div class="bg-white/20 backdrop-blur-sm rounded-full w-32 h-32 mx-auto flex items-center justify-center mb-6 border border-white/30">
                        <i class="fas fa-calendar-times text-white/60 text-6xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3 drop-shadow-lg">No Schedules Found</h3>
                    <p class="text-white/80 mb-8 text-lg drop-shadow">Generate schedules to get started with your academic planning.</p>
                    <button onclick="openScheduleTypeModal()"
                        class="bg-purple-500/30 backdrop-blur-md hover:bg-purple-500/40 text-white px-10 py-4 rounded-xl font-semibold text-lg shadow-lg border border-white/30 transition-all">
                        <i class="fas fa-magic mr-2"></i>Generate Schedule Now
                    </button>
                </div>
            @endif
        </div>
    </div>

    <!-- Schedule Type Selection Modal -->
    <div id="scheduleTypeModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-20 mx-auto p-8 border border-white/30 w-full max-w-2xl shadow-2xl rounded-2xl bg-white/10 backdrop-blur-xl">
            <div class="text-center mb-8">
                <div class="bg-gradient-to-r from-purple-500/30 to-indigo-500/30 backdrop-blur-sm rounded-full w-20 h-20 mx-auto flex items-center justify-center mb-4 border border-white/30">
                    <i class="fas fa-calendar-plus text-white text-4xl"></i>
                </div>
                <h3 class="text-3xl font-bold text-white mb-2 drop-shadow-lg">Choose Schedule Type</h3>
                <p class="text-white/80 text-lg">Select what type of schedule you want to generate</p>
            </div>

            <div class="grid md:grid-cols-2 gap-6 mb-8">
                <button onclick="selectScheduleType('regular')" 
                    class="schedule-type-card bg-blue-500/20 hover:bg-blue-500/30 backdrop-blur-md border-2 border-blue-400/50 hover:border-blue-400 rounded-2xl p-8 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="bg-blue-500/30 rounded-full w-16 h-16 mx-auto flex items-center justify-center mb-4">
                            <i class="fas fa-chalkboard-teacher text-white text-3xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2">Regular Classes</h4>
                        <p class="text-white/80 text-sm">Generate schedule for regular class sessions</p>
                        <div class="mt-4 flex items-center justify-center gap-2 text-blue-200 text-xs">
                            <i class="fas fa-check-circle"></i>
                            <span>Lectures & Labs</span>
                        </div>
                    </div>
                </button>

                <button onclick="selectScheduleType('examination')" 
                    class="schedule-type-card bg-orange-500/20 hover:bg-orange-500/30 backdrop-blur-md border-2 border-orange-400/50 hover:border-orange-400 rounded-2xl p-8 transition-all transform hover:scale-105">
                    <div class="text-center">
                        <div class="bg-orange-500/30 rounded-full w-16 h-16 mx-auto flex items-center justify-center mb-4">
                            <i class="fas fa-file-alt text-white text-3xl"></i>
                        </div>
                        <h4 class="text-2xl font-bold text-white mb-2">Examinations</h4>
                        <p class="text-white/80 text-sm">Generate schedule for exam periods</p>
                        <div class="mt-4 flex items-center justify-center gap-2 text-orange-200 text-xs">
                            <i class="fas fa-check-circle"></i>
                            <span>Midterm & Finals</span>
                        </div>
                    </div>
                </button>
            </div>

            <div class="flex justify-center">
                <button onclick="closeScheduleTypeModal()" 
                    class="bg-gray-500/30 backdrop-blur-md hover:bg-gray-500/40 text-white px-8 py-3 rounded-xl font-semibold border border-white/30 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
            </div>
        </div>
    </div>

    <!-- Timetable Preview Modal -->
    <div id="previewModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-8 border border-white/30 w-11/12 max-w-7xl shadow-2xl rounded-2xl bg-white/10 backdrop-blur-xl mb-10">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-white/20">
                <h3 class="text-3xl font-bold text-white flex items-center gap-3 drop-shadow-lg">
                    <div class="bg-gradient-to-r from-blue-500/30 to-indigo-500/30 backdrop-blur-sm rounded-xl p-3 border border-white/30">
                        <i class="fas fa-calendar-check text-white"></i>
                    </div>
                    <span id="previewTitle">Schedule Preview - Timetable View</span>
                </h3>
                <button onclick="closePreviewModal()" class="text-white/70 hover:text-white transition p-2 hover:bg-white/10 rounded-lg backdrop-blur-sm">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <div id="loadingIndicator" class="hidden text-center py-12">
                <i class="fas fa-spinner fa-spin text-white text-6xl mb-4"></i>
                <p class="text-white text-xl">Generating schedule...</p>
            </div>

            <div id="conflictsAlert" class="hidden bg-yellow-500/20 backdrop-blur-md border border-yellow-500/50 text-white px-6 py-4 rounded-xl mb-6">
                <h4 class="font-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Scheduling Conflicts</h4>
                <div id="conflictsList"></div>
            </div>

            <div id="previewContent" class="overflow-x-auto"></div>
            
            <div class="mt-6 flex justify-end gap-4 pt-4 border-t border-white/20">
                <button onclick="closePreviewModal()" class="bg-gray-500/30 backdrop-blur-md hover:bg-gray-500/40 text-white px-8 py-3 rounded-xl font-semibold border border-white/30 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button onclick="confirmSchedule()" id="confirmBtn" class="bg-green-500/30 backdrop-blur-md hover:bg-green-500/40 text-white px-8 py-3 rounded-xl font-semibold border border-white/30 transition-all">
                    <i class="fas fa-check mr-2"></i>Confirm & Save
                </button>
            </div>
        </div>
    </div>

    <style>
        .time-slot {
            height: 150px;
            min-height: 150px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.03);
            position: relative;
            padding: 0 !important;
            vertical-align: top;
        }
        
        .schedule-block {
            border-radius: 8px;
            padding: 10px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            display: flex;
            flex-direction: column;
            gap: 3px;
            font-size: 0.75rem;
        }
        
        .timetable-cell {
            position: relative;
            vertical-align: top;
            padding: 0 !important;
            width: 220px;
            min-width: 220px;
        }
        
        .timetable-wrapper {
            display: block;
            width: 100%;
            overflow-x: auto;
        }

        .timetable-container {
            min-width: max-content;
        }
        
        .schedule-text {
            color: white;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
            word-wrap: break-word;
            overflow-wrap: break-word;
            hyphens: auto;
            line-height: 1.3;
        }

        .schedule-type-card {
            cursor: pointer;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }

        .schedule-type-card:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.3);
        }

        .schedule-block-pink {
            background: linear-gradient(135deg, rgba(236, 72, 153, 0.75) 0%, rgba(219, 39, 119, 0.75) 100%);
        }
        
        .schedule-block-blue {
            background: linear-gradient(135deg, rgba(59, 130, 246, 0.75) 0%, rgba(37, 99, 235, 0.75) 100%);
        }
        
        .schedule-block-green {
            background: linear-gradient(135deg, rgba(34, 197, 94, 0.75) 0%, rgba(22, 163, 74, 0.75) 100%);
        }
        
        .schedule-block-yellow {
            background: linear-gradient(135deg, rgba(234, 179, 8, 0.75) 0%, rgba(202, 138, 4, 0.75) 100%);
        }
        
        .schedule-block-purple {
            background: linear-gradient(135deg, rgba(168, 85, 247, 0.75) 0%, rgba(147, 51, 234, 0.75) 100%);
        }
        
        .schedule-block-red {
            background: linear-gradient(135deg, rgba(239, 68, 68, 0.75) 0%, rgba(220, 38, 38, 0.75) 100%);
        }
        
        .schedule-block-indigo {
            background: linear-gradient(135deg, rgba(99, 102, 241, 0.75) 0%, rgba(79, 70, 229, 0.75) 100%);
        }
        
        .schedule-block-teal {
            background: linear-gradient(135deg, rgba(20, 184, 166, 0.75) 0%, rgba(13, 148, 136, 0.75) 100%);
        }
        
        .schedule-block-orange {
            background: linear-gradient(135deg, rgba(249, 115, 22, 0.75) 0%, rgba(234, 88, 12, 0.75) 100%);
        }
        
        .schedule-block-cyan {
            background: linear-gradient(135deg, rgba(6, 182, 212, 0.75) 0%, rgba(8, 145, 178, 0.75) 100%);
        }
        
        .schedule-block-lime {
            background: linear-gradient(135deg, rgba(132, 204, 22, 0.75) 0%, rgba(101, 163, 13, 0.75) 100%);
        }
        
        .schedule-block-fuchsia {
            background: linear-gradient(135deg, rgba(217, 70, 239, 0.75) 0%, rgba(192, 38, 211, 0.75) 100%);
        }

        .sticky {
            position: sticky;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(12px);
        }
    </style>

    <script>
        let generatedSchedules = null;
        let generatedExaminations = null;
        let selectedScheduleType = null;
        let responseData = null;

        const subjectColors = [
            'pink', 'blue', 'green', 'yellow', 'purple', 'red',
            'indigo', 'teal', 'orange', 'cyan', 'lime', 'fuchsia'
        ];

        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        function openScheduleTypeModal() {
            document.getElementById('scheduleTypeModal').classList.remove('hidden');
        }

        function closeScheduleTypeModal() {
            document.getElementById('scheduleTypeModal').classList.add('hidden');
        }

        function selectScheduleType(type) {
            selectedScheduleType = type;
            closeScheduleTypeModal();
            generateSchedule();
        }

        async function generateSchedule() {
            if (!selectedScheduleType) {
                openScheduleTypeModal();
                return;
            }

            const modal = document.getElementById('previewModal');
            const loading = document.getElementById('loadingIndicator');
            const content = document.getElementById('previewContent');
            const conflictsAlert = document.getElementById('conflictsAlert');
            const previewTitle = document.getElementById('previewTitle');
            
            previewTitle.innerHTML = selectedScheduleType === 'examination' 
                ? '<i class="fas fa-file-alt mr-2"></i>Examination Schedule Preview' 
                : '<i class="fas fa-chalkboard-teacher mr-2"></i>Regular Class Schedule Preview';
            
            modal.classList.remove('hidden');
            loading.classList.remove('hidden');
            content.innerHTML = '';
            conflictsAlert.classList.add('hidden');

            try {
                const res = await fetch('{{ route("admin.schedules.generate-preview") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        schedule_type: selectedScheduleType
                    })
                });
                
                const data = await res.json();
                loading.classList.add('hidden');

                console.log('Response data:', data);

                if(data.success){
                    responseData = data;
                    generatedSchedules = data.schedules;
                    generatedExaminations = data.examinations;
                    
                    if (data.conflicts && data.conflicts.length > 0) {
                        displayConflicts(data.conflicts);
                    }
                    
                    displayTimetable(data.schedules || data.examinations);
                } else {
                    alert(data.message || 'Failed to generate preview');
                    closePreviewModal();
                }
            } catch(e) {
                console.error('Error generating schedule:', e);
                loading.classList.add('hidden');
                alert('Error generating schedule: ' + e.message);
                closePreviewModal();
            }
        }

        function displayConflicts(conflicts) {
            const alert = document.getElementById('conflictsAlert');
            const list = document.getElementById('conflictsList');
            
            let html = '<ul class="list-disc list-inside space-y-1">';
            conflicts.forEach(conflict => {
                html += `<li><strong>${conflict.faculty}</strong> - ${conflict.subject} (${conflict.units} units): ${conflict.reason}</li>`;
            });
            html += '</ul>';
            
            list.innerHTML = html;
            alert.classList.remove('hidden');
        }

        function getSubjectColor(subjectId, subjectMap) {
            if (!subjectMap.has(subjectId)) {
                const colorIndex = subjectMap.size % subjectColors.length;
                subjectMap.set(subjectId, subjectColors[colorIndex]);
            }
            return subjectMap.get(subjectId);
        }

        function calculateDuration(startTime, endTime) {
            const [startHour, startMin] = startTime.split(':').map(Number);
            const [endHour, endMin] = endTime.split(':').map(Number);
            
            let duration = endHour - startHour;
            
            // If end minutes are greater than start minutes, we need another slot
            if (endMin > startMin) {
                duration += 1;
            }
            
            return Math.max(1, duration);
        }

        function displayTimetable(schedules) {
            const subjectMap = new Map();
            const container = document.getElementById('previewContent');
            
            if (!schedules || schedules.length === 0) {
                container.innerHTML = '<div class="text-center text-white py-8">No schedules to display</div>';
                return;
            }
            
            const timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];
            
            const schedulesByDayAndTime = {};
            const occupiedCells = {};
            
            days.forEach(day => {
                schedulesByDayAndTime[day] = {};
                occupiedCells[day] = {};
                timeSlots.forEach(time => {
                    schedulesByDayAndTime[day][time] = [];
                });
            });

            schedules.forEach(schedule => {
                const day = schedule.day_name || schedule.day;
                const startTime = schedule.start_time;
                const endTime = schedule.end_time;
                
                const duration = calculateDuration(startTime, endTime);
                
                schedule.calculated_rowspan = duration;
                
                if (schedulesByDayAndTime[day] && schedulesByDayAndTime[day][startTime]) {
                    schedulesByDayAndTime[day][startTime].push(schedule);
                    
                    for(let i = 1; i < duration; i++) {
                        const nextTimeIndex = timeSlots.indexOf(startTime) + i;
                        if(nextTimeIndex < timeSlots.length) {
                            const nextTime = timeSlots[nextTimeIndex];
                            occupiedCells[day][nextTime] = true;
                        }
                    }
                }
            });

            let html = `
                <div class="timetable-wrapper">
                    <div class="timetable-container bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                        <table class="border-collapse" style="width: 100%;">
                            <thead>
                                <tr>
                                    <th class="bg-white/20 backdrop-blur-sm text-white font-bold py-4 px-4 border border-white/20 sticky left-0 z-20" style="width: 120px; min-width: 120px;">Time</th>
                                    ${days.map(day => `<th class="bg-white/20 backdrop-blur-sm text-white font-bold py-4 px-4 border border-white/20" style="width: 220px; min-width: 220px;">${day}</th>`).join('')}
                                </tr>
                            </thead>
                            <tbody>`;
            
            timeSlots.forEach((time) => {
                html += `<tr>`;
                html += `<td class="bg-white/10 text-white text-center font-semibold border border-white/20 time-slot align-top sticky left-0 z-10">
                    <div class="py-3">${formatTimeSimple(time)}</div>
                </td>`;
                
                days.forEach(day => {
                    if(occupiedCells[day][time]) {
                        return;
                    }
                    
                    const schedulesAtThisTime = schedulesByDayAndTime[day][time] || [];
                    const rowspan = schedulesAtThisTime.length > 0 ? schedulesAtThisTime[0].calculated_rowspan : 1;
                    
                    html += `<td class="bg-white/5 border border-white/20 time-slot timetable-cell align-top" ${rowspan > 1 ? `rowspan="${rowspan}"` : ''}>`;
                    
                    if (schedulesAtThisTime.length > 0) {
                        html += `<div style="display: flex; gap: 4px; height: 100%; padding: 4px;">`;
                        
                        schedulesAtThisTime.forEach(schedule => {
                            const color = getSubjectColor(schedule.subject_id, subjectMap);
                            const blockHeight = (rowspan * 150) - 8;
                            
                            html += `
                                <div class="schedule-block schedule-block-${color}" style="flex: 1; min-width: 0; height: ${blockHeight}px;">
                                    <div class="schedule-text font-bold text-xs truncate">${schedule.course_code || 'N/A'}</div>
                                    <div class="schedule-text text-xs truncate">${schedule.course_subject || schedule.subject_name || 'N/A'}</div>
                                    <div class="schedule-text text-xs truncate">${schedule.classroom_name || 'N/A'}</div>
                                    <div class="schedule-text text-xs truncate">${schedule.faculty_name || 'N/A'}</div>
                                    <div class="schedule-text text-xs">${schedule.class_type || 'Lec'}</div>
                                    <div class="schedule-text text-xs">Yr ${schedule.year_level || 'N/A'}</div>
                                    <div class="schedule-text text-xs opacity-80">${formatTimeSimple(schedule.start_time)} - ${formatTimeSimple(schedule.end_time)}</div>
                                </div>`;
                        });
                        
                        html += `</div>`;
                    }
                    
                    html += `</td>`;
                });
                
                html += `</tr>`;
            });

            html += `</tbody></table></div></div>`;
            
            container.innerHTML = html;
        }

        function closePreviewModal() {
            document.getElementById('previewModal').classList.add('hidden');
            selectedScheduleType = null;
            responseData = null;
            generatedSchedules = null;
            generatedExaminations = null;
        }

        function formatTimeSimple(time) {
            if (!time) return 'N/A';
            const [h, m] = time.split(':');
            const hour = parseInt(h);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${m} ${ampm}`;
        }

        async function confirmSchedule(){
            if(!responseData || (!generatedSchedules && !generatedExaminations)) {
                alert('No schedules to save');
                return;
            }

            const confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

            try{
                const payload = {
                    schedule_type: selectedScheduleType,
                    schedules: generatedSchedules || [],
                    examinations: generatedExaminations || []
                };

                console.log('Sending payload:', {
                    schedule_type: payload.schedule_type,
                    schedule_count: payload.schedules.length,
                    exam_count: payload.examinations.length
                });
                
                console.log('First schedule sample:', payload.schedules[0]);
                console.log('First exam sample:', payload.examinations[0]);

                const res = await fetch('{{ route("admin.schedules.confirm") }}', {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify(payload)
                });
                
                console.log('Response status:', res.status);
                console.log('Response headers:', res.headers.get('content-type'));
                
                const contentType = res.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    const text = await res.text();
                    console.error('Server returned non-JSON response:');
                    console.error(text);
                    throw new Error('Server error: Expected JSON response but got HTML. Check browser console for full error.');
                }
                
                const data = await res.json();
                
                console.log('Confirmation response:', data);
                
                if(data.success) {
                    const message = data.message || 'Schedule saved successfully!';
                    if (data.errors && data.errors.length > 0) {
                        console.warn('Some items had errors:', data.errors);
                        alert(message + '\n\nNote: Some items had errors (see console for details)');
                    } else {
                        alert(message);
                    }
                    window.location.reload();
                } else {
                    const errorMsg = data.message || 'Failed to save schedules';
                    console.error('Save failed:', data);
                    alert('Error: ' + errorMsg);
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirm & Save';
                }
            }catch(e){
                console.error('Error saving schedule:', e);
                alert('Error saving schedule: ' + e.message + '\n\nCheck the browser console (F12) for more details.');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirm & Save';
            }
        }
    </script>
</x-app-layout>