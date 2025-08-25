@extends('user.layouts')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">
                        <i class="fas fa-calendar-minus mr-2"></i>Form Pengajuan Izin
                    </h5>
                </div>
                
                <div class="card-body">
                    <div class="welcome-message mb-4">
                        <h4 class="text-primary">
                            <i class="fas fa-user-circle mr-2"></i>Hai, <b>{{ ucfirst(session('username')) }}</b>
                        </h4>
                        <p class="text-muted">
                            Silakan isi form berikut untuk mengajukan izin tidak hadir.
                            <br>
                            <small class="text-muted">
                                <i class="fas fa-calendar-day mr-1"></i>
                                {{ date('l, d F Y') }}
                            </small>
                        </p>
                    </div>

                    <hr>

                    <form id="concessionForm" method="POST" action="{{ route('user.concession.store') }}">
                        @csrf
                        
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
                                      placeholder="Mohon jelaskan alasan tidak hadir secara detail (minimal 5 karakter)..."
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
                                    @error('end_date')
                                    <div class="invalid-feedback">
                                        <i class="fas fa-exclamation-triangle mr-1"></i>{{ $message }}
                                    </div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="form-group mt-4">
                            <div class="d-flex justify-content-between">
                                <a href="{{ route('user.home') }}" class="btn btn-outline-primary">
                                    <i class="fas fa-arrow-left mr-1"></i> Kembali
                                </a>
                                <button type="submit" class="btn btn-primary" id="submitBtn">
                                    <i class="fas fa-paper-plane mr-1"></i> Ajukan Izin
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Date validation
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
        
        submitBtn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin"></i> Mengirim...');
        
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
                            // Redirect ke halaman history setelah klik OK
                            window.location.href = "{{ route('user.concession.history') }}";
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
                submitBtn.prop('disabled', false).html('<i class="fas fa-paper-plane mr-1"></i> Ajukan Izin');
            }
        });
    });
});
</script>
@endpush

@push('styles')
<style>
.welcome-message {
    background-color: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}
.card {
    border: none;
    border-radius: 10px;
}
.card-header {
    border-radius: 10px 10px 0 0 !important;
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
@endpush