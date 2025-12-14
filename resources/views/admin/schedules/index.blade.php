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
                        <button onclick="generateSchedule()"
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
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl overflow-hidden border border-white/20">
                    <div class="overflow-x-auto">
                        <table class="min-w-full table-auto border-collapse">
                            <thead>
                                <tr class="bg-white/20 backdrop-blur-sm border-b-2 border-white/30 text-center text-xs uppercase font-semibold text-white">
                                    <th class="px-4 py-3 border-r border-white/20">ID</th>
                                    <th class="px-4 py-3 border-r border-white/20">Faculty</th>
                                    <th class="px-4 py-3 border-r border-white/20">Subject</th>
                                    <th class="px-4 py-3 border-r border-white/20">Units</th>
                                    <th class="px-4 py-3 border-r border-white/20">Class Type</th>
                                    <th class="px-4 py-3 border-r border-white/20">Classroom</th>
                                    <th class="px-4 py-3 border-r border-white/20">Day</th>
                                    <th class="px-4 py-3 border-r border-white/20">Time</th>
                                    <th class="px-4 py-3">Year Level</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($schedules as $schedule)
                                    <tr class="border-b border-white/10 hover:bg-white/20 text-center text-sm text-white transition-all">
                                        <td class="px-4 py-3 border-r border-white/10">#{{ $schedule->id }}</td>
                                        <td class="px-4 py-3 border-r border-white/10">{{ $schedule->faculty->name ?? 'N/A' }}</td>
                                        <td class="px-4 py-3 border-r border-white/10">
                                            <div class="font-bold">{{ $schedule->subject->course_code ?? 'N/A' }}</div>
                                            <div class="text-white/70">{{ $schedule->subject->subject_name ?? 'N/A' }}</div>
                                        </td>
                                        <td class="px-4 py-3 border-r border-white/10 font-semibold text-blue-300">
                                            {{ $schedule->subject->units ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 border-r border-white/10">
                                            <span class="inline-block px-3 py-1 rounded-lg text-xs font-bold backdrop-blur-sm border
                                                {{ $schedule->class_type === 'Laboratory' ? 'bg-orange-400/30 text-orange-100 border-orange-300/50' : 'bg-blue-400/30 text-blue-100 border-blue-300/50' }}">
                                                {{ $schedule->class_type === 'Laboratory' ? 'Lab' : 'Lec' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-3 border-r border-white/10 font-semibold text-purple-300">
                                            {{ $schedule->classroom->room_name ?? 'N/A' }}
                                        </td>
                                        <td class="px-4 py-3 border-r border-white/10">
                                            {{ strtoupper(substr($schedule->day ?? '',0,3)) }}
                                        </td>
                                        <td class="px-4 py-3 border-r border-white/10">
                                            {{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}
                                        </td>
                                        <td class="px-4 py-3">{{ $schedule->year_level ?? 'N/A' }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
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
                    <button onclick="generateSchedule()"
                        class="bg-purple-500/30 backdrop-blur-md hover:bg-purple-500/40 text-white px-10 py-4 rounded-xl font-semibold text-lg shadow-lg border border-white/30 transition-all">
                        <i class="fas fa-magic mr-2"></i>Generate Schedule Now
                    </button>
                </div>
            @endif
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
                    Schedule Preview - Timetable View
                </h3>
                <button onclick="closePreviewModal()" class="text-white/70 hover:text-white transition p-2 hover:bg-white/10 rounded-lg backdrop-blur-sm">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>
            
            <!-- Loading Indicator -->
            <div id="loadingIndicator" class="hidden text-center py-12">
                <i class="fas fa-spinner fa-spin text-white text-6xl mb-4"></i>
                <p class="text-white text-xl">Generating schedule...</p>
            </div>

            <!-- Conflicts Alert -->
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
            height: 80px;
            min-height: 80px;
            border: 1px solid rgba(255, 255, 255, 0.15);
            background: rgba(255, 255, 255, 0.03);
            position: relative;
        }
        
        .schedule-block {
            position: absolute;
            left: 0;
            right: 0;
            top: 0;
            border-radius: 8px;
            padding: 8px 10px;
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 2px solid rgba(255, 255, 255, 0.3);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.2);
            overflow: hidden;
            z-index: 10;
        }
        
        .timetable-cell {
            position: relative;
            vertical-align: top;
            padding: 0 !important;
        }

        /* Original vibrant colors with glass effect */
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

        .schedule-text {
            color: white;
            text-shadow: 0 1px 3px rgba(0, 0, 0, 0.3);
        }
    </style>

    <script>
        let generatedSchedules = null;
        let generatedExaminations = null;

        const subjectColors = [
            'pink', 'blue', 'green', 'yellow', 'purple', 'red',
            'indigo', 'teal', 'orange', 'cyan', 'lime', 'fuchsia'
        ];

        const days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];

        async function generateSchedule() {
            const modal = document.getElementById('previewModal');
            const loading = document.getElementById('loadingIndicator');
            const content = document.getElementById('previewContent');
            const conflictsAlert = document.getElementById('conflictsAlert');
            
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
                    }
                });
                
                const data = await res.json();
                loading.classList.add('hidden');

                if(data.success){
                    generatedSchedules = data.schedules;
                    generatedExaminations = data.examinations;
                    
                    if (data.conflicts && data.conflicts.length > 0) {
                        displayConflicts(data.conflicts);
                    }
                    
                    displayTimetable(data.schedules);
                } else {
                    alert(data.message || 'Failed to generate preview');
                    closePreviewModal();
                }
            } catch(e) {
                console.error(e);
                loading.classList.add('hidden');
                alert('Error generating schedule');
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

        function getSubjectColor(subjectId, subjectMap, usedColorsInRow) {
            if (!subjectMap.has(subjectId)) {
                // Find a color not used in this row
                let availableColors = subjectColors.filter(color => !usedColorsInRow.has(color));
                
                // If all colors are used in this row, fall back to any color
                if (availableColors.length === 0) {
                    availableColors = subjectColors;
                }
                
                // Pick a random color from available colors
                const selectedColor = availableColors[Math.floor(Math.random() * availableColors.length)];
                subjectMap.set(subjectId, selectedColor);
            }
            return subjectMap.get(subjectId);
        }

        function timeToMinutes(timeStr) {
            const parts = timeStr.split(':');
            const hours = parseInt(parts[0]);
            const minutes = parseInt(parts[1]);
            return hours * 60 + minutes;
        }

        function displayTimetable(schedules) {
            const subjectMap = new Map();
            const container = document.getElementById('previewContent');
            
            // Group schedules by day and time for rendering
            const schedulesByDay = {};
            days.forEach(day => {
                schedulesByDay[day] = schedules.filter(s => s.day === day);
            });

            let html = `
                <div class="bg-white/5 backdrop-blur-sm rounded-xl p-4 border border-white/20">
                    <table class="w-full border-collapse">
                        <thead>
                            <tr>
                                <th class="bg-white/20 backdrop-blur-sm text-white font-bold py-3 px-4 border border-white/20" style="width: 100px;">Time</th>
                                ${days.map(day => `<th class="bg-white/20 backdrop-blur-sm text-white font-bold py-3 px-4 border border-white/20">${day}</th>`).join('')}
                            </tr>
                        </thead>
                        <tbody>`;

            // Time slots from 8 AM to 7 PM
            const timeSlots = ['7:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];
            
            timeSlots.forEach((time) => {
                // Track colors used in this row to avoid repetition
                const usedColorsInRow = new Set();
                
                html += `<tr>`;
                html += `<td class="bg-white/10 text-white text-center font-semibold border border-white/20 time-slot align-top">
                    <div class="sticky top-0">${formatTimeSimple(time)}</div>
                </td>`;
                
                days.forEach(day => {
                    html += `<td class="bg-white/5 border border-white/20 time-slot timetable-cell">`;
                    
                    // Find schedules that START in this time slot for this day
                    const daySchedules = schedulesByDay[day] || [];
                    daySchedules.forEach(schedule => {
                        if (schedule.start_time === time) {
                            const color = getSubjectColor(schedule.subject_id, subjectMap, usedColorsInRow);
                            usedColorsInRow.add(color); // Mark this color as used in this row
                            
                            const startMinutes = timeToMinutes(schedule.start_time);
                            const endMinutes = timeToMinutes(schedule.end_time);
                            const durationHours = (endMinutes - startMinutes) / 60;
                            const heightPx = (durationHours * 80) - 4; // 80px per hour, minus 4px for border spacing
                            
                            html += `
                                <div class="schedule-block schedule-block-${color}" style="height: ${heightPx}px;">
                                    <div class="schedule-text font-bold text-sm mb-1">${schedule.course_code}</div>
                                    <div class="schedule-text text-xs mb-1 leading-tight">${schedule.course_subject}</div>
                                    <div class="schedule-text text-xs opacity-90">${schedule.classroom_name}</div>
                                    <div class="schedule-text text-xs opacity-90">${schedule.faculty_name}</div>
                                    <div class="schedule-text text-xs opacity-90 mt-1">${schedule.class_type}</div>
                                    <div class="schedule-text text-xs opacity-80">Yr ${schedule.year_level}</div>
                                </div>`;
                        }
                    });
                    
                    html += `</td>`;
                });
                
                html += `</tr>`;
            });

            html += `</tbody></table></div>`;
            
            container.innerHTML = html;
        }

        function closePreviewModal() {
            document.getElementById('previewModal').classList.add('hidden');
        }

        function formatTimeSimple(time) {
            const [h, m] = time.split(':');
            const hour = parseInt(h);
            const ampm = hour >= 12 ? 'PM' : 'AM';
            const displayHour = hour % 12 || 12;
            return `${displayHour}:${m} ${ampm}`;
        }

        async function confirmSchedule(){
            if(!generatedSchedules) {
                alert('No schedules to save');
                return;
            }

            const confirmBtn = document.getElementById('confirmBtn');
            confirmBtn.disabled = true;
            confirmBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

            try{
                const res = await fetch('{{ route("admin.schedules.confirm") }}', {
                    method:'POST',
                    headers:{
                        'Content-Type':'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                    },
                    body: JSON.stringify({
                        schedules: generatedSchedules,
                        examinations: generatedExaminations
                    })
                });
                
                const data = await res.json();
                
                if(data.success) {
                    window.location.reload();
                } else {
                    alert(data.message || 'Failed to save schedules');
                    confirmBtn.disabled = false;
                    confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirm & Save';
                }
            }catch(e){
                console.error(e);
                alert('Error saving schedule');
                confirmBtn.disabled = false;
                confirmBtn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirm & Save';
            }
        }
    </script>
</x-app-layout>