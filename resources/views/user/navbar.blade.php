<nav class="navbar navbar-expand-lg navbar-dark bg-primary shadow-sm">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="{{ url('/') }}">
            <img src="{{ app_logo() }}" alt="Logo" width="30" height="30" class="mr-2">
            <span class="font-weight-light">{{ strtoupper(setting('app_name', 'SMK NEGERI 2 BANDUNG')) }}</span>
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbarContent" 
                aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mr-auto">
                <li class="nav-item {{ request()->is('user/home') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('user/home') }}">
                        <i class="fas fa-home mr-1"></i> Beranda
                    </a>
                </li>
                <li class="nav-item {{ request()->is('user/about') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('user/about') }}">
                        <i class="fas fa-info-circle mr-1"></i> Tentang
                    </a>
                </li>
                <li class="nav-item {{ request()->is('user/guide') ? 'active' : '' }}">
                    <a class="nav-link" href="{{ url('user/guide') }}">
                        <i class="fas fa-book mr-1"></i> Panduan
                    </a>
                </li>
            </ul>
            
            <div class="d-flex align-items-center">
                @auth
                    <span class="text-white mr-3">Hai, {{ Auth::user()->name }}</span>
                    <form action="{{ route('user.logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-light btn-sm">
                            <i class="fas fa-sign-out-alt mr-1"></i> Logout
                        </button>
                    </form>
                @else

                @endauth
            </div>
        </div>
    </div>
</nav>

<style>
    .navbar {
        padding: 0.8rem 1rem;
    }
    
    .navbar-brand {
        font-size: 1.25rem;
    }
    
    .nav-item {
        margin: 0 0.25rem;
    }
    
    .nav-link {
        padding: 0.5rem 1rem;
        border-radius: 4px;
        transition: all 0.3s ease;
    }
    
    .nav-link:hover {
        background: rgba(255,255,255,0.1);
    }
    
    .nav-item.active .nav-link {
        background: rgba(255,255,255,0.2);
        font-weight: 500;
    }
    
    @media (max-width: 991.98px) {
        .navbar-collapse {
            padding: 1rem 0;
        }
        
        .nav-item {
            margin: 0.25rem 0;
        }
        
        .d-flex {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }
    }
</style>