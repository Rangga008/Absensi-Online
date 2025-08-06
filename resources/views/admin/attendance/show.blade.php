@extends('layouts.admin')

@section('content')
<!-- Page Heading -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.attendances.index') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
    <h1 class="h3 text-gray-800">Attendance Details</h1>
    <div></div> <!-- Spacer for alignment -->
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="row">
            <!-- Basic Information -->
            <div class="col-md-6">
                <h5 class="mb-3">Attendance Information</h5>
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Employee</th>
                        <td>{{ $attendance->user->name ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Date & Time</th>
                        <td>{{ $attendance->present_at->format('l, d F Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>
                            <span class="badge 
                                @if($attendance->description == 'Hadir') badge-success
                                @elseif($attendance->description == 'Terlambat') badge-warning
                                @elseif($attendance->description == 'Sakit') badge-info
                                @elseif($attendance->description == 'Izin') badge-secondary
                                @elseif($attendance->description == 'Dinas Luar') badge-primary
                                @elseif($attendance->description == 'WFH') badge-dark
                                @else badge-light
                                @endif">
                                {{ $attendance->description }}
                            </span>
                        </td>
                    </tr>
                    <tr>
                        <th>IP Address</th>
                        <td>{{ $attendance->ip_address ?? 'N/A' }}</td>
                    </tr>
                    <tr>
                        <th>Device</th>
                        <td>{{ $attendance->user_agent ?? 'N/A' }}</td>
                    </tr>
                </table>
            </div>

            <!-- Location Information -->
            <div class="col-md-6">
                <h5 class="mb-3">Location Information</h5>
                @if($attendance->latitude && $attendance->longitude)
                <table class="table table-bordered">
                    <tr>
                        <th width="40%">Latitude</th>
                        <td>{{ $attendance->latitude }}</td>
                    </tr>
                    <tr>
                        <th>Longitude</th>
                        <td>{{ $attendance->longitude }}</td>
                    </tr>
                    <tr>
                        <th>Distance from School</th>
                        <td>
                            {{ round(App\Models\Attendance::calculateDistance(
            $attendance->latitude,
            $attendance->longitude,
            -6.906000,
            107.623400
        )) }} meters
                        </td>
                    </tr>
                </table>
                @else
                <div class="alert alert-info">No location data available</div>
                @endif

                <!-- Map Preview -->
                <div id="mapPreview" style="height: 300px; width: 100%; border-radius: 8px;"></div>
            </div>
        </div>

        <!-- Action Buttons -->
        @if(session('role_id') == 1)
        <div class="mt-4">
            <a href="{{ route('admin.attendances.edit', $attendance->id) }}" class="btn btn-primary">
                <i class="fas fa-edit"></i> Edit
            </a>
            <form action="{{ route('admin.attendances.destroy', $attendance->id) }}" method="POST" class="d-inline">
                @csrf
                @method('DELETE')
                <button type="submit" onclick="return confirm('Are you sure you want to delete this attendance?')" 
                        class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </form>
        </div>
        @endif
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@if($attendance->latitude && $attendance->longitude)
<script>
// Initialize map for location preview
document.addEventListener('DOMContentLoaded', function() {
    const map = L.map('mapPreview').setView([{{ $attendance->latitude }}, {{ $attendance->longitude }}], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // School marker
    const schoolIcon = L.divIcon({
        html: '<i class="fas fa-school" style="color: #e63946; font-size: 24px;"></i>',
        iconSize: [24, 24],
        className: 'custom-div-icon'
    });
    
    L.marker([-6.906000, 107.623400], {icon: schoolIcon})
        .addTo(map)
        .bindPopup('<b>SMKN 2 Bandung</b>');

    // Attendance location marker
    const userIcon = L.divIcon({
        html: '<i class="fas fa-map-marker-alt" style="color: #007bff; font-size: 24px;"></i>',
        iconSize: [24, 24],
        className: 'custom-div-icon'
    });
    
    L.marker([{{ $attendance->latitude }}, {{ $attendance->longitude }}], {icon: userIcon})
        .addTo(map)
        .bindPopup('Attendance Location');

    // Add line connecting the two points
    const line = L.polyline([
        [-6.906000, 107.623400],
        [{{ $attendance->latitude }}, {{ $attendance->longitude }}]
    ], {color: 'red'}).addTo(map);

    // Add radius circle (500m)
    L.circle([-6.906000, 107.623400], {
        color: 'blue',
        fillColor: '#0066cc',
        fillOpacity: 0.1,
        radius: 500
    }).addTo(map).bindPopup('Valid Attendance Radius (500m)');
});
</script>
@endif

<style>
#mapPreview {
    border: 1px solid #ddd;
}
.custom-div-icon {
    background: transparent;
    border: none;
}
</style>
@endsection