<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pengajuan Izin - {{ $concession->user->name }}</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
            margin: 20px;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
        }
        .header img {
            max-width: 100%;
            max-height: 120px;
            object-fit: contain;
            margin-bottom: 10px;
        }
        .content {
            margin-top: 20px;
        }
        .content h2 {
            text-align: center;
            margin-bottom: 20px;
            font-weight: bold;
            font-size: 18px;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .info-table th, .info-table td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
            vertical-align: top;
        }
        .info-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            width: 30%;
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
            $hasValidKopsurat = $kopsuratPath && file_exists(public_path($kopsuratPath));
        @endphp
        @if($hasValidKopsurat)
            <img src="data:image/jpeg;base64,{{ base64_encode(file_get_contents(public_path($kopsuratPath))) }}" alt="Kopsurat">
        @endif
        <h1>Pengajuan Izin</h1>
    </div>

    <div class="content">
        <h2>Detail Pengajuan Izin</h2>
        <table class="info-table">
            <tr>
                <th>Nama Karyawan</th>
                <td>{{ $concession->user->name }}</td>
            </tr>
            <tr>
                <th>Email</th>
                <td>{{ $concession->user->email }}</td>
            </tr>
            <tr>
                <th>Jenis Izin</th>
                <td>{{ ucfirst($concession->reason) }}</td>
            </tr>
            <tr>
                <th>Tanggal Mulai</th>
                <td>{{ $concession->formatted_start_date }}</td>
            </tr>
            <tr>
                <th>Tanggal Selesai</th>
                <td>{{ $concession->formatted_end_date }}</td>
            </tr>
            <tr>
                <th>Durasi</th>
                <td>{{ $concession->duration }} hari</td>
            </tr>
            <tr>
                <th>Alasan/Keterangan</th>
                <td>{{ $concession->description }}</td>
            </tr>
            <tr>
                <th>Status</th>
                <td>{{ ucfirst($concession->status) }}</td>
            </tr>
            @if($concession->file_path)
            <tr>
                <th>Bukti Izin</th>
                <td>
                    @php
                        $fileExt = pathinfo($concession->file_path, PATHINFO_EXTENSION);
                        $hasValidFile = file_exists(public_path($concession->file_path));
                    @endphp
                    @if($hasValidFile && in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png']))
                        <img src="data:image/{{ strtolower($fileExt) }};base64,{{ base64_encode(file_get_contents(public_path($concession->file_path))) }}" alt="Bukti Izin" style="max-width: 200px; max-height: 200px; object-fit: contain;">
                    @elseif($hasValidFile)
                        <p>File terlampir: {{ basename($concession->file_path) }}</p>
                    @else
                        <p>File tidak ditemukan</p>
                    @endif
                </td>
            </tr>
            @endif
            @if($concession->approved_at)
            <tr>
                <th>Disetujui/Ditolak Pada</th>
                <td>{{ $concession->formatted_approved_at }}</td>
            </tr>
            @endif
            @if($concession->approver)
            <tr>
                <th>Disetujui Oleh</th>
                <td>{{ $concession->approver->name }}</td>
            </tr>
            @endif
        </table>
    </div>

    <div class="footer">
        <p>Dokumen ini dihasilkan secara otomatis oleh Sistem Absensi Online</p>
    </div>
</body>
</html>
