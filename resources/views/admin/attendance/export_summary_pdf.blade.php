<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Summary Laporan Absensi</title>
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
    </style>
</head>
<body>
    <div class="header">
        @php
            $kopsuratPath = setting('kopsurat');
            $hasValidKopsurat = $kopsuratPath && File::exists(public_path($kopsuratPath));
        @endphp

        @if($hasValidKopsurat)
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="data:image/jpeg;base64,{{ base64_encode(File::get(public_path($kopsuratPath))) }}"
                 alt="Kopsurat"
                 style="max-width: 100%; max-height: 120px; object-fit: contain;">
        </div>
        @endif

        <h1>Summary Laporan Data Absensi</h1>
        <p>{{ setting('company_name', 'Sekolah') }}</p>
        <p>Dicetak pada: {{ now()->format('d F Y H:i:s') }}</p>
    </div>

    <table>
        <thead>
            <tr>
                <th>User ID</th>
                <th>Nama</th>
                <th>Role</th>
                <th>Total Hari Hadir</th>
                <th>Terakhir Hadir</th>
            </tr>
        </thead>
        <tbody>
            @foreach($users as $user)
            <tr>
                <td style="text-align: center;">{{ $user->id }}</td>
                <td>{{ $user->name }}</td>
                <td>{{ $user->role->role_name ?? '-' }}</td>
                <td style="text-align: center;">{{ $user->attendances_count ?? 0 }}</td>
                <td>{{ $user->latestAttendance ? $user->latestAttendance->present_date : '-' }}</td>
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
