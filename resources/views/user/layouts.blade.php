<!doctype html>
<html lang="en">

<head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" href="{{ asset('images/logo-smk2.png') }}">

    <!-- Fonts and icons -->
    <link href="{{ asset('sbadmin') }}/vendor/fontawesome-free/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;600;700;800&display=swap" rel="stylesheet">

    <!-- Custom styles -->
    <link href="{{ asset('sbadmin') }}/css/sb-admin-2.min.css" rel="stylesheet">
    
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #858796;
            --light: #f8f9fc;
        }
        
        body {
            background-color: #f7f7f7;
            font-family: 'Nunito', sans-serif;
        }
        
        .main-container {
            min-height: calc(100vh - 120px);
            padding: 2rem 0;
        }
        
        .welcome-card {
            border-radius: 1rem;
            border: none;
            box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }
        
        .welcome-header {
            background: var(--primary);
            color: white;
            padding: 2rem;
        }
        
        .user-avatar {
            width: 80px;
            height: 80px;
            font-size: 2.5rem;
            background: rgba(255,255,255,0.2);
        }
        
        .action-card {
            border-radius: 0.75rem;
            transition: all 0.3s ease;
            border: none;
            box-shadow: 0 0.25rem 0.5rem rgba(0, 0, 0, 0.05);
            height: 100%;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 0.5rem 1.5rem rgba(0, 0, 0, 0.1);
        }
        
        .action-icon {
            width: 60px;
            height: 60px;
            font-size: 1.75rem;
        }
        
        footer {
            background: white;
            padding: 1.5rem 0;
            border-top: 1px solid rgba(0,0,0,0.1);
        }
        
        @media (max-width: 768px) {
            .welcome-header {
                padding: 1.5rem;
            }
            
            .user-avatar {
                width: 60px;
                height: 60px;
                font-size: 2rem;
            }
        }
    </style>

    <title>Aplikasi Absensi Berbasis Lokasi</title>
</head>

<body>
    @include('user.navbar')

    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <!-- Welcome Card -->
                    <div class="card welcome-card mb-5">
                        <div class="welcome-header">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h1 class="h3 mb-2">Selamat datang,</h1>
                                    <h2 class="h1 font-weight-bold mb-3">
                                   {{ ucfirst(session('username')) }}
                                    </h2>
                                    <p class="mb-1">Sistem absensi SMKN 2 Bandung</p>
                                    <p class="mb-0">{{ now()->translatedFormat('l, d F Y') }}</p>
                                </div>
                                @auth
                                <div class="col-md-4 text-right">
                                    <div class="user-avatar rounded-circle d-inline-flex align-items-center justify-content-center">
                                        {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
                                    </div>
                                </div>
                                @endauth
                            </div>
                        </div>
                    </div>

                    <!-- Main Content -->
                    <div class="row">
                        @yield('content')
                    </div>
                </div>
            </div>
        </div>
    </div>

    <footer class="mt-auto">
        <div class="container">
            <div class="text-center text-muted">
                Copyright &copy; {{ date('Y') }} SMKN 2 Bandung
            </div>
        </div>
    </footer>

    <!-- JavaScript -->
    <script src="{{ asset('sbadmin') }}/vendor/jquery/jquery.min.js"></script>
    <script src="{{ asset('sbadmin') }}/vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="{{ asset('sbadmin') }}/vendor/jquery-easing/jquery.easing.min.js"></script>

    @yield('scripts')
</body>
</html>