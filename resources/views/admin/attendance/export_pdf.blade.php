<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            line-height: 1.4;
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
            color: #666;
        }
        .filters {
            margin-bottom: 20px;
            background: #f8f8f8;
            padding: 10px;
            border-radius: 5px;
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
            vertical-align: top;
        }
        th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        tr:nth-child(even) {
            background-color: #f9f9f9;
        }
        .footer {
            margin-top: 30px;
            text-align: center;
            font-size: 10px;
            color: #666;
        }
        .summary {
            margin-bottom: 20px;
            background: #e8f4f8;
            padding: 15px;
            border-radius: 5px;
        }
        .summary h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        .summary p {
            margin: 5px 0;
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Laporan Data Absensi</h1>
        <p>{{ setting('company_name', 'Sekolah') }}</p>
        <p>Dicetak pada: {{ now()->format('d F Y H:i:s') }}</p>
        @if($startDate || $endDate)
            <p>Periode: {{ $startDate ?? 'Awal' }} s/d {{ $endDate ?? 'Sekarang' }}</p>
        @endif
    </div>

    @if($search || $roleFilter || $statusFilter)
    <div class="filters">
        <strong>Filter yang diterapkan:</strong><br>
        @if($search)
            <span>Pencarian: {{ $search }}</span><br>
        @endif
        @if($roleFilter && $roleFilter != 'all')
            <span>Role: {{ $roleFilter }}</span><br>
        @endif
        @if($statusFilter && $statusFilter != 'all')
            <span>Status: {{ $statusFilter }}</span><br>
        @endif
    </div>
    @endif

    <div class="summary">
        <h3>Ringkasan Data</h3>
        <p>Total Record: {{ $attendances->count() }}</p>
        @php
            $statusCount = $attendances->groupBy('description');
        @endphp
        @foreach($statusCount as $status => $records)
            <p>{{ $status }}: {{ $records->count() }} record</p>
        @endforeach
    </div>

    <table>
        <thead>
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Role</th>
                <th>Tanggal</th>
                <th>Waktu</th>
                <th>Status</th>
                <th>Lokasi</th>
                <th>Jarak</th>
            </tr>
        </thead>
        <tbody>
            @foreach($attendances as $index => $attendance)
            <tr>
                <td style="text-align: center;">{{ $index + 1 }}</td>
                <td>{{ $attendance->user->name ?? '-' }}</td>
                <td>{{ $attendance->user->role->role_name ?? '-' }}</td>
                <td>{{ $attendance->present_date }}</td>
                <td>{{ $attendance->present_at ? $attendance->present_at->format('H:i:s') : '-' }}</td>
                <td>{{ $attendance->description }}</td>
                <td>
                    @if($attendance->latitude && $attendance->longitude)
                        {{ number_format($attendance->latitude, 6) }}, {{ number_format($attendance->longitude, 6) }}
                    @else
                        -
                    @endif
                </td>
                <td style="text-align: center;">
                    @if($attendance->distance)
                        {{ number_format($attendance->distance) }} m
                    @else
                        -
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh Sistem Absensi Online</p>
        <p>&copy; {{ date('Y') }} - {{ setting('company_name', 'Sekolah') }}</p>
    </div>
</body>
</html>
