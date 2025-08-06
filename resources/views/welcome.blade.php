<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selamat Datang - Aplikasi Absen SMKN 2 Bandung</title>
    <link rel="icon" href="{{ asset('images/logo-smk2.png') }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .welcome-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            backdrop-filter: blur(10px);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
        }
        
        .school-logo {
            width: 120px;
            height: auto;
            border-radius: 50%;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
        }
        
        .btn-custom {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            margin: 10px;
        }
        
        .btn-primary-custom {
            background: linear-gradient(45deg, #667eea, #764ba2);
            border: none;
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.4);
            color: white;
        }
        
        .btn-secondary-custom {
            background: linear-gradient(45deg, #11998e, #38ef7d);
            border: none;
            color: white;
        }
        
        .btn-secondary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
            color: white;
        }
        
        .feature-icon {
            font-size: 3rem;
            color: #667eea;
            margin-bottom: 1rem;
        }
        
        .feature-card {
            background: white;
            border-radius: 15px;
            padding: 2rem;
            margin: 1rem 0;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-5px);
        }
        
        .clock {
            font-size: 2rem;
            font-weight: bold;
            color: #667eea;
        }
        
        .date {
            font-size: 1.2rem;
            color: #666;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-10">
                <div class="welcome-card p-5">
                    <!-- Header Section -->
                    <div class="text-center mb-5">
                        <img src="{{ asset('images/logo-smk2.png') }}" alt="Logo SMKN 2 Bandung" class="school-logo mb-4">
                        <h1 class="display-4 fw-bold text-primary mb-3">Selamat Datang</h1>
                        <h2 class="h3 text-dark mb-2">Sistem Absensi Digital</h2>
                        <h3 class="h4 text-primary mb-3">SMK NEGERI 2 BANDUNG</h3>
                        <p class="text-muted mb-4">
                            <i class="fas fa-map-marker-alt me-2"></i>
                            Jl. Ciliwung No.4, Cihapit, Bandung
                        </p>
                        
                        <!-- Live Clock -->
                        <div class="mb-4">
                            <div class="clock" id="clock"></div>
                            <div class="date" id="date"></div>
                        </div>
                    </div>
                    
                    <!-- Action Buttons -->
                    <div class="text-center mb-5">
                        <div class="row justify-content-center">
                            <div class="col-md-6">
                                <a href="{{ route('login') }}" class="btn btn-primary-custom btn-custom btn-lg w-100 mb-3">
                                    <i class="fas fa-user me-2"></i>
                                    Login Siswa/Guru
                                </a>
                            </div>
                            <div class="col-md-6">
                                <a href="{{ route('admin.login') }}" class="btn btn-secondary-custom btn-custom btn-lg w-100 mb-3">
                                    <i class="fas fa-user-shield me-2"></i>
                                    Login Admin
                                </a>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Features Section -->
                    <div class="row">
                        <div class="col-md-4">
                            <div class="feature-card text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-clock"></i>
                                </div>
                                <h5 class="fw-bold">Absensi Real-time</h5>
                                <p class="text-muted">Sistem absensi berbasis lokasi dengan teknologi GPS untuk akurasi tinggi</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-chart-line"></i>
                                </div>
                                <h5 class="fw-bold">Laporan Lengkap</h5>
                                <p class="text-muted">Dashboard analytics dengan laporan kehadiran yang komprehensif</p>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="feature-card text-center">
                                <div class="feature-icon">
                                    <i class="fas fa-mobile-alt"></i>
                                </div>
                                <h5 class="fw-bold">Mobile Friendly</h5>
                                <p class="text-muted">Dapat diakses melalui smartphone dengan tampilan responsif</p>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Footer -->
                    <div class="text-center mt-5 pt-4 border-top">
                        <p class="text-muted mb-0">
                            &copy; {{ date('Y') }} SMKN 2 Bandung. All rights reserved.
                        </p>
                        <small class="text-muted">
                            Developed with <i class="fas fa-heart text-danger"></i> for better education
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Live Clock Script -->
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
</body>
</html>