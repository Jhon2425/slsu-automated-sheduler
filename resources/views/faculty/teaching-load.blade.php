<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8 relative overflow-hidden">

        {{-- Animated background blobs --}}
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="absolute top-0 left-0 w-96 h-96 bg-blue-500/10 rounded-full blur-3xl animate-blob"></div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-purple-500/10 rounded-full blur-3xl animate-blob animation-delay-2000"></div>
            <div class="absolute bottom-0 left-1/2 w-96 h-96 bg-pink-500/10 rounded-full blur-3xl animate-blob animation-delay-4000"></div>
        </div>

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">

            {{-- Breadcrumb --}}
            <nav class="flex items-center text-sm text-white/80 mb-6">
                <a href="{{ route('faculty.dashboard') }}" class="flex items-center hover:text-violet-400 transition-colors">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right mx-3 text-xs text-white/50"></i>
                <span class="font-semibold text-white">My Teaching Load</span>
            </nav>

            {{-- Page Header --}}
            <div class="glass-card rounded-2xl shadow-xl p-8 mb-8 border border-white/20">
                <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                    <div>
                        <h1 class="text-4xl font-bold text-white flex items-center gap-3 drop-shadow-lg">
                            <i class="fas fa-calendar-alt"></i> Faculty Teaching Load
                        </h1>
                        <p class="mt-3 text-white/90 text-lg drop-shadow">
                            Academic Year {{ $schoolYear }}
                        </p>
                    </div>
                    <div class="flex gap-3 no-print">
                        <button onclick="window.print()"
                            class="bg-blue-500/30 backdrop-blur-md hover:bg-blue-500/40 text-white px-6 py-3 rounded-xl shadow-lg font-semibold border border-white/30 transition-all">
                            <i class="fas fa-print mr-2"></i>Print
                        </button>
                        <a href="{{ route('faculty.teaching-load.download-pdf') }}"
                            class="bg-red-500/30 backdrop-blur-md hover:bg-red-500/40 text-white px-6 py-3 rounded-xl shadow-lg font-semibold border border-white/30 transition-all">
                            <i class="fas fa-file-pdf mr-2"></i>Download PDF
                        </a>
                    </div>
                </div>
            </div>

            {{-- ═══════════════════════ DOCUMENT ═══════════════════════ --}}
            <div id="teaching-load-document" class="bg-white rounded-2xl shadow-2xl p-12 mb-8">

                {{-- ── Header ─────────────────────────────────────────── --}}
                <div class="text-center mb-8 border-b-2 border-gray-300 pb-6">
                    <div class="flex justify-center mb-4">
                        <img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo" class="h-20 w-20">
                    </div>
                    <h2 class="text-sm font-semibold text-gray-700">Republic of the Philippines</h2>
                    <h1 class="text-xl font-bold text-gray-900">SOUTHERN LUZON STATE UNIVERSITY</h1>
                    <p class="text-sm text-gray-600">Lucban, Quezon</p>
                    <h3 class="text-lg font-bold text-gray-800 mt-4">FACULTY TEACHING LOAD</h3>
                    <p class="text-sm text-gray-600">SCHOOL YEAR: {{ $schoolYear }}</p>
                    <p class="text-sm text-gray-600">SEMESTER: {{ $semester }}</p>
                </div>

                {{-- ── Faculty Information ─────────────────────────────── --}}
                <div class="grid grid-cols-2 gap-4 mb-6 text-sm">

                    {{-- Left column --}}
                    <div class="space-y-2">
                        @foreach([
                            'COLLEGE/TIAONG CAMPUS'    => 'TIAONG CAMPUS',
                            'LOCATION'                 => 'TIAONG, QUEZON',
                            'NAME'                     => strtoupper($faculty->name),
                            'HOME ADDRESS'             => $faculty->home_address ?? 'TIAONG, QUEZON',
                            'Monthly/Year Appointment' => $faculty->formatted_appointment_date ?? 'September 2023',
                        ] as $label => $value)
                            <div class="flex border border-gray-300">
                                <div class="bg-gray-100 px-3 py-2 font-semibold w-1/3">{{ $label }}:</div>
                                <div class="px-3 py-2 w-2/3">{{ $value }}</div>
                            </div>
                        @endforeach

                        {{-- ── PROGRAM/COURSE ────────────────────────────────────────────── --}}
                        <div class="flex border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold w-1/3">PROGRAM/COURSE:</div>
                            <div class="px-3 py-2 w-2/3">
                                @php
                                    $programDisplay = null;

                                    if (!empty($linkedProgram)) {
                                        $programDisplay = $linkedProgram->code
                                            ?? $linkedProgram->program_name
                                            ?? $linkedProgram->name
                                            ?? null;
                                    }

                                    if (!$programDisplay && $userPrograms->isNotEmpty()) {
                                        $programDisplay = $userPrograms
                                            ->map(fn($p) => $p->code ?? $p->program_name ?? $p->name ?? null)
                                            ->filter()
                                            ->implode(', ');
                                    }

                                    if (!$programDisplay && !empty($user?->program)) {
                                        $programDisplay = $user->program;
                                    }

                                    if (!$programDisplay && !empty($faculty->program)) {
                                        $programDisplay = $faculty->program;
                                    }
                                @endphp

                                {{ $programDisplay ?? 'N/A' }}
                            </div>
                        </div>
                        {{-- ── END PROGRAM/COURSE ───────────────────────────────────────── --}}
                    </div>

                    {{-- Right column --}}
                    <div class="space-y-2">
                        @foreach([
                            'RANK'                    => $faculty->rank ?? 'COSI',
                            'Year of Service in SLSU' => $faculty->years_of_service
                                ? number_format($faculty->years_of_service, 1) . ' year' . ($faculty->years_of_service != 1 ? 's' : '')
                                : '0 year',
                        ] as $label => $value)
                            <div class="flex border border-gray-300">
                                <div class="bg-gray-100 px-3 py-2 font-semibold w-1/2">{{ $label }}:</div>
                                <div class="px-3 py-2 w-1/2">{{ $value }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- ── Educational Qualifications ──────────────────────── --}}
                <div class="mb-6">
                    <table class="w-full border-collapse border border-gray-300 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th colspan="3" class="border border-gray-300 px-3 py-2 text-center font-bold">
                                    EDUCATIONAL QUALIFICATION
                                </th>
                            </tr>
                            <tr class="bg-gray-50">
                                <th class="border border-gray-300 px-3 py-2 text-center">Degree / Earned</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">Year Graduated / Units Earned</th>
                                <th class="border border-gray-300 px-3 py-2 text-center">School</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($educationalQualifications as $eq)
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2">
                                        {{ $eq->degree_earned ?? 'N/A' }}{{ $eq->course ? ' - ' . $eq->course : '' }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        {{ $eq->year_graduated ?? 'N/A' }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2">
                                        {{ $eq->school_graduated ?? 'N/A' }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2">Bachelor Degree - BS INDUSTRIAL TECHNOLOGY</td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">2013</td>
                                    <td class="border border-gray-300 px-3 py-2">SLSU-Tiaong Campus</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ── Employment / Civil Status / Birthdate ───────────── --}}
                <div class="grid grid-cols-3 gap-4 mb-6 text-sm">
                    @foreach([
                        'EMPLOYMENT STATUS' => $faculty->employment_status ?? 'Part-Time',
                        'CIVIL STATUS'      => $faculty->civil_status      ?? 'Single',
                        'DATE OF BIRTH'     => $faculty->birthdate
                            ? date('F d, Y', strtotime($faculty->birthdate))
                            : 'Not Set',
                    ] as $label => $value)
                        <div class="border border-gray-300">
                            <div class="bg-gray-100 px-3 py-2 font-semibold text-center">{{ $label }}</div>
                            <div class="px-3 py-2 text-center">{{ $value }}</div>
                        </div>
                    @endforeach
                </div>

                {{-- ── Teaching Load Table ─────────────────────────────── --}}
                <div class="mb-6">
                    <table class="w-full border-collapse border border-gray-300 text-xs">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="border border-gray-300 px-2 py-2">Subject/s</th>
                                <th class="border border-gray-300 px-2 py-2">Time</th>
                                <th class="border border-gray-300 px-2 py-2">Day</th>
                                <th class="border border-gray-300 px-2 py-2">Room</th>
                                <th class="border border-gray-300 px-2 py-2">Course</th>
                                <th class="border border-gray-300 px-2 py-2">Class Size</th>
                                <th class="border border-gray-300 px-2 py-2">Contact Hours</th>
                                <th class="border border-gray-300 px-2 py-2">Units</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($displayRows as $row)
                                <tr>
                                    {{-- Subject --}}
                                    <td class="border border-gray-300 px-2 py-2">
                                        @if($row['subject'])
                                            @if($row['subject']->course_code ?? false)
                                                <span class="font-medium">{{ $row['subject']->course_code }}</span>:
                                            @endif
                                            {{ $row['subject']->subject_name ?? 'N/A' }}
                                        @else
                                            N/A
                                        @endif
                                    </td>

                                    {{-- Time --}}
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        @if($row['has_schedule'] && $row['time_start'] && $row['time_end'])
                                            {{ date('g:i A', $row['time_start']) }}&ndash;{{ date('g:i A', $row['time_end']) }}
                                        @else
                                            <span class="text-gray-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Day --}}
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        @if($row['has_schedule'] && $row['day_name'])
                                            {{ $row['day_name'] }}
                                        @else
                                            <span class="text-gray-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Room --}}
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        @if($row['has_schedule'] && $row['room'])
                                            {{ $row['room'] }}
                                        @else
                                            <span class="text-gray-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Course / Program --}}
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        {{ $row['program_code'] }}
                                        @if($row['year_level'])
                                            {{ $row['year_level'] }}
                                        @endif
                                        @if($row['section'])
                                            - {{ $row['section'] }}
                                        @endif
                                    </td>

                                    {{-- Class Size --}}
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        {{ $row['class_size'] ?: '&mdash;' }}
                                    </td>

                                    {{-- Contact Hours --}}
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        @if($row['has_schedule'] && $row['contact_hours'] > 0)
                                            {{ number_format($row['contact_hours'], 1) }}
                                        @else
                                            <span class="text-gray-400">&mdash;</span>
                                        @endif
                                    </td>

                                    {{-- Units --}}
                                    <td class="border border-gray-300 px-2 py-2 text-center">
                                        {{ number_format($row['units'], 1) }}
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="border border-gray-300 px-2 py-4 text-center text-gray-500">
                                        No teaching load assigned yet.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- ── Summary ─────────────────────────────────────────── --}}
                <div class="mb-6 text-sm">
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <p><strong>Contact Hours per Week:</strong> {{ number_format($totalContactHours, 1) }}</p>
                            <p><strong>Units per Week:</strong> {{ number_format($totalUnits, 1) }}</p>
                            <p><strong>Total Workload per Day:</strong> {{ $totalWorkloadPerDay }}</p>
                        </div>
                        <div>
                            <p><strong>No. of Preparations:</strong> {{ $numberOfPreparations }}</p>
                            <p><strong>Excess Load:</strong> {{ $excessLoadDisplay }}</p>
                        </div>
                    </div>
                </div>

                {{-- ── Administrative / Other Assignments ─────────────── --}}
                <div class="mb-6">
                    <table class="w-full border-collapse border border-gray-300 text-sm">
                        <thead>
                            <tr class="bg-gray-100">
                                <th colspan="2" class="border border-gray-300 px-3 py-2 text-center font-bold">
                                    ADMINISTRATIVE WORK / ADVISORY / OTHER ASSIGNMENT
                                </th>
                                <th class="border border-gray-300 px-3 py-2 text-center font-bold">
                                    LTU / HOURS PER WEEK
                                </th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach(['Designation', 'Committee Work', 'Research Work', 'Extension', 'Production, if any'] as $type)
                                @php $key = $type === 'Production, if any' ? 'Production' : $type; @endphp
                                <tr>
                                    <td class="border border-gray-300 px-3 py-2">{{ $type }}</td>
                                    <td class="border border-gray-300 px-3 py-2">
                                        {{ $assignmentsByType[$key]->description ?? '' }}
                                    </td>
                                    <td class="border border-gray-300 px-3 py-2 text-center">
                                        {{ $assignmentsByType[$key]->hours_per_week ?? '' }}
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- ── Date Effective ───────────────────────────────────── --}}
                <div class="text-sm mb-8">
                    <p><strong>Date Effective:</strong> {{ $dateEffective }}</p>
                </div>

                {{-- ── Signatures ───────────────────────────────────────── --}}
                <div class="grid grid-cols-3 gap-8 mt-12 text-center">

                    {{-- Faculty signature --}}
                    <div class="flex flex-col justify-end">
                        <div class="border-t-2 border-gray-800 pt-2 mt-16">
                            <p class="font-bold">{{ $faculty->name }}</p>
                            <p class="text-xs text-gray-600">Signature Over Printed Name</p>
                        </div>
                    </div>

                    {{-- Campus Director --}}
                    <div class="flex flex-col justify-end">
                        <p class="text-sm mb-2"><strong>Noted by:</strong></p>
                        <div class="border-t-2 border-gray-800 pt-2 mt-12">
                            <p class="font-bold">{{ $campusDirector }}</p>
                            <p class="text-xs text-gray-600">Campus Director, SLSU-Tiaong</p>
                        </div>
                    </div>

                    {{-- VP Academic Affairs --}}
                    <div class="flex flex-col justify-end">
                        <p class="text-sm mb-2"><strong>Approved:</strong></p>
                        <div class="border-t-2 border-gray-800 pt-2 mt-12">
                            <p class="font-bold">Dhenalyn A. Dejelo, PhD</p>
                            <p class="text-xs text-gray-600">Vice President, Academic Affairs</p>
                        </div>
                    </div>

                </div>

                {{-- ── Footer ───────────────────────────────────────────── --}}
                <div class="text-xs text-gray-500 mt-8 text-center">
                    AA-INS-1.0-9/1, Rev.0 &nbsp;&nbsp;&nbsp;&nbsp; Page 1 of 1
                </div>

            </div>{{-- end #teaching-load-document --}}
        </div>
    </div>

    {{-- ═══════════════════════ STYLES ═══════════════════════════════════ --}}
    <style>
        .glass-card {
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
        }

        @keyframes blob {
            0%, 100% { transform: translate(0, 0) scale(1); }
            25%       { transform: translate(20px, -50px) scale(1.1); }
            50%       { transform: translate(-20px, 20px) scale(0.9); }
            75%       { transform: translate(50px, 50px) scale(1.05); }
        }
        .animate-blob          { animation: blob 7s infinite; }
        .animation-delay-2000  { animation-delay: 2s; }
        .animation-delay-4000  { animation-delay: 4s; }

        /* ═══════════════════════ PRINT STYLES ═══════════════════════════ */
        @media print {

            /* ── Page setup: landscape + tight margins ── */
            @page {
                size: A4 landscape;
                margin: 8mm 10mm;
            }

            /* ── Force accurate color rendering ── */
            * {
                -webkit-print-color-adjust: exact !important;
                print-color-adjust: exact !important;
                box-sizing: border-box !important;
            }

            /* ── Base font shrink so everything fits ── */
            html, body {
                width: 100% !important;
                height: auto !important;
                overflow: visible !important;
                background: white !important;
                font-size: 8pt !important;
            }

            /* ── Hide all UI chrome ── */
            .glass-card,
            nav,
            .no-print,
            .animate-blob,
            .absolute.inset-0,
            .min-h-screen > .absolute {
                display: none !important;
            }

            /* ── Remove wrappers' constraints ── */
            .min-h-screen,
            .max-w-7xl,
            .relative.z-10,
            .px-4, .sm\:px-6, .lg\:px-8 {
                max-width: 100% !important;
                padding: 0 !important;
                margin: 0 !important;
                background: none !important;
                min-height: unset !important;
            }

            /* ── The printable document ── */
            #teaching-load-document {
                box-shadow: none !important;
                border-radius: 0 !important;
                margin: 0 !important;
                padding: 5mm !important;
                width: 100% !important;

                /* ONE PAGE: prevent any breaks inside */
                page-break-before: avoid !important;
                page-break-after: avoid !important;
                page-break-inside: avoid !important;
                break-before: avoid !important;
                break-after: avoid !important;
                break-inside: avoid !important;
            }

            /* ── Prevent all children from breaking across pages ── */
            #teaching-load-document *,
            #teaching-load-document table,
            #teaching-load-document thead,
            #teaching-load-document tbody,
            #teaching-load-document tr,
            #teaching-load-document td,
            #teaching-load-document th {
                page-break-inside: avoid !important;
                break-inside: avoid !important;
            }

            /* ── Tighten vertical spacing ── */
            #teaching-load-document .mb-8  { margin-bottom: 2.5mm !important; }
            #teaching-load-document .mb-6  { margin-bottom: 2mm !important; }
            #teaching-load-document .mb-4  { margin-bottom: 1.5mm !important; }
            #teaching-load-document .mt-12 { margin-top: 3mm !important; }
            #teaching-load-document .mt-16 { margin-top: 4mm !important; }
            #teaching-load-document .mt-8  { margin-top: 2.5mm !important; }
            #teaching-load-document .mt-2  { margin-top: 1mm !important; }
            #teaching-load-document .p-8   { padding: 3mm !important; }
            #teaching-load-document .p-12  { padding: 4mm !important; }
            #teaching-load-document .py-8  { padding-top: 2mm !important; padding-bottom: 2mm !important; }
            #teaching-load-document .pb-6  { padding-bottom: 2mm !important; }
            #teaching-load-document .gap-4 { gap: 1.5mm !important; }
            #teaching-load-document .gap-8 { gap: 3mm !important; }
            #teaching-load-document .space-y-2 > * + * { margin-top: 1mm !important; }

            /* ── Table cell sizing ── */
            #teaching-load-document th,
            #teaching-load-document td {
                padding: 1mm 1.5mm !important;
                font-size: 7.5pt !important;
                line-height: 1.2 !important;
            }

            /* ── Typography ── */
            #teaching-load-document h1      { font-size: 10pt !important; margin: 0 !important; }
            #teaching-load-document h2      { font-size: 7.5pt !important; margin: 0 !important; }
            #teaching-load-document h3      { font-size: 8.5pt !important; margin: 1mm 0 !important; }
            #teaching-load-document p       { font-size: 7.5pt !important; line-height: 1.3 !important; margin: 0.5mm 0 !important; }
            #teaching-load-document .text-sm  { font-size: 7.5pt !important; }
            #teaching-load-document .text-xs  { font-size: 7pt !important; }
            #teaching-load-document .text-lg  { font-size: 9pt !important; }
            #teaching-load-document .text-xl  { font-size: 10pt !important; }

            /* ── Logo ── */
            #teaching-load-document img.h-20 {
                height: 11mm !important;
                width: 11mm !important;
            }

            /* ── Keep two-column and three-column grids ── */
            #teaching-load-document .grid-cols-2 {
                display: grid !important;
                grid-template-columns: 1fr 1fr !important;
            }
            #teaching-load-document .grid-cols-3 {
                display: grid !important;
                grid-template-columns: 1fr 1fr 1fr !important;
            }

            /* ── Signature section ── */
            #teaching-load-document .grid-cols-3.mt-12 {
                display: grid !important;
                grid-template-columns: 1fr 1fr 1fr !important;
                text-align: center !important;
            }
            #teaching-load-document .grid-cols-3.mt-12 > div {
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-end !important;
            }

            /* ── Signature line spacing ── */
            #teaching-load-document .border-t-2.border-gray-800 {
                margin-top: 5mm !important;
            }
        }
    </style>
</x-app-layout>