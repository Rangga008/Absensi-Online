<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi - {{ $user->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            color: #333;
            font-size: 10px; /* Reduce font size for better fit */
        }
        .header {
            text-align: center;
            margin-bottom: 20px; /* Reduced margin */
            border-bottom: 1px solid #333; /* Thinner border */
            padding-bottom: 10px; /* Reduced padding */
        }
        .header h1 {
            margin: 0;
            font-size: 18px; /* Smaller font size */
            color: #333;
        }
        .header p {
            margin: 3px 0;
            font-size: 10px; /* Smaller font size */
            color: #666;
        }
        .user-info {
            margin-bottom: 15px; /* Reduced margin */
        }
        .user-info table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10px; /* Smaller font size */
        }
        .user-info td {
            padding: 3px; /* Reduced padding */
            border: 1px solid #ddd;
        }
        .user-info .label {
            font-weight: bold;
            background-color: #f8f9fa;
            width: 120px; /* Reduced width */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px; /* Reduced margin */
            font-size: 10px; /* Smaller font size */
            table-layout: fixed; /* Fix column widths */
        }
        th, td {
            border: 1px solid #ddd;
            padding: 4px; /* Reduced padding */
            text-align: left;
            font-size: 10px; /* Smaller font size */
            white-space: nowrap; /* Prevent text wrapping */
            overflow: hidden;
            text-overflow: ellipsis;
        }
        th {
            background-color: #f8f9fa;
            font-weight: bold;
        }
        .status-badge {
            padding: 2px 5px; /* Reduced padding */
            border-radius: 4px;
            font-size: 9px; /* Smaller font size */
            font-weight: bold;
        }
        .status-hadir { background-color: #d4edda; color: #155724; }
        .status-terlambat { background-color: #fff3cd; color: #856404; }
        .status-sakit { background-color: #f8d7da; color: #721c24; }
        .status-izin { background-color: #e2e3e5; color: #383d41; }
        .status-dinas-luar { background-color: #d1ecf1; color: #0c5460; }
        .status-wfh { background-color: #d4edda; color: #155724; }
        .footer {
            margin-top: 20px; /* Reduced margin */
            text-align: center;
            font-size: 9px; /* Smaller font size */
            color: #666;
        }
        .summary {
            margin-bottom: 15px; /* Reduced margin */
            background-color: #f8f9fa;
            padding: 10px; /* Reduced padding */
            border-radius: 5px;
            font-size: 10px; /* Smaller font size */
        }
        .summary h3 {
            margin: 0 0 8px 0; /* Reduced margin */
            font-size: 14px; /* Smaller font size */
        }
        .summary-stats {
            display: table;
            width: 100%;
        }
        .summary-stats div {
            display: table-cell;
            text-align: center;
            padding: 3px; /* Reduced padding */
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

        <h1>Laporan Absensi</h1>
        <p>Dihasilkan pada {{ now()->format('d F Y H:i:s') }}</p>
    </div>

    <div class="user-info">
        <table>
            <tr>
                <td class="label">Nama Karyawan:</td>
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
                <td class="label">Periode Laporan:</td>
                <td>Seluruh Waktu</td>
            </tr>
            <tr>
                <td class="label">Total Record:</td>
                <td>{{ $attendances->count() }}</td>
            </tr>
        </table>
    </div>

    <div class="summary">
        <h3>Ringkasan Absensi</h3>
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
                <th width="15%">Tanggal</th>
                <th width="10%">Waktu</th>
                <th width="15%">Status</th>
                <th width="15%">Status Checkout</th>
                <th width="15%">Waktu Checkout</th>
                <th width="15%">Durasi Kerja</th>
                <th width="10%">Jarak (m)</th>
                <th width="15%">Alamat IP</th>
            </tr>
        </thead>
        <tbody>
            @forelse($attendances as $index => $attendance)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td>{{ $attendance->display_date }}</td>
                <td>{{ $attendance->present_at ? $attendance->present_at->format('H:i:s') : '-' }}</td>
                <td>
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $attendance->description)) }}">
                        {{ $attendance->description }}
                    </span>
                </td>
                <td>{{ $attendance->checkout_status ?? 'Belum Keluar' }}</td>
                <td>{{ $attendance->checkout_time_formatted ?? '-' }}</td>
                <td>{{ $attendance->work_duration_formatted ?? '-' }}</td>
                <td>{{ $attendance->distance ?: '-' }}</td>
                <td>{{ $attendance->ip_address ?: '-' }}</td>
            </tr>
            @empty
            <tr>
                <td colspan="9" style="text-align: center;">Tidak ada data absensi ditemukan</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="footer">
        <p>Laporan ini dihasilkan secara otomatis oleh Sistem Absensi Online</p>
    </div>
</body>
</html>
