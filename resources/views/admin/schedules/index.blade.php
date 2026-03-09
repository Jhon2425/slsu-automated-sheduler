<x-app-layout>
    <div class="min-h-screen bg-[url('/path/to/your/bg.jpg')] bg-cover bg-center py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Breadcrumb -->
            <nav class="flex items-center text-sm text-white/80 mb-6">
                <a href="{{ route('admin.dashboard') }}"
                   class="flex items-center hover:text-violet-400 transition-colors">
                    <i class="fas fa-home mr-2"></i>Dashboard
                </a>
                <i class="fas fa-chevron-right mx-3 text-xs text-white/50"></i>
                <span class="font-semibold text-white">Class Schedules</span>
            </nav>

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
                            <i class="fas fa-chalkboard-teacher"></i> Class Schedules
                        </h1>
                        <p class="mt-3 text-white/90 text-lg drop-shadow">View and manage regular class schedules</p>
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
                        <form action="{{ route('admin.schedules.clear') }}" method="POST"
                              onsubmit="return confirm('Are you sure you want to clear all schedules?');" class="inline">
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

            @php
                /*
                 * ============================================================
                 * TIMETABLE — CSS absolute positioning (no HTML rowspan)
                 * ============================================================
                 * rowspan can only produce ONE <td> per table cell, so it is
                 * fundamentally incapable of showing two schedules that share
                 * the same day and overlap in time. Absolute positioning inside
                 * a fixed-height day column is the correct approach.
                 *
                 * SLOT_HEIGHT : px height of one 30-minute interval
                 * GRID_START  : first visible minute (07:00)
                 * GRID_END    : last  visible minute (19:00)
                 */
                $SLOT_HEIGHT = 60;
                $GRID_START  = 7 * 60;
                $GRID_END    = 19 * 60;
                $GRID_PX     = (($GRID_END - $GRID_START) / 30) * $SLOT_HEIGHT;

                $timeSlots = [];
                for ($m = $GRID_START; $m <= $GRID_END; $m += 30) {
                    $timeSlots[] = sprintf('%02d:%02d', intdiv($m, 60), $m % 60);
                }

                $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

                $palette = ['pink','blue','green','yellow','purple','red',
                            'indigo','teal','orange','cyan','lime','fuchsia'];
                $subjectColors = [];
                $colorIndex    = 0;

                // ── 1. Convert each schedule to a positioned block ──────────
                $byDay = [];
                foreach ($days as $d) { $byDay[$d] = []; }

                foreach ($schedules as $schedule) {
                    $day = $schedule->day_name;
                    if (!in_array($day, $days)) continue;

                    $st = substr($schedule->start_time, 0, 5);
                    $et = substr($schedule->end_time,   0, 5);

                    [$sh, $sm] = explode(':', $st);
                    [$eh, $em] = explode(':', $et);

                    $startMins = max((int)$sh * 60 + (int)$sm, $GRID_START);
                    $endMins   = min((int)$eh * 60 + (int)$em, $GRID_END);
                    if ($endMins <= $startMins) continue;

                    $topPx    = (($startMins - $GRID_START) / 30) * $SLOT_HEIGHT;
                    $heightPx = (($endMins   - $startMins)  / 30) * $SLOT_HEIGHT;

                    if (!isset($subjectColors[$schedule->subject_id])) {
                        $subjectColors[$schedule->subject_id] = $palette[$colorIndex % count($palette)];
                        $colorIndex++;
                    }

                    $byDay[$day][] = [
                        'schedule'  => $schedule,
                        'topPx'     => $topPx,
                        'heightPx'  => $heightPx,
                        'color'     => $subjectColors[$schedule->subject_id],
                        'col'       => 0,
                        'totalCols' => 1,
                    ];
                }

                // ── 2. Collision layout — split overlapping blocks into side-by-side columns ───
                //
                // Algorithm:
                //   Pass 1 – greedy column assignment: place each block in the first
                //             sub-column whose last block has already ended.
                //   Pass 2 – Union-Find groups: every pair of blocks whose time ranges
                //             overlap are merged into one group. All blocks in the same
                //             group share totalCols = (max col index in group) + 1.
                //             This guarantees no two blocks ever overlap visually.
                foreach ($days as $day) {
                    // Sort by start time; break ties by longer duration first
                    usort($byDay[$day], function($a, $b) {
                        if ($a['topPx'] !== $b['topPx']) return $a['topPx'] <=> $b['topPx'];
                        return $b['heightPx'] <=> $a['heightPx'];
                    });

                    $cnt     = count($byDay[$day]);
                    $colEnds = []; // bottomPx of the last block placed in each sub-column

                    // Pass 1: assign sub-column index
                    for ($i = 0; $i < $cnt; $i++) {
                        $top    = $byDay[$day][$i]['topPx'];
                        $placed = false;
                        for ($c = 0; $c < count($colEnds); $c++) {
                            if ($colEnds[$c] <= $top) {
                                $byDay[$day][$i]['col'] = $c;
                                $colEnds[$c] = $top + $byDay[$day][$i]['heightPx'];
                                $placed = true;
                                break;
                            }
                        }
                        if (!$placed) {
                            $byDay[$day][$i]['col'] = count($colEnds);
                            $colEnds[] = $top + $byDay[$day][$i]['heightPx'];
                        }
                    }

                    // Pass 2: Union-Find to group all mutually-overlapping blocks
                    $parent = range(0, $cnt - 1);
                    $find = function(int $x) use (&$find, &$parent): int {
                        if ($parent[$x] !== $x) $parent[$x] = $find($parent[$x]);
                        return $parent[$x];
                    };
                    $union = function(int $x, int $y) use (&$find, &$parent): void {
                        $px = $find($x); $py = $find($y);
                        if ($px !== $py) $parent[$px] = $py;
                    };

                    for ($i = 0; $i < $cnt; $i++) {
                        for ($j = $i + 1; $j < $cnt; $j++) {
                            $aTop = $byDay[$day][$i]['topPx'];
                            $aBot = $aTop + $byDay[$day][$i]['heightPx'];
                            $bTop = $byDay[$day][$j]['topPx'];
                            $bBot = $bTop + $byDay[$day][$j]['heightPx'];
                            if ($bTop < $aBot && $bBot > $aTop) {   // strict overlap
                                $union($i, $j);
                            }
                        }
                    }

                    // For every group collect the max col index used in that group
                    $groupMaxCol = [];
                    for ($i = 0; $i < $cnt; $i++) {
                        $root = $find($i);
                        $groupMaxCol[$root] = max($groupMaxCol[$root] ?? 0, $byDay[$day][$i]['col']);
                    }

                    // Assign totalCols = groupMaxCol + 1 uniformly across the group
                    for ($i = 0; $i < $cnt; $i++) {
                        $byDay[$day][$i]['totalCols'] = ($groupMaxCol[$find($i)] ?? 0) + 1;
                    }
                }
            @endphp

            <!-- ── Timetable ────────────────────────────────────────────── -->
            <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl border border-white/20 p-6">
                <div class="timetable-scroll">
                    <div class="timetable-inner">

                        <!-- Header -->
                        <div class="tt-header-row">
                            <div class="tt-time-gutter"></div>
                            @foreach($days as $day)
                                <div class="tt-day-header">{{ $day }}</div>
                            @endforeach
                        </div>

                        <!-- Body -->
                        <div class="tt-body-row">

                            <!-- Time gutter -->
                            <div class="tt-time-gutter" style="height:{{ $GRID_PX }}px; position:relative;">
                                @foreach($timeSlots as $slot)
                                    @php
                                        $h   = (int)substr($slot, 0, 2);
                                        $min = (int)substr($slot, 3, 2);
                                        $sm  = $h * 60 + $min;
                                        $tp  = (($sm - $GRID_START) / 30) * $SLOT_HEIGHT;
                                        $ap  = $h >= 12 ? 'PM' : 'AM';
                                        $dh  = $h % 12 ?: 12;
                                    @endphp
                                    <div class="tt-time-label" style="top:{{ $tp }}px; height:{{ $SLOT_HEIGHT }}px;">
                                        {{ $dh }}:{{ substr($slot, 3, 2) }} {{ $ap }}
                                    </div>
                                @endforeach
                            </div>

                            <!-- Day columns -->
                            @foreach($days as $day)
                                <div class="tt-day-col" style="height:{{ $GRID_PX }}px;">

                                    {{-- Grid lines --}}
                                    @foreach($timeSlots as $slot)
                                        @php
                                            $h   = (int)substr($slot, 0, 2);
                                            $min = (int)substr($slot, 3, 2);
                                            $sm  = $h * 60 + $min;
                                            $tp  = (($sm - $GRID_START) / 30) * $SLOT_HEIGHT;
                                            $cls = $min === 0 ? 'tt-gridline-hour' : 'tt-gridline-half';
                                        @endphp
                                        <div class="tt-gridline {{ $cls }}" style="top:{{ $tp }}px;"></div>
                                    @endforeach

                                    {{-- Schedule blocks — each absolutely positioned by real start/end time --}}
                                    @foreach($byDay[$day] as $block)
                                        @php
                                            $sched     = $block['schedule'];
                                            $topPx     = $block['topPx'];
                                            $heightPx  = max($block['heightPx'] - 3, 22);
                                            $col       = $block['col'];
                                            $total     = $block['totalCols'];
                                            $wPct      = 100 / $total;
                                            $lPct      = $col * $wPct;
                                        @endphp
                                        <div class="tt-block tt-block-{{ $block['color'] }}"
                                             style="top:{{ $topPx }}px;
                                                    height:{{ $heightPx }}px;
                                                    left:calc({{ $lPct }}% + 2px);
                                                    width:calc({{ $wPct }}% - 4px);">
                                            <div class="tt-block-code">{{ $sched->subject->course_code ?? 'N/A' }}</div>
                                            <div class="tt-block-name">{{ $sched->subject->subject_name ?? 'N/A' }}</div>
                                            <div class="tt-block-info">{{ $sched->classroom->room_name ?? 'N/A' }}</div>
                                            <div class="tt-block-info">{{ $sched->faculty->name ?? 'N/A' }}</div>
                                            <div class="tt-block-info">
                                                {{ $sched->class_type === 'Laboratory' ? 'Lab' : 'Lec' }} · Yr {{ $sched->year_level }}
                                            </div>
                                            <div class="tt-block-time">
                                                {{ date('g:i A', strtotime($sched->start_time)) }}
                                                – {{ date('g:i A', strtotime($sched->end_time)) }}
                                            </div>
                                        </div>
                                    @endforeach

                                </div>{{-- .tt-day-col --}}
                            @endforeach

                        </div>{{-- .tt-body-row --}}
                    </div>{{-- .timetable-inner --}}
                </div>{{-- .timetable-scroll --}}
            </div>

            @else
                <div class="bg-white/10 backdrop-blur-lg rounded-2xl shadow-xl p-16 text-center border border-white/20">
                    <div class="bg-white/20 backdrop-blur-sm rounded-full w-32 h-32 mx-auto flex items-center justify-center mb-6 border border-white/30">
                        <i class="fas fa-calendar-times text-white/60 text-6xl"></i>
                    </div>
                    <h3 class="text-2xl font-bold text-white mb-3 drop-shadow-lg">No Class Schedules Found</h3>
                    <p class="text-white/80 mb-8 text-lg drop-shadow">
                        Generate class schedules to get started with your academic planning.
                    </p>
                    <button onclick="generateSchedule()"
                        class="bg-purple-500/30 backdrop-blur-md hover:bg-purple-500/40 text-white px-10 py-4 rounded-xl font-semibold text-lg shadow-lg border border-white/30 transition-all">
                        <i class="fas fa-magic mr-2"></i>Generate Schedule Now
                    </button>
                </div>
            @endif

        </div>
    </div>

    <!-- ======================================================= -->
    <!--  PREVIEW MODAL                                           -->
    <!-- ======================================================= -->
    <div id="previewModal" class="hidden fixed inset-0 bg-black/50 backdrop-blur-sm overflow-y-auto h-full w-full z-50">
        <div class="relative top-10 mx-auto p-8 border border-white/30 w-11/12 max-w-7xl shadow-2xl rounded-2xl bg-white/10 backdrop-blur-xl mb-10">
            <div class="flex justify-between items-center mb-6 pb-4 border-b border-white/20">
                <h3 class="text-3xl font-bold text-white flex items-center gap-3 drop-shadow-lg">
                    <div class="bg-gradient-to-r from-blue-500/30 to-indigo-500/30 backdrop-blur-sm rounded-xl p-3 border border-white/30">
                        <i class="fas fa-chalkboard-teacher text-white"></i>
                    </div>
                    <span>Class Schedule Preview</span>
                </h3>
                <button onclick="closePreviewModal()" class="text-white/70 hover:text-white transition p-2 hover:bg-white/10 rounded-lg">
                    <i class="fas fa-times text-2xl"></i>
                </button>
            </div>

            <div id="loadingIndicator" class="hidden text-center py-12">
                <i class="fas fa-spinner fa-spin text-white text-6xl mb-4"></i>
                <p class="text-white text-xl">Generating class schedule...</p>
            </div>

            <div id="conflictsAlert" class="hidden bg-yellow-500/20 backdrop-blur-md border border-yellow-500/50 text-white px-6 py-4 rounded-xl mb-6">
                <h4 class="font-bold mb-2"><i class="fas fa-exclamation-triangle mr-2"></i>Scheduling Conflicts</h4>
                <div id="conflictsList"></div>
            </div>

            <div id="previewContent" class="overflow-x-auto"></div>

            <div class="mt-6 flex justify-end gap-4 pt-4 border-t border-white/20">
                <button onclick="closePreviewModal()"
                    class="bg-gray-500/30 backdrop-blur-md hover:bg-gray-500/40 text-white px-8 py-3 rounded-xl font-semibold border border-white/30 transition-all">
                    <i class="fas fa-times mr-2"></i>Cancel
                </button>
                <button onclick="confirmSchedule()" id="confirmBtn"
                    class="bg-green-500/30 backdrop-blur-md hover:bg-green-500/40 text-white px-8 py-3 rounded-xl font-semibold border border-white/30 transition-all">
                    <i class="fas fa-check mr-2"></i>Confirm & Save
                </button>
            </div>
        </div>
    </div>

    <!-- ======================================================= -->
    <!--  STYLES                                                  -->
    <!-- ======================================================= -->
    <style>
    /* ── Shell ──────────────────────────────────────────────── */
    .timetable-scroll { overflow-x: auto; }
    .timetable-inner  { min-width: max-content; }

    /* ── Header ─────────────────────────────────────────────── */
    .tt-header-row {
        display: flex;
        position: sticky;
        top: 0;
        z-index: 20;
    }
    .tt-day-header {
        width: 200px; min-width: 200px;
        background: rgba(255,255,255,0.18);
        backdrop-filter: blur(12px);
        color: white; font-weight: 700;
        text-align: center;
        padding: 14px 8px;
        border: 1px solid rgba(255,255,255,0.2);
        font-size: 0.9rem; letter-spacing: 0.04em;
    }

    /* ── Body ───────────────────────────────────────────────── */
    .tt-body-row { display: flex; }

    /* ── Time gutter ────────────────────────────────────────── */
    .tt-time-gutter {
        width: 80px; min-width: 80px;
        position: relative; flex-shrink: 0;
        background: rgba(255,255,255,0.05);
        border: 1px solid rgba(255,255,255,0.12);
    }
    .tt-time-label {
        position: absolute; left: 0; right: 0;
        display: flex; align-items: flex-start;
        justify-content: center; padding-top: 4px;
        font-size: 0.63rem; font-weight: 600;
        color: rgba(255,255,255,0.7);
        border-top: 1px solid rgba(255,255,255,0.08);
        pointer-events: none;
    }

    /* ── Day columns ────────────────────────────────────────── */
    .tt-day-col {
        width: 200px; min-width: 200px;
        position: relative;      /* blocks positioned inside here */
        border: 1px solid rgba(255,255,255,0.12);
        background: rgba(255,255,255,0.03);
        flex-shrink: 0;
        overflow: visible;       /* allow hover scale to pop above neighbours */
    }

    /* Grid lines */
    .tt-gridline {
        position: absolute; left: 0; right: 0; height: 1px;
        pointer-events: none;
    }
    .tt-gridline-hour { background: rgba(255,255,255,0.18); }
    .tt-gridline-half { background: rgba(255,255,255,0.07); }

    /* ── Schedule blocks ────────────────────────────────────── */
    .tt-block {
        position: absolute;
        border-radius: 6px;
        padding: 5px 7px;
        backdrop-filter: blur(10px);
        border: 1.5px solid rgba(255,255,255,0.3);
        box-shadow: 0 2px 8px rgba(0,0,0,0.25);
        overflow: hidden;
        cursor: default;
        transition: transform 0.12s ease, box-shadow 0.12s ease, z-index 0s;
        box-sizing: border-box;
        z-index: 1;
    }
    .tt-block:hover {
        transform: scale(1.04);
        box-shadow: 0 6px 20px rgba(0,0,0,0.45);
        z-index: 100;
    }

    /* Text layers inside block */
    .tt-block-code { font-weight:700; font-size:0.7rem; color:#fff; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .tt-block-name { font-size:0.65rem; color:rgba(255,255,255,0.92); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px; }
    .tt-block-info { font-size:0.6rem; color:rgba(255,255,255,0.75); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; margin-top:1px; }
    .tt-block-time { font-size:0.58rem; color:rgba(255,255,255,0.62); margin-top:2px; white-space:nowrap; }

    /* Colour gradients */
    .tt-block-pink    { background:linear-gradient(160deg,rgba(236,72,153,.88),rgba(219,39,119,.88)); }
    .tt-block-blue    { background:linear-gradient(160deg,rgba(59,130,246,.88),rgba(37,99,235,.88)); }
    .tt-block-green   { background:linear-gradient(160deg,rgba(34,197,94,.88),rgba(22,163,74,.88)); }
    .tt-block-yellow  { background:linear-gradient(160deg,rgba(234,179,8,.88),rgba(202,138,4,.88)); }
    .tt-block-purple  { background:linear-gradient(160deg,rgba(168,85,247,.88),rgba(147,51,234,.88)); }
    .tt-block-red     { background:linear-gradient(160deg,rgba(239,68,68,.88),rgba(220,38,38,.88)); }
    .tt-block-indigo  { background:linear-gradient(160deg,rgba(99,102,241,.88),rgba(79,70,229,.88)); }
    .tt-block-teal    { background:linear-gradient(160deg,rgba(20,184,166,.88),rgba(13,148,136,.88)); }
    .tt-block-orange  { background:linear-gradient(160deg,rgba(249,115,22,.88),rgba(234,88,12,.88)); }
    .tt-block-cyan    { background:linear-gradient(160deg,rgba(6,182,212,.88),rgba(8,145,178,.88)); }
    .tt-block-lime    { background:linear-gradient(160deg,rgba(132,204,22,.88),rgba(101,163,13,.88)); }
    .tt-block-fuchsia { background:linear-gradient(160deg,rgba(217,70,239,.88),rgba(192,38,211,.88)); }
    </style>

    <!-- ======================================================= -->
    <!--  JAVASCRIPT — preview modal uses the same layout engine  -->
    <!-- ======================================================= -->
    <script>
    let generatedSchedules = null;
    let responseData       = null;

    // Must match PHP constants above
    const SLOT_HEIGHT = 60;
    const GRID_START  = 7 * 60;
    const GRID_END    = 19 * 60;
    const GRID_PX     = ((GRID_END - GRID_START) / 30) * SLOT_HEIGHT;

    const DAYS = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];
    const DAY_MAP = { 1:'Monday',2:'Tuesday',3:'Wednesday',4:'Thursday',5:'Friday',6:'Saturday',7:'Sunday' };
    const COLORS  = ['pink','blue','green','yellow','purple','red','indigo','teal','orange','cyan','lime','fuchsia'];

    // ── Utilities ─────────────────────────────────────────────
    function normTime(t) { return (t || '00:00').substring(0, 5); }

    function toMinutes(t) {
        const [h, m] = normTime(t).split(':').map(Number);
        return h * 60 + m;
    }

    function formatTime(t) {
        const [h, m] = normTime(t).split(':').map(Number);
        return `${h % 12 || 12}:${String(m).padStart(2,'0')} ${h >= 12 ? 'PM' : 'AM'}`;
    }

    function resolveDayName(s) {
        if (s.day_name && DAYS.includes(s.day_name)) return s.day_name;
        return DAY_MAP[parseInt(s.day_name ?? s.day, 10)] || null;
    }

    function assignColor(id, map) {
        if (!map.has(id)) map.set(id, COLORS[map.size % COLORS.length]);
        return map.get(id);
    }

    // ── Collision layout ─────────────────────────────────────────────────────
    // Pass 1: greedy column assignment — place each block in the leftmost
    //         sub-column that has already ended by the time this block starts.
    // Pass 2: Union-Find groups — every pair of overlapping blocks is merged
    //         into one group. All blocks in the group share the same totalCols
    //         = (max col index used by any group member) + 1.
    //         This guarantees zero visual overlap regardless of how many
    //         schedules share the same day/time.
    function layoutBlocks(blocks) {
        // Sort by start, longer blocks first on ties
        blocks.sort((a, b) => a.topPx !== b.topPx ? a.topPx - b.topPx : b.heightPx - a.heightPx);

        const colEnds = []; // bottomPx of last block in each sub-column

        // Pass 1: assign col index
        blocks.forEach(block => {
            let placed = false;
            for (let c = 0; c < colEnds.length; c++) {
                if (colEnds[c] <= block.topPx) {
                    block.col  = c;
                    colEnds[c] = block.topPx + block.heightPx;
                    placed = true;
                    break;
                }
            }
            if (!placed) {
                block.col = colEnds.length;
                colEnds.push(block.topPx + block.heightPx);
            }
        });

        // Pass 2: Union-Find
        const n      = blocks.length;
        const parent = Array.from({length: n}, (_, i) => i);

        function find(x) {
            if (parent[x] !== x) parent[x] = find(parent[x]);
            return parent[x];
        }
        function union(x, y) {
            const px = find(x), py = find(y);
            if (px !== py) parent[px] = py;
        }

        for (let i = 0; i < n; i++) {
            for (let j = i + 1; j < n; j++) {
                const aBot = blocks[i].topPx + blocks[i].heightPx;
                const bBot = blocks[j].topPx + blocks[j].heightPx;
                // Strictly overlapping time ranges
                if (blocks[j].topPx < aBot && bBot > blocks[i].topPx) {
                    union(i, j);
                }
            }
        }

        // Collect max col index per group root
        const groupMaxCol = {};
        blocks.forEach((b, i) => {
            const root = find(i);
            groupMaxCol[root] = Math.max(groupMaxCol[root] ?? 0, b.col);
        });

        // Assign totalCols uniformly across each group
        blocks.forEach((b, i) => {
            b.totalCols = (groupMaxCol[find(i)] ?? 0) + 1;
        });

        return blocks;
    }

    // ── Build timetable HTML ──────────────────────────────────
    function buildTimetable(schedules) {
        const colorMap = new Map();

        // Group + compute geometry
        const byDay = {};
        DAYS.forEach(d => { byDay[d] = []; });

        schedules.forEach(s => {
            const day = resolveDayName(s);
            if (!day || !DAYS.includes(day)) return;

            const startMins = Math.max(toMinutes(s.start_time), GRID_START);
            const endMins   = Math.min(toMinutes(s.end_time),   GRID_END);
            if (endMins <= startMins) return;

            byDay[day].push({
                schedule  : s,
                topPx     : ((startMins - GRID_START) / 30) * SLOT_HEIGHT,
                heightPx  : ((endMins   - startMins)  / 30) * SLOT_HEIGHT,
                color     : assignColor(s.subject_id, colorMap),
                col       : 0,
                totalCols : 1,
            });
        });

        DAYS.forEach(d => { byDay[d] = layoutBlocks(byDay[d]); });

        // ── Time gutter ───────────────────────────────────────
        let gutter = '';
        for (let m = GRID_START; m <= GRID_END; m += 30) {
            const topPx = ((m - GRID_START) / 30) * SLOT_HEIGHT;
            const hour  = Math.floor(m / 60);
            const min   = m % 60;
            const ampm  = hour >= 12 ? 'PM' : 'AM';
            const dh    = hour % 12 || 12;
            gutter += `<div class="tt-time-label" style="top:${topPx}px;height:${SLOT_HEIGHT}px;">${dh}:${String(min).padStart(2,'0')} ${ampm}</div>`;
        }

        // ── Grid lines (shared template) ──────────────────────
        let gridLines = '';
        for (let m = GRID_START; m <= GRID_END; m += 30) {
            const topPx = ((m - GRID_START) / 30) * SLOT_HEIGHT;
            const cls   = m % 60 === 0 ? 'tt-gridline-hour' : 'tt-gridline-half';
            gridLines += `<div class="tt-gridline ${cls}" style="top:${topPx}px;"></div>`;
        }

        // ── Day columns ───────────────────────────────────────
        const dayColsHtml = DAYS.map(day => {
            const blocksHtml = byDay[day].map(block => {
                const s        = block.schedule;
                const h        = Math.max(block.heightPx - 3, 22);
                const wPct     = 100 / block.totalCols;
                const lPct     = block.col * wPct;

                return `
                <div class="tt-block tt-block-${block.color}"
                     style="top:${block.topPx}px;height:${h}px;
                            left:calc(${lPct}% + 2px);width:calc(${wPct}% - 4px);">
                  <div class="tt-block-code">${s.course_code || 'N/A'}</div>
                  <div class="tt-block-name">${s.course_subject || s.subject_name || 'N/A'}</div>
                  <div class="tt-block-info">${s.classroom_name || 'N/A'}</div>
                  <div class="tt-block-info">${s.faculty_name || 'N/A'}</div>
                  <div class="tt-block-info">${s.class_type || 'Lec'} · Yr ${s.year_level || 'N/A'}</div>
                  <div class="tt-block-time">${formatTime(s.start_time)} – ${formatTime(s.end_time)}</div>
                </div>`;
            }).join('');

            return `<div class="tt-day-col" style="height:${GRID_PX}px;">${gridLines}${blocksHtml}</div>`;
        }).join('');

        return `
        <div class="timetable-scroll">
          <div class="timetable-inner">
            <div class="tt-header-row">
              <div class="tt-time-gutter"></div>
              ${DAYS.map(d => `<div class="tt-day-header">${d}</div>`).join('')}
            </div>
            <div class="tt-body-row">
              <div class="tt-time-gutter" style="height:${GRID_PX}px;position:relative;">${gutter}</div>
              ${dayColsHtml}
            </div>
          </div>
        </div>`;
    }

    // ── Modal: generate preview ───────────────────────────────
    async function generateSchedule() {
        const modal     = document.getElementById('previewModal');
        const loading   = document.getElementById('loadingIndicator');
        const content   = document.getElementById('previewContent');
        const conflicts = document.getElementById('conflictsAlert');

        modal.classList.remove('hidden');
        loading.classList.remove('hidden');
        content.innerHTML = '';
        conflicts.classList.add('hidden');

        try {
            const res  = await fetch('{{ route("admin.schedules.generate-preview") }}', {
                method : 'POST',
                headers: {
                    'Content-Type' : 'application/json',
                    'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ schedule_type: 'regular' }),
            });
            const data = await res.json();
            loading.classList.add('hidden');

            if (data.success) {
                responseData       = data;
                generatedSchedules = data.schedules;

                if (data.conflicts?.length) {
                    document.getElementById('conflictsList').innerHTML =
                        '<ul class="list-disc list-inside space-y-1">'
                        + data.conflicts.map(c => `<li><strong>${c.faculty}</strong> – ${c.subject}: ${c.reason}</li>`).join('')
                        + '</ul>';
                    conflicts.classList.remove('hidden');
                }

                content.innerHTML = data.schedules.length
                    ? buildTimetable(data.schedules)
                    : '<div class="text-center text-white py-8">No schedules to display</div>';
            } else {
                alert(data.message || 'Failed to generate preview');
                closePreviewModal();
            }
        } catch (e) {
            loading.classList.add('hidden');
            console.error(e);
            alert('Error generating schedule: ' + e.message);
            closePreviewModal();
        }
    }

    function closePreviewModal() {
        document.getElementById('previewModal').classList.add('hidden');
        responseData = generatedSchedules = null;
    }

    async function confirmSchedule() {
        if (!generatedSchedules) { alert('No schedules to save'); return; }

        const btn = document.getElementById('confirmBtn');
        btn.disabled  = true;
        btn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving...';

        try {
            const res = await fetch('{{ route("admin.schedules.confirm") }}', {
                method : 'POST',
                headers: {
                    'Content-Type' : 'application/json',
                    'X-CSRF-TOKEN' : document.querySelector('meta[name="csrf-token"]').content,
                },
                body: JSON.stringify({ schedule_type:'regular', schedules: generatedSchedules }),
            });

            const ct = res.headers.get('content-type') || '';
            if (!ct.includes('application/json')) {
                throw new Error('Server returned non-JSON. Check console (F12).');
            }

            const data = await res.json();
            if (data.success) {
                alert(data.message || 'Schedule saved successfully!');
                window.location.reload();
            } else {
                alert('Error: ' + (data.message || 'Failed to save schedules'));
                btn.disabled  = false;
                btn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirm & Save';
            }
        } catch (e) {
            console.error(e);
            alert('Error saving: ' + e.message);
            btn.disabled  = false;
            btn.innerHTML = '<i class="fas fa-check mr-2"></i>Confirm & Save';
        }
    }
    </script>
</x-app-layout>