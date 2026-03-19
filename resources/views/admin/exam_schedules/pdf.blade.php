<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Examination Schedule — SLSU Tiaong Campus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700;900&family=DM+Sans:wght@300;400;500;600&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
<style>
:root {
    --forest:#2D4A35; --forest-mid:#3E6347; --forest-light:#6D9773; --forest-pale:#B8D4BC;
    --ink:#0F1A13; --ink-mid:#2D3830; --ink-soft:#4A5C4E; --ink-muted:#7A8C7E; --ink-ghost:#B4C1B7;
    --cream:#F8F6F0; --cream-border:#DDD9CE; --white:#FFFFFF; --accent-gold:#C8A84B;
    --c1:#2D4A35;--c1b:#EDF4EE; --c2:#1A3A5C;--c2b:#EAF1F8; --c3:#5C2D1A;--c3b:#FAF0EB;
    --c4:#3D1A5C;--c4b:#F2EAF8; --c5:#1A4A4A;--c5b:#EAF5F5; --c6:#4A3D1A;--c6b:#F5F1E8;
    --c7:#1A3D1A;--c7b:#E8F5E8; --c8:#4A1A2D;--c8b:#F8EAF0; --c9:#1A2D4A;--c9b:#E8EDF5;
    --c10:#2D4A1A;--c10b:#EEF5E8; --c11:#4A2D1A;--c11b:#F5EFE8; --c12:#1A4A3D;--c12b:#E8F5F2;
}
*,*::before,*::after{margin:0;padding:0;box-sizing:border-box;}
body{font-family:'DM Sans',sans-serif;background:#dde3de;color:var(--ink);padding:24px 24px 80px;}

/* ── Print button ── */
.btn-wrap{width:100%;max-width:1340px;margin:0 auto 10px;display:flex;justify-content:flex-end;}
.btn-print{display:flex;align-items:center;gap:8px;padding:11px 22px;background:var(--forest);color:#fff;border:none;border-radius:6px;font-family:'DM Sans',sans-serif;font-size:13px;font-weight:600;letter-spacing:.04em;cursor:pointer;box-shadow:0 4px 16px rgba(45,74,53,.35);transition:transform .15s,box-shadow .15s;}
.btn-print:hover{transform:translateY(-2px);box-shadow:0 8px 24px rgba(45,74,53,.45);}
.btn-print svg{width:15px;height:15px;}

/* ── Page card ── */
.page{width:100%;max-width:1340px;margin:0 auto;background:var(--white);border:1px solid var(--cream-border);border-radius:4px;box-shadow:0 2px 8px rgba(0,0,0,.06),0 16px 48px rgba(0,0,0,.09);padding:18px 24px 14px;}

/* ── Header ── */
.hdr{display:grid;grid-template-columns:60px 1fr 60px;align-items:center;gap:12px;margin-bottom:8px;}
.hdr-logo{display:flex;align-items:center;justify-content:center;}
.hdr-logo img{width:52px;height:52px;object-fit:contain;}
.hdr-center{text-align:center;}
.hdr-uni{font-family:'Playfair Display',serif;font-size:16px;font-weight:900;color:var(--forest);letter-spacing:.09em;text-transform:uppercase;line-height:1;}
.hdr-campus{font-family:'Playfair Display',serif;font-size:11px;font-weight:700;color:var(--forest-mid);letter-spacing:.12em;text-transform:uppercase;margin-top:2px;}
.hdr-addr{font-size:9px;color:var(--ink-muted);margin-top:2px;}
.hdr-rule{height:1px;margin-bottom:7px;background:linear-gradient(90deg,transparent,var(--forest-pale) 20%,var(--forest-light) 50%,var(--forest-pale) 80%,transparent);}

/* ── Banner ── */
.banner{display:flex;align-items:center;justify-content:space-between;padding:7px 14px;background:var(--forest);position:relative;overflow:hidden;margin-bottom:8px;}
.banner::before{content:'';position:absolute;inset:0;background:repeating-linear-gradient(-55deg,transparent,transparent 10px,rgba(255,255,255,.03) 10px,rgba(255,255,255,.03) 20px);}
.banner::after{content:'';position:absolute;left:0;top:0;bottom:0;width:4px;background:var(--accent-gold);}
.banner-l,.banner-r{position:relative;z-index:1;}
.banner-r{text-align:right;}
.banner-title{font-family:'Playfair Display',serif;font-size:13px;font-weight:700;color:#fff;letter-spacing:.12em;text-transform:uppercase;}
.banner-sub{font-size:9px;color:var(--forest-pale);margin-top:1px;}
.banner-meta{font-size:9px;color:var(--forest-pale);font-weight:500;}
.banner-meta strong{color:#fff;}

/* ── Table ── */
.tbl-wrap{overflow-x:auto;}
table{width:100%;border-collapse:collapse;table-layout:fixed;}
col.col-t{width:52px;}
col.col-d{width:calc((100% - 52px)/6);}
thead th{padding:7px 4px;font-size:9px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:#fff;background:var(--forest);border:1px solid var(--forest-mid);}
thead th:first-child{background:var(--ink);color:var(--ink-ghost);}
td.td-t{background:var(--cream);border:1px solid var(--cream-border);text-align:right;vertical-align:top;padding:4px 6px 0;}
.tl{font-family:'DM Mono',monospace;font-size:9px;font-weight:600;color:var(--ink-soft);display:block;white-space:nowrap;}
.tap{font-size:7px;color:var(--ink-muted);display:block;letter-spacing:.05em;}
td.td-s{border:1px solid #E0E5E1;padding:3px;vertical-align:top;background:var(--white);}
tr:nth-child(even) td.td-s{background:#F8FAF8;}
tr.h-start td{border-top:2px solid #B8C8BA !important;}
.ci{display:flex;gap:3px;height:100%;}

/* ── Exam block ── */
.sb{
    flex:1;min-width:0;
    border-radius:3px;border-left:4px solid;
    padding:5px 6px 4px;
    display:flex;flex-direction:column;gap:2px;
    position:relative;overflow:hidden;
}
.sb::after{content:'';position:absolute;inset:0;background:linear-gradient(135deg,rgba(255,255,255,.35) 0%,transparent 50%);pointer-events:none;}

/* Exam type badge */
.sb-badge{
    display:inline-flex;align-items:center;
    font-size:6.5px;font-weight:800;letter-spacing:.08em;text-transform:uppercase;
    padding:1px 5px;border-radius:2px;
    background:var(--forest);color:#fff;
    align-self:flex-start;
    position:relative;z-index:1;
    margin-bottom:1px;
}

/* Course code */
.sb-code{
    font-family:'DM Mono',monospace;
    font-size:9px;font-weight:700;
    letter-spacing:.04em;line-height:1.2;
    white-space:nowrap;overflow:hidden;text-overflow:ellipsis;
    position:relative;z-index:1;
}

/* Subject name */
.sb-name{
    font-size:8.5px;font-weight:900;line-height:1.3;
    overflow:hidden;
    display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;
    position:relative;z-index:1;
}

/* Detail rows */
.sb-row{display:flex;flex-wrap:wrap;gap:1px 6px;margin-top:2px;position:relative;z-index:1;}
.sb-tag{font-size:7.5px;font-weight:700;display:flex;align-items:center;gap:2px;white-space:nowrap;}
.sb-lbl{font-weight:800;text-transform:uppercase;font-size:6.5px;letter-spacing:.06em;opacity:.65;}

/* Date highlight */
.sb-date{
    font-size:7.5px;font-weight:800;
    color:var(--forest);
    display:flex;align-items:center;gap:2px;
    position:relative;z-index:1;
    margin-top:1px;
}

/* Time + section badges */
.sb-bdg{display:flex;align-items:center;gap:3px;margin-top:2px;flex-wrap:wrap;position:relative;z-index:1;}
.sb-type{font-size:7px;font-weight:800;letter-spacing:.08em;padding:1px 4px;border-radius:2px;background:rgba(0,0,0,.1);text-transform:uppercase;}
.sb-time{font-family:'DM Mono',monospace;font-size:7px;font-weight:500;padding:1px 4px;border-radius:2px;background:rgba(0,0,0,.07);letter-spacing:.02em;}

/* Colour themes */
[data-c="c1"] {background:var(--c1b);border-color:var(--c1);color:var(--c1);}
[data-c="c2"] {background:var(--c2b);border-color:var(--c2);color:var(--c2);}
[data-c="c3"] {background:var(--c3b);border-color:var(--c3);color:var(--c3);}
[data-c="c4"] {background:var(--c4b);border-color:var(--c4);color:var(--c4);}
[data-c="c5"] {background:var(--c5b);border-color:var(--c5);color:var(--c5);}
[data-c="c6"] {background:var(--c6b);border-color:var(--c6);color:var(--c6);}
[data-c="c7"] {background:var(--c7b);border-color:var(--c7);color:var(--c7);}
[data-c="c8"] {background:var(--c8b);border-color:var(--c8);color:var(--c8);}
[data-c="c9"] {background:var(--c9b);border-color:var(--c9);color:var(--c9);}
[data-c="c10"]{background:var(--c10b);border-color:var(--c10);color:var(--c10);}
[data-c="c11"]{background:var(--c11b);border-color:var(--c11);color:var(--c11);}
[data-c="c12"]{background:var(--c12b);border-color:var(--c12);color:var(--c12);}

/* ── Legend ── */
.leg{margin-top:6px;}
.leg-hd{font-size:6.5px;font-weight:700;letter-spacing:.1em;text-transform:uppercase;color:var(--ink-muted);margin-bottom:4px;display:flex;align-items:center;gap:6px;}
.leg-hd::after{content:'';flex:1;height:1px;background:var(--cream-border);}
.leg-grid{display:flex;flex-wrap:wrap;gap:3px 10px;}
.leg-item{display:flex;align-items:center;gap:4px;font-size:7px;color:var(--ink-soft);font-weight:500;}
.leg-dot{width:8px;height:8px;border-radius:2px;flex-shrink:0;}

/* ── Footer ── */
.ftr{margin-top:10px;border-top:1.5px solid var(--forest-pale);padding-top:8px;}
.sigs{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:10px;}
.sig{text-align:center;}
.sig-sp{height:32px;}
.sig-ln{border-top:1.5px solid var(--ink-mid);padding-top:4px;}
.sig-n{font-family:'Playfair Display',serif;font-size:9px;font-weight:600;color:var(--ink);}
.sig-t{font-size:7px;color:var(--ink-muted);letter-spacing:.05em;text-transform:uppercase;margin-top:1px;}
.fbar{display:flex;justify-content:space-between;align-items:center;padding:5px 12px;background:var(--cream);border:1px solid var(--cream-border);border-radius:3px;}
.fbi{font-size:7px;color:var(--ink-muted);font-weight:500;display:flex;align-items:center;gap:3px;}
.fbi strong{color:var(--ink-soft);font-weight:600;}
.fdot{width:3px;height:3px;border-radius:50%;background:var(--ink-ghost);}

/* ── Watermark ── */
.wm{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%) rotate(-30deg);width:460px;opacity:.04;pointer-events:none;z-index:0;}
.wm img{width:100%;}

/* ── Print ── */
@media print {
    @page { size: A4 landscape; margin: 0.4cm 0.5cm; }
    body { background:white !important; padding:0 !important; print-color-adjust:exact; -webkit-print-color-adjust:exact; }
    .btn-wrap,.no-print { display:none !important; }
    .wm { opacity:.03; }
    body > .page { max-width:none !important; margin:0 !important; padding:8px 10px 6px !important; border:none !important; border-radius:0 !important; box-shadow:none !important; zoom:var(--print-zoom,1); page-break-inside:avoid; break-inside:avoid; }
    .tbl-wrap { overflow:visible !important; }
    .hdr { margin-bottom:5px; }
    .banner { margin-bottom:5px; padding:5px 12px; }
    .leg { margin-top:4px; }
    .ftr { margin-top:6px; padding-top:5px; }
    .sigs { margin-bottom:6px; }
    .sig-sp { height:22px; }
}
</style>
</head>
<body>

<div class="wm" aria-hidden="true">
    <img src="{{ asset('slsu-logo.png') }}" alt="">
</div>

<div class="btn-wrap no-print">
<button class="btn-print" onclick="window.print()">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
        <polyline points="6 9 6 2 18 2 18 9"/>
        <path d="M6 18H4a2 2 0 0 1-2-2v-5a2 2 0 0 1 2-2h16a2 2 0 0 1 2 2v5a2 2 0 0 1-2 2h-2"/>
        <rect x="6" y="14" width="12" height="8"/>
    </svg>
    Print Examination Schedule
</button>
</div>

<div class="page" id="printPage">

<div class="hdr">
    <div class="hdr-logo"><img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo"></div>
    <div class="hdr-center">
        <div class="hdr-uni">Southern Luzon State University</div>
        <div class="hdr-campus">Tiaong Campus</div>
        <div class="hdr-addr">Tiaong, Quezon, Philippines</div>
    </div>
    <div class="hdr-logo"><img src="{{ asset('slsu-logo.png') }}" alt="SLSU Logo"></div>
</div>

<div class="hdr-rule"></div>

<div class="banner">
    <div class="banner-l">
        <div class="banner-title">Examination Schedule</div>
        <div class="banner-sub">
            @if(isset($semester))
                {{ $semester }} Semester
            @else
                First Semester
            @endif
            &nbsp;&#183;&nbsp; S.Y.&nbsp;{{ isset($schoolYear) ? $schoolYear : date('Y').'-'.(date('Y')+1) }}
        </div>
    </div>
    <div class="banner-r">
        <div class="banner-meta">Generated <strong>{{ date('F j, Y') }}</strong></div>
        <div class="banner-meta">By <strong>{{ auth()->user()->name ?? 'Administrator' }}</strong></div>
    </div>
</div>

@php
    $days = ['Monday','Tuesday','Wednesday','Thursday','Friday','Saturday'];

    $allSlots = [];
    for ($h = 7; $h <= 19; $h++) {
        $allSlots[] = sprintf('%02d:00', $h);
        if ($h < 19) {
            $allSlots[] = sprintf('%02d:30', $h);
        }
    }

    $palette   = ['c1','c2','c3','c4','c5','c6','c7','c8','c9','c10','c11','c12'];
    $subColors = [];
    $colorIdx  = 0;

    $grid     = [];
    $occupied = [];
    foreach ($days as $d) {
        $grid[$d]     = array_fill_keys($allSlots, []);
        $occupied[$d] = [];
    }

    if (isset($examinations)) {
        foreach ($examinations as $exam) {
            $dayName = $exam->day_name ?? null;
            if (!in_array($dayName, $days)) continue;

            if (!isset($subColors[$exam->subject_id])) {
                $subColors[$exam->subject_id] = $palette[$colorIdx % count($palette)];
                $colorIdx++;
            }

            $st = is_string($exam->start_time) ? substr($exam->start_time, 0, 5) : $exam->start_time->format('H:i');
            $et = is_string($exam->end_time)   ? substr($exam->end_time,   0, 5) : $exam->end_time->format('H:i');

            $stParts = explode(':', $st);
            $etParts = explode(':', $et);
            $durMins = ((int)$etParts[0] * 60 + (int)$etParts[1]) - ((int)$stParts[0] * 60 + (int)$stParts[1]);
            $rowspan = max(1, (int)ceil($durMins / 30));

            $exam->_rowspan = $rowspan;
            $exam->_color   = $subColors[$exam->subject_id];

            $startMins   = (int)$stParts[0] * 60 + (int)$stParts[1];
            $matchedSlot = null;
            $matchedIdx  = 0;

            foreach ($allSlots as $si => $ts) {
                if ($ts === $st) { $matchedSlot = $ts; $matchedIdx = $si; break; }
            }
            if (!$matchedSlot) {
                $best = PHP_INT_MAX;
                foreach ($allSlots as $si => $ts) {
                    $tsParts = explode(':', $ts);
                    $tMins   = (int)$tsParts[0] * 60 + (int)$tsParts[1];
                    if ($tMins <= $startMins && ($startMins - $tMins) < $best) {
                        $best = $startMins - $tMins; $matchedSlot = $ts; $matchedIdx = $si;
                    }
                }
            }

            if ($matchedSlot && isset($grid[$dayName][$matchedSlot])) {
                $grid[$dayName][$matchedSlot][] = $exam;
                for ($i = 1; $i < $rowspan; $i++) {
                    $ni = $matchedIdx + $i;
                    if ($ni < count($allSlots)) {
                        $occupied[$dayName][$allSlots[$ni]] = true;
                    }
                }
            }
        }
    }

    $legendItems = [];
    foreach ($subColors as $subId => $color) {
        if (isset($examinations)) {
            foreach ($examinations as $exam) {
                if ($exam->subject_id == $subId) {
                    $legendItems[$subId] = [
                        'color' => $color,
                        'code'  => optional($exam->subject)->course_code  ?? 'N/A',
                        'name'  => optional($exam->subject)->subject_name ?? 'N/A',
                    ];
                    break;
                }
            }
        }
    }

    $colorHex = [
        'c1'  => '#2D4A35', 'c2'  => '#1A3A5C', 'c3'  => '#5C2D1A', 'c4'  => '#3D1A5C',
        'c5'  => '#1A4A4A', 'c6'  => '#4A3D1A', 'c7'  => '#1A3D1A', 'c8'  => '#4A1A2D',
        'c9'  => '#1A2D4A', 'c10' => '#2D4A1A', 'c11' => '#4A2D1A', 'c12' => '#1A4A3D',
    ];

    $timeSlots = $allSlots;
@endphp

<div class="tbl-wrap">
<table>
    <colgroup>
        <col class="col-t">
        @foreach($days as $d)
            <col class="col-d">
        @endforeach
    </colgroup>
    <thead>
        <tr>
            <th style="background:var(--ink);color:var(--ink-ghost);font-size:9px;letter-spacing:.1em;">TIME</th>
            @foreach($days as $d)
                <th>{{ strtoupper(substr($d, 0, 3)) }}</th>
            @endforeach
        </tr>
    </thead>
    <tbody>
    @foreach($timeSlots as $time)
        @php
            $timeParts = explode(':', $time);
            $isHour    = ((int)$timeParts[1] === 0);
            $ampm      = (int)$timeParts[0] >= 12 ? 'PM' : 'AM';
            $dHour     = (int)$timeParts[0] % 12;
            if ($dHour === 0) $dHour = 12;
            $dTime     = $dHour . ':' . $timeParts[1];
        @endphp
        <tr class="{{ $isHour ? 'h-start' : '' }}">
            <td class="td-t">
                <span class="tl">{{ $dTime }}</span>
                <span class="tap">{{ $ampm }}</span>
            </td>
            @foreach($days as $day)
                @php
                    if (isset($occupied[$day][$time])) continue;
                    $ss = $grid[$day][$time] ?? [];
                    $rs = 1;
                    foreach ($ss as $eItem) {
                        if (($eItem->_rowspan ?? 1) > $rs) $rs = $eItem->_rowspan;
                    }
                    $ch = $rs * 28;
                @endphp
                <td class="td-s"
                    @if($rs > 1) rowspan="{{ $rs }}" @endif
                    style="height:{{ $ch }}px;">
                    @if(count($ss) > 0)
                    <div class="ci">
                        @foreach($ss as $eItem)
                        @php
                            $dispStart = is_string($eItem->start_time)
                                ? date('g:i', strtotime($eItem->start_time))
                                : $eItem->start_time->format('g:i');
                            $dispEnd = is_string($eItem->end_time)
                                ? date('g:iA', strtotime($eItem->end_time))
                                : $eItem->end_time->format('g:iA');
                            $dispDate = is_string($eItem->exam_date)
                                ? date('M d, Y', strtotime($eItem->exam_date))
                                : $eItem->exam_date->format('M d, Y');
                        @endphp
                        <div class="sb" data-c="{{ $eItem->_color }}">
                            <div class="sb-badge">Examination</div>
                            <div class="sb-code">{{ optional($eItem->subject)->course_code ?? '—' }}</div>
                            <div class="sb-name"><strong>{{ optional($eItem->subject)->subject_name ?? 'N/A' }}</strong></div>
                            <div class="sb-date">
                                <span class="sb-lbl">Date</span>
                                <strong>{{ $dispDate }}</strong>
                            </div>
                            <div class="sb-row">
                                <span class="sb-tag">
                                    <span class="sb-lbl">Rm</span>
                                    {{ $eItem->classroom->room_name ?? $eItem->classroom->name ?? 'N/A' }}
                                </span>
                                <span class="sb-tag">
                                    <span class="sb-lbl">By</span>
                                    <strong>{{ $eItem->faculty->name ?? 'N/A' }}</strong>
                                </span>
                            </div>
                            <div class="sb-bdg">
                                <span class="sb-tag"><span class="sb-lbl">Sec</span>{{ $eItem->year_section ?? '—' }}</span>
                                <span class="sb-time">{{ $dispStart }}-{{ $dispEnd }}</span>
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

@if(!empty($legendItems))
<div class="leg">
    <div class="leg-hd">Subject Legend</div>
    <div class="leg-grid">
        @foreach($legendItems as $item)
        <div class="leg-item">
            <div class="leg-dot" style="background:{{ $colorHex[$item['color']] ?? '#555' }};"></div>
            <span><strong>{{ $item['code'] }}</strong> &mdash; {{ Str::limit($item['name'], 44) }}</span>
        </div>
        @endforeach
    </div>
</div>
@endif

<div class="ftr">
    <div class="sigs">
        <div class="sig">
            <div class="sig-sp"></div>
            <div class="sig-ln">
                <div class="sig-n">Department Head</div>
                <div class="sig-t">Academic Affairs</div>
            </div>
        </div>
        <div class="sig">
            <div class="sig-sp"></div>
            <div class="sig-ln">
                <div class="sig-n">Dean</div>
                <div class="sig-t">College Dean</div>
            </div>
        </div>
        <div class="sig">
            <div class="sig-sp"></div>
            <div class="sig-ln">
                <div class="sig-n">Registrar</div>
                <div class="sig-t">Office of the Registrar</div>
            </div>
        </div>
    </div>
    <div class="fbar">
        <div class="fbi">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Printed <strong>{{ date('F j, Y g:i A') }}</strong>
        </div>
        <div class="fdot"></div>
        <div class="fbi">Southern Luzon State University &mdash; Tiaong Campus</div>
        <div class="fdot"></div>
        <div class="fbi">
            <svg width="10" height="10" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/>
                <circle cx="12" cy="7" r="4"/>
            </svg>
            Generated by <strong>{{ auth()->user()->name ?? 'Administrator' }}</strong>
        </div>
    </div>
</div>

</div>

@verbatim
<script>
(function() {
    var TARGET_H = 720;
    var TARGET_W = 1060;
    var MIN_ZOOM = 0.45;
    var MAX_ZOOM = 1.0;
    var page = document.getElementById('printPage');
    if (!page) return;

    function applyZoom() {
        page.style.zoom      = '1';
        page.style.transform = '';
        page.style.width     = '';
        var h    = page.scrollHeight;
        var w    = page.scrollWidth;
        var zoom = Math.min(TARGET_H / h, TARGET_W / w, MAX_ZOOM);
        zoom     = Math.max(zoom, MIN_ZOOM);
        zoom     = Math.round(zoom * 1000) / 1000;
        page.style.setProperty('--print-zoom', zoom);
        page.style.zoom = zoom;
        if (typeof page.style.zoom === 'undefined' || page.style.zoom === '') {
            page.style.transform       = 'scale(' + zoom + ')';
            page.style.transformOrigin = '0 0';
            page.style.width           = (100 / zoom) + '%';
        }
    }

    window.addEventListener('load',        applyZoom);
    window.addEventListener('resize',      applyZoom);
    window.addEventListener('beforeprint', applyZoom);
    window.addEventListener('afterprint',  function() {
        page.style.zoom      = '1';
        page.style.transform = '';
        page.style.width     = '';
    });
}());
</script>
@endverbatim

</body>
</html>