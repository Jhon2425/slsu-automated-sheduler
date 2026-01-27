<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8 relative overflow-hidden">
        <!-- Enhanced Animated Background Elements -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-pink-500/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm text-white/80 mb-6">
                <a href="{{ route('faculty.dashboard') }}" class="flex items-center hover:text-violet-400 transition-colors">
                    <i class="fas fa-home mr-2"></i>
                    Dashboard
                </a>
                <i class="fas fa-chevron-right mx-3 text-xs text-white/50"></i>
                <span class="font-semibold text-white">My Teaching Load</span>
            </nav>

            <!-- Page Header -->
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-8 border border-white/20">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-bold text-white flex items-center gap-3 drop-shadow-lg">
                            <i class="fas fa-calendar-alt"></i> Faculty Teaching Load
                        </h1>
                        <p class="mt-3 text-white/90 text-lg drop-shadow">Academic Year {{ $schoolYear ?? date('Y') . '-' . (date('Y') + 1) }}</p>
                    </div>
                    <div class="flex gap-3">
                        <button onclick="window.print()" class="bg-blue-500/30 backdrop-blur-md hover:bg-blue-500/40 text-white px-6 py-3 rounded-xl shadow-lg font-semibold border border-white/30 transition-all no-print">
                            <i class="fas fa-print mr-2"></i>Print
                        </button>
                        <a href="{{ route('faculty.schedule.download-pdf') }}" class="bg-red-500/30 backdrop-blur-md hover:bg-red-500/40 text-white px-6 py-3 rounded-xl shadow-lg font-semibold border border-white/30 transition-all no-print">
                            <i class="fas fa-file-pdf mr-2"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>

            <!-- Teaching Load Document -->
            <div id="teaching-load-document" class="bg-white rounded-2xl shadow-2xl p-12 mb-8">
                <!-- Header Section -->
                <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
                    <div class="flex justify-center mb-4">
                        <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo" class="h-20 w-20">
                    </div>
                    <h2 class="text-sm font-semibold text-gray-700">Republic of the Philippines</h2>
                    <h1 class="text-xl font-bold text-gray-900">SOUTHERN LUZON STATE UNIVERSITY</h1>
                    <p class="text-sm text-gray-600">Lucban, Quezon</p>
                    <h3 class="text-lg font-bold text-gray-800 mt-4">FACULTY TEACHING LOAD</h3>
                    <p class="text-sm text-gray-600">SCHOOL YEAR: {{ $schoolYear ?? '2023-2024' }}</p>
                    <p class="text-sm text-gray-600">SEMESTER: {{ $semester ?? '1st' }}</p>
                </div>

                <!-- Faculty Information -->
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">
                    <div class="space-y-2">
                        <div class="flex border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold w-1/3">COLLEGE/TIAONG CAMPUS</div>
                            <div class="px-3 py-2 w-2/3">{{ $faculty->college ?? 'TIAONG CAMPUS' }}</div>
                        </div>
                        <div class="flex border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold w-1/3">LOCATION:</div>
                            <div class="px-3 py-2 w-2/3">{{ $faculty->location ?? 'TIAONG, QUEZON' }}</div>
                        </div>
                        <div class="flex border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold w-1/3">NAME:</div>
                            <div class="px-3 py-2 w-2/3 uppercase">{{ $faculty->name }}</div>
                        </div>
                        <div class="flex border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold w-1/3">HOME ADDRESS:</div>
                            <div class="px-3 py-2 w-2/3">{{ $faculty->address ?? 'TIAONG, QUEZON' }}</div>
                        </div>
                        <div class="flex border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold w-1/3">Monthly/Year Appointment:</div>
                            <div class="px-3 py-2 w-2/3">{{ $faculty->formatted_appointment_date ?? ($faculty->appointment_date ? date('F Y', strtotime($faculty->appointment_date)) : 'September 2023') }}</div>
                        </div>
                    </div>
                    <div class="space-y-2">
                        <div class="flex border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold w-1/2">RANK:</div>
                            <div class="px-3 py-2 w-1/2">{{ $faculty->rank ?? 'COSI' }}</div>
                        </div>
                        <div class="flex border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold w-1/2">Year of Service in SLSU:</div>
                            <div class="px-3 py-2 w-1/2">{{ $faculty->years_of_service ? number_format($faculty->years_of_service, 1) . ' year' . ($faculty->years_of_service != 1 ? 's' : '') : '0 year' }}</div>
                        </div>
                    </div>
                </div>

                <!-- Educational Qualification -->
                <div class="mb-6">
                    <table class="w-full border-collapse border border-gray-300 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th colspan="3" class="border border-gray-300 px-3 py-2 text-center font-bold">EDUCATIONAL QUALIFICATION</th>
                            </tr>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-300 px-3 py-2 text-center">Degree/Earned</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">Year Graduated/Units Earned</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">School</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($educationalQualifications as $qualification)
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">{{ $qualification->degree }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $qualification->year }}</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $qualification->school }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">BS INDUSTRIAL TECHNOLOGY</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">2013</td>
                                <td class="border border-gray-300 px-3 py-2">SLSU-Tiaong Campus</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Employment and Civil Status -->
                <div class="grid grid-cols-3 gap-4 mb-6 text-sm">
                    <div class="border border-gray-300">
                        <div class="bg-gray-100 px-3 py-2 font-semibold text-center">EMPLOYMENT STATUS</div>
                        <div class="px-3 py-2 text-center">{{ $faculty->employment_status ?? 'PART-TIME' }}</div>
                    </div>
                    <div class="border border-gray-300">
                        <div class="bg-gray-100 px-3 py-2 font-semibold text-center">CIVIL STATUS</div>
                        <div class="px-3 py-2 text-center">{{ $faculty->civil_status ?? 'SINGLE' }}</div>
                    </div>
                    <div class="border border-gray-300">
                        <div class="bg-gray-100 px-3 py-2 font-semibold text-center">DATE OF BIRTH</div>
                        <div class="px-3 py-2 text-center">{{ $faculty->date_of_birth ? date('F d, Y', strtotime($faculty->date_of_birth)) : 'February 18, 1993' }}</div>
                    </div>
                </div>

                <!-- Teaching Load Table -->
                <div class="mb-6">
                    <table class="w-full border-collapse border border-gray-300 text-xs">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-2 py-2">Subject/s</th>
                                <th class="border border-gray-300 px-2 py-2">Time</th>
                                <th class="border border-gray-300 px-2 py-2">Day</th>
                                <th class="border border-gray-300 px-2 py-2">Room</th>
                                <th class="border border-gray-300 px-2 py-2">Course</th>
                                <th class="border border-gray-300 px-2 py-2">Contact Hours</th>
                                <th class="border border-gray-300 px-2 py-2">Units</th>
                                <th class="border border-gray-300 px-2 py-2">Class Size</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $displayTotalContactHours = 0;
                                $displayTotalUnits = 0;
                            @endphp
                            @forelse($schedules as $schedule)
                            <tr>
                                <td class="border border-gray-300 px-2 py-2">
                                    {{ $schedule->subject->course_code ?? 'N/A' }}: {{ $schedule->subject->subject_name ?? 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center">
                                    {{ date('g:i A', strtotime($schedule->start_time)) }}-{{ date('g:i A', strtotime($schedule->end_time)) }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center">
                                    {{ $schedule->day_name ?? 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center">
                                    {{ $schedule->classroom->room_name ?? 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center">
                                    BSIT {{ $schedule->year_level ?? 'N/A' }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center">
                                    @php
                                        $start = strtotime($schedule->start_time);
                                        $end = strtotime($schedule->end_time);
                                        $hours = ($end - $start) / 3600;
                                        $displayTotalContactHours += $hours;
                                    @endphp
                                    {{ number_format($hours, 1) }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center">
                                    @php
                                        $units = ($schedule->lecture_units ?? 0) + ($schedule->laboratory_units ?? 0);
                                        $displayTotalUnits += $units;
                                    @endphp
                                    {{ number_format($units, 1) }}
                                </td>
                                <td class="border border-gray-300 px-2 py-2 text-center">
                                    {{ $schedule->class_size ?? '-' }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="8" class="border border-gray-300 px-2 py-2 text-center text-gray-500">
                                    No teaching load assigned yet
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Summary -->
                <div class="mb-6 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p><strong>Contact Hours per Week:</strong> {{ number_format($totalContactHours ?? 0, 1) }}</p>
                            <p><strong>Units per Week:</strong> {{ number_format($totalUnits ?? 0, 1) }}</p>
                            <p><strong>Total Workload per Day:</strong> {{ $totalWorkloadPerDay ?? 'Not set' }}</p>
                        </div>
                        <div>
                            <p><strong>No. of Preparations:</strong> {{ $numberOfPreparations ?? 0 }}</p>
                            <p><strong>Excess Load:</strong> {{ $excessLoadDisplay ?? 'NONE' }}</p>
                        </div>
                    </div>
                </div>

                <!-- Other Assignment -->
                <div class="mb-6">
                    <table class="w-full border-collapse border border-gray-300 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th colspan="2" class="border border-gray-300 px-3 py-2 text-center font-bold">
                                    ADMINISTRATIVE WORK/ ADVISORY/ OTHER ASSIGNMENT
                                </th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold">LTU / HOURS PER WEEK</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">Designation</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $assignmentsByType['Designation']->description ?? '' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $assignmentsByType['Designation']->hours_per_week ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">Committee Work</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $assignmentsByType['Committee Work']->description ?? '' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $assignmentsByType['Committee Work']->hours_per_week ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">Research Work</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $assignmentsByType['Research Work']->description ?? '' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $assignmentsByType['Research Work']->hours_per_week ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">Extension</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $assignmentsByType['Extension']->description ?? '' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $assignmentsByType['Extension']->hours_per_week ?? '' }}</td>
                            </tr>
                            <tr>
                                <td class="border border-gray-300 px-3 py-2">Production, if any</td>
                                <td class="border border-gray-300 px-3 py-2">{{ $assignmentsByType['Production']->description ?? '' }}</td>
                                <td class="border border-gray-300 px-3 py-2 text-center">{{ $assignmentsByType['Production']->hours_per_week ?? '' }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <!-- Date and Signatures -->
                <div class="text-sm mb-8">
                    <p><strong>Date Effective:</strong> {{ $dateEffective ?? 'August 30, 2023' }}</p>
                </div>

                <div class="grid grid-cols-2 gap-8 mt-12">
                    <div class="text-center">
                        <div class="border-t-2 border-gray-800 pt-2 mt-16">
                            <p class="font-bold">{{ $faculty->name }}</p>
                            <p class="text-xs text-gray-600">Signature Over Printed Name</p>
                        </div>
                    </div>
                    <div>
                        <p class="text-sm mb-4"><strong>Noted by:</strong></p>
                        <div class="text-center">
                            <div class="border-t-2 border-gray-800 pt-2 mt-12">
                                <p class="font-bold">{{ $campusDirector ?? 'ALMA J. CARINGAL' }}</p>
                                <p class="text-xs text-gray-600">Campus Director, SLSU-Tiaong</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="text-center mt-12">
                    <div class="border-t-2 border-gray-800 pt-2 inline-block min-w-[300px]">
                        <p class="font-bold">{{ $vicePresident ?? 'GONDELINA A. MADOVAN, PhD' }}</p>
                        <p class="text-xs text-gray-600">Vice President, Academic Affairs</p>
                    </div>
                    <p class="text-xs mt-2">Approved:</p>
                </div>

                <!-- Footer -->
                <div class="text-xs text-gray-500 mt-8 text-center">
                    AA-INS-1.0-9/1, Rev.0 &nbsp;&nbsp;&nbsp;&nbsp; Page 1 of 3
                </div>
            </div>

        </div>
    </div>

    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25% { transform: translate(20px, -50px) scale(1.1); }
            50% { transform: translate(-20px, 20px) scale(0.9); }
            75% { transform: translate(50px, 50px) scale(1.05); }
        }

        .animate-blob {
            animation: blob 7s infinite;
        }

        .animation-delay-2000 {
            animation-delay: 2s;
        }

        .animation-delay-4000 {
            animation-delay: 4s;
        }

        @media print {
            body {
                background: white !important;
            }
            .glass-card, nav, .no-print {
                display: none !important;
            }
            #teaching-load-document {
                box-shadow: none !important;
                margin: 0 !important;
                padding: 20px !important;
            }
        }
    </style>
</x-app-layout>