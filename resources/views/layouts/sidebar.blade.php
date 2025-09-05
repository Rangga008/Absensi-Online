<!-- Sidebar -->
<ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">

    <!-- Sidebar - Brand -->
    <a class="sidebar-brand d-flex align-items-center justify-content-center" href="{{ route('admin.dashboard') }}">
        
        <div class="sidebar-brand-text mx-3">
        {{ setting('app_name', 'Presensi Online') }}
        <small>
            <br>{{ setting('company_name', 'SMKN 2 Bandung') }}</small>
    </div>
    </a>


    <!-- Divider -->
    <hr class="sidebar-divider my-0">

    <!-- Nav Item - Dashboard -->
    <li class="nav-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.dashboard') }}">
            <i class="fas fa-fw fa-tachometer-alt"></i>
            <span>Dashboard</span></a>
    </li>

    <!-- Divider -->
    <hr class="sidebar-divider">

    <!-- Heading -->
    <div class="sidebar-heading">
        Management
    </div>
    @if(session('role_id') == 1)
    <!-- Nav Item - Users -->
    <li class="nav-item {{ request()->is('admin/users*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.users.index') }}">
            <i class="fas fa-fw fa-users"></i>
            <span>Users</span></a>
    </li>

    <!-- Nav Item - Attendance -->
    <li class="nav-item {{ request()->is('admin/attendances*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.attendances.index') }}">
            <i class="fas fa-fw fa-user"></i>
            <span>Attendance</span></a>
    </li>

    <!-- Nav Item - Roles -->
    <li class="nav-item {{ request()->is('admin/roles*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.roles.index') }}">
            <i class="fas fa-fw fa-lock"></i>
            <span>Roles</span></a>
    </li>

    <!-- Nav Item - Concession -->
    <li class="nav-item {{ request()->is('admin/concessions*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.concessions.index') }}">
            <i class="fas fa-fw fa-edit"></i>
            <span>Concession</span></a>
    </li>

    <!-- Nav Item - Settings -->
    <li class="nav-item {{ request()->is('admin/settings*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.settings.index') }}">
            <i class="fas fa-fw fa-cog"></i>
            <span>Settings</span></a>
    </li>

    @endif

    @if(session('role_id') == 2)
    <!-- Nav Item - Salary -->
    <li class="nav-item {{ request()->is('admin/salaries*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.salaries.index') }}">
            <i class="fas fa-fw fa-dollar-sign"></i>
            <span>Salary</span></a>
    </li>

    <!-- Nav Item - Report Attendance -->
    <li class="nav-item {{ request()->is('admin/attendances*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.attendances.index') }}">
            <i class="fas fa-fw fa-user"></i>
            <span>Report Attendance</span></a>
    </li>
    @endif

    @if(session('role_id') == 3)
    <!-- Nav Item - Report Attendance -->
    <li class="nav-item {{ request()->is('admin/attendances*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.attendances.index') }}">
            <i class="fas fa-fw fa-user"></i>
            <span>Report Attendance</span></a>
    </li>

    <!-- Nav Item - Report Concession -->
    <li class="nav-item {{ request()->is('admin/concessions*') ? 'active' : '' }}">
        <a class="nav-link" href="{{ route('admin.concessions.index') }}">
            <i class="fas fa-fw fa-edit"></i>
            <span>Report Concession</span></a>
    </li>
    @endif

    <hr class="sidebar-divider d-none d-md-block">

    <!-- Nav Item - Logout -->
    <li class="nav-item">
            <a class="nav-link" href="{{ url('admin.logout') }}" data-toggle="modal" data-target="#logoutModal">
                                     <i class="fas fa-fw fa-sign-out-alt"></i>
                                    Logout
                                </a>
    </li>

    

    <!-- Divider -->
    <hr class="sidebar-divider d-none d-md-block">

</ul>
<!-- End of Sidebar -->