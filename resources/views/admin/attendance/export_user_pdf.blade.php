<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Attendance Report - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #333;
            padding-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 24px;
            color: #333;
        }
        .header p {
            margin: 5px 0;
            font-size: 14px;
            color: #666;
        }
        .user-info {
            margin-bottom: 20px;
        }
        .user-info table {
            width: 100%;
            border-collapse: collapse;
        }
        .user-info td {
            padding: 5px;
            border: 1px solid #ddd;
        }
        .user-info .label {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 150px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            font-size: 12px;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .status-badge {
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 11px;
            font-weight: bold;
        }
        .status-hadir { background-color: #d4edda; color: #155724; }
        .status-terlambat { background-color: #fff3cd; color: #856404; }
        .status-sakit { background-color: #f8d7da; color: #721c24; }
        .status-izin { background-color: #e2e3e5; color: #383d41; }
        .status-dinas-luar { background-color: #d1ecf1; color: #0c5460; }
        .status-wfh { background-color: #d4edda; color: #155724; }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            font-size: 16px;
        }
        .summary-stats {
            display: table;
            width: 100%;
        }
        .summary-stats div {
            display: table-cell;
            text-align: center;
            padding: 5px;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Attendance Report</h1>
        <p>Generated on {{ now()->format('d F Y H:i:s') }}</p>
    </div>

    <div class="user-info">
        <table>
            <tr>
                <td class="label">Employee Name:</td>
                <td>{{ $user->name }}</td>
            </tr>
            <tr>
                <td class="label">Email:</td>
                <td>{{ $user->email }}</td>
            </tr>
            <tr>
                <td class="label">Role:</td>
                <td>{{ $user->role->role_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <td class="label">Report Period:</td>
                <td>All Time</td>
            </tr>
            <tr>
                <td class="label">Total Records:</td>
                <td>{{ $attendances->count() }}</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <h3>Attendance Summary</h3>
        <div class="summary-stats">
            @php
                $statusCounts = $attendances->groupBy('description')->map->count();
            @endphp
            <div><strong>Hadir:</strong> {{ $statusCounts->get('Hadir', 0) }}</div>
            <div><strong>Terlambat:</strong> {{ $statusCounts->get('Terlambat', 0) }}</div>
            <div><strong>Sakit:</strong> {{ $statusCounts->get('Sakit', 0) }}</div>
            <div><strong>Izin:</strong> {{ $statusCounts->get('Izin', 0) }}</div>
            <div><strong>Dinas Luar:</strong> {{ $statusCounts->get('Dinas Luar', 0) }}</div>
            <div><strong>WFH:</strong> {{ $statusCounts->get('WFH', 0) }}</div>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th width="5%">No</th>
                <th width="15%">Date</th>
                <th width="10%">Time</th>
                <th width="15%">Status</th>
                <th width="15%">Latitude</th>
                <th width="15%">Longitude</th>
                <th width="10%">Distance (m)</th>
                <th width="15%">IP Address</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $attendance->present_date }}</td>
                <td>{{ $attendance->present_at ? $attendance->present_at->format('H:i:s') : '-' }}</td>
                <td>
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $attendance->description)) }}">
                        {{ $attendance->description }}
                    </span>
                </td>
                <td>{{ $attendance->latitude ?: '-' }}</td>
                <td>{{ $attendance->longitude ?: '-' }}</td>
                <td>{{ $attendance->distance ?: '-' }}</td>
                <td>{{ $attendance->ip_address ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="8" style="text-align: center;">No attendance records found</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>This report was generated automatically by the Attendance Management System</p>
    </div>
</body>
</html>
