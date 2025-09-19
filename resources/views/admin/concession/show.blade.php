@extends('layouts.admin')

@section('title', 'Detail Pengajuan Izin - ' . $concession->user->name)

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-alt mr-2"></i>Detail Pengajuan Izin
        </h1>
        <div>
            <a href="{{ route('admin.concessions.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-1"></i> Kembali
            </a>
            <a href="{{ route('admin.concessions.exportPdf', $concession->id) }}" class="btn btn-primary ml-2" target="_blank">
                <i class="fas fa-file-pdf mr-1"></i> Export PDF
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i>Informasi Pengajuan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>User:</strong></label>
                                <p class="form-control-plaintext">
                                    <div class="d-flex align-items-center">
                                        @if($concession->user->profile_photo_path)
                                            <img src="{{ asset('storage/' . $concession->user->profile_photo_path) }}" 
                                                 class="rounded-circle mr-2" 
                                                 width="40" 
                                                 height="40" 
                                                 alt="{{ $concession->user->name }}">
                                        @else
                                            <div class="avatar bg-primary text-white rounded-circle mr-2 d-flex align-items-center justify-content-center" 
                                                 style="width: 40px; height: 40px;">
                                                {{ substr($concession->user->name, 0, 1) }}
                                            </div>
                                        @endif
                                        <div>
                                            <strong>{{ $concession->user->name }}</strong>
                                            <br>
                                            <small class="text-muted">{{ $concession->user->email }}</small>
                                        </div>
                                    </div>
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Jenis Izin:</strong></label>
                                <p class="form-control-plaintext">
                                    <span class="badge badge-info badge-pill py-2 px-3 text-uppercase">
                                        {{ $concession->reason }}
                                    </span>
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Tanggal Mulai:</strong></label>
                                <p class="form-control-plaintext text-primary">
                                    <i class="fas fa-calendar-start mr-2"></i>
                                    {{ $concession->formatted_start_date }}
                                </p>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label><strong>Tanggal Selesai:</strong></label>
                                <p class="form-control-plaintext text-primary">
                                    <i class="fas fa-calendar-check mr-2"></i>
                                    {{ $concession->formatted_end_date }}
                                </p>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label><strong>Durasi:</strong></label>
                        <p class="form-control-plaintext">
                            <span class="badge badge-secondary py-2 px-3">
                                <i class="fas fa-clock mr-2"></i>
                                {{ $concession->duration }} hari
                            </span>
                        </p>
                    </div>

                    <div class="form-group">
                        <label><strong>Alasan/Keterangan:</strong></label>
                        <div class="border rounded p-3 bg-light">
                            {{ $concession->description }}
                        </div>
                    </div>

                    @if($concession->file_path)
                    <div class="form-group">
                        <label><strong>Bukti Izin:</strong></label>
                        <div class="border rounded p-3 bg-light">
                            @php
                                $fileExt = pathinfo($concession->file_path, PATHINFO_EXTENSION);
                                $hasValidFile = file_exists(public_path($concession->file_path));
                            @endphp
                            @if($hasValidFile)
                                <div class="d-flex align-items-center">
                                    @if(in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png']))
                                        <img src="{{ asset($concession->file_path) }}" alt="Bukti Izin" class="img-thumbnail mr-3" style="max-width: 150px; max-height: 150px;">
                                    @else
                                        <i class="fas fa-file-alt fa-3x text-primary mr-3"></i>
                                    @endif
                                    <div>
                                        <p class="mb-1"><strong>{{ basename($concession->file_path) }}</strong></p>
                                        <p class="mb-2 text-muted">Format: {{ strtoupper($fileExt) }}</p>
                                        <a href="{{ asset($concession->file_path) }}" target="_blank" class="btn btn-sm btn-primary">
                                            <i class="fas fa-download mr-1"></i> Download File
                                        </a>
                                    </div>
                                </div>
                            @else
                                <div class="text-muted">
                                    <i class="fas fa-exclamation-triangle mr-1"></i>
                                    File tidak ditemukan
                                </div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card shadow mb-4">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-tasks mr-2"></i>Status Pengajuan
                    </h6>
                </div>
                <div class="card-body">
                    <div class="form-group">
                        <label><strong>Status:</strong></label>
                        <p>
                            <span class="badge badge-pill py-2 px-3 badge-{{ 
                                $concession->status == 'approved' ? 'success' : 
                                ($concession->status == 'rejected' ? 'danger' : 'warning') 
                            }}">
                                <i class="fas {{ 
                                    $concession->status == 'approved' ? 'fa-check' : 
                                    ($concession->status == 'rejected' ? 'fa-times' : 'fa-clock') 
                                }} mr-1"></i>
                                {{ strtoupper($concession->status) }}
                            </span>
                        </p>
                    </div>

                    <div class="form-group">
                        <label><strong>Diajukan Pada:</strong></label>
                        <p class="form-control-plaintext">
                            <i class="fas fa-calendar-plus mr-2"></i>
                            {{ $concession->formatted_created_at }}
                        </p>
                    </div>

                    @if($concession->approved_at)
                    <div class="form-group">
                        <label><strong>Disetujui/Ditolak Pada:</strong></label>
                        <p class="form-control-plaintext">
                            <i class="fas fa-calendar-check mr-2"></i>
                            {{ $concession->formatted_approved_at }}
                        </p>
                    </div>

                    @if($concession->approver)
                    <div class="form-group">
                        <label><strong>Oleh:</strong></label>
                        <p class="form-control-plaintext">
                            <div class="d-flex align-items-center">
                                @if($concession->approver->profile_photo_path)
                                    <img src="{{ asset('storage/' . $concession->approver->profile_photo_path) }}" 
                                         class="rounded-circle mr-2" 
                                         width="35" 
                                         height="35" 
                                         alt="{{ $concession->approver->name }}">
                                @else
                                    <div class="avatar bg-success text-white rounded-circle mr-2 d-flex align-items-center justify-content-center" 
                                         style="width: 35px; height: 35px;">
                                        {{ substr($concession->approver->name, 0, 1) }}
                                    </div>
                                @endif
                                <div>
                                    <strong>{{ $concession->approver->name }}</strong>
                                    <br>
                                    <small class="text-muted">Admin</small>
                                </div>
                            </div>
                        </p>
                    </div>
                    @endif
                    @endif
                </div>
            </div>

            @if($concession->is_pending)
            <div class="card shadow">
                <div class="card-header bg-warning text-dark py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-cogs mr-2"></i>Aksi
                    </h6>
                </div>
                <div class="card-body">
                    <div class="d-grid gap-2">
                        <form action="{{ route('admin.concessions.approve', $concession->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success btn-block mb-2">
                                <i class="fas fa-check mr-2"></i>Setujui
                            </button>
                        </form>
                        
                        <form action="{{ route('admin.concessions.reject', $concession->id) }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-danger btn-block" 
                                    onclick="return confirm('Yakin ingin menolak pengajuan ini?')">
                                <i class="fas fa-times mr-2"></i>Tolak
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

<style>
.avatar {
    font-weight: bold;
    font-size: 14px;
}
.form-control-plaintext {
    min-height: 2.5rem;
    padding: 0.375rem 0;
}
.border-rounded {
    border-radius: 8px;
}
</style>
@endsection