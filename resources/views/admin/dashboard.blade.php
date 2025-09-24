@extends('layouts.admin')

@section('content')

<div class="container-fluid">

    <!-- Page Heading -->
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <h1 class="h3 mb-0 text-gray-800">Dashboard</h1>
        <a href="#" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm"><i class="fas fa-download fa-sm text-white-50"></i> Generate Report</a>
    </div>

    <!-- Content Row -->
    <div class="row">

        <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                            Users</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_users }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-users fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

        <!-- Earnings (Monthly) Card Example -->
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                            Concessions</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_concessions }}</div>
                    </div>
                    <div class="col-auto">
                        <i class="fas fa-edit fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Attendance Today</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $today_attendances }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-check fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Roles</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_roles }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-user-tie fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>


    </div>

    <!-- Attendance Statistics Row -->
    <div class="row">
        <!-- Weekly Attendance -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-info shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                This Week</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $week_attendances }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-week fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Monthly Attendance -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-warning shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                This Month</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $month_attendances }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-calendar-alt fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Total Attendance -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-success shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                Total Records</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $total_attendances }}</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-database fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Attendance Rate -->
        <div class="col-xl-3 col-md-6 mb-4">
            <div class="card border-left-danger shadow h-100 py-2">
                <div class="card-body">
                    <div class="row no-gutters align-items-center">
                        <div class="col mr-2">
                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                Attendance Rate</div>
                            <div class="h5 mb-0 font-weight-bold text-gray-800">{{ $attendance_rate }}%</div>
                        </div>
                        <div class="col-auto">
                            <i class="fas fa-percentage fa-2x text-gray-300"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Attendance Details Row -->
    <div class="row">
        <!-- Attendance by Status -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Attendance by Status</h6>
                </div>
                <div class="card-body">
                    @if(count($attendance_by_status) > 0)
                        @foreach($attendance_by_status as $status => $count)
                        <div class="row mb-2">
                            <div class="col-8">
                                <span class="text-xs font-weight-bold">{{ $status }}</span>
                            </div>
                            <div class="col-4 text-right">
                                <span class="badge badge-primary">{{ $count }}</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No attendance data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Recent Attendance -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Recent Attendance</h6>
                </div>
                <div class="card-body">
                    @if($recent_attendances->count() > 0)
                        @foreach($recent_attendances->take(5) as $attendance)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-primary">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <div class="small text-gray-500">{{ $attendance->present_at->format('M d, Y H:i') }}</div>
                                <span class="font-weight-bold">{{ $attendance->user->name ?? 'Unknown' }}</span>
                                <span class="badge badge-{{ $attendance->description == 'Hadir' ? 'success' : 'warning' }} ml-2">{{ $attendance->description }}</span>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No recent attendance records</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Top Users and Trend Row -->
    <div class="row">
        <!-- Top Users -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">Top Users This Month</h6>
                </div>
                <div class="card-body">
                    @if($top_users->count() > 0)
                        @foreach($top_users as $user)
                        <div class="d-flex align-items-center mb-3">
                            <div class="mr-3">
                                <div class="icon-circle bg-success">
                                    <i class="fas fa-user text-white"></i>
                                </div>
                            </div>
                            <div class="flex-grow-1">
                                <span class="font-weight-bold">{{ $user->name }}</span>
                                <div class="small text-gray-500">{{ $user->attendances_count }} attendances</div>
                            </div>
                        </div>
                        @endforeach
                    @else
                        <p class="text-center text-muted">No user attendance data available</p>
                    @endif
                </div>
            </div>
        </div>

        <!-- Attendance Trend -->
        <div class="col-xl-6 col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">7-Day Attendance Trend</h6>
                </div>
                <div class="card-body">
                    @if(count($attendance_trend) > 0)
                        <div class="chart-area">
                            <canvas id="attendanceTrendChart" width="100%" height="50"></canvas>
                        </div>
                    @else
                        <p class="text-center text-muted">No trend data available</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <!-- Content Row -->
    
</div>

@endsection

@section('styles')
<style>
.icon-circle {
    height: 40px;
    width: 40px;
    border-radius: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
}
</style>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Attendance Trend Chart
    @if(count($attendance_trend) > 0)
    var ctx = document.getElementById('attendanceTrendChart');
    if (ctx) {
        var attendanceTrendChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: [
                    @foreach($attendance_trend as $trend)
                        '{{ \Carbon\Carbon::parse($trend['date'])->format('M d') }}',
                    @endforeach
                ],
                datasets: [{
                    label: 'Daily Attendance',
                    data: [
                        @foreach($attendance_trend as $trend)
                            {{ $trend['count'] }},
                        @endforeach
                    ],
                    borderColor: 'rgb(75, 192, 192)',
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    tension: 0.1
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                },
                plugins: {
                    legend: {
                        display: false
                    }
                }
            }
        });
    }
    @endif
});
</script>
@endsection
