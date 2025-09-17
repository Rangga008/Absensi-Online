@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Attendance Records for {{ $user->name }}</h1>
    <div>
        <a href="{{ route('admin.attendances.exportUserPdf', $user->id) }}" class="btn btn-danger mr-2" target="_blank">
            <i class="fas fa-file-pdf"></i> Export PDF
        </a>
        <a href="{{ route('admin.attendances.exportUserExcel', $user->id) }}" class="btn btn-success mr-2" target="_blank">
            <i class="fas fa-file-excel"></i> Export Excel
        </a>
        <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left mr-2"></i>Back to All Users
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Attendance History</h6>
        <a href="{{ route('admin.attendances.create', ['user_id' => $user->id]) }}" 
           class="btn btn-primary btn-sm">
            <i class="fas fa-plus mr-2"></i>Add Record
        </a>
    </div>
    
    <div class="card-body">
        @if($attendances->isEmpty())
            <div class="alert alert-info">
                No attendance records found for this user.
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Time</th>
                            <th>Status</th>
                            <th>Actions</th>
                            <th>Checkout</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($attendances as $attendance)
                        <tr>
                            <td>{{ $attendance->present_at->format('M d, Y') }}</td>
                            <td>{{ $attendance->present_at->format('H:i') }}</td>
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
                                <span class="badge badge-{{ $badgeClass }}">
                                    {{ $attendance->description }}
                                </span>
                            </td>
                            
                            <td>
                                <a href="{{ route('admin.attendances.edit', $attendance->id) }}" 
                                class="btn btn-sm btn-primary">
                                    <i class="fas fa-edit"></i>
                                </a>
                            
                                <a href="{{ route('admin.attendances.show', $attendance->id) }}" 
                                class="btn btn-sm btn-warning">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </td>
                            <td>
                                @if($attendance->hasCheckedOut())
                                    <span class="badge badge-success">Sudah Keluar</span><br>
                                    <small>Checkout: {{ $attendance->formatted_checkout_time }}</small><br>
                                    <small>Durasi Kerja: {{ $attendance->work_duration_formatted ?: 'N/A' }}</small>
                                @else
                                    <span class="badge badge-danger">Belum Keluar</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                
                <div class="d-flex justify-content-center mt-3">
                    {{ $attendances->links() }}
                </div>
            </div>
        @endif
    </div>
</div>

<!-- Add this modal at the bottom of your view -->
<!-- Modal -->
<div class="modal fade" id="mapModal" tabindex="-1" role="dialog" aria-labelledby="mapModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="mapModalLabel">Attendance Location</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="map"></div>
                <div class="mt-3">
                    <p><strong>Distance from {{ strtoupper(setting('company_name', 'SMK NEGERI 2 BANDUNG')) }}:</strong> <span id="distanceDisplay"></span> meters</p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>
@section('scripts')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.7.1/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.7.1/dist/leaflet.js"></script>

<script>
$(document).ready(function() {
    // School coordinates (SMKN 2 Bandung)
    const schoolLat = -6.906000;
    const schoolLng = 107.623400;
    
    // Use event delegation for dynamically loaded buttons
    $(document).on('click', '.show-map', function() {
        const lat = $(this).data('lat');
        const lng = $(this).data('lng');
        const distance = $(this).data('distance');
        
        // Initialize map
        const map = L.map('map').setView([lat, lng], 15);
        
        // Add tile layer
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
        }).addTo(map);
        
        // Add school marker
        const schoolIcon = L.divIcon({
            html: '<i class="fas fa-school" style="color: #e63946; font-size: 24px;"></i>',
            iconSize: [24, 24],
            className: 'custom-div-icon'
        });
        
        L.marker([schoolLat, schoolLng], {icon: schoolIcon})
            .addTo(map)
            .bindPopup('<b>{{ strtoupper(setting('company_name', 'SMK NEGERI 2 BANDUNG')) }}</b>');
        
        // Add attendance location marker
        const userIcon = L.divIcon({
            html: '<i class="fas fa-map-marker-alt" style="color: #007bff; font-size: 24px;"></i>',
            iconSize: [24, 24],
            className: 'custom-div-icon'
        });
        
        L.marker([lat, lng], {icon: userIcon})
            .addTo(map)
            .bindPopup('Attendance Location');
        
        // Add line connecting the two points
        const line = L.polyline([
            [schoolLat, schoolLng],
            [lat, lng]
        ], {color: 'red'}).addTo(map);
        
        // Add radius circle (500m)
        L.circle([schoolLat, schoolLng], {
            color: 'blue',
            fillColor: '#0066cc',
            fillOpacity: 0.1,
            radius: 500
        }).addTo(map).bindPopup('Valid Attendance Radius (500m)');
        
        // Display distance
        $('#distanceDisplay').text(distance);
        
        // Store map instance for later cleanup
        $('#mapModal').data('map', map);
        
        // Show the modal
        $('#mapModal').modal('show');
    });
    
    // Clean up map when modal is closed
    $('#mapModal').on('hidden.bs.modal', function() {
        const map = $(this).data('map');
        if (map) {
            map.remove();
        }
    });
});
</script>

<style>
.custom-div-icon {
    background: transparent;
    border: none;
}
#map { 
    height: 400px; 
    width: 100%;
    border-radius: 4px;
}
</style>
@endsection
@endsection