@extends('layouts.admin')

@section('title', 'Work Time Details')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Work Time Details</h4>
                        <div>
                            <a href="{{ route('admin.settings.work-times.edit', $workTime->id) }}" class="btn btn-warning btn-sm">
                                <i class="fas fa-edit"></i> Edit
                            </a>
                            <a href="{{ route('admin.settings.work-times.index') }}" class="btn btn-secondary btn-sm">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                        </div>
                    </div>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <table class="table table-borderless">
                                <tr>
                                    <th width="40%">Name:</th>
                                    <td>{{ $workTime->name }}</td>
                                </tr>
                                <tr>
                                    <th>Start Time:</th>
                                    <td>
                                        <span class="badge bg-primary">{{ $workTime->formatted_start_time }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>End Time:</th>
                                    <td>
                                        <span class="badge bg-success">{{ $workTime->formatted_end_time }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Late Threshold:</th>
                                    <td>
                                        <span class="badge bg-warning">{{ $workTime->formatted_late_threshold }}</span>
                                    </td>
                                </tr>
                                <tr>
                                    <th>Status:</th>
                                    <td>
                                        <span class="badge {{ $workTime->is_active ? 'bg-success' : 'bg-danger' }}">
                                            {{ $workTime->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <div class="col-md-6">
                            <h6>Description:</h6>
                            <p class="text-muted">
                                {{ $workTime->description ?: 'No description provided' }}
                            </p>

                            <h6>Work Duration:</h6>
                            <p>
                                @php
                                    $start = \Carbon\Carbon::createFromFormat('H:i:s', $workTime->start_time);
                                    $end = \Carbon\Carbon::createFromFormat('H:i:s', $workTime->end_time);
                                    $duration = $start->diffInHours($end) . ' hours ' . ($start->diffInMinutes($end) % 60) . ' minutes';
                                @endphp
                                <span class="badge bg-info">{{ $duration }}</span>
                            </p>

                            <h6>Users Assigned:</h6>
                            <p>
                                <span class="badge bg-secondary">{{ $workTime->users()->count() }} users</span>
                            </p>
                        </div>
                    </div>

                    @if($workTime->users()->count() > 0)
                        <div class="mt-4">
                            <h6>Assigned Users:</h6>
                            <div class="table-responsive">
                                <table class="table table-sm table-striped">
                                    <thead>
                                        <tr>
                                            <th>Name</th>
                                            <th>Email</th>
                                            <th>Phone</th>
                                            <th>Role</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($workTime->users as $user)
                                            <tr>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->email }}</td>
                                                <td>{{ $user->phone ?: '-' }}</td>
                                                <td>
                                                    <span class="badge bg-primary">{{ $user->role->name ?? 'No Role' }}</span>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @endif

                    <div class="mt-4">
                        <h6>Attendance Statistics (Last 30 days):</h6>
                        @php
                            $thirtyDaysAgo = now()->subDays(30);
                            $recentAttendances = $workTime->attendances()
                                ->where('present_date', '>=', $thirtyDaysAgo->format('Y-m-d'))
                                ->get();

                            $stats = [
                                'total' => $recentAttendances->count(),
                                'hadir' => $recentAttendances->where('description', 'Hadir')->count(),
                                'terlambat' => $recentAttendances->where('description', 'Terlambat')->count(),
                                'sakit' => $recentAttendances->where('description', 'Sakit')->count(),
                                'izin' => $recentAttendances->where('description', 'Izin')->count(),
                            ];
                        @endphp

                        <div class="row">
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-primary">{{ $stats['total'] }}</h4>
                                    <small class="text-muted">Total Records</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-success">{{ $stats['hadir'] }}</h4>
                                    <small class="text-muted">Present</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-warning">{{ $stats['terlambat'] }}</h4>
                                    <small class="text-muted">Late</small>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="text-center p-3 border rounded">
                                    <h4 class="text-info">{{ $stats['sakit'] + $stats['izin'] }}</h4>
                                    <small class="text-muted">Absent</small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
