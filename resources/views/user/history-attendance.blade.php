@extends('user.layouts')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-10 col-lg-8">
            <div class="card shadow-lg border-0 rounded-lg">
                <div class="card-header bg-gradient-primary text-white">
                    <div class="d-flex justify-content-between align-items-center">
                        <h5 class="mb-0">
                            <i class="fas fa-history mr-2"></i>Rekap 7 Hari Terakhir
                        </h5>
                        <span class="badge badge-light">
                            <i class="fas fa-calendar-alt mr-1"></i>
                            {{ \Carbon\Carbon::now()->isoFormat('D MMM YYYY') }}
                        </span>
                    </div>
                </div>
                
                <div class="card-body">
                    <div class="welcome-section mb-4 p-3 bg-light rounded">
                        <div class="d-flex align-items-center">
                            <div class="avatar-circle bg-primary mr-3">
                                <i class="fas fa-user text-white"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 text-primary">Hai, <strong>{{ ucfirst(session('username')) }}</strong></h6>
                                <small class="text-muted">Selamat datang di sistem absensi</small>
                            </div>
                        </div>
                    </div>

                    @if($histories->isEmpty())
                        <div class="text-center py-5">
                            <div class="empty-state">
                                <i class="fas fa-calendar-times fa-3x text-muted mb-3"></i>
                                <h5 class="text-muted">Tidak ada riwayat</h5>
                                <p class="text-muted">Tidak ada riwayat kehadiran dalam 7 hari terakhir</p>
                            </div>
                        </div>
                    @else
                        <div class="timeline">
                            @foreach($histories as $history)
                                <div class="timeline-item">
                                    <div class="timeline-marker">
                                        @if($history->type === 'hadir')
                                            <div class="timeline-icon bg-success">
                                                <i class="fas fa-check"></i>
                                            </div>
                                        @else
                                            {{-- Untuk izin --}}
                                            @if(isset($history->status) && $history->status === 'approved')
                                                <div class="timeline-icon bg-success">
                                                    <i class="fas fa-check"></i>
                                                </div>
                                            @elseif(isset($history->status) && $history->status === 'rejected')
                                                <div class="timeline-icon bg-danger">
                                                    <i class="fas fa-times"></i>
                                                </div>
                                            @else
                                                <div class="timeline-icon bg-warning">
                                                    <i class="fas fa-clock"></i>
                                                </div>
                                            @endif
                                        @endif
                                    </div>
                                    <div class="timeline-content card shadow-sm">
                                        <div class="card-body">
                                            <div class="d-flex justify-content-between align-items-start mb-2">
                                                <h6 class="mb-0 font-weight-bold">
                                                    @if($history->type === 'hadir')
                                                        Hadir
                                                    @else
                                                        {{ ucfirst($history->present_at) }}
                                                    @endif
                                                </h6>
                                                <span class="badge badge-{{ 
                                                    $history->type === 'hadir' ? 'success' : 
                                                    (isset($history->status) && $history->status === 'approved' ? 'success' : 
                                                    (isset($history->status) && $history->status === 'rejected' ? 'danger' : 'warning')) 
                                                }}">
                                                    @if($history->type === 'hadir')
                                                        Hadir
                                                    @else
                                                        {{ isset($history->status) ? 
                                                            ($history->status === 'approved' ? 'Disetujui' : 
                                                            ($history->status === 'rejected' ? 'Ditolak' : 'Menunggu')) : 
                                                            'Menunggu' 
                                                        }}
                                                    @endif
                                                </span>
                                            </div>
                                            
                                            <div class="d-flex justify-content-between align-items-center">
                                                <div>
                                                    <small class="text-muted">
                                                        <i class="fas fa-calendar-day mr-1"></i>
                                                        {{ $history->created_at->isoFormat('dddd, D MMMM YYYY') }}
                                                    </small>
                                                    <br>
                                                    <small class="text-muted">
                                                        <i class="fas fa-clock mr-1"></i>
                                                        {{ $history->created_at->format('H:i') }} WIB
                                                    </small>
                                                </div>
                                                <div class="text-right">
                                                    @if($history->description)
                                                        <button class="btn btn-sm btn-outline-primary" data-toggle="collapse"
                                                                data-target="#detail-{{ $history->id }}" aria-expanded="false">
                                                            <i class="fas fa-info-circle"></i> Detail
                                                        </button>
                                                    @endif
                                                    @if($history->checkout_at)
                                                        <button class="btn btn-sm btn-outline-success ml-1" data-toggle="collapse"
                                                                data-target="#checkout-detail-{{ $history->id }}" aria-expanded="false">
                                                            <i class="fas fa-sign-out-alt"></i> Checkout
                                                        </button>
                                                    @endif
                                                </div>
                                            </div>
                                            
                                            @if($history->description || $history->photo_path)
                                                <div class="collapse mt-3" id="detail-{{ $history->id }}">
                                                    <div class="detail-card p-3 bg-light rounded">
                                                        @if($history->description)
                                                            <h6 class="text-primary mb-2">
                                                                <i class="fas fa-sticky-note mr-1"></i>Keterangan
                                                            </h6>
                                                            <p class="mb-0 text-dark">{{ $history->description }}</p>
                                                        @endif
                                                        @if($history->photo_path)
                                                            <h6 class="text-primary mb-2 mt-3">
                                                                <i class="fas fa-camera mr-1"></i>Foto Absensi
                                                            </h6>
                                                            <img src="{{ asset('storage/' . $history->photo_path) }}" alt="Foto Absensi" class="img-fluid rounded" style="max-width: 200px;">
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                            @if($history->checkout_at)
                                                <div class="collapse mt-3" id="checkout-detail-{{ $history->id }}">
                                                    <div class="detail-card p-3 bg-light rounded">
                                                        <h6 class="text-primary mb-2">
                                                            <i class="fas fa-sign-out-alt mr-1"></i>Checkout
                                                        </h6>
                                                        <p class="mb-0 text-dark">
                                                            Waktu Checkout: {{ $history->checkout_at->format('H:i') }} WIB<br>
                                                            Durasi Kerja: {{ $history->work_duration_formatted ?: '0 jam 0 menit' }}<br>
                                                            Jarak Checkout: {{ $history->checkout_distance ? round($history->checkout_distance) : '-' }} meter
                                                        </p>
                                                        @if($history->checkout_photo_path)
                                                            <img src="{{ asset('storage/' . $history->checkout_photo_path) }}" alt="Foto Checkout" class="img-fluid rounded mt-2" style="max-width: 200px;">
                                                        @endif
                                                    </div>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <div class="mt-4 text-center">
                        <a href="{{ url('user/home') }}" class="btn btn-primary btn-lg">
                            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('styles')
<style>
    .bg-gradient-primary {
        background: linear-gradient(135deg, #4e73df 0%, #224abe 100%) !important;
    }
    
    .avatar-circle {
        width: 50px;
        height: 50px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
    }
    
    .welcome-section {
        background-color: #f8f9fc !important;
        border-left: 4px solid #4e73df;
    }
    
    .timeline {
        position: relative;
        padding-left: 3rem;
    }
    
    .timeline:before {
        content: '';
        position: absolute;
        left: 15px;
        top: 0;
        bottom: 0;
        width: 2px;
        background-color: #e3e6f0;
    }
    
    .timeline-item {
        position: relative;
        margin-bottom: 1.5rem;
    }
    
    .timeline-marker {
        position: absolute;
        left: -3rem;
        top: 0;
        z-index: 2;
    }
    
    .timeline-icon {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 0.8rem;
        box-shadow: 0 0 0 4px white, 0 2px 5px rgba(0,0,0,0.15);
    }
    
    .timeline-content {
        transition: transform 0.2s ease, box-shadow 0.2s ease;
        border-radius: 0.5rem;
        overflow: hidden;
    }
    
    .timeline-content:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 15px rgba(0,0,0,0.1) !important;
    }
    
    .detail-card {
        border-left: 3px solid #4e73df;
    }
    
    .empty-state {
        opacity: 0.7;
    }
    
    @media (max-width: 768px) {
        .timeline {
            padding-left: 2.5rem;
        }
        
        .timeline-marker {
            left: -2.5rem;
        }
        
        .card-header h5 {
            font-size: 1.1rem;
        }
    }
</style>
@endsection

@section('scripts')
<script>
    $(document).ready(function() {
        // Animasi untuk timeline items
        $('.timeline-item').each(function(i) {
            $(this).delay(i * 200).animate({ opacity: 1 }, 400);
        });
        
        // Tooltip untuk button detail
        $('[data-toggle="collapse"]').tooltip({
            title: "Klik untuk melihat detail",
            placement: "top"
        });
    });
</script>
@endsection