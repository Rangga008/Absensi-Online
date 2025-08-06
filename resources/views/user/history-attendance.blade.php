@extends('user.layouts')

@section('content')
<div class="card p-3 shadow rounded">
    <div class="welcome-message mb-4">
        <h5>Hai <b>{{ ucfirst(session('username')) }}</b>, selamat datang di sistem absensi</h5>
        <small class="text-muted">{{ \Carbon\Carbon::now()->isoFormat('dddd, D MMMM Y') }}</small>
    </div>

    <hr>

    <div class="attendance-history">
        <h5 class="mb-3"><i class="fas fa-history mr-2"></i>Riwayat 7 Hari Terakhir</h5>
        
        @if($histories->isEmpty())
            <div class="alert alert-info">
                <i class="fas fa-info-circle"></i> Tidak ada riwayat dalam 7 hari terakhir
            </div>
        @else
            <div class="list-group">
                @foreach($histories as $history)
                    <div class="list-group-item list-group-item-action flex-column align-items-start">
                        <div class="d-flex w-100 justify-content-between">
                            <div class="d-flex align-items-center">
                                @if(in_array(strtolower($history->present_at), ['cuti', 'sakit', 'izin']))
                                    <span class="status-icon text-danger mr-3"><i class="fas fa-times-circle fa-lg"></i></span>
                                    <h6 class="mb-1">{{ ucfirst($history->present_at) }}</h6>
                                @else
                                    <span class="status-icon text-success mr-3"><i class="fas fa-check-circle fa-lg"></i></span>
                                    <h6 class="mb-1">Hadir</h6>
                                @endif
                            </div>
                            <small class="text-muted">
                                {{ $history->created_at->isoFormat('dddd, D MMMM Y') }}
                            </small>
                        </div>
                        <div class="mt-2">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> 
                                {{ $history->created_at->format('H:i') }} WIB
                            </small>
                            @if($history->description)
                                <div class="mt-1">
                                    <small><strong>Keterangan:</strong> {{ $history->description }}</small>
                                </div>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <div class="mt-4">
        <a href="{{ url('user/home') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left mr-2"></i> Kembali ke Beranda
        </a>
    </div>
</div>

<style>
    .welcome-message {
        background-color: #f8f9fa;
        padding: 15px;
        border-radius: 8px;
    }
    
    .attendance-history {
        background-color: #fff;
        border-radius: 8px;
    }
    
    .status-icon {
        width: 30px;
        text-align: center;
    }
    
    .list-group-item {
        border-left: none;
        border-right: none;
    }
    
    .list-group-item:first-child {
        border-top: none;
    }
</style>
@endsection