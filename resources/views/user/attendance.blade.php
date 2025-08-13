@extends('user.layouts')

@section('content')

<div class="card p-3 shadow rounded">
    <div id="msg"></div>
    <div>
        Hai <b>{{ ucfirst(session('username')) }}</b>, selamat datang di sistem absensi SMKN 2 Bandung. <br />
        <small>{{ date('D, d F Y') }}</small>
    </div>

    <hr />

    <!-- Status Location -->
    <div class="alert alert-info" id="location-status">
        <i class="fas fa-info-circle"></i> Mengambil lokasi Anda...
    </div>

    <!-- Map Container -->
    <div class="mt-3 mb-3">
        <div class="card">
            <div class="card-header">
                <h6 class="mb-0"><i class="fas fa-map-marker-alt"></i> Lokasi Anda Saat Ini</h6>
            </div>
            <div class="card-body p-0">
                <div id="map" style="height: 400px; width: 100%;"></div>
            </div>
        </div>
    </div>

    <!-- Location Info -->
    <div class="row mb-3">
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-crosshairs"></i> Koordinat</h6>
                    <p class="card-text">
                        <small class="text-muted">Latitude:</small> <span id="current-lat">-</span><br>
                        <small class="text-muted">Longitude:</small> <span id="current-lng">-</span><br>
                        <small class="text-muted">Akurasi:</small> <span id="accuracy">-</span> meter
                    </p>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-school"></i> Lokasi Sekolah</h6>
                    <p class="card-text">
                        <span class="badge badge-info">SMKN 2 Bandung</span><br>
                        <small class="text-muted">Jl. Ciliwung No.4, Bandung</small><br>
                        <small class="text-muted">Jarak: <span id="distance">-</span> meter</small><br>
                        <small class="text-muted">Status: <span id="location-validation" class="badge">Checking...</span></small>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Form -->
    <div class="mb-3 text-center">
        <input type="hidden" id="user_id" value="{{ session('user_id') }}">
        <input type="hidden" id="user_lat" value="">
        <input type="hidden" id="user_lng" value="">
        <input type="hidden" id="request_id" value="">
        
        <div class="form-group mb-3">
            <label for="description"><strong>Keterangan Absensi:</strong></label>
            <select class="form-control" id="description" required>
                <option value="">-- Pilih Keterangan --</option>
                <option value="Hadir">Hadir</option>
                <option value="Terlambat">Terlambat</option>
                <option value="Sakit">Sakit</option>
            </select>
            <small class="form-text text-muted" id="description-info">
                <i class="fas fa-info-circle"></i> 
                Status Sakit, Izin, dan WFH tidak memerlukan validasi lokasi
            </small>
        </div>
        
        <button class="btn btn-primary mt-3" id="attendance" disabled>
            <i class="fas fa-clock"></i> Absen Sekarang
        </button>
    </div>
    
    <div id="result"></div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
// Constants
const OFFICE_LAT = -6.906000000000;
const OFFICE_LNG = 107.623400000000;
const MAX_DISTANCE = 50000; // 50km maximum distance in meters
const MIN_ACCURACY = 100; // Minimum acceptable accuracy in meters
const DEBOUNCE_TIME = 500; // Debounce time in ms
const LOCATION_REFRESH_INTERVAL = 30000; // 30 seconds
const ATTENDANCE_CHECK_INTERVAL = 15000; // 15 seconds

// Global variables
let map;
let userMarker;
let officeMarker;
let accuracyCircle;
let userLat, userLng, userAccuracy;
let isSubmittingAttendance = false;
let hasAttendedToday = false;
let attendanceProcessed = false;
let debounceTimer = null;

// Initialize map
function initMap() {
    map = L.map('map').setView([OFFICE_LAT, OFFICE_LNG], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // School marker
    const schoolIcon = L.divIcon({
        html: '<i class="fas fa-school" style="color: #e63946; font-size: 24px;"></i>',
        iconSize: [24, 24],
        className: 'custom-div-icon'
    });
    
    officeMarker = L.marker([OFFICE_LAT, OFFICE_LNG], {icon: schoolIcon})
        .addTo(map)
        .bindPopup(`
            <b>SMKN 2 Bandung</b><br>
            <small>Jl. Ciliwung No.4, Cihapit</small><br>
            <small>Kota Bandung, Jawa Barat</small>
        `);

    // Add radius circle for attendance area
    L.circle([OFFICE_LAT, OFFICE_LNG], {
        color: 'blue',
        fillColor: '#0066cc',
        fillOpacity: 0.1,
        radius: MAX_DISTANCE
    }).addTo(map).bindPopup('Area Absensi (Radius ' + MAX_DISTANCE + ' meter)');
}

// Get user location with high accuracy
function getUserLocation() {
    if (!navigator.geolocation) {
        handleLocationError({code: 0, message: "Browser tidak mendukung geolokasi"});
        return;
    }
    
    updateLocationStatus('Mengambil lokasi Anda...', 'info', 'fa-spinner fa-spin');
    
    const options = {
        enableHighAccuracy: true,
        timeout: 20000,
        maximumAge: 0
    };
    
    const watchId = navigator.geolocation.watchPosition(
        position => handleLocationSuccess(position, watchId),
        handleLocationError,
        options
    );
    
    // Fallback timeout
    setTimeout(() => {
        navigator.geolocation.clearWatch(watchId);
        if (!userLat) {
            handleLocationError({code: 3, message: "Timeout mendapatkan lokasi"});
        }
    }, 25000);
}

// Handle successful location retrieval
function handleLocationSuccess(position, watchId = null) {
    // Stop watching if we have accurate enough location
    if (position.coords.accuracy <= MIN_ACCURACY && watchId) {
        navigator.geolocation.clearWatch(watchId);
    }
    
    userLat = position.coords.latitude;
    userLng = position.coords.longitude;
    userAccuracy = position.coords.accuracy;
    
    updateUserLocation(userLat, userLng, userAccuracy);
}

// Update UI with new location data
function updateUserLocation(lat, lng, accuracy) {
    // Update form inputs
    document.getElementById('user_lat').value = lat;
    document.getElementById('user_lng').value = lng;
    
    // Update display
    document.getElementById('current-lat').textContent = lat.toFixed(6);
    document.getElementById('current-lng').textContent = lng.toFixed(6);
    document.getElementById('accuracy').textContent = Math.round(accuracy);
    
    // Update or create user marker
    if (userMarker) {
        map.removeLayer(userMarker);
    }
    
    const userIcon = L.divIcon({
        html: '<i class="fas fa-user-circle" style="color: #007bff; font-size: 20px;"></i>',
        iconSize: [20, 20],
        className: 'custom-div-icon'
    });
    
    userMarker = L.marker([lat, lng], {icon: userIcon})
        .addTo(map)
        .bindPopup(`<b>Lokasi Anda</b><br><small>Akurasi: ±${Math.round(accuracy)} meter</small>`);
    
    // Update accuracy circle
    if (accuracyCircle) {
        map.removeLayer(accuracyCircle);
    }
    accuracyCircle = L.circle([lat, lng], {
        color: 'green',
        fillColor: '#00ff00',
        fillOpacity: 0.1,
        radius: accuracy
    }).addTo(map).bindPopup('Area Akurasi GPS (±' + Math.round(accuracy) + ' meter)');
    
    // Calculate distance to school
    const distance = calculateDistance(lat, lng, OFFICE_LAT, OFFICE_LNG);
    document.getElementById('distance').textContent = Math.round(distance);
    
    // Update validation status
    updateLocationValidation(distance, accuracy);
    
    // Update status message
    let statusClass, statusIcon, statusMessage;
    if (accuracy <= MIN_ACCURACY) {
        statusClass = 'success';
        statusIcon = 'fa-check-circle';
        statusMessage = 'Lokasi berhasil diambil';
    } else {
        statusClass = 'warning';
        statusIcon = 'fa-exclamation-triangle';
        statusMessage = `Lokasi kurang akurat (±${Math.round(accuracy)} meter). Pastikan GPS aktif dan sinyal baik.`;
    }
    
    updateLocationStatus(statusMessage, statusClass, statusIcon);
    
    // Enable attendance button
    document.getElementById('attendance').disabled = false;
    
    // Adjust map view
    const group = new L.featureGroup([userMarker, officeMarker]);
    map.fitBounds(group.getBounds().pad(0.5));
}

// Handle location errors
function handleLocationError(error) {
    let errorMsg = '';
    switch(error.code) {
        case 1: errorMsg = "Izin lokasi ditolak. Izinkan akses lokasi untuk hasil akurat."; break;
        case 2: errorMsg = "Informasi lokasi tidak tersedia. Coba di tempat terbuka."; break;
        case 3: errorMsg = "Timeout mengambil lokasi. Pastikan GPS aktif."; break;
        default: errorMsg = "Error: " + error.message; break;
    }
    
    updateLocationStatus(errorMsg, 'warning', 'fa-exclamation-circle');
    
    // Update display for no location
    document.getElementById('current-lat').textContent = 'Tidak tersedia';
    document.getElementById('current-lng').textContent = 'Tidak tersedia';
    document.getElementById('accuracy').textContent = 'Tidak tersedia';
    document.getElementById('distance').textContent = 'Tidak tersedia';
    document.getElementById('location-validation').textContent = 'Tidak tersedia';
    document.getElementById('location-validation').className = 'badge badge-secondary';
    
    // Still allow attendance with special statuses
    document.getElementById('attendance').disabled = false;
}

// Update location validation status
function updateLocationValidation(distance, accuracy) {
    const validationElement = document.getElementById('location-validation');
    
    if (distance <= MAX_DISTANCE) {
        validationElement.textContent = 'Valid';
        validationElement.className = 'badge badge-success';
    } else {
        validationElement.textContent = 'Terlalu Jauh';
        validationElement.className = 'badge badge-danger';
    }
}

// Calculate distance using Haversine formula
function calculateDistance(lat1, lng1, lat2, lng2) {
    const R = 6371e3; // Earth's radius in meters
    const φ1 = lat1 * Math.PI/180;
    const φ2 = lat2 * Math.PI/180;
    const Δφ = (lat2-lat1) * Math.PI/180;
    const Δλ = (lng2-lng1) * Math.PI/180;

    const a = Math.sin(Δφ/2) * Math.sin(Δφ/2) +
              Math.cos(φ1) * Math.cos(φ2) *
              Math.sin(Δλ/2) * Math.sin(Δλ/2);
    const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));

    return R * c; // Distance in meters
}

// Update location status message
function updateLocationStatus(message, type = 'info', icon = 'fa-info-circle') {
    const statusElement = document.getElementById('location-status');
    statusElement.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
    statusElement.className = `alert alert-${type}`;
}

// Handle attendance submission
document.getElementById('attendance').addEventListener('click', function(e) {
    e.preventDefault();
    
    // Debounce click to prevent double submission
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        processAttendance();
    }, DEBOUNCE_TIME);
});

// Process attendance submission
function processAttendance() {
    // Prevent multiple submissions
    if (isSubmittingAttendance || hasAttendedToday || attendanceProcessed) {
        return;
    }

    const userId = document.getElementById('user_id').value;
    const description = document.getElementById('description').value;
    const latitude = document.getElementById('user_lat').value;
    const longitude = document.getElementById('user_lng').value;
    
    // Validate description
    if (!description) {
        showAlert('Silakan pilih keterangan absensi terlebih dahulu!', 'danger');
        return;
    }
    
    // Set submission state
    isSubmittingAttendance = true;
    attendanceProcessed = true;
    disableForm(true);
    
    // Generate unique request ID
    const requestId = 'att_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
    document.getElementById('request_id').value = requestId;
    
    // Validate location for certain statuses
    const exemptDescriptions = ['WFH', 'Sakit', 'Izin'];
    if (!exemptDescriptions.includes(description)) {
        if (!latitude || !longitude) {
            showAlert('Lokasi tidak terdeteksi. Pastikan GPS aktif dan izinkan akses lokasi.', 'danger');
            resetFormState();
            return;
        }
        
        const distance = calculateDistance(
            parseFloat(latitude), 
            parseFloat(longitude), 
            OFFICE_LAT, 
            OFFICE_LNG
        );
        
        if (distance > MAX_DISTANCE) {
            const confirmMsg = `Anda berada ${Math.round(distance)} meter dari sekolah (maksimal ${MAX_DISTANCE} meter). ` +
                             `Apakah Anda yakin ingin melanjutkan absensi dengan status "${description}"?`;
            if (!confirm(confirmMsg)) {
                resetFormState();
                return;
            }
        }
    }
    
    // Prepare attendance data
    const attendanceData = {
        user_id: userId,
        description: description,
        latitude: latitude ? parseFloat(latitude) : null,
        longitude: longitude ? parseFloat(longitude) : null,
        client_timestamp: Date.now(),
        request_id: requestId
    };
    
    // Store request ID in session storage
    sessionStorage.setItem('current_attendance_request', requestId);
    
    // Send attendance data with timeout
    const controller = new AbortController();
    const timeoutId = setTimeout(() => controller.abort(), 30000);
    
    fetch('{{ route("attendance.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        signal: controller.signal,
        body: JSON.stringify(attendanceData)
    })
    .then(response => {
        clearTimeout(timeoutId);
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        sessionStorage.removeItem('current_attendance_request');
        
        if (data.success) {
            handleAttendanceSuccess(data, userId);
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        clearTimeout(timeoutId);
        sessionStorage.removeItem('current_attendance_request');
        
        console.error('Attendance error:', error);
        handleAttendanceError(error, userId);
    });
}

// Handle successful attendance submission
function handleAttendanceSuccess(data, userId) {
    // Mark as attended permanently
    hasAttendedToday = true;
    localStorage.setItem('attended_today_' + userId, new Date().toDateString());
    
    // Show success message
    document.getElementById('result').innerHTML = 
        `<div class="alert alert-success">
            <i class="fas fa-check-circle"></i> ${data.message}
            <br><small>Waktu: ${data.data.time} | Tanggal: ${data.data.date}</small>
            ${data.data.distance ? `<br><small>Jarak dari sekolah: ${data.data.distance} meter</small>` : ''}
        </div>`;
    
    // Update button state
    const btn = document.getElementById('attendance');
    btn.innerHTML = '<i class="fas fa-check"></i> Absensi Berhasil';
    btn.className = btn.className.replace('btn-primary', 'btn-success');
    
    // Redirect after delay
    setTimeout(() => {
        window.location.href = '{{ url("user/home") }}';
    }, 3000);
}

// Handle attendance errors
function handleAttendanceError(error, userId) {
    let errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
    if (error.message) {
        errorMessage = error.message;
    }
    
    // Handle specific error types
    if (error.name === 'AbortError') {
        errorMessage = 'Request timeout. Silakan coba lagi.';
    }
    
    // Check if error indicates already attended
    if (error.message && error.message.includes('sudah melakukan absensi')) {
        hasAttendedToday = true;
        localStorage.setItem('attended_today_' + userId, new Date().toDateString());
        
        const btn = document.getElementById('attendance');
        btn.innerHTML = '<i class="fas fa-check"></i> Sudah Absen Hari Ini';
        btn.className = btn.className.replace('btn-primary', 'btn-secondary');
        return;
    }
    
    showAlert(errorMessage, 'danger');
    resetFormState();
}

// Show alert message
function showAlert(message, type = 'info') {
    document.getElementById('result').innerHTML = 
        `<div class="alert alert-${type}">
            <i class="fas ${type === 'danger' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}
        </div>`;
}

// Reset form state after error
function resetFormState() {
    isSubmittingAttendance = false;
    attendanceProcessed = false;
    disableForm(false);
}

// Enable/disable form elements
function disableForm(disabled) {
    const btn = document.getElementById('attendance');
    btn.disabled = disabled;
    btn.innerHTML = disabled 
        ? '<i class="fas fa-spinner fa-spin"></i> Memproses...' 
        : '<i class="fas fa-clock"></i> Absen Sekarang';
    
    document.getElementById('description').disabled = disabled;
}

// Check if user already attended today
function checkTodayAttendance() {
    const userId = document.getElementById('user_id').value;
    const today = new Date().toDateString();
    
    // Check with server first, ignore localStorage
    fetch('{{ route("attendance.check-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.can_attend && data.attendance) {
            localStorage.setItem('attended_today_' + userId, today);
            markAsAttended(data.attendance);
        } else {
            // Clear localStorage jika tidak ada absensi
            localStorage.removeItem('attended_today_' + userId);
            resetFormState();
        }
    })
    .catch(error => {
        console.error('Error checking attendance status:', error);
        // Fallback ke localStorage jika server error
        const storedAttendance = localStorage.getItem('attended_today_' + userId);
        if (storedAttendance === today) {
            markAsAttended();
        }
    });
}

// Mark user as already attended
function markAsAttended(attendanceData = null) {
    hasAttendedToday = true;
    attendanceProcessed = true;
    
    if (attendanceData) {
        document.getElementById('result').innerHTML = 
            `<div class="alert alert-info">
                <i class="fas fa-info-circle"></i> 
                Anda sudah melakukan absensi hari ini pada pukul ${attendanceData.time} 
                dengan status: <strong>${attendanceData.description}</strong>
            </div>`;
    }
    
    const btn = document.getElementById('attendance');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-check"></i> Sudah Absen Hari Ini';
    btn.className = btn.className.replace('btn-primary', 'btn-secondary');
    document.getElementById('description').disabled = true;
}

// Auto refresh location periodically
function startLocationRefresh() {
    setInterval(() => {
        if (navigator.geolocation && userLat && userLng) {
            navigator.geolocation.getCurrentPosition(
                position => {
                    const newLat = position.coords.latitude;
                    const newLng = position.coords.longitude;
                    const newAccuracy = position.coords.accuracy;
                    
                    // Only update if significant change or better accuracy
                    if (Math.abs(newLat - userLat) > 0.0001 || 
                        Math.abs(newLng - userLng) > 0.0001 || 
                        newAccuracy < userAccuracy) {
                        updateUserLocation(newLat, newLng, newAccuracy);
                    }
                },
                error => console.log('Location refresh failed:', error.message),
                { enableHighAccuracy: true, timeout: 10000, maximumAge: 30000 }
            );
        }
    }, LOCATION_REFRESH_INTERVAL);
}

function resetAttendanceState() {
    const userId = document.getElementById('user_id').value;
    localStorage.removeItem('attended_today_' + userId);
    hasAttendedToday = false;
    attendanceProcessed = false;
    disableForm(false);
    
    const btn = document.getElementById('attendance');
    btn.innerHTML = '<i class="fas fa-clock"></i> Absen Sekarang';
    btn.className = btn.className.replace('btn-secondary', 'btn-primary');
    document.getElementById('description').disabled = false;
    
    document.getElementById('result').innerHTML = '';
}

// Start periodic attendance status check
function startAttendanceStatusCheck() {
    setInterval(() => {
        if (!hasAttendedToday && !isSubmittingAttendance) {
            checkTodayAttendance();
        }
    }, ATTENDANCE_CHECK_INTERVAL);
}

// Clear old attendance data from localStorage
function clearOldAttendanceData() {
    const today = new Date().toDateString();
    Object.keys(localStorage).forEach(key => {
        if (key.startsWith('attended_today_') && localStorage.getItem(key) !== today) {
            localStorage.removeItem(key);
        }
    });
}

// Handle description change for location requirement info
document.getElementById('description').addEventListener('change', function() {
    const selectedValue = this.value;
    const exemptDescriptions = ['WFH', 'Sakit', 'Izin'];
    const infoElement = document.getElementById('description-info');
    
    if (exemptDescriptions.includes(selectedValue)) {
        infoElement.innerHTML = 
            '<i class="fas fa-info-circle text-success"></i> ' +
            'Status ini tidak memerlukan validasi lokasi';
        infoElement.className = 'form-text text-success';
    } else if (selectedValue) {
        infoElement.innerHTML = 
            '<i class="fas fa-map-marker-alt text-warning"></i> ' +
            'Status ini memerlukan validasi lokasi (maksimal ' + MAX_DISTANCE + ' meter dari sekolah)';
        infoElement.className = 'form-text text-warning';
    } else {
        infoElement.innerHTML = 
            '<i class="fas fa-info-circle"></i> ' +
            'Status Sakit, Izin, dan WFH tidak memerlukan validasi lokasi';
        infoElement.className = 'form-text text-muted';
    }
});

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    clearOldAttendanceData();
    initMap();
    getUserLocation();
    checkTodayAttendance();
    startLocationRefresh();
    startAttendanceStatusCheck();
    
    // Generate initial request ID
    document.getElementById('request_id').value = 
        'req_' + Date.now() + '_' + Math.random().toString(36).substr(2, 9);
});

// Custom CSS for map icons
const style = document.createElement('style');
style.textContent = `
    .custom-div-icon {
        background: transparent;
        border: none;
    }
    
    .leaflet-popup-content-wrapper {
        border-radius: 8px;
    }
    
    #map {
        border-radius: 8px;
    }
    
    .alert {
        border-radius: 8px;
    }
    
    .card {
        border-radius: 8px;
    }
    
    .btn {
        border-radius: 6px;
    }
    
    .form-control {
        border-radius: 6px;
    }
    
    .badge {
        font-size: 0.8em;
    }
    
    #accuracy, #distance {
        font-weight: bold;
    }
    
    .leaflet-control-attribution {
        font-size: 10px;
    }
`;
document.head.appendChild(style);
</script>

@endsection