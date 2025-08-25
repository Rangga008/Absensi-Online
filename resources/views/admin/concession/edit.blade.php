@extends('layouts.admin')

@section('title', 'Edit Pengajuan Izin - ' . ($concession->user->name ?? 'Unknown'))

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.concessions.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-edit mr-2"></i>Edit Pengajuan Izin
        </h1>
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-file-alt mr-2"></i>Form Edit Pengajuan
            </h6>
        </div>
        <div class="card-body">
            @if($errors->any())
                <div class="alert alert-danger">
                    <h6><i class="fas fa-exclamation-triangle mr-2"></i>Error Validasi</h6>
                    <ul class="mb-0 mt-2">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                </div>
            @endif

            <form action="{{ route('admin.concessions.update', $concession->id) }}" method="POST">
                @csrf
                @method('PUT')
                
                <div class="row">
                    <!-- Employee Selection -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="user_id" class="font-weight-bold text-dark">
                                <i class="fas fa-user mr-2"></i>Karyawan *
                            </label>
                            <select name="user_id" id="user_id" class="form-control" required>
                                <option value="">-- Pilih Karyawan --</option>
                                @foreach($users as $user)
                                    <option value="{{ $user->id }}" 
                                        {{ old('user_id', $concession->user_id) == $user->id ? 'selected' : '' }}>
                                        {{ $user->name }} 
                                        @if($user->email)
                                            ({{ $user->email }})
                                        @endif
                                    </option>
                                @endforeach
                            </select>
                            <small class="form-text text-muted">Pilih karyawan yang mengajukan izin</small>
                        </div>
                    </div>
                    
                    <!-- Reason Selection -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="reason" class="font-weight-bold text-dark">
                                <i class="fas fa-tag mr-2"></i>Jenis Izin *
                            </label>
                            <select name="reason" id="reason" class="form-control" required>
                                <option value="">-- Pilih Jenis Izin --</option>
                                <option value="sakit" {{ old('reason', $concession->reason) == 'sakit' ? 'selected' : '' }}>Sakit</option>
                                <option value="izin" {{ old('reason', $concession->reason) == 'izin' ? 'selected' : '' }}>Izin</option>
                                <option value="cuti" {{ old('reason', $concession->reason) == 'cuti' ? 'selected' : '' }}>Cuti</option>
                            </select>
                            <small class="form-text text-muted">Pilih jenis izin yang diajukan</small>
                        </div>
                    </div>
                    
                    <!-- Start Date -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date" class="font-weight-bold text-dark">
                                <i class="fas fa-calendar-start mr-2"></i>Tanggal Mulai *
                            </label>
                            <input type="date" name="start_date" id="start_date" 
                                   class="form-control" 
                                   value="{{ old('start_date', $concession->start_date ? $concession->start_date->format('Y-m-d') : '') }}" 
                                   required>
                            <small class="form-text text-muted">Tanggal mulai izin</small>
                        </div>
                    </div>
                    
                    <!-- End Date -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date" class="font-weight-bold text-dark">
                                <i class="fas fa-calendar-check mr-2"></i>Tanggal Selesai *
                            </label>
                            <input type="date" name="end_date" id="end_date" 
                                   class="form-control" 
                                   value="{{ old('end_date', $concession->end_date ? $concession->end_date->format('Y-m-d') : '') }}" 
                                   required>
                            <small class="form-text text-muted">Tanggal selesai izin</small>
                        </div>
                    </div>
                    
                    <!-- Status Selection -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="status" class="font-weight-bold text-dark">
                                <i class="fas fa-check-circle mr-2"></i>Status *
                            </label>
                            <select name="status" id="status" class="form-control" required>
                                <option value="">-- Pilih Status --</option>
                                <option value="pending" {{ old('status', $concession->status) == 'pending' ? 'selected' : '' }}>Pending</option>
                                <option value="approved" {{ old('status', $concession->status) == 'approved' ? 'selected' : '' }}>Approved</option>
                                <option value="rejected" {{ old('status', $concession->status) == 'rejected' ? 'selected' : '' }}>Rejected</option>
                            </select>
                            <small class="form-text text-muted">Status persetujuan pengajuan</small>
                        </div>
                    </div>

                    <!-- Approved By (jika sudah disetujui/ditolak) -->
                    @if($concession->approved_by)
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="font-weight-bold text-dark">
                                <i class="fas fa-user-check mr-2"></i>Disetujui/Oleh
                            </label>
                            <p class="form-control-plaintext">
                                {{ $concession->approver->name ?? 'Unknown' }}
                                <br>
                                <small class="text-muted">
                                    {{ $concession->approved_at ? $concession->approved_at->format('d M Y H:i') : '' }}
                                </small>
                            </p>
                        </div>
                    </div>
                    @endif
                    
                    <!-- Description -->
                    <div class="col-md-12">
                        <div class="form-group">
                            <label for="description" class="font-weight-bold text-dark">
                                <i class="fas fa-align-left mr-2"></i>Keterangan/Alasan *
                            </label>
                            <textarea name="description" id="description" 
                                      class="form-control" rows="5" 
                                      placeholder="Masukkan alasan lengkap pengajuan izin..." 
                                      required>{{ old('description', $concession->description) }}</textarea>
                            <small class="form-text text-muted">Jelaskan alasan pengajuan izin secara detail</small>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="col-md-12 mt-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="fas fa-save mr-2"></i> Simpan Perubahan
                                </button>
                                <a href="{{ route('admin.concessions.index') }}" class="btn btn-secondary btn-lg ml-2">
                                    <i class="fas fa-times mr-2"></i> Batal
                                </a>
                            </div>
                            <div>
                                <span class="text-muted"><small>* wajib diisi</small></span>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
$(document).ready(function() {
    // Date validation
    $('#start_date').change(function() {
        $('#end_date').attr('min', $(this).val());
    });

    $('#end_date').change(function() {
        if ($('#start_date').val() && new Date($(this).val()) < new Date($('#start_date').val())) {
            alert('Tanggal selesai tidak boleh sebelum tanggal mulai!');
            $(this).val($('#start_date').val());
        }
    });

    // Form validation
    $('form').submit(function(e) {
        let isValid = true;
        $('select[required], input[required], textarea[required]').each(function() {
            if (!$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            e.preventDefault();
            alert('Harap lengkapi semua field yang wajib diisi!');
        }
    });
});
</script>

<style>
.form-control {
    border-radius: 8px;
    padding: 5px;
    border: 1px solid #ddd;
}
.form-control:focus {
    border-color: #007bff;
    box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
}
.btn {
    border-radius: 8px;
    padding: 12px 24px;
    font-weight: 600;
}
.alert {
    border-radius: 8px;
    border: none;
}
.card {
    border-radius: 12px;
    border: none;
}
.card-header {
    border-radius: 12px 12px 0 0 !important;
}
.form-control-plaintext {
    background-color: #f8f9fa;
    padding: 10px;
    border-radius: 6px;
    border: 1px solid #e9ecef;
}
</style>
@endsection