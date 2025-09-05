<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Selamat Datang - {{ setting('app_name', 'Aplikasi Absen') }}</title>
    <link rel="icon" href="{{ app_logo() }}" type="image/png" id="favicon">
    <link rel="shortcut icon" href="{{ app_logo() }}" type="image/png">
    <link rel="apple-touch-icon" href="{{ app_logo() }}">
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary-blue: #1E40AF;
            --secondary-blue: #3B82F6;
            --light-blue: #DBEAFE;
            --accent-yellow: #FCD34D;
            --dark-navy: #0F172A;
            --light-gray: #F8FAFC;
            --medium-gray: #64748B;
            --white: #FFFFFF;
        }
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, var(--primary-blue) 0%, var(--dark-navy) 100%);
            min-height: 100vh;
            overflow-x: hidden;
        }
        
        /* Animated background elements */
        .bg-decoration {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            pointer-events: none;
            z-index: 0;
        }
        
        .bg-circle {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.03);
            animation: float 20s infinite ease-in-out;
        }
        
        .bg-circle:nth-child(1) {
            width: 300px;
            height: 300px;
            top: -150px;
            right: -150px;
            animation-delay: -5s;
        }
        
        .bg-circle:nth-child(2) {
            width: 200px;
            height: 200px;
            bottom: -100px;
            left: -100px;
            animation-delay: -10s;
        }
        
        .bg-circle:nth-child(3) {
            width: 400px;
            height: 400px;
            top: 50%;
            left: -200px;
            animation-delay: -15s;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px) scale(1); }
            50% { transform: translateY(-30px) scale(1.1); }
        }
        
        .main-container {
            position: relative;
            z-index: 1;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        
        .hero-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 32px;
            box-shadow: 
                0 32px 64px rgba(0, 0, 0, 0.12),
                0 0 0 1px rgba(255, 255, 255, 0.05);
            padding: 0;
            overflow: hidden;
            position: relative;
        }
        
        .hero-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-yellow), var(--secondary-blue));
        }
        
        .hero-content {
            padding: 4rem 3rem;
        }
        
        .school-logo {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            box-shadow: 0 16px 32px rgba(30, 64, 175, 0.2);
            margin-bottom: 2rem;
            transition: transform 0.3s ease;
        }
        
        .school-logo:hover {
            transform: scale(1.05);
        }
        
        .hero-title {
            font-size: clamp(2.5rem, 5vw, 3.5rem);
            font-weight: 800;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
            margin-bottom: 1rem;
            line-height: 1.1;
        }
        
        .hero-subtitle {
            font-size: clamp(1.2rem, 2.5vw, 1.5rem);
            font-weight: 600;
            color: var(--dark-navy);
            margin-bottom: 0.5rem;
        }
        
        .hero-school {
            font-size: clamp(1rem, 2vw, 1.25rem);
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 1rem;
        }
        
        .hero-location {
            color: var(--medium-gray);
            font-size: 1rem;
            font-weight: 500;
            margin-bottom: 2.5rem;
        }
        
        .location-icon {
            color: var(--primary-blue);
            margin-right: 0.5rem;
        }
        
        .time-display {
            background: linear-gradient(135deg, var(--light-blue), rgba(255, 255, 255, 0.8));
            border-radius: 20px;
            padding: 1.5rem;
            margin: 2rem 0;
            text-align: center;
            border: 1px solid rgba(30, 64, 175, 0.1);
        }
        
        .clock {
            font-size: clamp(2rem, 4vw, 2.5rem);
            font-weight: 700;
            color: var(--primary-blue);
            margin-bottom: 0.5rem;
            font-feature-settings: 'tnum';
        }
        
        .date {
            font-size: clamp(0.9rem, 2vw, 1.1rem);
            color: var(--medium-gray);
            font-weight: 500;
        }
        
        .cta-section {
            margin: 3rem 0;
        }
        
        .cta-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1rem;
        }
        
        .cta-btn {
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.25rem 2rem;
            border-radius: 16px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            border: none;
            position: relative;
            overflow: hidden;
            min-height: 60px;
        }
        
        .cta-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.2), transparent);
            transition: left 0.5s;
        }
        
        .cta-btn:hover::before {
            left: 100%;
        }
        
        .cta-primary {
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            color: white;
            box-shadow: 0 8px 24px rgba(30, 64, 175, 0.3);
        }
        
        .cta-primary:hover {
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(30, 64, 175, 0.4);
        }
        
        .cta-secondary {
            background: linear-gradient(135deg, var(--accent-yellow), #F59E0B);
            color: var(--dark-navy);
            box-shadow: 0 8px 24px rgba(252, 211, 77, 0.3);
        }
        
        .cta-secondary:hover {
            color: var(--dark-navy);
            transform: translateY(-2px);
            box-shadow: 0 12px 32px rgba(252, 211, 77, 0.4);
        }
        
        .cta-icon {
            margin-right: 0.75rem;
            font-size: 1.1rem;
        }
        
        .features-section {
            margin-top: 4rem;
            padding-top: 3rem;
            border-top: 1px solid rgba(30, 64, 175, 0.1);
        }
        
        .features-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-top: 2rem;
        }
        
        .feature-card {
            background: rgba(255, 255, 255, 0.6);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.3);
            border-radius: 20px;
            padding: 2rem;
            text-align: center;
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .feature-card::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: conic-gradient(from 0deg, transparent, var(--primary-blue), transparent);
            opacity: 0;
            transition: opacity 0.3s ease;
            animation: rotate 4s linear infinite;
        }
        
        .feature-card:hover::before {
            opacity: 0.1;
        }
        
        .feature-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(30, 64, 175, 0.15);
        }
        
        @keyframes rotate {
            to { transform: rotate(360deg); }
        }
        
        .feature-icon {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, var(--primary-blue), var(--secondary-blue));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 1.5rem;
            color: white;
            font-size: 1.5rem;
            position: relative;
            z-index: 1;
        }
        
        .feature-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--dark-navy);
            margin-bottom: 1rem;
            position: relative;
            z-index: 1;
        }
        
        .feature-description {
            color: var(--medium-gray);
            line-height: 1.6;
            font-size: 0.95rem;
            position: relative;
            z-index: 1;
        }
        
        .footer {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid rgba(30, 64, 175, 0.1);
            text-align: center;
        }
        
        .footer-text {
            color: var(--medium-gray);
            font-size: 0.9rem;
            margin-bottom: 0.5rem;
        }
        
        .footer-love {
            color: var(--medium-gray);
            font-size: 0.85rem;
        }
        
        .heart-icon {
            color: #EF4444;
            animation: heartbeat 1.5s ease-in-out infinite;
        }
        
        @keyframes heartbeat {
            0% { transform: scale(1); }
            14% { transform: scale(1.1); }
            28% { transform: scale(1); }
            42% { transform: scale(1.1); }
            70% { transform: scale(1); }
        }
        
        /* Responsive Design */
        @media (max-width: 768px) {
            .hero-content {
                padding: 2.5rem 1.5rem;
            }
            
            .cta-grid {
                grid-template-columns: 1fr;
            }
            
            .features-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
            
            .feature-card {
                padding: 1.5rem;
            }
        }
        
        @media (max-width: 480px) {
            .hero-content {
                padding: 2rem 1rem;
            }
            
            .time-display {
                padding: 1rem;
                margin: 1.5rem 0;
            }
            
            .cta-btn {
                padding: 1rem 1.5rem;
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="bg-decoration">
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
        <div class="bg-circle"></div>
    </div>

    <div class="main-container">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-12 col-lg-11 col-xl-10">
                    <div class="hero-card">
                        <div class="hero-content">
                            <!-- Header Section with Dynamic Content -->
                            <div class="text-center">
                                <!-- Use app_logo() instead of hardcoded path -->
                                <img src="{{ app_logo() }}" alt="Logo {{ setting('company_name', 'SMKN 2 Bandung') }}" class="school-logo">
                                <h1 class="hero-title">Selamat Datang</h1>
                                <h2 class="hero-subtitle">Sistem Absensi Digital</h2>
                                <!-- Use dynamic company name -->
                                <h3 class="hero-school">{{ strtoupper(setting('company_name', 'SMK NEGERI 2 BANDUNG')) }}</h3>
                                <p class="hero-location">
                                    <i class="fas fa-map-marker-alt location-icon"></i>
                                </p>
                                
                                <!-- Live Clock with Dynamic Timezone -->
                                <div class="time-display">
                                    <div class="clock" id="clock"></div>
                                    <div class="date" id="date"></div>
                                </div>
                            </div>
                            
                            <!-- CTA Buttons -->
                            <div class="cta-section">
                                <div class="cta-grid">
                                    <a href="{{ route('login') }}" class="cta-btn cta-primary">
                                        <i class="fas fa-user cta-icon"></i>
                                        Login Siswa/Guru
                                    </a>
                                    <a href="{{ route('admin.login') }}" class="cta-btn cta-secondary">
                                        <i class="fas fa-user-shield cta-icon"></i>
                                        Login Admin
                                    </a>
                                </div>
                            </div>
                            
                            <!-- Features Section -->
                            <div class="features-section">
                                <div class="features-grid">
                                    <div class="feature-card">
                                        <div class="feature-icon">
                                            <i class="fas fa-clock"></i>
                                        </div>
                                        <h5 class="feature-title">Absensi Real-time</h5>
                                        <p class="feature-description">Sistem absensi berbasis lokasi dengan teknologi GPS untuk akurasi tinggi</p>
                                    </div>
                                    <div class="feature-card">
                                        <div class="feature-icon">
                                            <i class="fas fa-chart-line"></i>
                                        </div>
                                        <h5 class="feature-title">Laporan Lengkap</h5>
                                        <p class="feature-description">Dashboard analytics dengan laporan kehadiran yang komprehensif</p>
                                    </div>
                                    <div class="feature-card">
                                        <div class="feature-icon">
                                            <i class="fas fa-mobile-alt"></i>
                                        </div>
                                        <h5 class="feature-title">Mobile Friendly</h5>
                                        <p class="feature-description">Dapat diakses melalui smartphone dengan tampilan responsif</p>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Footer with Dynamic Company Name -->
                            <div class="footer">
                                <p class="footer-text">
                                    &copy; {{ date('Y') }} {{ setting('company_name', 'SMKN 2 Bandung') }}. All rights reserved.
                                </p>
                                <small class="footer-love">
                                    Developed with <i class="fas fa-heart heart-icon"></i> for better education
                                </small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Live Clock Script with Dynamic Timezone -->
    <script>
        // Get timezone from settings (default to Asia/Jakarta)
        const appTimezone = '{{ setting("timezone", "Asia/Jakarta") }}';
        
        function updateTime() {
            const now = new Date();
            
            // Format time with timezone
            const timeOptions = {
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: false,
                timeZone: appTimezone
            };
            const timeString = now.toLocaleTimeString('id-ID', timeOptions);
            
            // Format date with timezone
            const dateOptions = {
                weekday: 'long',
                year: 'numeric',
                month: 'long',
                day: 'numeric',
                timeZone: appTimezone
            };
            const dateString = now.toLocaleDateString('id-ID', dateOptions);
            
            document.getElementById('clock').textContent = timeString;
            document.getElementById('date').textContent = dateString;
        }
        
        // Update time immediately and then every second
        updateTime();
        setInterval(updateTime, 1000);
        
        // Enhanced favicon refresh system
        document.addEventListener('DOMContentLoaded', function() {
            function updateFavicon() {
                try {
                    const favicon = document.getElementById('favicon');
                    const shortcutIcon = document.querySelector('link[rel="shortcut icon"]');
                    const appleIcon = document.querySelector('link[rel="apple-touch-icon"]');
                    
                    // Get fresh logo URL with timestamp
                    const logoUrl = '{{ app_logo() }}';
                    const freshUrl = logoUrl.split('?')[0] + '?v=' + Date.now();
                    
                    // Update all favicon related links
                    if (favicon) favicon.href = freshUrl;
                    if (shortcutIcon) shortcutIcon.href = freshUrl;
                    if (appleIcon) appleIcon.href = freshUrl;
                    
                } catch (error) {
                    console.error('Error updating favicon:', error);
                }
            }
            
            // Initial favicon update
            updateFavicon();
            
            // Update favicon when page becomes visible
            document.addEventListener('visibilitychange', function() {
                if (!document.hidden) {
                    setTimeout(updateFavicon, 500);
                }
            });
            
            // Listen for storage events (if logo updated in another tab)
            window.addEventListener('storage', function(e) {
                if (e.key === 'logoUpdated') {
                    setTimeout(updateFavicon, 1000);
                }
            });
        });
        
        // Add smooth scroll behavior
        document.documentElement.style.scrollBehavior = 'smooth';
    </script>
</body>
</html>