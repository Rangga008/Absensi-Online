<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Sistem Absensi Digital - SMKN 2 Bandung</title>
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
            background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%);
            min-height: 100vh;
        }
        
        .login-card {
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            background: rgba(255, 255, 255, 0.95);
            border: 1px solid rgba(255, 255, 255, 0.2);
            position: relative;
        }
        
        .school-logo {
            width: 100px;
            height: auto;
            margin-bottom: 1rem;
        }
        
        .btn-login {
            border-radius: 50px;
            padding: 12px 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            background: linear-gradient(45deg, #11998e, #38ef7d);
            border: none;
            color: white;
        }
        
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(17, 153, 142, 0.4);
            color: white;
        }

        .btn-back {
            position: absolute;
            top: 20px;
            left: 20px;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            color: #11998e;
            border: none;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .btn-back:hover {
            background: #e9ecef;
            transform: translateY(-2px);
        }
        
        .form-control {
            border-radius: 50px;
            padding: 12px 20px;
            border: 1px solid #e0e0e0;
        }
        
        .form-control:focus {
            border-color: #11998e;
            box-shadow: 0 0 0 0.25rem rgba(17, 153, 142, 0.25);
        }
        
        .clock {
            font-size: 1.5rem;
            font-weight: bold;
            color: #11998e;
        }
        
        .date {
            font-size: 1rem;
            color: #666;
        }
        
        .footer {
            color: rgba(255, 255, 255, 0.8);
            font-size: 0.9rem;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-lg-5 col-md-8">
                <div class="login-card p-5">
                    <!-- Back Button -->
                    <a href="/" class="btn-back">
                        <i class="fas fa-arrow-left"></i>
                    </a>
                    
                    <!-- Header Section -->
                    <div class="text-center mb-4">
                        <img src="{{ app_logo() }}" alt="Logo {{ setting('company_name', 'SMKN 2 Bandung') }}" class="school-logo"> 
                        <h3 class="fw-bold text-dark mb-1">{{ strtoupper(setting('app_name', 'SMK NEGERI 2 BANDUNG')) }}</h3>
                        <h4 class="h5 text-success mb-3">{{ strtoupper(setting('company_name', 'SMK NEGERI 2 BANDUNG')) }}</h4>
                        
                        <!-- Live Clock -->
                        <div class="mb-3">
                            <div class="clock" id="clock"></div>
                            <div class="date" id="date"></div>
                        </div>
                    </div>
                    
                    @if(session()->has('message'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        {{ session('message') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    @if(session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                    @endif
                    
                    <form class="mt-2" action="{{ url('login') }}" method="POST">
                        @CSRF
                        <div class="form-group mb-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fas fa-envelope text-success"></i></span>
                                <input type="text" name="email" class="form-control" placeholder="Email" />
                            </div>
                            @error('email')
                            <small class="text-danger ml-2">{{ $message }}</small>
                            @enderror
                        </div>
                        
                        <div class="form-group mb-4">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent"><i class="fas fa-lock text-success"></i></span>
                                <input type="password" name="password" class="form-control" placeholder="Password" />
                            </div>
                            @error('password')
                            <small class="text-danger ml-2">{{ $message }}</small>
                            @enderror
                        </div>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-login">
                                <i class="fas fa-sign-in-alt me-2"></i> Login
                            </button>
                        </div>
                    </form>
                    
                    <div class="text-center mt-4">
                        <p class="text-muted mb-1">
                            <i class="fas fa-map-marker-alt me-2"></i>
                        </p>
                    </div>
                </div>
                
                <!-- Footer -->
                <div class="text-center mt-3 footer">
                    <p class="mb-0">
                        &copy; {{ date('Y') }} {{ strtoupper(setting('company_name', 'SMK NEGERI 2 BANDUNG')) }}. All rights reserved.
                    </p>
                    <small>
                        Developed with <i class="fas fa-heart text-danger"></i> for better education
                    </small>
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