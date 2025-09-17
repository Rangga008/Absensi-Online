@extends('user.layouts')

@section('content')

<div class="card p-3 shadow rounded">
    <div id="msg"></div>
    <div>
        Hai <b>{{ ucfirst(session('username')) }}</b>, silakan lakukan checkout di {{ strtoupper(setting('company_name', 'SMK NEGERI 2 BANDUNG')) }}. <br />
        <small>{{ date('D, d F Y') }}</small>
    </div>

    <hr />

    <!-- Checkout Status -->
    <div class="alert alert-info" id="checkout-status">
        <i class="fas fa-info-circle"></i> Memeriksa status checkout...
    </div>

    <!-- Status Location -->
    <div class="alert alert-info" id="location-status">
        <i class="fas fa-info-circle"></i> Mengambil lokasi Anda...
    </div>

    <!-- Camera Section -->
    <div class="card mb-3">
        <div class="card-header">
            <h6 class="mb-0"><i class="fas fa-camera"></i> Foto Checkout</h6>
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
                                <p>Foto checkout akan muncul di sini</p>
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
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Latitude:</small> 
                        <span id="current-lat" class="font-weight-bold">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Longitude:</small> 
                        <span id="current-lng" class="font-weight-bold">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Akurasi:</small> 
                        <span id="accuracy" class="font-weight-bold">-</span>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-6">
            <div class="card">
                <div class="card-body">
                    <h6 class="card-title"><i class="fas fa-ruler"></i> Jarak</h6>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Dari {{ setting('company_name', 'SMKN 2 Bandung') }}:</small> 
                        <span id="distance" class="font-weight-bold">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Status:</small> 
                        <span id="location-validation" class="badge badge-secondary">-</span>
                    </div>
                    <div class="d-flex justify-content-between">
                        <small class="text-muted">Pembaruan:</small> 
                        <span id="location-updates" class="font-weight-bold">0</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Checkout Form -->
    <div class="mb-3 text-center">
        <input type="hidden" id="user_id" value="{{ session('user_id') }}">
        <input type="hidden" id="user_lat" value="">
        <input type="hidden" id="user_lng" value="">
        
        <button class="btn btn-danger mt-3" id="checkout-btn" disabled>
            <i class="fas fa-sign-out-alt"></i> Checkout Sekarang
        </button>
    </div>
    
    <div id="result"></div>
</div>

<!-- Leaflet CSS -->
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />

<!-- Leaflet JS -->
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

@include('user.scripts.checkout')

@endsection