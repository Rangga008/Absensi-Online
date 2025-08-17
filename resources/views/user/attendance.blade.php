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

    <!-- Camera Section -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-camera"></i> Foto Absensi</h6>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <div class="camera-container">
                        <video id="camera-video" width="100%" height="300" autoplay muted></video>
                        <canvas id="camera-canvas" style="display: none;"></canvas>
                        <div class="camera-controls mt-2">
                            <button type="button" class="btn btn-primary" id="start-camera">
                                <i class="fas fa-video"></i> Buka Kamera
                            </button>
                            <button type="button" class="btn btn-success" id="take-photo" style="display: none;">
                                <i class="fas fa-camera"></i> Ambil Foto
                            </button>
                            <button type="button" class="btn btn-warning" id="retake-photo" style="display: none;">
                                <i class="fas fa-redo"></i> Ambil Ulang
                            </button>
                        </div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="photo-preview">
                        <img id="photo-preview" style="width: 100%; height: 300px; object-fit: cover; border: 1px solid #ddd; border-radius: 8px; display: none;">
                        <div id="no-photo" class="text-center text-muted" style="height: 300px; display: flex; align-items: center; justify-content: center; border: 2px dashed #ddd; border-radius: 8px;">
                            <div>
                                <i class="fas fa-camera fa-3x mb-2"></i>
                                <p>Foto absensi akan muncul di sini</p>
                            </div>
                        </div>
                        <input type="hidden" id="photo-data" name="photo_data">
                    </div>
                </div>
            </div>
        </div>
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

@include('user.scripts.attendance')

@endsection
