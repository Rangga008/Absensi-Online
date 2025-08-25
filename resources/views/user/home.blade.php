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

   
    </div>

    <!-- Carousel -->
    <div id="infoCarousel" class="carousel slide mb-4" data-ride="carousel">
        <ol class="carousel-indicators">
            <li data-target="#infoCarousel" data-slide-to="0" class="active"></li>
            <li data-target="#infoCarousel" data-slide-to="1"></li>
            <li data-target="#infoCarousel" data-slide-to="2"></li>
        </ol>
        <div class="carousel-inner rounded-lg">
            <div class="carousel-item active">
                <img class="d-block w-100" src="https://images.pexels.com/photos/3184357/pexels-photo-3184357.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940" alt="First slide">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h5>Sistem Absensi Online</h5>
                    <p>Mudah, cepat, dan akurat</p>
                </div>
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="https://images.pexels.com/photos/840996/pexels-photo-840996.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940" alt="Second slide">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
                    <h5>Pantau Kehadiran</h5>
                    <p>History kehadiran tersimpan rapi</p>
                </div>
            </div>
            <div class="carousel-item">
                <img class="d-block w-100" src="https://images.pexels.com/photos/2041627/pexels-photo-2041627.jpeg?auto=compress&cs=tinysrgb&dpr=2&h=650&w=940" alt="Third slide">
                <div class="carousel-caption d-none d-md-block bg-dark bg-opacity-50 rounded">
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
    
    .carousel-item img {
        height: 200px;
        object-fit: cover;
    }
    
    @media (min-width: 768px) {
        .carousel-item img {
            height: 250px;
        }
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
@endsection