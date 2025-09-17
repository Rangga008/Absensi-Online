<script>
// Constants
const OFFICE_LAT = {{ setting('office_lat', -6.906000) }};
const OFFICE_LNG = {{ setting('office_lng', 107.623400) }};
const MAX_DISTANCE = {{ setting('max_distance', 50000) }};
const MIN_ACCURACY = 100; // Minimum acceptable accuracy in meters
const LOCATION_TIMEOUT = 15000; // 15 seconds timeout
const LOCATION_MAX_AGE = 10000; // 10 seconds maximum age
const DEBOUNCE_TIME = 500; // Debounce time in ms

// Global variables
let map;
let userMarker;
let officeMarker;
let accuracyCircle;
let watchId = null;
let userLat, userLng, userAccuracy;
let isLocationStable = false;
let isSubmittingCheckout = false;
let canCheckout = false;
let checkoutProcessed = false;
let stream = null;
let photoTaken = false;
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
           <b>{{ setting('company_name', 'SMKN 2 Bandung') }}</b><br>
           <small>Lokasi Sekolah</small>
        `);

    // Add radius circle for checkout area
    L.circle([OFFICE_LAT, OFFICE_LNG], {
        color: 'red',
        fillColor: '#ff0000',
        fillOpacity: 0.1,
        radius: MAX_DISTANCE
    }).addTo(map).bindPopup('Area Checkout (Radius ' + MAX_DISTANCE + ' meter)');
}

// Camera Functions
async function startCamera() {
    try {
        const constraints = {
            video: {
                width: { ideal: 640 },
                height: { ideal: 480 },
                facingMode: 'user'
            }
        };
        
        stream = await navigator.mediaDevices.getUserMedia(constraints);
        const video = document.getElementById('camera-video');
        video.srcObject = stream;
        
        document.getElementById('start-camera').style.display = 'none';
        document.getElementById('take-photo').style.display = 'inline-block';
        
    } catch (error) {
        console.error('Error accessing camera:', error);
        showAlert('Tidak dapat mengakses kamera. Pastikan izin kamera diizinkan.', 'danger');
    }
}

function takePhoto() {
    const video = document.getElementById('camera-video');
    const canvas = document.getElementById('camera-canvas');
    const context = canvas.getContext('2d');
    
    // Set canvas dimensions to match video
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    
    // Draw current video frame to canvas
    context.drawImage(video, 0, 0, canvas.width, canvas.height);

    const imageData = canvas.toDataURL('image/jpeg', 0.8);
    
    if (!imageData.startsWith('data:image/jpeg;base64,')) {
        showAlert('Format foto tidak valid', 'danger');
        return;
    }
    
    // Display preview
    document.getElementById('photo-preview').src = imageData;
    document.getElementById('photo-preview').style.display = 'block';
    document.getElementById('no-photo').style.display = 'none';
    document.getElementById('photo-data').value = imageData;
    
    // Update button visibility
    document.getElementById('take-photo').style.display = 'none';
    document.getElementById('retake-photo').style.display = 'inline-block';
    
    photoTaken = true;
    
    // Stop camera stream
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    
    // Enable checkout button if location is available
    enableCheckoutButton();
}

function retakePhoto() {
    // Reset photo state
    document.getElementById('photo-preview').style.display = 'none';
    document.getElementById('no-photo').style.display = 'flex';
    document.getElementById('photo-data').value = '';
    
    // Reset buttons
    document.getElementById('retake-photo').style.display = 'none';
    document.getElementById('start-camera').style.display = 'inline-block';
    
    photoTaken = false;
    
    // Disable checkout button
    document.getElementById('checkout-btn').disabled = true;
}

// Location Functions
function getUserLocation() {
    if (!navigator.geolocation) {
        handleLocationError({code: 0, message: "Browser tidak mendukung geolokasi"});
        return;
    }
    
    updateLocationStatus('Mengambil lokasi dengan GPS...', 'info', 'fa-spinner fa-spin');
    
    const options = {
        enableHighAccuracy: true,
        timeout: LOCATION_TIMEOUT,
        maximumAge: LOCATION_MAX_AGE
    };
    
    // Clear any existing watch
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }
    
    // Get initial position
    navigator.geolocation.getCurrentPosition(
        handleLocationSuccess,
        handleLocationError,
        options
    );
    
    // Set up watch for updates
    watchId = navigator.geolocation.watchPosition(
        handleWatchPosition,
        handleLocationError,
        options
    );
}

function handleWatchPosition(position) {
    processLocationData(position, 'update');
}

function handleLocationSuccess(position) {
    processLocationData(position, 'initial');
}

function processLocationData(position, source) {
    const newLat = position.coords.latitude;
    const newLng = position.coords.longitude;
    const newAccuracy = position.coords.accuracy;
    
    // For initial fix or significant accuracy improvement
    if (source === 'initial' || !userAccuracy || newAccuracy < userAccuracy * 0.7) {
        userLat = newLat;
        userLng = newLng;
        userAccuracy = newAccuracy;
        updateUserLocationDisplay();
    }
    
    // Stop watching if we have good accuracy
    if (userAccuracy <= MIN_ACCURACY) {
        navigator.geolocation.clearWatch(watchId);
        watchId = null;
        updateLocationStatus('Lokasi stabil dan akurat', 'success', 'fa-check-circle');
        isLocationStable = true;
    }
}

function updateUserLocationDisplay() {
    // Update form inputs
    document.getElementById('user_lat').value = userLat;
    document.getElementById('user_lng').value = userLng;
    
    // Update display
    document.getElementById('current-lat').textContent = userLat.toFixed(6);
    document.getElementById('current-lng').textContent = userLng.toFixed(6);
    document.getElementById('accuracy').textContent = Math.round(userAccuracy);
    
    // Update or create user marker
    if (userMarker) {
        userMarker.setLatLng([userLat, userLng]);
    } else {
        const userIcon = L.divIcon({
            html: '<i class="fas fa-user-circle" style="color: #dc3545; font-size: 20px;"></i>',
            iconSize: [20, 20],
            className: 'custom-div-icon'
        });
        
        userMarker = L.marker([userLat, userLng], {icon: userIcon})
            .addTo(map)
            .bindPopup(`<b>Lokasi Anda</b><br><small>Akurasi: ±${Math.round(userAccuracy)} meter</small>`);
    }
    
    // Update accuracy circle
    if (accuracyCircle) {
        accuracyCircle.setLatLng([userLat, userLng]);
        accuracyCircle.setRadius(userAccuracy);
    } else {
        accuracyCircle = L.circle([userLat, userLng], {
            color: 'red',
            fillColor: '#ff0000',
            fillOpacity: 0.1,
            radius: userAccuracy
        }).addTo(map).bindPopup('Area Akurasi GPS (±' + Math.round(userAccuracy) + ' meter)');
    }
    
    // Calculate distance to school
    const distance = calculateDistance(userLat, userLng, OFFICE_LAT, OFFICE_LNG);
    document.getElementById('distance').textContent = Math.round(distance);
    
    // Update validation status
    updateLocationValidation(distance, userAccuracy);
    
    // Update status message
    updateLocationStatusBasedOnAccuracy();
    
    // Enable checkout button if photo is taken
    enableCheckoutButton();
    
    // Adjust map view to show both markers
    if (userMarker && officeMarker) {
        const group = new L.featureGroup([userMarker, officeMarker]);
        map.fitBounds(group.getBounds().pad(0.3));
    }
}

function updateLocationStatusBasedOnAccuracy() {
    let statusClass, statusIcon, statusMessage;
    
    if (userAccuracy <= 20) {
        statusClass = 'success';
        statusIcon = 'fa-check-circle';
        statusMessage = 'Lokasi sangat akurat';
    } else if (userAccuracy <= 50) {
        statusClass = 'info';
        statusIcon = 'fa-info-circle';
        statusMessage = 'Lokasi cukup akurat';
    } else if (userAccuracy <= 100) {
        statusClass = 'warning';
        statusIcon = 'fa-exclamation-triangle';
        statusMessage = 'Lokasi kurang akurat (±' + Math.round(userAccuracy) + ' meter)';
    } else {
        statusClass = 'danger';
        statusIcon = 'fa-exclamation-circle';
        statusMessage = 'Lokasi tidak akurat (±' + Math.round(userAccuracy) + ' meter). Silakan refresh.';
    }
    
    updateLocationStatus(statusMessage, statusClass, statusIcon);
}

function enableCheckoutButton() {
    // Enable checkout button only if we have location data, photo, and can checkout
    if (userLat && userLng && photoTaken && canCheckout) {
        document.getElementById('checkout-btn').disabled = false;
    }
}

function handleLocationError(error) {
    let errorMsg = '';
    switch(error.code) {
        case 1: 
            errorMsg = "Izin lokasi ditolak. Izinkan akses lokasi untuk checkout.";
            break;
        case 2: 
            errorMsg = "Informasi lokasi tidak tersedia. Coba di tempat terbuka.";
            break;
        case 3: 
            errorMsg = "Timeout mengambil lokasi. Pastikan GPS aktif.";
            break;
        default: 
            errorMsg = "Error: " + error.message;
            break;
    }
    
    updateLocationStatus(errorMsg, 'warning', 'fa-exclamation-circle');
    
    // Update display for no location
    document.getElementById('current-lat').textContent = 'Tidak tersedia';
    document.getElementById('current-lng').textContent = 'Tidak tersedia';
    document.getElementById('accuracy').textContent = 'Tidak tersedia';
    document.getElementById('distance').textContent = 'Tidak tersedia';
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

function updateLocationStatus(message, type = 'info', icon = 'fa-info-circle') {
    const statusElement = document.getElementById('location-status');
    statusElement.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
    statusElement.className = `alert alert-${type}`;
}

function updateCheckoutStatus(message, type = 'info', icon = 'fa-info-circle') {
    const statusElement = document.getElementById('checkout-status');
    statusElement.innerHTML = `<i class="fas ${icon}"></i> ${message}`;
    statusElement.className = `alert alert-${type}`;
}

function refreshLocation() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }
    getUserLocation();
}

function cleanupLocation() {
    if (watchId !== null) {
        navigator.geolocation.clearWatch(watchId);
    }
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
    }
}

// Checkout Functions
document.getElementById('checkout-btn').addEventListener('click', function(e) {
    e.preventDefault();
    
    // Debounce click to prevent double submission
    clearTimeout(debounceTimer);
    debounceTimer = setTimeout(() => {
        processCheckout();
    }, DEBOUNCE_TIME);
});

async function processCheckout() {
    if (isSubmittingCheckout || checkoutProcessed || !canCheckout) {
        return;
    }

    try {
        const userId = document.getElementById('user_id').value;
        const latitude = document.getElementById('user_lat').value;
        const longitude = document.getElementById('user_lng').value;
        const photoData = document.getElementById('photo-data').value;

        // Validate all required fields
        if (!userId) {
            throw new Error('User ID is required');
        }
        if (!photoData) {
            throw new Error('Silakan ambil foto untuk checkout terlebih dahulu!');
        }
        if (!latitude || !longitude) {
            throw new Error('Lokasi tidak terdeteksi. Pastikan GPS aktif dan izinkan akses lokasi.');
        }

        // Set submission state
        isSubmittingCheckout = true;
        disableForm(true);

        // Prepare checkout data
        const checkoutData = {
            user_id: parseInt(userId),
            latitude: parseFloat(latitude),
            longitude: parseFloat(longitude),
            photo: photoData
        };

        // Send checkout data
        const controller = new AbortController();
        const timeoutId = setTimeout(() => controller.abort(), 30000);

        const response = await fetch('/api/checkout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'X-Requested-With': 'XMLHttpRequest'
            },
            signal: controller.signal,
            body: JSON.stringify(checkoutData)
        });

        clearTimeout(timeoutId);

        const data = await response.json();
        
        if (!response.ok) {
            if (data.errors) {
                const errorMessages = Object.values(data.errors).flat().join('\n');
                throw new Error(errorMessages);
            } else {
                throw new Error(data.message || 'Checkout submission failed');
            }
        }

        if (data.success) {
            handleCheckoutSuccess(data, userId);
        } else {
            throw new Error(data.message || 'Terjadi kesalahan');
        }

    } catch (error) {
        console.error('Checkout error:', error);
        showAlert(error.message, 'danger');
        resetFormState();
    }
}

function handleCheckoutSuccess(data, userId) {
    // Mark as checked out
    canCheckout = false;
    checkoutProcessed = true;
    
    // Show success message
    document.getElementById('result').innerHTML = 
        `<div class="alert alert-success">
            <i class="fas fa-check-circle"></i> ${data.message}
            <br><small>Waktu Checkout: ${data.data.checkout_time} | Tanggal: ${data.data.date}</small>
            ${data.data.distance ? `<br><small>Jarak dari sekolah: ${data.data.distance} meter</small>` : ''}
            ${data.data.work_duration ? `<br><small>Durasi kerja: ${data.data.work_duration}</small>` : ''}
        </div>`;
    
    // Update button state
    const btn = document.getElementById('checkout-btn');
    btn.innerHTML = '<i class="fas fa-check"></i> Checkout Berhasil';
    btn.className = btn.className.replace('btn-danger', 'btn-success');
    btn.disabled = true;
    
    // Redirect after delay
    setTimeout(() => {
        window.location.href = '{{ url("user/home") }}';
    }, 3000);
}

function showAlert(message, type = 'info') {
    document.getElementById('result').innerHTML = 
        `<div class="alert alert-${type}">
            <i class="fas ${type === 'danger' ? 'fa-exclamation-circle' : 'fa-info-circle'}"></i> ${message}
        </div>`;
}

function resetFormState() {
    isSubmittingCheckout = false;
    disableForm(false);
}

function disableForm(disabled) {
    const btn = document.getElementById('checkout-btn');
    btn.disabled = disabled;
    btn.innerHTML = disabled 
        ? '<i class="fas fa-spinner fa-spin"></i> Memproses...' 
        : '<i class="fas fa-sign-out-alt"></i> Checkout Sekarang';
}

function checkCheckoutStatus() {
    const userId = document.getElementById('user_id').value;
    
    fetch('/api/checkout/status', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
        },
        body: JSON.stringify({ user_id: userId })
    })
    .then(response => response.json())
    .then(data => {
        if (data.can_checkout) {
            canCheckout = true;
            updateCheckoutStatus('Anda dapat melakukan checkout', 'success', 'fa-check-circle');
            enableCheckoutButton();
        } else {
            canCheckout = false;
            updateCheckoutStatus(data.message, 'warning', 'fa-exclamation-triangle');
            
            const btn = document.getElementById('checkout-btn');
            btn.disabled = true;
            btn.innerHTML = '<i class="fas fa-times"></i> Tidak Dapat Checkout';
            btn.className = btn.className.replace('btn-danger', 'btn-secondary');
        }
    })
    .catch(error => {
        console.error('Error checking checkout status:', error);
        updateCheckoutStatus('Error memeriksa status checkout', 'danger', 'fa-exclamation-circle');
    });
}

// Event Listeners
document.getElementById('start-camera').addEventListener('click', startCamera);
document.getElementById('take-photo').addEventListener('click', takePhoto);
document.getElementById('retake-photo').addEventListener('click', retakePhoto);

// Initialize when page loads
document.addEventListener('DOMContentLoaded', function() {
    initMap();
    
    // Add manual refresh button
    const refreshButton = document.createElement('button');
    refreshButton.innerHTML = '<i class="fas fa-sync-alt"></i> Refresh Lokasi';
    refreshButton.className = 'btn btn-sm btn-outline-primary ml-2';
    refreshButton.onclick = refreshLocation;
    document.getElementById('location-status').appendChild(refreshButton);
    
    getUserLocation();
    checkCheckoutStatus();
    
    // Clean up when leaving page
    window.addEventListener('beforeunload', cleanupLocation);
    window.addEventListener('pagehide', cleanupLocation);
});

// Custom CSS
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
        border: 1px solid #dee2e6;
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
        font-size: 0.85em;
        padding: 0.4em 0.6em;
    }
    
    #accuracy, #distance {
        font-weight: bold;
    }
    
    .leaflet-control-attribution {
        font-size: 10px;
    }
    
    .camera-container {
        position: relative;
    }
    
    #camera-video {
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    
    .camera-controls {
        text-align: center;
    }
    
    .photo-preview {
        position: relative;
    }
    
    #photo-preview {
        border-radius: 8px;
        border: 1px solid #ddd;
    }
    
    .location-updating {
        animation: locationUpdate 1s ease-in-out;
    }
    
    @keyframes locationUpdate {
        0% { background-color: transparent; }
        50% { background-color: rgba(220, 53, 69, 0.1); }
        100% { background-color: transparent; }
    }
`;
document.head.appendChild(style);
</script>