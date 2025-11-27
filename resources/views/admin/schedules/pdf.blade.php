<!-- resources/views/admin/schedules/pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Complete Schedule - SLSU</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
        }
        .header p {
            margin: 5px 0;
            color: #666;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #2563eb;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>South Luzon State University</h1>
        <p>Automated Scheduling System</p>
        <p><strong>Complete Schedule Report</strong></p>
        <p>Generated on: {{ date('F d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Faculty</th>
                <th>Subject</th>
                <th>Classroom</th>
                <th>Day</th>
                <th>Time</th>
                <th>Section</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $schedule)
            <tr>
                <td>{{ $schedule->faculty->name }}</td>
                <td>{{ $schedule->faculty->course_subject }}</td>
                <td>{{ $schedule->classroom->room_name }}</td>
                <td>{{ $schedule->day }}</td>
                <td>{{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</td>
                <td>{{ $schedule->faculty->year_section }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>&copy; {{ date('Y') }} South Luzon State University. All rights reserved.</p>
    </div>
</body>
</html>

<!-- resources/views/faculty/schedule-pdf.blade.php -->
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>My Schedule - {{ $faculty->name }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 3px solid #2563eb;
            padding-bottom: 15px;
        }
        .header h1 {
            margin: 0;
            color: #2563eb;
        }
        .info-box {
            background-color: #eff6ff;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }
        .info-box p {
            margin: 5px 0;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th {
            background-color: #2563eb;
            color: white;
            padding: 10px;
            text-align: left;
            font-weight: bold;
        }
        td {
            padding: 8px;
            border-bottom: 1px solid #ddd;
        }
        tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>South Luzon State University</h1>
        <p>Faculty Schedule</p>
    </div>

    <div class="info-box">
        <p><strong>Faculty Name:</strong> {{ $faculty->name }}</p>
        <p><strong>Subject:</strong> {{ $faculty->course_subject }}</p>
        <p><strong>Year & Section:</strong> {{ $faculty->year_section }}</p>
        <p><strong>Units:</strong> {{ $faculty->units }}</p>
        <p><strong>Total Hours:</strong> {{ $faculty->no_of_hours }}</p>
        <p><strong>Generated:</strong> {{ date('F d, Y h:i A') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Day</th>
                <th>Time</th>
                <th>Classroom</th>
                <th>Date</th>
            </tr>
        </thead>
        <tbody>
            @foreach($schedules as $schedule)
            <tr>
                <td><strong>{{ $schedule->day }}</strong></td>
                <td>{{ date('g:i A', strtotime($schedule->start_time)) }} - {{ date('g:i A', strtotime($schedule->end_time)) }}</td>
                <td>{{ $schedule->classroom->room_name }}</td>
                <td>{{ $schedule->schedule_date->format('M d, Y') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>This is a computer-generated document. No signature is required.</p>
        <p>&copy; {{ date('Y') }} South Luzon State University. All rights reserved.</p>
    </div>
</body>
</html>