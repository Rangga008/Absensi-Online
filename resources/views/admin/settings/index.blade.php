@extends('layouts.admin')

@section('title', 'System Settings')

@section('content')
<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 text-gray-800">
            <i class="fas fa-cog mr-2"></i>System Settings
        </h1>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fas fa-exclamation-circle mr-2"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    <!-- Work Times Management Section -->
    <div class="card shadow mb-4">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0">
                <i class="fas fa-clock mr-2"></i>Work Times / Shifts Management
            </h5>
        </div>
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <p class="text-muted mb-0">Manage different work shifts and their schedules</p>
                <a href="{{ route('admin.settings.work-times.create') }}" class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i>Add New Shift
                </a>
            </div>

            @if(isset($workTimes) && $workTimes->count() > 0)
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-light">
                            <tr>
                                <th>Shift Name</th>
                                <th>Start Time</th>
                                <th>End Time</th>
                                <th>Late Threshold</th>
                                <th>Status</th>
                                <th>Users</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($workTimes as $workTime)
                                <tr>
                                    <td>
                                        <strong>{{ $workTime->name }}</strong>
                                        @if($workTime->description)
                                            <br><small class="text-muted">{{ $workTime->description }}</small>
                                        @endif
                                    </td>
                                    <td>{{ $workTime->formatted_start_time }}</td>
                                    <td>{{ $workTime->formatted_end_time }}</td>
                                    <td>{{ $workTime->formatted_late_threshold }}</td>
                                    <td>
                                        <span class="badge badge-{{ $workTime->is_active ? 'success' : 'danger' }}">
                                            {{ $workTime->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $workTime->users->count() }} users</span>
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('admin.settings.work-times.show', $workTime) }}"
                                               class="btn btn-sm btn-info" title="View">
                                                <i class="fas fa-eye"></i>
                                            </a>
                                            <a href="{{ route('admin.settings.work-times.edit', $workTime) }}"
                                               class="btn btn-sm btn-warning" title="Edit">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="{{ route('admin.settings.work-times.toggle-status', $workTime) }}"
                                                  style="display: inline;"
                                                  onsubmit="return confirm('Are you sure you want to {{ $workTime->is_active ? "deactivate" : "activate" }} this shift?')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="btn btn-sm btn-{{ $workTime->is_active ? 'danger' : 'success' }}"
                                                        title="{{ $workTime->is_active ? 'Deactivate' : 'Activate' }}">
                                                    <i class="fas fa-{{ $workTime->is_active ? 'times' : 'check' }}"></i>
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @else
                <div class="text-center py-4">
                    <i class="fas fa-clock fa-3x text-muted mb-3"></i>
                    <h5 class="text-muted">No Work Times Defined</h5>
                    <p class="text-muted">Create your first work shift to get started with flexible scheduling.</p>
                    <a href="{{ route('admin.settings.work-times.create') }}" class="btn btn-success">
                        <i class="fas fa-plus mr-1"></i>Create First Shift
                    </a>
                </div>
            @endif
        </div>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body">
            <form action="{{ route('admin.settings.update') }}" method="POST" enctype="multipart/form-data" id="settingsForm">
                @csrf

                <div class="row">
                    <!-- Application Settings -->
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-desktop mr-2"></i>Application Settings
                        </h5>

                        <div class="form-group">
                            <label for="app_name">Application Name</label>
                            <input type="text" class="form-control" id="app_name" name="app_name"
                                   value="{{ old('app_name', $settings['app_name']) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="company_name">Company/Institution Name</label>
                            <input type="text" class="form-control" id="company_name" name="company_name"
                                   value="{{ old('company_name', $settings['company_name']) }}" required>
                        </div>

                        <div class="form-group">
                            <label for="timezone">Timezone</label>
                            <select class="form-control" id="timezone" name="timezone" required>
                                <option value="Asia/Jakarta" {{ $settings['timezone'] == 'Asia/Jakarta' ? 'selected' : '' }}>Asia/Jakarta (WIB)</option>
                                <option value="Asia/Makassar" {{ $settings['timezone'] == 'Asia/Makassar' ? 'selected' : '' }}>Asia/Makassar (WITA)</option>
                                <option value="Asia/Jayapura" {{ $settings['timezone'] == 'Asia/Jayapura' ? 'selected' : '' }}>Asia/Jayapura (WIT)</option>
                                <option value="UTC">UTC</option>
                            </select>
                        </div>
                    </div>

                    <!-- Location & Work Hours Settings -->
                    <div class="col-md-6">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-map-marker-alt mr-2"></i>Location & Work Hours
                        </h5>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="office_lat">Office Latitude</label>
                                    <input type="number" step="any" class="form-control" id="office_lat" name="office_lat"
                                        value="{{ old('office_lat', $settings['office_lat']) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="office_lng">Office Longitude</label>
                                    <input type="number" step="any" class="form-control" id="office_lng" name="office_lng"
                                        value="{{ old('office_lng', $settings['office_lng']) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="max_distance">Maximum Allowed Distance (meters)</label>
                            <input type="number" class="form-control" id="max_distance" name="max_distance"
                                   value="{{ old('max_distance', $settings['max_distance']) }}" required>
                            <small class="form-text text-muted">Maximum distance from office for valid attendance</small>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="work_start_time">Work Start Time</label>
                                    <input type="time" class="form-control" id="work_start_time" name="work_start_time"
                                           value="{{ old('work_start_time', $settings['work_start_time']) }}" required>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="work_end_time">Work End Time</label>
                                    <input type="time" class="form-control" id="work_end_time" name="work_end_time"
                                           value="{{ old('work_end_time', $settings['work_end_time']) }}" required>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="late_threshold">Late Threshold Time</label>
                            <input type="time" class="form-control" id="late_threshold" name="late_threshold"
                                   value="{{ old('late_threshold', $settings['late_threshold']) }}" required>
                            <small class="form-text text-muted">Time after which attendance is marked as late</small>
                        </div>
                    </div>
                </div>

                <!-- Image Uploads Section -->
                <div class="row mt-4">
                    <div class="col-12">
                        <h5 class="text-primary mb-3">
                            <i class="fas fa-images mr-2"></i>Images & Branding
                        </h5>
                    </div>
                </div>

                <div class="row">
                    <!-- Logo Upload -->
                    <div class="col-md-6">
                        <div class="card border-primary">
                            <div class="card-header bg-primary text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-image mr-2"></i>Application Logo
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="logo" name="logo" accept="image/*">
                                        <label class="custom-file-label" for="logo">Choose logo file...</label>
                                    </div>
                                </div>

                                <!-- Current Logo Preview -->
                                @php
                                    $logoPath = setting('logo');
                                    $hasValidLogo = $logoPath && File::exists(public_path($logoPath));
                                @endphp

                                <div class="text-center mb-3">
                                    @if($hasValidLogo)
                                        <small class="text-muted d-block mb-2">Current Logo:</small>
                                        <img src="{{ asset($logoPath) }}?v={{ time() }}"
                                            alt="Current Logo"
                                            class="img-thumbnail current-logo-preview"
                                            style="max-height: 120px; max-width: 100%;"
                                            onerror="this.style.display='none'; document.getElementById('logoError').style.display='block';">
                                        <div id="logoError" style="display: none;" class="text-danger mt-2">
                                            <small><i class="fas fa-exclamation-triangle"></i> Logo file not found</small>
                                        </div>
                                    @else
                                        <small class="text-muted d-block mb-2">No logo uploaded</small>
                                        <div class="border rounded p-3 bg-light">
                                            <i class="fas fa-image fa-3x text-muted"></i>
                                            <p class="text-muted mb-0 mt-2">Default logo will be used</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- New Logo Preview -->
                                <div id="newLogoPreview" style="display: none;" class="text-center">
                                    <small class="text-muted d-block mb-2">New Logo Preview:</small>
                                    <img id="logoPreviewImage" class="img-thumbnail" style="max-height: 120px; max-width: 100%;">
                                </div>

                                <small class="form-text text-muted d-block text-center">
                                    Format: PNG, ICO, JPG, JPEG. Max: 2MB.<br>
                                    Logo will be used for favicon and branding.
                                </small>
                            </div>
                        </div>
                    </div>

                    <!-- Kopsurat Upload -->
                    <div class="col-md-6">
                        <div class="card border-success">
                            <div class="card-header bg-success text-white">
                                <h6 class="mb-0">
                                    <i class="fas fa-file-alt mr-2"></i>Kopsurat (Letterhead)
                                </h6>
                            </div>
                            <div class="card-body">
                                <div class="form-group">
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="kopsurat" name="kopsurat" accept="image/*">
                                        <label class="custom-file-label" for="kopsurat">Choose kopsurat file...</label>
                                    </div>
                                </div>

                                <!-- Current Kopsurat Preview -->
                                @php
                                    $kopsuratPath = setting('kopsurat');
                                    $hasValidKopsurat = $kopsuratPath && File::exists(public_path($kopsuratPath));
                                @endphp

                                <div class="text-center mb-3">
                                    @if($hasValidKopsurat)
                                        <small class="text-muted d-block mb-2">Current Kopsurat:</small>
                                        <img src="{{ asset($kopsuratPath) }}?v={{ time() }}"
                                            alt="Current Kopsurat"
                                            class="img-thumbnail current-kopsurat-preview"
                                            style="max-height: 120px; max-width: 100%;"
                                            onerror="this.style.display='none'; document.getElementById('kopsuratError').style.display='block';">
                                        <div id="kopsuratError" style="display: none;" class="text-danger mt-2">
                                            <small><i class="fas fa-exclamation-triangle"></i> Kopsurat file not found</small>
                                        </div>
                                    @else
                                        <small class="text-muted d-block mb-2">No kopsurat uploaded</small>
                                        <div class="border rounded p-3 bg-light">
                                            <i class="fas fa-file-alt fa-3x text-muted"></i>
                                            <p class="text-muted mb-0 mt-2">No letterhead image</p>
                                        </div>
                                    @endif
                                </div>

                                <!-- New Kopsurat Preview -->
                                <div id="newKopsuratPreview" style="display: none;" class="text-center">
                                    <small class="text-muted d-block mb-2">New Kopsurat Preview:</small>
                                    <img id="kopsuratPreviewImage" class="img-thumbnail" style="max-height: 120px; max-width: 100%;">
                                </div>

                                <small class="form-text text-muted d-block text-center">
                                    Format: PNG, ICO, JPG, JPEG. Max: 2MB.<br>
                                    Kopsurat will be displayed on attendance export PDFs.
                                </small>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="row mt-4">
                    <div class="col-12">
                        <button type="submit" class="btn btn-primary btn-lg">
                            <i class="fas fa-save mr-2"></i>Save All Settings
                        </button>
                        <button type="reset" class="btn btn-outline-secondary btn-lg ml-2">
                            <i class="fas fa-undo mr-2"></i>Reset Form
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <!-- Location Selection Map -->
    <div class="card shadow mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0">
                <i class="fas fa-map-marker-alt mr-2"></i>Select Office Location
            </h5>
        </div>
        <div class="card-body">
            <div class="alert alert-info">
                <i class="fas fa-info-circle mr-2"></i>Click on the map to select the office location.
                The blue circle shows the allowed attendance radius.
            </div>

            <!-- Map Container -->
            <div id="locationPickerMap" style="height: 500px; width: 100%; border: 1px solid #ddd; border-radius: 8px;">
                <div id="mapLoading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; text-align: center;">
                    <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading map...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading map...</p>
                </div>
            </div>

            <div class="mt-3 text-center">
                <button id="useCurrentLocation" class="btn btn-info">
                    <i class="fas fa-location-arrow mr-1"></i> Use Current Location
                </button>
                <button id="resetLocation" class="btn btn-outline-secondary ml-2">
                    <i class="fas fa-undo mr-1"></i> Reset to Default
                </button>
            </div>
        </div>
    </div>

    <!-- Current Location Map (Read-only) -->
    <div class="card shadow mb-4">
        <div class="card-header bg-info text-white">
            <h5 class="mb-0">
                <i class="fas fa-map mr-2"></i>Current Office Location Preview
            </h5>
        </div>
        <div class="card-body">
            <div id="officeMap" style="height: 400px; width: 100%; border: 1px solid #ddd; border-radius: 8px;">
                <div id="previewMapLoading" style="position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 1000; text-align: center;">
                    <div class="spinner-border text-info" role="status" style="width: 3rem; height: 3rem;">
                        <span class="sr-only">Loading preview map...</span>
                    </div>
                    <p class="mt-2 text-muted">Loading preview map...</p>
                </div>
            </div>
            <div class="mt-3 text-center">
                <small class="text-muted">
                    Latitude: {{ $settings['office_lat'] }}, Longitude: {{ $settings['office_lng'] }}
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

<script>
let locationPickerMap, officePreviewMap;
let selectionMarker, officeMarker, radiusCircle, previewRadiusCircle;
let officeLat, officeLng, maxDistance;

// Initialize maps when page loads
document.addEventListener('DOMContentLoaded', function() {
    initializeMaps();
});

// File input handler for logo and kopsurat
document.addEventListener('DOMContentLoaded', function() {
    function setupFileInput(inputId, previewId, labelSelector) {
        const fileInput = document.getElementById(inputId);
        const fileLabel = document.querySelector(labelSelector);
        const newPreview = document.getElementById(previewId);
        const previewImage = newPreview ? newPreview.querySelector('img') || newPreview.querySelector('img') : null;

        if (fileInput && fileLabel) {
            fileInput.addEventListener('change', function(e) {
                const file = e.target.files[0];

                if (file) {
                    const fileName = file.name;
                    fileLabel.textContent = fileName;

                    const allowedTypes = ['image/png', 'image/x-icon', 'image/jpeg', 'image/jpg'];
                    if (!allowedTypes.includes(file.type)) {
                        alert('Please select a valid image file (PNG, ICO, JPG, JPEG)');
                        fileInput.value = '';
                        fileLabel.textContent = 'Choose file...';
                        if (newPreview) newPreview.style.display = 'none';
                        return;
                    }

                    if (file.size > 2 * 1024 * 1024) {
                        alert('File size must be less than 2MB');
                        fileInput.value = '';
                        fileLabel.textContent = 'Choose file...';
                        if (newPreview) newPreview.style.display = 'none';
                        return;
                    }

                    const reader = new FileReader();
                    reader.onload = function(e) {
                        if (previewImage) {
                            previewImage.src = e.target.result;
                        }
                        if (newPreview) newPreview.style.display = 'block';
                    }
                    reader.readAsDataURL(file);

                } else {
                    fileLabel.textContent = 'Choose file...';
                    if (newPreview) newPreview.style.display = 'none';
                }
            });
        }
    }

    setupFileInput('logo', 'newLogoPreview', '.custom-file-label[for="logo"]');
    setupFileInput('kopsurat', 'newKopsuratPreview', '.custom-file-label[for="kopsurat"]');
});

// Form submission handler to show loading state
document.getElementById('settingsForm').addEventListener('submit', function(e) {
    const submitBtn = this.querySelector('button[type="submit"]');
    const originalText = submitBtn.innerHTML;

    submitBtn.disabled = true;
    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin mr-2"></i>Saving Settings...';

    // Re-enable after 10 seconds as fallback
    setTimeout(function() {
        submitBtn.disabled = false;
        submitBtn.innerHTML = originalText;
    }, 10000);
});

function initializeMaps() {
    // Get elements
    const latInput = document.getElementById('office_lat');
    const lngInput = document.getElementById('office_lng');
    const distanceInput = document.getElementById('max_distance');

    // Office location from settings
    officeLat = parseFloat(latInput.value || {{ $settings['office_lat'] }});
    officeLng = parseFloat(lngInput.value || {{ $settings['office_lng'] }});
    maxDistance = parseInt(distanceInput.value || {{ $settings['max_distance'] }});

    // Fallback to Jakarta if coordinates are invalid
    if (isNaN(officeLat) || isNaN(officeLng)) {
        officeLat = -6.2088;
        officeLng = 106.8456;
    }

    console.log('Initializing with coordinates:', officeLat, officeLng);

    // Initialize Location Picker Map
    try {
        const mapElement = document.getElementById('locationPickerMap');
        if (mapElement) {
            // Hide loading indicator
            const loadingElement = document.getElementById('mapLoading');
            if (loadingElement) loadingElement.style.display = 'none';

            // Initialize map
            locationPickerMap = L.map('locationPickerMap').setView([officeLat, officeLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(locationPickerMap);

            // Add selection marker
            selectionMarker = L.marker([officeLat, officeLng], {
                draggable: true
            }).addTo(locationPickerMap);

            // Add radius circle
            updateRadiusCircle(officeLat, officeLng);

            // Event handlers
            selectionMarker.on('dragend', function(e) {
                const position = selectionMarker.getLatLng();
                updateLocationFields(position.lat, position.lng);
                updateRadiusCircle(position.lat, position.lng);
                updateOfficePreviewMap(position.lat, position.lng);
            });

            locationPickerMap.on('click', function(e) {
                selectionMarker.setLatLng(e.latlng);
                updateLocationFields(e.latlng.lat, e.latlng.lng);
                updateRadiusCircle(e.latlng.lat, e.latlng.lng);
                updateOfficePreviewMap(e.latlng.lat, e.latlng.lng);
            });
        }
    } catch (error) {
        console.error('Error initializing location picker map:', error);
    }

    // Initialize Office Preview Map
    try {
        const previewElement = document.getElementById('officeMap');
        if (previewElement) {
            // Hide loading indicator
            const loadingElement = document.getElementById('previewMapLoading');
            if (loadingElement) loadingElement.style.display = 'none';

            // Initialize map
            officePreviewMap = L.map('officeMap').setView([officeLat, officeLng], 15);

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(officePreviewMap);

            // Office marker
            officeMarker = L.marker([officeLat, officeLng]).addTo(officePreviewMap)
                .bindPopup(`<b>Office Location</b><br>Lat: ${officeLat.toFixed(6)}, Lng: ${officeLng.toFixed(6)}<br>Max Distance: ${maxDistance}m`);

            // Add radius circle
            previewRadiusCircle = L.circle([officeLat, officeLng], {
                color: '#4e73df',
                fillColor: '#4e73df',
                fillOpacity: 0.2,
                radius: maxDistance
            }).addTo(officePreviewMap).bindPopup(`Valid Attendance Radius: ${maxDistance}m`);
        }
    } catch (error) {
        console.error('Error initializing office preview map:', error);
    }

    // Distance input change handler
    if (distanceInput) {
        distanceInput.addEventListener('input', function() {
            maxDistance = parseInt(this.value) || 50;
            updateRadiusCircle(officeLat, officeLng);
            updateOfficePreviewMap(officeLat, officeLng);
        });
    }

    // Use current location button
    const useCurrentLocationBtn = document.getElementById('useCurrentLocation');
    if (useCurrentLocationBtn) {
        useCurrentLocationBtn.addEventListener('click', function() {
            if (!navigator.geolocation) {
                alert('Browser tidak mendukung geolokasi');
                return;
            }

            this.disabled = true;
            this.innerHTML = '<i class="fas fa-spinner fa-spin mr-1"></i> Getting location...';

            navigator.geolocation.getCurrentPosition(
                function(position) {
                    const lat = position.coords.latitude;
                    const lng = position.coords.longitude;

                    if (selectionMarker) {
                        selectionMarker.setLatLng([lat, lng]);
                    }
                    updateLocationFields(lat, lng);
                    updateRadiusCircle(lat, lng);
                    updateOfficePreviewMap(lat, lng);

                    if (locationPickerMap) {
                        locationPickerMap.setView([lat, lng], 15);
                    }

                    useCurrentLocationBtn.disabled = false;
                    useCurrentLocationBtn.innerHTML = '<i class="fas fa-location-arrow mr-1"></i> Use Current Location';

                    alert('Current location has been set successfully!');
                },
                function(error) {
                    useCurrentLocationBtn.disabled = false;
                    useCurrentLocationBtn.innerHTML = '<i class="fas fa-location-arrow mr-1"></i> Use Current Location';
                    alert('Error getting location: ' + error.message);
                }
            );
        });
    }

    // Reset location button
    const resetLocationBtn = document.getElementById('resetLocation');
    if (resetLocationBtn) {
        resetLocationBtn.addEventListener('click', function() {
            const defaultLat = -6.2088;
            const defaultLng = 106.8456;

            if (selectionMarker) {
                selectionMarker.setLatLng([defaultLat, defaultLng]);
            }
            updateLocationFields(defaultLat, defaultLng);
            updateRadiusCircle(defaultLat, defaultLng);
            updateOfficePreviewMap(defaultLat, defaultLng);

            if (locationPickerMap) {
                locationPickerMap.setView([defaultLat, defaultLng], 15);
            }

            alert('Location reset to Jakarta');
        });
    }
}

// Helper functions
function updateLocationFields(lat, lng) {
    const latInput = document.getElementById('office_lat');
    const lngInput = document.getElementById('office_lng');

    if (latInput && lngInput) {
        officeLat = lat;
        officeLng = lng;
        latInput.value = lat.toFixed(8);
        lngInput.value = lng.toFixed(8);
    }
}

function updateRadiusCircle(lat, lng) {
    if (radiusCircle && locationPickerMap) {
        locationPickerMap.removeLayer(radiusCircle);
    }

    if (locationPickerMap) {
        radiusCircle = L.circle([lat, lng], {
            color: '#007bff',
            fillColor: '#007bff',
            fillOpacity: 0.2,
            radius: maxDistance
        }).addTo(locationPickerMap);
    }
}

function updateOfficePreviewMap(lat, lng) {
    if (officePreviewMap && officeMarker) {
        officePreviewMap.setView([lat, lng], 15);
        officeMarker.setLatLng([lat, lng]);
        officeMarker.getPopup().setContent(`<b>Office Location</b><br>Lat: ${lat.toFixed(6)}, Lng: ${lng.toFixed(6)}<br>Max Distance: ${maxDistance}m`);

        if (previewRadiusCircle) {
            officePreviewMap.removeLayer(previewRadiusCircle);
        }

        previewRadiusCircle = L.circle([lat, lng], {
            color: '#4e73df',
            fillColor: '#4e73df',
            fillOpacity: 0.2,
            radius: maxDistance
        }).addTo(officePreviewMap);
    }
}
</script>

@endsection
