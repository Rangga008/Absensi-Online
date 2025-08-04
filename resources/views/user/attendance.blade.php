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
        
        <div class="form-group mb-3">
            <label for="description"><strong>Keterangan Absensi:</strong></label>
            <select class="form-control" id="description" required>
                <option value="">-- Pilih Keterangan --</option>
                <option value="Hadir">Hadir</option>
                <option value="Terlambat">Terlambat</option>
                <option value="Sakit">Sakit</option>
                <option value="Izin">Izin</option>
                <option value="Dinas Luar">Dinas Luar</option>
                <option value="WFH">Work From Home</option>
            </select>
            <small class="form-text text-muted">
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
let map;
let userMarker;
let officeMarker;
let userLat, userLng;
let userAccuracy = 0;

// Koordinat SMKN 2 Bandung
const OFFICE_LAT = -6.906000000000;
const OFFICE_LNG = 107.623400000000;
const MAX_DISTANCE = 10000; // Maximum distance in meters

// Initialize map
function initMap() {
    // Default view ke lokasi sekolah
    map = L.map('map').setView([OFFICE_LAT, OFFICE_LNG], 15);
    
    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
    }).addTo(map);

    // Marker sekolah
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

function getUserLocation() {
    if (navigator.geolocation) {
        document.getElementById('location-status').innerHTML = 
            '<i class="fas fa-spinner fa-spin"></i> Mengambil lokasi Anda...';
        
        const options = {
            enableHighAccuracy: true,  // Gunakan GPS jika tersedia
            timeout: 20000,           // Timeout 20 detik
            maximumAge: 0             // Jangan gunakan cache lokasi lama
        };
        
        const watchId = navigator.geolocation.watchPosition(
            function(position) {
                // Hentikan pemantauan setelah mendapatkan lokasi akurat
                if (position.coords.accuracy <= 100) {
                    navigator.geolocation.clearWatch(watchId);
                }
                
                userLat = position.coords.latitude;
                userLng = position.coords.longitude;
                userAccuracy = position.coords.accuracy;
                
                // Update lokasi
                updateUserLocation(userLat, userLng, userAccuracy);
            },
            function(error) {
                navigator.geolocation.clearWatch(watchId);
                handleLocationError(error);
            },
            options
        );
        
        // Timeout tambahan untuk kasus watchPosition tidak merespon
        setTimeout(() => {
            navigator.geolocation.clearWatch(watchId);
            if (!userLat) {
                handleLocationError({code: 3, message: "Timeout mendapatkan lokasi"});
            }
        }, 25000);
    } else {
        handleLocationError({code: 0, message: "Browser tidak mendukung geolokasi"});
    }
}

function updateUserLocation(lat, lng, accuracy) {
    // Update hidden inputs
    document.getElementById('user_lat').value = lat;
    document.getElementById('user_lng').value = lng;
    
    // Update display
    document.getElementById('current-lat').textContent = lat.toFixed(6);
    document.getElementById('current-lng').textContent = lng.toFixed(6);
    document.getElementById('accuracy').textContent = Math.round(accuracy);
    
    // Add/update user marker
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
    
    // Add accuracy circle
    L.circle([lat, lng], {
        color: 'green',
        fillColor: '#00ff00',
        fillOpacity: 0.1,
        radius: accuracy
    }).addTo(map).bindPopup('Area Akurasi GPS (±' + Math.round(accuracy) + ' meter)');
    
    // Calculate distance
    const distance = calculateDistance(lat, lng, OFFICE_LAT, OFFICE_LNG);
    document.getElementById('distance').textContent = Math.round(distance);
    
    // Update validation status
    updateLocationValidation(distance, accuracy);
    
    // Update status
    let statusClass = 'alert-success';
    let statusIcon = 'fas fa-check-circle text-success';
    let statusMessage = 'Lokasi berhasil diambil';
    
    if (accuracy > 100) {
        statusClass = 'alert-warning';
        statusIcon = 'fas fa-exclamation-triangle text-warning';
        statusMessage = `Lokasi kurang akurat (±${Math.round(accuracy)} meter). Pastikan GPS aktif dan sinyal baik.`;
    }
    
    document.getElementById('location-status').innerHTML = `<i class="${statusIcon}"></i> ${statusMessage}`;
    document.getElementById('location-status').className = `alert ${statusClass}`;
    
    // Enable attendance button
    document.getElementById('attendance').disabled = false;
    
    // Adjust map view
    const group = new L.featureGroup([userMarker, officeMarker]);
    map.fitBounds(group.getBounds().pad(0.5));
}

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

function handleLocationError(error) {
    let errorMsg = '';
    switch(error.code) {
        case 1: // PERMISSION_DENIED
            errorMsg = "Izin lokasi ditolak. Izinkan akses lokasi untuk hasil akurat.";
            break;
        case 2: // POSITION_UNAVAILABLE
            errorMsg = "Informasi lokasi tidak tersedia. Coba di tempat terbuka.";
            break;
        case 3: // TIMEOUT
            errorMsg = "Timeout mengambil lokasi. Pastikan GPS aktif.";
            break;
        default:
            errorMsg = "Error: " + error.message;
            break;
    }
    
    document.getElementById('location-status').innerHTML = 
        `<i class="fas fa-exclamation-circle text-warning"></i> ${errorMsg}`;
    document.getElementById('location-status').className = 'alert alert-warning';
    
    // Update display for no location
    document.getElementById('current-lat').textContent = 'Tidak tersedia';
    document.getElementById('current-lng').textContent = 'Tidak tersedia';
    document.getElementById('accuracy').textContent = 'Tidak tersedia';
    document.getElementById('distance').textContent = 'Tidak tersedia';
    document.getElementById('location-validation').textContent = 'Tidak tersedia';
    document.getElementById('location-validation').className = 'badge badge-secondary';
    
    // Tetap bisa absen dengan catatan khusus
    document.getElementById('attendance').disabled = false;
}

// Calculate distance between two points using Haversine formula
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

// Handle attendance submission
document.getElementById('attendance').addEventListener('click', function() {
    const userId = document.getElementById('user_id').value;
    const description = document.getElementById('description').value;
    const latitude = document.getElementById('user_lat').value;
    const longitude = document.getElementById('user_lng').value;
    
    if (!description) {
        alert('Silakan pilih keterangan absensi terlebih dahulu!');
        return;
    }
    
    // Validasi lokasi untuk status tertentu
    const exemptDescriptions = ['WFH', 'Sakit', 'Izin'];
    if (!exemptDescriptions.includes(description)) {
        if (!latitude || !longitude) {
            alert('Lokasi tidak terdeteksi. Pastikan GPS aktif dan izinkan akses lokasi.');
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
                return;
            }
        }
    }
    
    // Disable button and show loading
    this.disabled = true;
    this.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses...';
    
    // Prepare data
    const attendanceData = {
        user_id: userId,
        present_at: new Date().toISOString().slice(0, 19).replace('T', ' '),
        description: description,
        latitude: latitude ? parseFloat(latitude) : null,
        longitude: longitude ? parseFloat(longitude) : null
    };
    
    // Send attendance data
    fetch('{{ route("attendance.store") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify(attendanceData)
    })
    .then(response => {
        if (!response.ok) {
            return response.json().then(err => Promise.reject(err));
        }
        return response.json();
    })
    .then(data => {
        if (data.success) {
            document.getElementById('result').innerHTML = 
                `<div class="alert alert-success">
                    <i class="fas fa-check-circle"></i> ${data.message}
                    <br><small>Waktu: ${data.data.time} | Tanggal: ${data.data.date}</small>
                    ${data.data.distance ? `<br><small>Jarak dari sekolah: ${data.data.distance} meter</small>` : ''}
                </div>`;
            
            // Reset form
            document.getElementById('description').value = '';
            this.innerHTML = '<i class="fas fa-check"></i> Absensi Berhasil';
            
            // Show success message and redirect
            setTimeout(() => {
                window.location.href = '{{ url("user/home") }}';
            }, 3000);
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        
        let errorMessage = 'Terjadi kesalahan. Silakan coba lagi.';
        if (error.message) {
            errorMessage = error.message;
        }
        
        document.getElementById('result').innerHTML = 
            `<div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i> ${errorMessage}
            </div>`;
        
        this.disabled = false;
        this.innerHTML = '<i class="fas fa-clock"></i> Absen Sekarang';
    });
});

// Check if user already attended today
function checkTodayAttendance() {
    const userId = document.getElementById('user_id').value;
    
    fetch('{{ route("attendance.check-status") }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': '{{ csrf_token() }}',
            'X-Requested-With': 'XMLHttpRequest'
        },
        credentials: 'same-origin',
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (!data.can_attend && data.attendance) {
            document.getElementById('result').innerHTML = 
                `<div class="alert alert-info">
                    <i class="fas fa-info-circle"></i> 
                    Anda sudah melakukan absensi hari ini pada pukul ${data.attendance.time} 
                    dengan status: <strong>${data.attendance.description}</strong>
                </div>`;
            
            document.getElementById('attendance').disabled = true;
            document.getElementById('attendance').innerHTML = 
                '<i class="fas fa-check"></i> Sudah Absen Hari Ini';
            document.getElementById('description').disabled = true;
        }
    })
    .catch(error => {
        console.error('Error checking attendance status:', error);
    });
}

// Auto refresh location every 30 seconds for better accuracy
function startLocationRefresh() {
    setInterval(() => {
        if (navigator.geolocation && userLat && userLng) {
            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const newLat = position.coords.latitude;
                    const newLng = position.coords.longitude;
                    const newAccuracy = position.coords.accuracy;
                    
                    // Only update if there's significant change or better accuracy
                    if (Math.abs(newLat - userLat) > 0.0001 || 
                        Math.abs(newLng - userLng) > 0.0001 || 
                        newAccuracy < userAccuracy) {
                        updateUserLocation(newLat, newLng, newAccuracy);
                    }
                },
                function(error) {
                    console.log('Location refresh failed:', error.message);
                },
                {
                    enableHighAccuracy: true,
                    timeout: 10000,
                    maximumAge: 30000
                }
            );
        }
    }, 30000); // Refresh every 30 seconds
}

// Handle description change for location requirement info
document.getElementById('description').addEventListener('change', function() {
    const selectedValue = this.value;
    const exemptDescriptions = ['WFH', 'Sakit', 'Izin'];
    const infoElement = document.querySelector('.form-text');
    
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
    initMap();
    getUserLocation();
    checkTodayAttendance();
    startLocationRefresh();
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
    
    #accuracy {
        font-weight: bold;
    }
    
    #distance {
        font-weight: bold;
    }
    
    .leaflet-control-attribution {
        font-size: 10px;
    }
`;
document.head.appendChild(style);
</script>

@endsection