@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <a href="{{ route('admin.concessions.index') }}" class="btn btn-primary">
            <i class="fas fa-arrow-left mr-1"></i> Kembali
        </a>
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-calendar-minus mr-2"></i>Tambah concessions Baru
        </h1>
    </div>

    <div class="card shadow-sm">
        <div class="card-body">
            @if ($errors->any())
                <div class="alert alert-danger">
                    <h6 class="font-weight-bold">
                        <i class="fas fa-exclamation-triangle mr-1"></i>Terjadi Kesalahan
                    </h6>
                    <ul class="mb-0 pl-3">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            @if (session('success'))
                <div class="alert alert-success">
                    <i class="fas fa-check-circle mr-1"></i>{{ session('success') }}
                </div>
            @endif

            <form id="concessionForm" method="POST" action="{{ route('admin.concessions.store') }}">
                @csrf
                
                <div class="form-group">
                <label for="user_id" class="font-weight-bold">
                    <i class="fas fa-user mr-1"></i>Pilih Pengguna
                </label>
                <select class="form-control select2 @error('user_id') is-invalid @enderror" 
                        name="user_id" 
                        id="user_id"
                        required>
                    <option value="" selected disabled>-- Pilih Pengguna --</option>
                    @foreach($users as $user)
                        @if($user->role && $user->role->id != 0) {{-- Exclude admin (role_id = 1) --}}
                            <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                {{ $user->name }} - {{ $user->role->role_name }}
                            </option>
                        @endif
                    @endforeach
                </select>
                @error('user_id')
                <div class="invalid-feedback">
                    <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                </div>
                @enderror
            </div>

                <div class="form-group">
                    <label for="reason" class="font-weight-bold">
                        <i class="fas fa-exclamation-circle mr-1"></i>Alasan Tidak Hadir
                    </label>
                    <select class="form-control @error('reason') is-invalid @enderror" 
                            name="reason" 
                            id="reason"
                            required>
                        <option value="" selected disabled>-- Pilih Alasan --</option>
                        <option value="sakit" {{ old('reason') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                        <option value="izin" {{ old('reason') == 'izin' ? 'selected' : '' }}>Izin</option>
                        <option value="cuti" {{ old('reason') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                        <option value="dinas_luar" {{ old('reason') == 'dinas_luar' ? 'selected' : '' }}>Dinas Luar</option>
                        <option value="wfh" {{ old('reason') == 'wfh' ? 'selected' : '' }}>Work From Home</option>
                        <option value="lainnya" {{ old('reason') == 'lainnya' ? 'selected' : '' }}>Lainnya</option>
                    </select>
                    @error('reason')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group">
                    <label for="description" class="font-weight-bold">
                        <i class="fas fa-align-left mr-1"></i>Keterangan Lengkap
                    </label>
                    <textarea class="form-control @error('description') is-invalid @enderror" 
                              name="description" 
                              id="description" 
                              rows="5"
                              placeholder="Jelaskan alasan konsesi secara detail (minimal 5 karakter)..."
                              required>{{ old('description') }}</textarea>
                    <small class="form-text text-muted">
                        Minimal 5 karakter
                    </small>
                    @error('description')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="start_date" class="font-weight-bold">
                                <i class="fas fa-calendar-day mr-1"></i>Tanggal Mulai
                            </label>
                            <input type="date" class="form-control @error('start_date') is-invalid @enderror" 
                                   name="start_date" id="start_date" 
                                   value="{{ old('start_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            @error('start_date')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="end_date" class="font-weight-bold">
                                <i class="fas fa-calendar-day mr-1"></i>Tanggal Selesai
                            </label>
                            <input type="date" class="form-control @error('end_date') is-invalid @enderror" 
                                   name="end_date" id="end_date" 
                                   value="{{ old('end_date') }}"
                                   min="{{ date('Y-m-d') }}"
                                   required>
                            <small class="form-text text-muted">
                                Kosongkan jika hanya satu hari
                            </small>
                            @error('end_date')
                            <div class="invalid-feedback">
                                <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                            </div>
                            @enderror
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label for="status" class="font-weight-bold">
                        <i class="fas fa-check-circle mr-1"></i>Status
                    </label>
                    <select class="form-control @error('status') is-invalid @enderror" 
                            name="status" 
                            id="status"
                            required>
                        <option value="pending" {{ old('status') == 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="approved" {{ old('status') == 'approved' ? 'selected' : '' }}>Disetujui</option>
                        <option value="rejected" {{ old('status') == 'rejected' ? 'selected' : '' }}>Ditolak</option>
                    </select>
                    @error('status')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                    </div>
                    @enderror
                </div>

                <div class="form-group mt-4">
                    <div class="d-flex justify-content-between">
                        <button type="submit" class="btn btn-primary" id="submitBtn">
                            <i class="fas fa-save mr-1"></i> Simpan Konsesi
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('styles')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<style>
.select2-container .select2-selection--single {
    height: 38px;
}
.select2-container--default .select2-selection--single .select2-selection__rendered {
    line-height: 36px;
}
.card {
    border: none;
    border-radius: 10px;
}
.btn {
    border-radius: 5px;
    padding: 10px 20px;
    font-weight: 500;
}
.btn-primary {
    background: linear-gradient(45deg, #007bff, #0056b3);
    border: none;
    transition: all 0.3s ease;
}
.btn-primary:hover {
    background: linear-gradient(45deg, #0056b3, #004085);
    transform: translateY(-2px);
    box-shadow: 0 4px 8px rgba(0, 123, 255, 0.3);
}
.btn-outline-primary {
    border: 2px solid #007bff;
    color: #007bff;
    transition: all 0.3s ease;
}
.btn-outline-primary:hover {
    background-color: #007bff;
    color: white;
    transform: translateY(-2px);
}
</style>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Inisialisasi Select2
    $('.select2').select2({
        placeholder: "Pilih Pengguna",
        allowClear: true
    });
    
    // Validasi tanggal
    $('#start_date').change(function() {
        $('#end_date').attr('min', $(this).val());
        if ($('#end_date').val() && new Date($('#end_date').val()) < new Date($(this).val())) {
            $('#end_date').val($(this).val());
        }
    });

    // Character counter
    const description = document.getElementById('description');
    const charCounter = document.createElement('small');
    charCounter.className = 'form-text text-right text-muted float-right';
    description.parentNode.appendChild(charCounter);
    
    description.addEventListener('input', function() {
        charCounter.textContent = `${this.value.length} karakter`;
        if (this.value.length < 5) {
            charCounter.classList.add('text-danger');
            charCounter.classList.remove('text-success');
        } else {
            charCounter.classList.add('text-success');
            charCounter.classList.remove('text-danger');
        }
    });

    // Form submission handling
    $('#concessionForm').submit(function(e) {
        e.preventDefault();
        
        const form = $(this);
        const submitBtn = $('#submitBtn');
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Menyimpan...');
        
        $.ajax({
            url: form.attr('action'),
            method: 'POST',
            data: form.serialize(),
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
            },
            success: function(response) {
                if (response.success) {
                    // Tampilkan SweetAlert sukses
                    Swal.fire({
                        icon: 'success',
                        title: 'Sukses!',
                        text: response.message,
                        confirmButtonText: 'OK',
                        confirmButtonColor: '#007bff'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Redirect ke halaman daftar konsesi setelah klik OK
                            window.location.href = "{{ route('admin.concessions.index') }}";
                        }
                    });
                }
            },
            error: function(xhr) {
                let errorMessage = 'Terjadi kesalahan';
                
                if (xhr.responseJSON && xhr.responseJSON.errors) {
                    const errors = xhr.responseJSON.errors;
                    errorMessage = Object.values(errors)[0][0];
                } else if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                
                // Tampilkan SweetAlert error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#dc3545'
                });
            },
            complete: function() {
                submitBtn.prop('disabled', false).html('<i class="fas fa-save mr-1"></i> Simpan Konsesi');
            }
        });
    });
});
</script>
@endsection