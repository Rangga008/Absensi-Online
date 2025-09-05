@extends('layouts.admin')

@section('title', 'Attendance Details')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h3 class="card-title">Attendance Details</h3>
                    <div class="card-tools">
                        <a href="{{ url('admin/users/' . $attendance->user_id . '/attendances') }}" class="btn btn-primary">
                            <i class="fas fa-arrow-left"></i> Back
                        </a>
                    </div>
                </div>
                
                <div class="card-body">
                    <!-- Photo Section at the Top -->
                    @if($attendance->photo_path && Storage::disk('public')->exists($attendance->photo_path))
                    <div class="row mb-4">
                        <div class="col-12">
                            <div class="card">
                                <div class="card-header bg-info text-white">
                                    <h5 class="mb-0">
                                        <i class="fas fa-camera mr-2"></i>Attendance Photo
                                    </h5>
                                </div>
                                <div class="card-body text-center">
                                    @php
                                        $imageData = base64_encode(Storage::disk('public')->get($attendance->photo_path));
                                        $src = 'data:image/jpeg;base64,'.$imageData;
                                    @endphp
                                    
                                    
                                    <div class="photo-container mb-3">
                                        <img src="{{ $src }}" 
                                            alt="Attendance Photo" 
                                            class="img-fluid rounded shadow" 
                                            style="max-height: 500px; border: 1px solid #ddd;">
                                    </div>
                                    
                                    <div class="photo-actions">
                                        <button class="btn btn-primary btn-sm mr-2" onclick="window.open('{{ $src }}', '_blank')">
                                            <i class="fas fa-expand mr-1"></i> Full View
                                        </button>
                                        <button class="btn btn-info btn-sm" onclick="downloadBase64Image('{{ $src }}', 'attendance_photo_{{ $attendance->id }}.jpg')">
                                            <i class="fas fa-download mr-1"></i> Download
                                        </button>
                                    </div>
                                    
                                    <div class="mt-2 text-muted small">
                                        <i class="fas fa-info-circle"></i> Photo taken at {{ $attendance->present_at->format('H:i:s') }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endif

                    <div class="row">
                        <div class="col-md-8">
                            <div class="card mb-4">
                                <div class="card-header bg-secondary text-white">
                                    <h4 class="card-title mb-0">Attendance Information</h4>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered table-striped">
                                            <tr>
                                                <th width="30%">User</th>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="avatar bg-primary text-white rounded-circle mr-2" 
                                                            style="width: 40px; height: 40px; display: flex; align-items: center; justify-content: center;">
                                                            {{ substr($attendance->user->name, 0, 1) }}
                                                        </div>
                                                        <div>
                                                            <strong>{{ $attendance->user->name }}</strong><br>
                                                            <small class="text-muted">{{ $attendance->user->role->role_name ?? 'N/A' }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            
                                            <tr>
                                                <th>Date & Time</th>
                                                <td>
                                                    {{ $attendance->present_at->translatedFormat('l, d F Y') }}<br>
                                                    <span class="text-muted">{{ $attendance->present_at->format('H:i:s') }}</span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Status</th>
                                                <td>
                                                    @php
                                                        $badgeClass = [
                                                            'Hadir' => 'success',
                                                            'Terlambat' => 'warning',
                                                            'Sakit' => 'info',
                                                            'Izin' => 'secondary',
                                                            'Dinas Luar' => 'primary',
                                                            'WFH' => 'dark'
                                                        ][$attendance->description] ?? 'light';
                                                    @endphp
                                                    <span class="badge badge-{{ $badgeClass }} badge-pill py-2 px-3">
                                                        <i class="fas @switch($attendance->description)
                                                            @case('Hadir') fa-check-circle @break
                                                            @case('Terlambat') fa-clock @break
                                                            @case('Sakit') fa-procedures @break
                                                            @case('Izin') fa-envelope @break
                                                            @case('Dinas Luar') fa-briefcase @break
                                                            @case('WFH') fa-home @break
                                                        @endswitch mr-1"></i>
                                                        {{ $attendance->description }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Location</th>
                                                <td>
                                                    <div class="d-flex justify-content-between">
                                                        <div>
                                                            <strong>Coordinates:</strong><br>
                                                            <small class="text-muted">Lat:</small> {{ number_format($attendance->latitude, 6) ?? 'N/A' }}<br>
                                                            <small class="text-muted">Lng:</small> {{ number_format($attendance->longitude, 6) ?? 'N/A' }}
                                                        </div>
                                                        <div class="text-right">
                                                            <strong>Distance:</strong><br>
                                                            @php
                                                                $maxDistance = setting('max_distance', 500);
                                                                $companyName = setting('company_name', 'sekolah');
                                                            @endphp
                                                            @if($attendance->distance)
                                                                @if($attendance->distance <= $maxDistance)
                                                                    <span class="badge badge-success">
                                                                        {{ $attendance->distance }} meters
                                                                    </span>
                                                                @else
                                                                    <span class="badge badge-danger">
                                                                        {{ $attendance->distance }} meters
                                                                    </span>
                                                                    <div class="text-danger small mt-1">
                                                                        (Outside {{ $maxDistance }}m radius from {{ $companyName }})
                                                                    </div>
                                                                @endif
                                                            @else
                                                                N/A
                                                            @endif
                                                        </div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Device Information</th>
                                                <td>
                                                    <div class="mb-2">
                                                        <strong>IP Address:</strong><br>
                                                        <code>{{ $attendance->ip_address ?? 'N/A' }}</code>
                                                    </div>
                                                    <div>
                                                        <strong>User Agent:</strong><br>
                                                        <small class="text-muted">{{ $attendance->user_agent ?? 'N/A' }}</small>
                                                    </div>
                                                </td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="col-md-4">
                            <div class="card mb-4">
                                <div class="card-header bg-success text-white">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-map-marked-alt mr-2"></i>Location Map
                                    </h4>
                                </div>
                                <div class="card-body p-0">
                                    @if($attendance->latitude && $attendance->longitude)
                                        <div id="map" style="height: 400px; width: 100%;"></div>
                                        <div class="p-3 border-top">
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <span class="badge badge-danger mr-2">●</span>
                                                    <small>{{ setting('company_name', 'SMKN 2 Bandung') }}</small>
                                                </div>
                                                <div>
                                                    <span class="badge badge-primary mr-2">●</span>
                                                    <small>Attendance Location</small>
                                                </div>
                                                <a href="https://www.google.com/maps?q={{ $attendance->latitude }},{{ $attendance->longitude }}" 
                                                target="_blank" 
                                                class="btn btn-sm btn-outline-success">
                                                    <i class="fas fa-external-link-alt mr-1"></i> Google Maps
                                                </a>
                                            </div>
                                        </div>
                                    @else
                                        <div class="alert alert-warning m-3">
                                            <i class="fas fa-exclamation-triangle mr-2"></i>
                                            No location data available for this attendance
                                        </div>
                                    @endif
                                </div>
                            </div>
                            
                            <div class="card">
                                <div class="card-header bg-warning text-dark">
                                    <h4 class="card-title mb-0">
                                        <i class="fas fa-history mr-2"></i>Recent Activities
                                    </h4>
                                </div>
                                <div class="card-body">
                                    @if($recentAttendances->count() > 0)
                                        <ul class="list-group list-group-flush">
                                            @foreach($recentAttendances as $recent)
                                                <li class="list-group-item d-flex justify-content-between align-items-center py-2 px-0">
                                                    <div>
                                                        <small class="text-muted">{{ $recent->present_at->format('d M') }}</small><br>
                                                        <strong>{{ $recent->description }}</strong>
                                                    </div>
                                                    <span class="badge badge-light">
                                                        {{ $recent->present_at->format('H:i') }}
                                                    </span>
                                                </li>
                                            @endforeach
                                        </ul>
                                    @else
                                        <div class="text-center text-muted py-3">
                                            <i class="fas fa-info-circle fa-2x mb-2"></i>
                                            <p>No recent attendance records</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="card-footer bg-light">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <a href="{{ route('admin.attendances.edit', $attendance->id) }}" 
                            class="btn btn-warning">
                                <i class="fas fa-edit mr-1"></i> Edit Record
                            </a>
                        </div>
                        <div>
                            <form action="{{ route('admin.attendances.destroy', $attendance->id) }}" 
                                method="POST" 
                                onsubmit="return confirm('Are you sure you want to delete this attendance record? This action cannot be undone.')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger">
                                    <i class="fas fa-trash-alt mr-1"></i> Delete
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@if($attendance->latitude && $attendance->longitude)
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get office coordinates from settings
        const officeLat = {{ setting('office_lat', -6.906000) }};
        const officeLng = {{ setting('office_lng', 107.623400) }};
        const maxDistance = {{ setting('max_distance', 500) }};
        const companyName = "{{ setting('company_name', 'SMKN 2 Bandung') }}";
        
        const attendanceLat = {{ $attendance->latitude }};
        const attendanceLng = {{ $attendance->longitude }};
        
        // Initialize map
        const map = L.map('map').setView([
            (officeLat + attendanceLat) / 2,
            (officeLng + attendanceLng) / 2
        ], 15);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);

        // Office marker with custom icon
        const officeIcon = L.divIcon({
            html: '<div style="background-color: #e63946; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; color: white;"><i class="fas fa-building"></i></div>',
            iconSize: [24, 24],
            className: 'custom-div-icon'
        });
        
        const officeMarker = L.marker([officeLat, officeLng], {
            icon: officeIcon
        }).addTo(map).bindPopup(`
            <b>${companyName}</b><br>
            <small>Office Location</small>
        `);

        // Attendance location marker with custom icon
        const userIcon = L.divIcon({
            html: '<div style="background-color: #007bff; border-radius: 50%; width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; color: white;"><i class="fas fa-user"></i></div>',
            iconSize: [24, 24],
            className: 'custom-div-icon'
        });
        
        const userMarker = L.marker([attendanceLat, attendanceLng], {
            icon: userIcon
        }).addTo(map).bindPopup(`
            <b>Attendance Location</b><br>
            <small>Recorded at {{ $attendance->present_at->format('H:i:s') }}</small>
        `);

        // Add line connecting the two points
        const line = L.polyline([
            [officeLat, officeLng],
            [attendanceLat, attendanceLng]
        ], {
            color: '#6c757d',
            dashArray: '5, 5',
            weight: 2
        }).addTo(map);

        // Add radius circle using configured max distance
        const circle = L.circle([officeLat, officeLng], {
            color: '#28a745',
            fillColor: '#28a745',
            fillOpacity: 0.1,
            radius: maxDistance
        }).addTo(map).bindPopup(`Valid Attendance Radius (${maxDistance}m)`);

        // Add distance label
        const distance = {{ $attendance->distance ?? 0 }};
        const midpoint = {
            lat: (officeLat + attendanceLat) / 2,
            lng: (officeLng + attendanceLng) / 2
        };
        
        L.marker(midpoint, {
            icon: L.divIcon({
                html: `<div style="background: white; padding: 2px 6px; border-radius: 10px; border: 1px solid #6c757d; font-weight: bold;">${distance}m</div>`,
                iconSize: [60, 20],
                className: 'distance-label'
            })
        }).addTo(map);
    });

    // Function to download base64 image
    function downloadBase64Image(base64, filename) {
        const link = document.createElement('a');
        link.href = base64;
        link.download = filename;
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);
    }
</script>

<style>
.custom-div-icon {
    background: transparent;
    border: none;
}
.distance-label {
    background: transparent;
    border: none;
    pointer-events: none;
}
.photo-container {
    transition: all 0.3s ease;
}
.photo-container:hover {
    transform: translateY(-2px);
}
.photo-actions {
    margin-top: 15px;
}
</style>
@endif
@endsection