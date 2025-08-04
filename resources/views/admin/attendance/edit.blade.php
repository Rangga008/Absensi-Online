@extends('layouts.admin')

@section('content')
<!-- Page Heading -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ url('attendance') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
    <h1 class="h3 text-gray-800">Edit Attendance</h1>
    <div></div> <!-- Spacer for alignment -->
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url('attendance') }}/{{ $attendance->id }}" method="POST">
            @method('PUT')
            @csrf
            <div class="row">
                <!-- User Selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Employee</label>
                    <select name="user_id" class="form-control" required>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $attendance->user_id == $user->id ? 'selected' : '' }}>
                            {{ $user->name }} ({{ $user->nip ?? 'N/A' }})
                        </option>
                        @endforeach
                    </select>
                </div>

                <!-- Date and Time -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date</label>
                    <input type="date" class="form-control" name="present_date" 
                           value="{{ $attendance->present_at->format('Y-m-d') }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Time</label>
                    <input type="time" class="form-control" name="present_time" 
                           value="{{ $attendance->present_at->format('H:i') }}" required>
                </div>

                <!-- Attendance Status -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status</label>
                    <select name="description" class="form-control" required>
                        <option value="Hadir" {{ $attendance->description == 'Hadir' ? 'selected' : '' }}>Hadir</option>
                        <option value="Terlambat" {{ $attendance->description == 'Terlambat' ? 'selected' : '' }}>Terlambat</option>
                        <option value="Sakit" {{ $attendance->description == 'Sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="Izin" {{ $attendance->description == 'Izin' ? 'selected' : '' }}>Izin</option>
                        <option value="Dinas Luar" {{ $attendance->description == 'Dinas Luar' ? 'selected' : '' }}>Dinas Luar</option>
                        <option value="WFH" {{ $attendance->description == 'WFH' ? 'selected' : '' }}>Work From Home</option>
                    </select>
                </div>

                <!-- Location Information -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Latitude</label>
                    <input type="text" class="form-control" name="latitude" 
                           value="{{ $attendance->latitude }}" placeholder="e.g., -6.906000">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Longitude</label>
                    <input type="text" class="form-control" name="longitude" 
                           value="{{ $attendance->longitude }}" placeholder="e.g., 107.623400">
                </div>

                <!-- Map Preview -->
                <div class="col-12 mb-3">
                    <div id="mapPreview" style="height: 300px; width: 100%; border-radius: 8px;"></div>
                    <small class="text-muted">Location preview (SMKN 2 Bandung shown as red marker)</small>
                </div>

                <!-- Submit Button -->
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Update Attendance
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Initialize map for location preview
function initMapPreview() {
    // Default coordinates (use attendance location or school location as fallback)
    const defaultLat = {{ $attendance->latitude ?? -6.906000 }};
    const defaultLng = {{ $attendance->longitude ?? 107.623400 }};
    
    const map = L.map('mapPreview').setView([defaultLat, defaultLng], 15);
    
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

    // Add attendance location marker if available
    @if($attendance->latitude && $attendance->longitude)
    const userIcon = L.divIcon({
        html: '<i class="fas fa-map-marker-alt" style="color: #007bff; font-size: 24px;"></i>',
        iconSize: [24, 24],
        className: 'custom-div-icon'
    });
    
    L.marker([{{ $attendance->latitude }}, {{ $attendance->longitude }}], {icon: userIcon})
        .addTo(map)
        .bindPopup('Attendance Location');
    @endif

    // Add radius circle (500m)
    L.circle([-6.906000, 107.623400], {
        color: 'blue',
        fillColor: '#0066cc',
        fillOpacity: 0.1,
        radius: 500
    }).addTo(map).bindPopup('Valid Attendance Radius (500m)');
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initMapPreview();
    
    // Combine date and time fields into present_at before submission
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const date = document.querySelector('input[name="present_date"]').value;
        const time = document.querySelector('input[name="present_time"]').value;
        
        // Create a hidden input for present_at
        const hiddenInput = document.createElement('input');
        hiddenInput.type = 'hidden';
        hiddenInput.name = 'present_at';
        hiddenInput.value = `${date} ${time}:00`;
        
        form.appendChild(hiddenInput);
    });
});
</script>

<style>
.custom-div-icon {
    background: transparent;
    border: none;
}
#mapPreview {
    border: 1px solid #ddd;
}
</style>

@endsection