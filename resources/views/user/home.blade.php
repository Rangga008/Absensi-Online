@extends('user.layouts')

@section('content')
<div class="container-fluid">
    @if(session()->has('message'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('message') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
    @endif

    <!-- Carousel -->
    <div id="infoCarousel" class="carousel slide mb-4" data-ride="carousel" data-interval="3000" data-wrap="true">
        <ol class="carousel-indicators">
            <li data-target="#infoCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#infoCarousel" data-slide-to="1"></li>
            <li data-target="#infoCarousel" data-slide-to="2"></li>
            <li data-target="#infoCarousel" data-slide-to="3"></li>
            <li data-target="#infoCarousel" data-slide-to="4"></li>
            <li data-target="#infoCarousel" data-slide-to="5"></li>
            <li data-target="#infoCarousel" data-slide-to="6"></li>
            <li data-target="#infoCarousel" data-slide-to="7"></li>
        </ol>
        
        <div class="carousel-inner rounded-lg">
            <div class="carousel-item active">
                <img class="d-block w-100" src="{{ asset('images/Photo1.jpg') }}" alt="Slide 1">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Sistem {{ strtoupper(setting('company_name', 'SMK NEGERI 2 BANDUNG')) }}</h5>
                    <p>Mudah, cepat, dan akurat</p>
                </div>
            </div>
            
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/Photo2.jpg') }}" alt="Slide 2">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Pantau Kehadiran</h5>
                    <p>History kehadiran tersimpan rapi</p>
                </div>
            </div>
            
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/Photo3.jpg') }}" alt="Slide 3">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Teknologi Modern</h5>
                    <p>Menggunakan teknologi terkini</p>
                </div>
            </div>
            
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/Photo4.jpg') }}" alt="Slide 4">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Fasilitas Lengkap</h5>
                    <p>Mendukung kegiatan pembelajaran</p>
                </div>
            </div>
            
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/Photo5.jpg') }}" alt="Slide 5">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Lingkungan Belajar</h5>
                    <p>Suasana kondusif untuk belajar</p>
                </div>
            </div>
            
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/Photo6.jpg') }}" alt="Slide 6">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Prestasi Terbaik</h5>
                    <p>Meraih berbagai prestasi</p>
                </div>
            </div>
            
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/Photo7.jpg') }}" alt="Slide 7">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Guru Berkualitas</h5>
                    <p>Tenaga pengajar profesional</p>
                </div>
            </div>
            
            <div class="carousel-item">
                <img class="d-block w-100" src="{{ asset('images/Photo8.jpg') }}" alt="Slide 8">
                <div class="carousel-caption d-none d-md-block">
                    <h5>Izin Online</h5>
                    <p>Ajukan izin dengan mudah</p>
                </div>
            </div>
        </div>

        <a class="carousel-control-prev" href="#infoCarousel" role="button" data-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="sr-only">Previous</span>
        </a>
        <a class="carousel-control-next" href="#infoCarousel" role="button" data-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="sr-only">Next</span>
        </a>
    </div>

    <!-- Quick Actions -->
    <div class="row mb-4">
        <div class="col-md-6 mb-3">
            <a href="{{ url('user/attendance') }}" class="card action-card h-100 text-decoration-none">
                <div class="card-body text-center">
                    <div class="action-icon bg-primary-light text-primary mb-3">
                        <i class="fas fa-fingerprint"></i>
                    </div>
                    <h5 class="card-title">Absen Online</h5>
                    <p class="card-text text-muted">Lakukan presensi harian Anda</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 mb-3">
            <a href="{{ route('user.concession.create') }}" class="card action-card h-100 text-decoration-none">
                <div class="card-body text-center">
                    <div class="action-icon bg-warning-light text-warning mb-3">
                        <i class="fas fa-envelope-open-text"></i>
                    </div>
                    <h5 class="card-title">Izin Online</h5>
                    <p class="card-text text-muted">Ajukan permohonan izin</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 mb-3">
            <a href="{{ url('user/history') }}" class="card action-card h-100 text-decoration-none">
                <div class="card-body text-center">
                    <div class="action-icon bg-info-light text-info mb-3">
                        <i class="fas fa-history"></i>
                    </div>
                    <h5 class="card-title">History Absen</h5>
                    <p class="card-text text-muted">Lihat riwayat kehadiran</p>
                </div>
            </a>
        </div>
        <div class="col-md-6 mb-3">
            <form action="{{ route('user.logout') }}" method="POST" class="h-100">
                @csrf
                <button type="submit" class="card action-card h-100 w-100 border-0 text-decoration-none">
                    <div class="card-body text-center">
                        <div class="action-icon bg-danger-light text-danger mb-3">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <h5 class="card-title">Logout</h5>
                        <p class="card-text text-muted">Keluar dari sistem</p>
                    </div>
                </button>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    :root {
        --primary-light: #e3f2fd;
        --warning-light: #fff8e1;
        --info-light: #e1f5fe;
        --danger-light: #ffebee;
    }
    
    body {
        background-color: #f8f9fa;
    }
    
    .avatar {
        width: 50px;
        height: 50px;
        font-size: 1.5rem;
        font-weight: bold;
    }
    
    .action-card {
        transition: transform 0.2s, box-shadow 0.2s;
        border: none;
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.05);
    }
    
    .action-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    }
    
    .action-icon {
        width: 60px;
        height: 60px;
        border-radius: 50%;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        font-size: 1.5rem;
    }
    
    .bg-primary-light {
        background-color: var(--primary-light);
    }
    
    .bg-warning-light {
        background-color: var(--warning-light);
    }
    
    .bg-info-light {
        background-color: var(--info-light);
    }
    
    .bg-danger-light {
        background-color: var(--danger-light);
    }
    
    /* Carousel Styles - Using background-image for consistent sizing */
    .carousel-item {
        width: 100%;
        height: 400px;
        position: relative;
        background-size: cover;
        background-position: center;
        background-repeat: no-repeat;
        display: flex;
        align-items: end;
    }

    /* Remove img specific styles since we're using background-image now */

    /* Responsive heights */
    @media (max-width: 576px) {
        .carousel-item {
            height: 250px;
        }
    }

    @media (min-width: 577px) and (max-width: 768px) {
        .carousel-item {
            height: 300px;
        }
    }

    @media (min-width: 769px) and (max-width: 992px) {
        .carousel-item {
            height: 350px;
        }
    }

    @media (min-width: 993px) and (max-width: 1200px) {
        .carousel-item {
            height: 400px;
        }
    }

    @media (min-width: 1201px) {
        .carousel-item {
            height: 450px;
        }
    }

    /* Carousel caption styling */
    .carousel-caption {
        position: absolute;
        bottom: 20px;
        left: 15%;
        right: 15%;
        padding: 20px;
        background: rgba(0, 0, 0, 0.7);
        border-radius: 10px;
        backdrop-filter: blur(10px);
    }

    .carousel-caption h5 {
        font-size: 1.5rem;
        font-weight: bold;
        margin-bottom: 10px;
        color: #fff;
    }

    .carousel-caption p {
        font-size: 1rem;
        margin-bottom: 0;
        color: #f8f9fa;
    }

    /* Carousel controls styling */
    .carousel-control-prev,
    .carousel-control-next {
        width: 5%;
        opacity: 0.8;
    }

    .carousel-control-prev:hover,
    .carousel-control-next:hover {
        opacity: 1;
    }

    .carousel-control-prev-icon,
    .carousel-control-next-icon {
        width: 30px;
        height: 30px;
        background-size: 100%, 100%;
    }

    /* Carousel indicators */
    .carousel-indicators {
        bottom: 10px;
    }

    .carousel-indicators li {
        width: 12px;
        height: 12px;
        border-radius: 50%;
        margin: 0 3px;
        background-color: rgba(255, 255, 255, 0.5);
        border: 2px solid rgba(255, 255, 255, 0.8);
    }

    .carousel-indicators .active {
        background-color: #fff;
        border-color: #fff;
    }

    /* Smooth transitions */
    .carousel-inner {
        border-radius: 15px;
        overflow: hidden;
        box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
    }
    
    .navbar {
        box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
    }
    
    #footer {
        padding: 20px 0;
        background-color: #f8f9fa;
        margin-top: 40px;
        border-top: 1px solid #eee;
    }
</style>
@endsection

@section('scripts')
<!-- Font Awesome for icons -->
<script src="https://kit.fontawesome.com/a076d05399.js" crossorigin="anonymous"></script>

<!-- Custom Carousel JavaScript -->
<script>
$(document).ready(function() {
    // Initialize carousel with proper looping
    $('#infoCarousel').carousel({
        interval: 3000,  // 3 seconds
        wrap: true,      // Enable infinite looping
        pause: 'hover',  // Pause on hover
        keyboard: true   // Enable keyboard navigation
    });

    // Ensure smooth looping
    $('#infoCarousel').on('slide.bs.carousel', function (e) {
        // Add any custom slide effects here if needed
    });

    // Auto-restart after manual navigation
    $('#infoCarousel').on('slid.bs.carousel', function (e) {
        $(this).carousel('cycle');
    });
});
</script>
@endsection