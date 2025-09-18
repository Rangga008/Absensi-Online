<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Laporan Absensi - {{ $attendance->user->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 20px;
            color: #333;
            line-height: 1.4;
        }
        .header {
            text-align: center;
            border-bottom: 3px solid #007bff;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header h1 {
            color: #007bff;
            margin: 0;
            font-size: 28px;
            font-weight: bold;
        }
        .header p {
            margin: 5px 0;
            color: #666;
            font-size: 14px;
        }
        .company-info {
            text-align: center;
            margin-bottom: 20px;
            font-size: 16px;
            font-weight: bold;
        }
        .info-section {
            margin-bottom: 25px;
            page-break-inside: avoid;
        }
        .section-title {
            color: #007bff;
            font-size: 18px;
            font-weight: bold;
            border-bottom: 2px solid #007bff;
            padding-bottom: 8px;
            margin-bottom: 15px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
            font-size: 12px;
        }
        .info-table th,
        .info-table td {
            padding: 10px 12px;
            text-align: left;
            border: 1px solid #ddd;
            vertical-align: top;
        }
        .info-table th {
            background-color: #f8f9fa;
            font-weight: bold;
            width: 35%;
            font-size: 13px;
        }
        .status-badge {
            padding: 6px 12px;
            border-radius: 6px;
            color: white;
            font-size: 12px;
            font-weight: bold;
            display: inline-block;
        }
        .status-hadir { background-color: #28a745; }
        .status-terlambat { background-color: #ffc107; color: #000; }
        .status-sakit { background-color: #17a2b8; }
        .status-izin { background-color: #6c757d; }
        .status-dinas-luar { background-color: #007bff; }
        .status-wfh { background-color: #343a40; }
        .photo-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .photo-container {
            display: flex;
            justify-content: space-between;
            margin-bottom: 15px;
        }
        .photo-box {
            width: 48%;
            text-align: center;
            border: 1px solid #ddd;
            padding: 10px;
            border-radius: 8px;
            background-color: #f9f9f9;
        }
        .photo-box img {
            max-width: 100%;
            max-height: 180px;
            border: 1px solid #ccc;
            border-radius: 4px;
        }
        .photo-label {
            margin-top: 8px;
            font-size: 11px;
            color: #666;
            font-weight: bold;
        }
        .location-section {
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .location-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 6px;
            border: 1px solid #dee2e6;
        }
        .location-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
        }
        .location-item {
            flex: 1;
        }
        .distance-indicator {
            font-size: 16px;
            font-weight: bold;
            color: {{ $attendance->distance <= setting('max_distance', 500) ? '#28a745' : '#dc3545' }};
        }
        .footer {
            margin-top: 40px;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 2px solid #007bff;
            padding-top: 20px;
        }
        .work-duration {
            background-color: #e9ecef;
            padding: 8px 12px;
            border-radius: 4px;
            margin-top: 5px;
            font-weight: bold;
            color: #495057;
        }
        .coordinates {
            font-family: 'Courier New', monospace;
            font-size: 11px;
            background-color: #f8f9fa;
            padding: 4px 8px;
            border-radius: 3px;
            margin-top: 5px;
        }
        .signature-section {
            margin-top: 40px;
            page-break-inside: avoid;
        }
        .signature-box {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }
        .signature-item {
            text-align: center;
            width: 30%;
        }
        .signature-line {
            border-bottom: 1px solid #000;
            margin-bottom: 40px;
            margin-top: 40px;
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

        <div class="company-info">
            {{ setting('company_name', 'SMKN 2 Bandung') }}
        </div>
        <h1>LAPORAN ABSENSI INDIVIDU</h1>
        <p>Laporan Detail Kehadiran Karyawan</p>
        <p><strong>Dibuat pada:</strong> {{ now()->translatedFormat('l, d F Y H:i:s') }}</p>
    </div>

    <div class="info-section">
        <h2 class="section-title">📋 Informasi Karyawan</h2>
        <table class="info-table">
            <tr>
                <th>Nama Karyawan</th>
                <td>{{ $attendance->user->name }}</td>
            </tr>
            <tr>
                <th>Jabatan/Posisi</th>
                <td>{{ $attendance->user->role->role_name ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>ID Karyawan</th>
                <td>{{ $attendance->user->id }}</td>
            </tr>
        </table>
    </div>

    <div class="info-section">
        <h2 class="section-title">⏰ Detail Absensi</h2>
        <table class="info-table">
            <tr>
                <th>Tanggal Absensi</th>
                <td>{{ $attendance->present_at->translatedFormat('l, d F Y') }}</td>
            </tr>
            <tr>
                <th>Waktu Check-in</th>
                <td>{{ $attendance->present_at->format('H:i:s') }}</td>
            </tr>
            <tr>
                <th>Status Kehadiran</th>
                <td>
                    <span class="status-badge status-{{ strtolower(str_replace(' ', '-', $attendance->description)) }}">
                        {{ $attendance->description }}
                    </span>
                </td>
            </tr>
            @if($attendance->checkout_at)
            <tr>
                <th>Waktu Check-out</th>
                <td>{{ $attendance->checkout_at->format('H:i:s') }}</td>
            </tr>
            <tr>
                <th>Durasi Kerja</th>
                <td>
                    <div class="work-duration">
                        {{ $attendance->work_duration_formatted ?: 'N/A' }}
                    </div>
                </td>
            </tr>
            @endif
        </table>
    </div>

    @if($attendance->latitude && $attendance->longitude)
    <div class="location-section">
        <h2 class="section-title">📍 Informasi Lokasi</h2>
        <div class="location-info">
            <div class="location-row">
                <div class="location-item">
                    <strong>Koordinat Check-in:</strong><br>
                    <div class="coordinates">
                        Lat: {{ number_format($attendance->latitude, 6) }}<br>
                        Lng: {{ number_format($attendance->longitude, 6) }}
                    </div>
                </div>
                @if($attendance->distance)
                <div class="location-item" style="text-align: right;">
                    <strong>Jarak dari Kantor:</strong><br>
                    <span class="distance-indicator">
                        {{ $attendance->distance }} meter
                    </span>
                    @if($attendance->distance > setting('max_distance', 500))
                    <br><small style="color: #dc3545;">(Di luar radius yang diizinkan)</small>
                    @endif
                </div>
                @endif
            </div>

            @if($attendance->checkout_latitude && $attendance->checkout_longitude)
            <div style="border-top: 1px solid #dee2e6; margin: 15px 0; padding-top: 15px;">
                <div class="location-row">
                    <div class="location-item">
                        <strong>Koordinat Check-out:</strong><br>
                        <div class="coordinates">
                            Lat: {{ number_format($attendance->checkout_latitude, 6) }}<br>
                            Lng: {{ number_format($attendance->checkout_longitude, 6) }}
                        </div>
                    </div>
                    @if($attendance->checkout_distance)
                    <div class="location-item" style="text-align: right;">
                        <strong>Jarak Check-out:</strong><br>
                        <span style="font-size: 14px; font-weight: bold; color: {{ $attendance->checkout_distance <= setting('max_distance', 500) ? '#28a745' : '#dc3545' }};">
                            {{ $attendance->checkout_distance }} meter
                        </span>
                        @if($attendance->checkout_distance > setting('max_distance', 500))
                        <br><small style="color: #dc3545;">(Di luar radius yang diizinkan)</small>
                        @endif
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    @if(($attendance->photo_path && \Storage::disk('public')->exists($attendance->photo_path)) || ($attendance->checkout_photo_path && \Storage::disk('public')->exists($attendance->checkout_photo_path)))
    <div class="photo-section">
        <h2 class="section-title">📷 Foto Absensi</h2>

        <div class="photo-container">
            @if($attendance->photo_path && \Storage::disk('public')->exists($attendance->photo_path))
            <div class="photo-box">
                <img src="data:image/jpeg;base64,{{ base64_encode(\Storage::disk('public')->get($attendance->photo_path)) }}"
                     alt="Foto Check-in">
                <div class="photo-label">
                    <strong>Foto Check-in</strong><br>
                    Diambil pada {{ $attendance->present_at->format('H:i:s') }}
                </div>
            </div>
            @endif

            @if($attendance->checkout_photo_path && \Storage::disk('public')->exists($attendance->checkout_photo_path))
            <div class="photo-box">
                <img src="data:image/jpeg;base64,{{ base64_encode(\Storage::disk('public')->get($attendance->checkout_photo_path)) }}"
                     alt="Foto Check-out">
                <div class="photo-label">
                    <strong>Foto Check-out</strong><br>
                    Diambil pada {{ $attendance->checkout_at->format('H:i:s') }}
                </div>
            </div>
            @endif
        </div>
    </div>
    @endif

    <div class="info-section">
        <h2 class="section-title">🔧 Informasi Teknis</h2>
        <table class="info-table">
            <tr>
                <th>Alamat IP</th>
                <td>{{ $attendance->ip_address ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>User Agent</th>
                <td style="font-size: 10px; font-family: 'Courier New', monospace;">{{ $attendance->user_agent ?? 'N/A' }}</td>
            </tr>
            <tr>
                <th>ID Record</th>
                <td>{{ $attendance->id }}</td>
            </tr>
        </table>
    </div>

    <div class="signature-section">
        <h2 class="section-title">✍️ Persetujuan</h2>
        <div class="signature-box">
            <div class="signature-item">
                <div class="signature-line"></div>
                <p><strong>Karyawan</strong></p>
                <p>{{ $attendance->user->name }}</p>
            </div>
            <div class="signature-item">
                <div class="signature-line"></div>
                <p><strong>Admin HR</strong></p>
                <p>&nbsp;</p>
            </div>
            <div class="signature-item">
                <div class="signature-line"></div>
                <p><strong>Kepala Sekolah</strong></p>
                <p>&nbsp;</p>
            </div>
        </div>
    </div>

    <div class="footer">
        <p>Laporan ini dibuat secara otomatis oleh Sistem Manajemen Absensi</p>
        <p>{{ setting('company_name', 'SMKN 2 Bandung') }} - {{ now()->format('Y') }}</p>
        <p style="font-size: 9px; margin-top: 10px; color: #999;">
            Dokumen ini bersifat resmi dan dapat digunakan sebagai bukti kehadiran karyawan
        </p>
    </div>
</body>
</html>
