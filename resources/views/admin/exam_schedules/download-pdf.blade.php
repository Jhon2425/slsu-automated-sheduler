<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Examination Schedule - Print View</title>
    <style>
        @page {
            size: landscape;
            margin: 0.75cm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            font-size: 9pt;
            position: relative;
            padding: 15px;
            background: #f8f9fa;
            color: #1a1a1a;
        }

        /* Watermark - Behind everything */
        .watermark {
            position: fixed;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%) rotate(-45deg);
            opacity: 0.08;
            z-index: -1;
            pointer-events: none;
            width: 700px;
            height: 700px;
        }

        .watermark img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            filter: grayscale(30%) contrast(1.2);
        }

        /* Print Button */
        .print-button {
            position: fixed;
            top: 20px;
            right: 20px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            border: none;
            padding: 14px 32px;
            font-size: 13px;
            font-weight: 600;
            border-radius: 10px;
            cursor: pointer;
            box-shadow: 0 6px 25px rgba(249, 115, 22, 0.35);
            z-index: 1000;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .print-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 35px rgba(249, 115, 22, 0.5);
        }

        .print-button:active {
            transform: translateY(-1px);
        }

        /* Main Container */
        .print-container {
            position: relative;
            z-index: 1;
            max-width: 100%;
            background: white;
            border-radius: 16px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.08);
            padding: 25px;
        }

        /* Header */
        .header {
            text-align: center;
            margin-bottom: 25px;
            padding-bottom: 20px;
            border-bottom: 3px solid #f97316;
            position: relative;
        }

        .header::after {
            content: '';
            position: absolute;
            bottom: -3px;
            left: 50%;
            transform: translateX(-50%);
            width: 120px;
            height: 3px;
            background: linear-gradient(90deg, transparent, #ea580c, transparent);
        }

        .header-logo-section {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 35px;
            margin-bottom: 12px;
        }

        .header-logo {
            width: 75px;
            height: 75px;
            background: linear-gradient(135deg, #f9731615, #ea580c15);
            border-radius: 50%;
            padding: 8px;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .header-logo img {
            width: 100%;
            height: 100%;
            object-fit: contain;
        }

        .header-text h1 {
            font-size: 20pt;
            font-weight: 800;
            color: #1a202c;
            margin-bottom: 6px;
            letter-spacing: 1.5px;
        }

        .header-text h2 {
            font-size: 15pt;
            color: #4a5568;
            margin-bottom: 4px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .header-text p {
            font-size: 10pt;
            color: #718096;
            font-weight: 500;
        }

        .schedule-info {
            margin-top: 18px;
            padding: 16px 28px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            border-radius: 12px;
            box-shadow: 0 6px 20px rgba(249, 115, 22, 0.25);
            position: relative;
            overflow: hidden;
        }

        .schedule-info::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 25%, transparent 25%, transparent 50%, rgba(255,255,255,0.1) 50%, rgba(255,255,255,0.1) 75%, transparent 75%);
            background-size: 20px 20px;
            opacity: 0.3;
        }

        .schedule-info .schedule-type {
            font-size: 16pt;
            font-weight: 800;
            color: white;
            margin-bottom: 4px;
            text-transform: uppercase;
            letter-spacing: 2px;
            position: relative;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .schedule-info .academic-year {
            font-size: 11pt;
            color: rgba(255, 255, 255, 0.95);
            font-weight: 600;
            position: relative;
        }

        /* Timetable */
        .timetable-container {
            overflow-x: auto;
            margin-bottom: 25px;
            border-radius: 12px;
            box-shadow: 0 3px 12px rgba(0,0,0,0.08);
            position: relative;
            z-index: 2;
        }

        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            background: white;
            position: relative;
            z-index: 2;
        }

        th, td {
            border: 1px solid #e2e8f0;
            padding: 3px;
            text-align: center;
            vertical-align: middle;
        }

        th {
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            font-weight: 700;
            font-size: 10pt;
            padding: 12px 6px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            position: relative;
        }

        th::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            height: 2px;
            background: linear-gradient(90deg, #f97316, #ea580c);
        }

        th:first-child {
            border-top-left-radius: 12px;
        }

        th:last-child {
            border-top-right-radius: 12px;
        }

        th.time-header {
            width: 85px;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
        }

        td.time-slot {
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            font-weight: 700;
            color: #7c2d12;
            font-size: 9pt;
            padding: 10px 6px;
            border-right: 2px solid #fed7aa;
        }

        td.schedule-cell {
            background: white;
            padding: 0;
            min-height: 85px;
            height: 85px;
            position: relative;
        }

        .schedule-block {
            border: none;
            border-radius: 0;
            padding: 10px;
            height: 100%;
            min-height: 100%;
            font-size: 8pt;
            line-height: 1.5;
            background: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            gap: 3px;
            position: relative;
            overflow: hidden;
        }

        .schedule-block::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 5px;
            height: 100%;
            background: currentColor;
            opacity: 0.8;
        }

        /* Enhanced color scheme with exam-specific styling */
        .schedule-block-pink { 
            background: linear-gradient(135deg, #fff5f7 0%, #ffe4ea 100%); 
            border-color: #e91e63;
            color: #c2185b;
        }
        .schedule-block-blue { 
            background: linear-gradient(135deg, #f0f9ff 0%, #dbeafe 100%); 
            border-color: #2196f3;
            color: #1565c0;
        }
        .schedule-block-green { 
            background: linear-gradient(135deg, #f0fdf4 0%, #dcfce7 100%); 
            border-color: #4caf50;
            color: #2e7d32;
        }
        .schedule-block-yellow { 
            background: linear-gradient(135deg, #fefce8 0%, #fef3c7 100%); 
            border-color: #fbc02d;
            color: #f57f17;
        }
        .schedule-block-purple { 
            background: linear-gradient(135deg, #faf5ff 0%, #f3e8ff 100%); 
            border-color: #9c27b0;
            color: #7b1fa2;
        }
        .schedule-block-red { 
            background: linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%); 
            border-color: #f44336;
            color: #c62828;
        }
        .schedule-block-indigo { 
            background: linear-gradient(135deg, #eef2ff 0%, #e0e7ff 100%); 
            border-color: #3f51b5;
            color: #283593;
        }
        .schedule-block-teal { 
            background: linear-gradient(135deg, #f0fdfa 0%, #ccfbf1 100%); 
            border-color: #009688;
            color: #00695c;
        }
        .schedule-block-orange { 
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%); 
            border-color: #ff9800;
            color: #e65100;
        }
        .schedule-block-cyan { 
            background: linear-gradient(135deg, #ecfeff 0%, #cffafe 100%); 
            border-color: #00bcd4;
            color: #00838f;
        }
        .schedule-block-lime { 
            background: linear-gradient(135deg, #f7fee7 0%, #ecfccb 100%); 
            border-color: #8bc34a;
            color: #558b2f;
        }
        .schedule-block-fuchsia { 
            background: linear-gradient(135deg, #fdf4ff 0%, #fae8ff 100%); 
            border-color: #e91e63;
            color: #ad1457;
        }

        .schedule-text {
            word-wrap: break-word;
            position: relative;
            z-index: 1;
        }

        .course-code {
            font-weight: 800;
            font-size: 9pt;
            color: inherit;
            border-bottom: 2px solid currentColor;
            padding-bottom: 2px;
            margin-bottom: 3px;
            letter-spacing: 0.3px;
        }

        .subject-name {
            font-size: 8pt;
            font-weight: 700;
            color: inherit;
            margin-bottom: 4px;
            opacity: 0.9;
        }

        .exam-badge {
            font-size: 7pt;
            font-weight: 800;
            background: linear-gradient(135deg, #f97316 0%, #ea580c 100%);
            color: white;
            padding: 3px 8px;
            border-radius: 4px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-block;
            margin-bottom: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .schedule-detail {
            font-size: 7pt;
            color: inherit;
            display: flex;
            align-items: center;
            gap: 3px;
            opacity: 0.85;
        }

        .detail-label {
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 6.5pt;
        }

        .exam-date-highlight {
            font-weight: 800;
            color: #f97316;
            font-size: 7.5pt;
        }

        /* Footer */
        .footer {
            margin-top: 30px;
            page-break-inside: avoid;
        }

        .signatures {
            display: flex;
            justify-content: space-around;
            margin-bottom: 25px;
            gap: 15px;
        }

        .signature-block {
            text-align: center;
            flex: 1;
            max-width: 240px;
        }

        .signature-line {
            border-top: 2.5px solid #2d3748;
            margin-top: 45px;
            padding-top: 8px;
            font-weight: 700;
            font-size: 10pt;
            color: #2d3748;
        }

        .signature-title {
            font-size: 8pt;
            color: #718096;
            margin-top: 3px;
            text-transform: uppercase;
            letter-spacing: 0.8px;
            font-weight: 600;
        }

        .footer-info {
            display: flex;
            justify-content: space-between;
            padding: 14px 20px;
            background: linear-gradient(135deg, #fff7ed 0%, #ffedd5 100%);
            border-radius: 10px;
            font-size: 8pt;
            color: #7c2d12;
            font-weight: 600;
            border: 1px solid #fed7aa;
        }

        @media print {
            body {
                print-color-adjust: exact;
                -webkit-print-color-adjust: exact;
                background: white;
                padding: 0;
            }
            
            .print-button {
                display: none !important;
            }

            .no-print {
                display: none !important;
            }

            .print-container {
                box-shadow: none;
                border-radius: 0;
            }

            .watermark {
                opacity: 0.05;
            }
        }

        /* Responsive adjustments for smaller screens */
        @media screen and (max-width: 1200px) {
            .schedule-block {
                font-size: 7pt;
            }
            
            .course-code {
                font-size: 8pt;
            }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button class="print-button no-print" onclick="window.print()">
        🖨️ Print Examination Schedule
    </button>

    <!-- Watermark with SLSU Logo -->
    <div class="watermark">
        <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Watermark">
    </div>

    <div class="print-container">
        <!-- Header -->
        <div class="header">
            <div class="header-logo-section">
                <div class="header-logo">
                    <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo">
                </div>
                <div class="header-text">
                    <h1>SOUTHERN LUZON STATE UNIVERSITY</h1>
                    <h2>TIAONG CAMPUS</h2>
                    <p>Tiaong, Quezon, Philippines</p>
                </div>
                <div class="header-logo">
                    <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo">
                </div>
            </div>
            
            <div class="schedule-info">
                <div class="schedule-type">
                    📋 EXAMINATION SCHEDULE
                </div>
                <div class="academic-year">
                    @if(isset($semester))
                        {{ $semester }} Semester, S.Y. {{ $schoolYear ?? date('Y') . '-' . (date('Y') + 1) }}
                    @else
                        Academic Year {{ date('Y') . '-' . (date('Y') + 1) }}
                    @endif
                </div>
            </div>
        </div>

        <!-- Timetable -->
        <div class="timetable-container">
            <table>
                <thead>
                    <tr>
                        <th class="time-header">TIME</th>
                        <th>MONDAY</th>
                        <th>TUESDAY</th>
                        <th>WEDNESDAY</th>
                        <th>THURSDAY</th>
                        <th>FRIDAY</th>
                        <th>SATURDAY</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $timeSlots = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00', '19:00'];
                        $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
                        
                        // Group examinations
                        $schedulesByDayAndTime = [];
                        $occupiedCells = [];
                        
                        foreach($days as $day) {
                            $schedulesByDayAndTime[$day] = [];
                            $occupiedCells[$day] = [];
                            foreach($timeSlots as $time) {
                                $schedulesByDayAndTime[$day][$time] = [];
                            }
                        }
                        
                        // Fetch examinations
                        $examinations = \App\Models\Examination::with(['subject', 'faculty', 'classroom'])
                            ->orderBy('exam_date')
                            ->orderBy('start_time')
                            ->get();
                        
                        foreach($examinations as $exam) {
                            $day = $exam->day_name;
                            $startTime = is_string($exam->start_time) ? substr($exam->start_time, 0, 5) : $exam->start_time->format('H:i');
                            $endTime = is_string($exam->end_time) ? substr($exam->end_time, 0, 5) : $exam->end_time->format('H:i');
                            
                            $startHour = (int)substr($startTime, 0, 2);
                            $endHour = (int)substr($endTime, 0, 2);
                            $duration = $endHour - $startHour;
                            
                            $exam->calculated_rowspan = max(1, $duration);
                            
                            if(isset($schedulesByDayAndTime[$day][$startTime])) {
                                $schedulesByDayAndTime[$day][$startTime][] = $exam;
                                
                                for($i = 1; $i < $duration; $i++) {
                                    $nextTimeIndex = array_search($startTime, $timeSlots) + $i;
                                    if($nextTimeIndex < count($timeSlots)) {
                                        $nextTime = $timeSlots[$nextTimeIndex];
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
                            <td class="time-slot">
                                @php
                                    $hour = (int)substr($time, 0, 2);
                                    $ampm = $hour >= 12 ? 'PM' : 'AM';
                                    $displayHour = $hour % 12 ?: 12;
                                @endphp
                                {{ $displayHour }}:{{ substr($time, 3, 2) }} {{ $ampm }}
                            </td>
                            
                            @foreach($days as $day)
                                @php
                                    if(isset($occupiedCells[$day][$time])) {
                                        continue;
                                    }
                                    
                                    $dayExams = $schedulesByDayAndTime[$day][$time];
                                @endphp
                                
                                <td class="schedule-cell" 
                                    @if(count($dayExams) > 0 && isset($dayExams[0]->calculated_rowspan))
                                        rowspan="{{ $dayExams[0]->calculated_rowspan }}"
                                    @endif>
                                    @if(count($dayExams) > 0)
                                        @foreach($dayExams as $exam)
                                            @php
                                                if(!isset($subjectColors[$exam->subject_id])) {
                                                    $subjectColors[$exam->subject_id] = $colors[$colorIndex % count($colors)];
                                                    $colorIndex++;
                                                }
                                                $color = $subjectColors[$exam->subject_id];
                                                
                                                $displayStartTime = is_string($exam->start_time) ? date('g:i A', strtotime($exam->start_time)) : $exam->start_time->format('g:i A');
                                                $displayEndTime = is_string($exam->end_time) ? date('g:i A', strtotime($exam->end_time)) : $exam->end_time->format('g:i A');
                                                $examDate = is_string($exam->exam_date) ? $exam->exam_date : $exam->exam_date->format('M d, Y');
                                            @endphp
                                            
                                            <div class="schedule-block schedule-block-{{ $color }}">
                                                <div class="exam-badge">{{ $exam->exam_type ?? 'FINAL' }} EXAM</div>
                                                <div class="schedule-text course-code">{{ $exam->subject->course_code ?? 'N/A' }}</div>
                                                <div class="schedule-text subject-name">{{ $exam->subject->subject_name ?? 'N/A' }}</div>
                                                <div class="schedule-detail"><span class="detail-label">📅 Date:</span> <span class="exam-date-highlight">{{ $examDate }}</span></div>
                                                <div class="schedule-detail"><span class="detail-label">🏫 Room:</span> {{ $exam->classroom->room_name ?? 'N/A' }}</div>
                                                <div class="schedule-detail"><span class="detail-label">👤 Faculty:</span> {{ $exam->faculty->name ?? 'N/A' }}</div>
                                                <div class="schedule-detail"><span class="detail-label">🎓 Year:</span> {{ $exam->year_section ?? 'N/A' }}</div>
                                                <div class="schedule-detail"><span class="detail-label">⏰ Time:</span> {{ $displayStartTime }} - {{ $displayEndTime }}</div>
                                            </div>
                                        @endforeach
                                    @endif
                                </td>
                            @endforeach
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer">
            <div class="signatures">
                <div class="signature-block">
                    <div class="signature-line">Department Head</div>
                    <div class="signature-title">Academic Affairs</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line">Dean</div>
                    <div class="signature-title">College Dean</div>
                </div>
                <div class="signature-block">
                    <div class="signature-line">Registrar</div>
                    <div class="signature-title">Office of the Registrar</div>
                </div>
            </div>
            
            <div class="footer-info">
                <div>📅 Printed: {{ date('F d, Y h:i A') }}</div>
                <div>👤 Generated by: {{ auth()->user()->name ?? 'Admin' }}</div>
            </div>
        </div>
    </div>
</body>
</html>