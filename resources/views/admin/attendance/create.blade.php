@extends('layouts.admin')

@section('content')
<!-- Page Heading -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.attendances.index') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
    <h1 class="h3 text-gray-800">Create New Attendance</h1>
    <div></div> <!-- Spacer for alignment -->
</div>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ route('admin.attendances.store') }}" method="POST">
            @csrf
            <div class="row">
                <!-- User Selection -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Employee *</label>
                    <select name="user_id" class="form-control" required>
                        <option value="">Select Employee</option>
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }} ({{ $user->nip ?? 'N/A' }})</option>
                        @endforeach
                    </select>
                </div>

                <!-- Date and Time -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Date *</label>
                    <input type="date" class="form-control" name="present_date" 
                           value="{{ old('present_date', now()->format('Y-m-d')) }}" required>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Time *</label>
                    <input type="time" class="form-control" name="present_time" 
                           value="{{ old('present_time', now()->format('H:i')) }}" required>
                </div>

                <!-- Attendance Status -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Status *</label>
                    <select name="description" class="form-control" required>
                        <option value="Hadir">Hadir</option>
                        <option value="Terlambat">Terlambat</option>
                        <option value="Sakit">Sakit</option>
                        <option value="Izin">Izin</option>
                        <option value="Dinas Luar">Dinas Luar</option>
                        <option value="WFH">Work From Home</option>
                    </select>
                </div>

                <!-- Location Information -->
                <div class="col-md-6 mb-3">
                    <label class="form-label">Latitude</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="latitude" id="latitude" 
                               value="{{ old('latitude', '-6.906000') }}" placeholder="e.g., -6.906000">
                        <button type="button" class="btn btn-outline-primary" onclick="getCurrentLocation()">
                            <i class="fas fa-location-arrow"></i> Current
                        </button>
                    </div>
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Longitude</label>
                    <div class="input-group">
                        <input type="text" class="form-control" name="longitude" id="longitude" 
                               value="{{ old('longitude', '107.623400') }}" placeholder="e.g., 107.623400">
                        <button type="button" class="btn btn-outline-primary" onclick="getCurrentLocation()">
                            <i class="fas fa-location-arrow"></i> Current
                        </button>
                    </div>
                </div>

                <!-- Map Picker -->
                <div class="col-12 mb-3">
                    <div id="mapPicker" style="height: 400px; width: 100%; border-radius: 8px;"></div>
                    <small class="text-muted">Click on the map to select location or use current location</small>
                </div>

                <!-- Submit Button -->
                <div class="col-12">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Create Attendance
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
// Initialize map for location picking
let map;
let marker;
let circle;

function initMapPicker() {
    // Default coordinates (school location)
    const defaultLat = parseFloat(document.getElementById('latitude').value) || -6.906000;
    const defaultLng = parseFloat(document.getElementById('longitude').value) || 107.623400;
    
    map = L.map('mapPicker').setView([defaultLat, defaultLng], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Add marker for selected location
    marker = L.marker([defaultLat, defaultLng], {
        draggable: true
    }).addTo(map);
    
    // Add circle for valid radius
    circle = L.circle([-6.906000, 107.623400], {
        color: 'blue',
        fillColor: '#0066cc',
        fillOpacity: 0.1,
        radius: 500
    }).addTo(map).bindPopup('Valid Attendance Radius (500m)');

    // Update form fields when marker is moved
    marker.on('dragend', function(e) {
        const position = marker.getLatLng();
        document.getElementById('latitude').value = position.lat.toFixed(6);
        document.getElementById('longitude').value = position.lng.toFixed(6);
        map.setView(position);
    });

    // Add click event to update marker position
    map.on('click', function(e) {
        marker.setLatLng(e.latlng);
        document.getElementById('latitude').value = e.latlng.lat.toFixed(6);
        document.getElementById('longitude').value = e.latlng.lng.toFixed(6);
    });
}

// Get current location
function getCurrentLocation() {
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                
                document.getElementById('latitude').value = lat.toFixed(6);
                document.getElementById('longitude').value = lng.toFixed(6);
                
                marker.setLatLng([lat, lng]);
                map.setView([lat, lng], 15);
            },
            function(error) {
                alert('Error getting location: ' + error.message);
            }
        );
    } else {
        alert('Geolocation is not supported by this browser.');
    }
}

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initMapPicker();
    
    // Combine date and time fields into present_at before submission
    const form = document.querySelector('form');
    form.addEventListener('submit', function(e) {
        const date = document.querySelector('input[name="present_date"]').value;
        const time = document.querySelector('input[name="present_time"]').value;
        
        // Create a hidden input for present_at
        
        
        form.appendChild(hiddenInput);
    });
});
</script>

<style>
#mapPicker {
    border: 1px solid #ddd;
    margin-bottom: 10px;
}
.leaflet-marker-draggable {
    cursor: move;
}
</style>
@endsection