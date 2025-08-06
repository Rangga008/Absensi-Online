@extends('layouts.auth')

@section('content')
<!-- Outer Row -->
<div class="row justify-content-center min-vh-100 align-items-center">
    <div class="card o-hidden border-0 shadow-lg my-5" style="width: 500px; border-radius: 20px;">
        <div class="card-body p-0">
            <!-- Nested Row within Card Body -->
            <div class="row">
                <div class="col-lg-12">
                    <div class="p-5">
                        <!-- Back Button -->
                        <a href="/" class="btn btn-circle btn-light" style="position: absolute; top: 20px; left: 20px;">
                            <i class="fas fa-arrow-left text-success"></i>
                        </a>
                        
                        <div class="text-center">
                            <img src="{{ asset('images/logo-smk2.png') }}" width="100" alt="SMKN 2 Bandung" class="mb-3">
                            <h1 class="h4 text-gray-900 mb-1">Sistem Absensi Digital</h1>
                            <h2 class="h5 text-success mb-4">SMK Negeri 2 Bandung</h2>
                            
                            <!-- Live Clock -->
                            <div class="mb-4">
                                <div class="clock h5 text-success" id="clock"></div>
                                <div class="date small text-muted" id="date"></div>
                            </div>
                            
                            @if(session()->has('message'))
                            <div class="alert alert-danger alert-dismissible fade show">
                                {{ session('message') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif
                            @if(session()->has('success'))
                            <div class="alert alert-success alert-dismissible fade show">
                                {{ session('success') }}
                                <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            @endif
                        </div>
                        
                        <form class="user" method="POST" action="{{ route('admin.login.process') }}">
                            @csrf
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-right-0"><i class="fas fa-envelope text-success"></i></span>
                                    <input type="email" class="form-control form-control-user" name="email" placeholder="Email" style="border-radius: 0 50px 50px 0;">
                                </div>
                                @error('email')
                                <small class="text-danger ml-3">{{ $message }}</small>
                                @enderror
                            </div>
                            <div class="form-group">
                                <div class="input-group">
                                    <span class="input-group-text bg-transparent border-right-0"><i class="fas fa-lock text-success"></i></span>
                                    <input type="password" class="form-control form-control-user" name="password" placeholder="Password" style="border-radius: 0 50px 50px 0;">
                                </div>
                                @error('password')
                                <small class="text-danger ml-3">{{ $message }}</small>
                                @enderror
                            </div>
                            <button type="submit" class="btn btn-success btn-user btn-block" style="border-radius: 50px;">
                                <i class="fas fa-sign-in-alt mr-2"></i> Login
                            </button>
                        </form>
                        <hr>
                        
                        <div class="text-center mt-2">
                            <p class="small text-muted">
                                <i class="fas fa-map-marker-alt mr-1"></i> Jl. Ciliwung No.4, Cihapit, Bandung
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
    function updateTime() {
        const now = new Date();
        
        // Format time
        const timeOptions = {
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: false
        };
        const timeString = now.toLocaleTimeString('id-ID', timeOptions);
        
        // Format date
        const dateOptions = {
            weekday: 'long',
            year: 'numeric',
            month: 'long',
            day: 'numeric'
        };
        const dateString = now.toLocaleDateString('id-ID', dateOptions);
        
        document.getElementById('clock').textContent = timeString;
        document.getElementById('date').textContent = dateString;
    }
    
    // Update time immediately and then every second
    updateTime();
    setInterval(updateTime, 1000);
</script>
@endpush

@endsection