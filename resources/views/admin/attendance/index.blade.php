@extends('layouts.admin')

@section('title', 'Attendance Management')

@section('styles')
<style>
.filter-section {
    background: #f8f9fa;
    padding: 15px;
    border-radius: 5px;
    margin-bottom: 20px;
    box-shadow: 0 2px 4px rgba(0,0,0,0.05);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 15px;
}

.filter-controls {
    display: flex;
    gap: 15px;
    flex-wrap: wrap;
}

.filter-group {
    min-width: 200px;
}

.table-responsive {
    overflow-x: auto;
    border-radius: 5px;
    box-shadow: 0 0 10px rgba(0,0,0,0.05);
}

#attendanceTable {
    font-size: 0.9rem;
}

#attendanceTable th {
    white-space: nowrap;
    position: relative;
    background: #f8f9fa;
}



.badge-attendance {
    font-size: 0.8em;
    font-weight: 500;
    padding: 5px 8px;
}

.user-avatar {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    object-fit: cover;
    margin-right: 10px;
}

.last-attendance {
    display: flex;
    align-items: center;
}

.last-attendance-date {
    font-weight: 500;
    color: #4e73df;
}

.last-attendance-status {
    font-size: 0.8em;
    margin-left: 5px;
}

.action-buttons .btn {
    padding: 0.25rem 0.5rem;
    font-size: 0.8rem;
}

.pagination {
    justify-content: center;
    margin-top: 20px;
}

.empty-state {
    text-align: center;
    padding: 40px 0;
    color: #6c757d;
}

.empty-state i {
    font-size: 3rem;
    color: #dee2e6;
    margin-bottom: 15px;
}

@media (max-width: 768px) {
    .filter-group {
        min-width: 100%;
    }
    
    .card-header {
        flex-direction: column;
        align-items: flex-start;
    }
}
</style>
@endsection

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">Attendance Management</h1>
    <div>
        <a href="{{ route('admin.attendances.import.form') }}" class="btn btn-info mr-2">
            <i class="fas fa-file-import mr-2"></i>Import
        </a>
        <a href="{{ route('admin.attendances.export.form') }}" class="btn btn-success mr-2">
            <i class="fas fa-file-export mr-2"></i>Export Attendance
        </a>
        <a href="{{ route('admin.attendances.create') }}" class="btn btn-primary">
            <i class="fas fa-plus mr-2"></i>Add Attendance
        </a>
    </div>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Attendance Summary</h6>
    </div>
    
    <div class="card-body">
        <!-- Filter Section -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="form-group">
                    <label for="search"><strong>Search:</strong></label>
                    <div class="input-group">
                    <input type="text" class="form-control" id="search" placeholder="Search by name..." onkeyup="filterTable()">
                        <div class="input-group-append">
                            <span class="input-group-text">
                                <i class="fas fa-search"></i>
                            </span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="role_id"><strong>Filter by Role:</strong></label>
                    <select class="form-control" id="role_id" onchange="filterTable()">
                        <option value="">All Roles</option>
                        @foreach($roles as $role)
                            <option value="{{ $role->id }}">
                                {{ $role->role_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="status"><strong>Filter by Status:</strong></label>
                    <select class="form-control" id="status" onchange="filterTable()">
                        <option value="">All Status</option>
                        <option value="present">Hadir</option>
                        <option value="late">Terlambat</option>
                        <option value="absent">Sakit/Izin</option>
                        <option value="other">Dinas Luar/WFH</option>
                        <option value="no_record">No Record</option>
                    </select>
                </div>
            </div>
            <div class="col-md-3">
                <div class="form-group">
                    <label for="reset"><strong>Reset Filters:</strong></label>
                    <button class="btn btn-outline-secondary btn-block" onclick="resetFilters()">
                        <i class="fas fa-sync-alt mr-2"></i>Reset
                    </button>
                </div>
            </div>
        </div>

        @if($users->isEmpty())
            <div class="empty-state">
                <i class="fas fa-user-clock"></i>
                <h4>No attendance records found</h4>
                <p>Try adjusting your filters or add new attendance records</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table table-hover" id="attendanceTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Role</th>
                        <th>Total Attendance</th>
                        <th>Last Attendance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                        <tr data-user-id="{{ $user->id }}" data-role-id="{{ $user->role_id }}" data-status="{{ $user->latestAttendance ? \App\Http\Controllers\AttendanceController::getStatusFilterValue($user->latestAttendance->description) : 'no_record' }}" data-name="{{ $user->name }}">
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($user->profile_photo_path)
                                        <img src="{{ asset('storage/' . $user->profile_photo_path) }}"
                                             class="user-avatar"
                                             alt="{{ $user->name }}">
                                    @else
                                        <div class="avatar bg-primary text-white rounded-circle mr-2 d-flex align-items-center justify-content-center"
                                             style="width: 32px; height: 32px;">
                                            {{ substr($user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <span>{{ $user->name }}</span>
                                </div>
                            </td>
                            <td>{{ $user->role->role_name }}</td>
                            <td>
                                <span class="badge badge-primary badge-attendance">
                                    {{ $user->attendances_count ?? 0 }} days
                                </span>
                            </td>
                            <td>
                                @if($user->latestAttendance)
                                    <div class="last-attendance">
                                        <span class="last-attendance-date">
                                            {{ $user->latestAttendance->present_at->locale('id')->isoFormat('D MMMM YYYY') }}
                                        </span>
                                        <span class="last-attendance-time text-muted">
                                            {{ $user->latestAttendance->present_at->format('H:i') }}
                                        </span>
                                    </div>
                                @else
                                    <span class="text-muted">No record</span>
                                @endif
                            </td>
                            <td>
                                @if($user->latestAttendance)
                                    @php
                                        $status = $user->latestAttendance->description;
                                        $badgeClass = [
                                            'Hadir' => 'success',
                                            'Terlambat' => 'warning',
                                            'Sakit' => 'info',
                                            'Izin' => 'secondary',
                                            'Dinas Luar' => 'primary',
                                            'WFH' => 'dark'
                                        ][$status] ?? 'light';
                                    @endphp
                                    <span class="badge badge-{{ $badgeClass }} last-attendance-status">
                                        {{ $status }}
                                    </span>
                                @else
                                    <span class="badge badge-light">No Record</span>
                                @endif
                            </td>
                            <td class="action-buttons">
                                <a href="{{ route('admin.users.attendances', $user->id) }}"
                                class="btn btn-sm btn-info"
                                title="View Details">
                                    <i class="fas fa-eye"></i>
                                </a>
                                <a href="{{ route('admin.attendances.create', ['user_id' => $user->id]) }}"
                                class="btn btn-sm btn-success"
                                title="Add Attendance">
                                    <i class="fas fa-plus"></i>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
                
                <div class="d-flex justify-content-between align-items-center mt-3">
                    <div class="text-muted">
                        Showing {{ $users->firstItem() }} to {{ $users->lastItem() }} of {{ $users->total() }} entries
                    </div>
                    <div>
                        {{ $users->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@if(session('role_id') !== 3)
<script>
function filterTable() {
    const searchFilter = document.getElementById('search').value.toLowerCase();
    const roleFilter = document.getElementById('role_id').value;
    const statusFilter = document.getElementById('status').value;

    const rows = document.querySelectorAll('#attendanceTable tbody tr');

    rows.forEach(row => {
        const name = row.getAttribute('data-name').toLowerCase();
        const roleId = row.getAttribute('data-role-id');
        const status = row.getAttribute('data-status');

        const searchMatch = searchFilter === '' || name.includes(searchFilter);
        const roleMatch = roleFilter === '' || roleId === roleFilter;
        const statusMatch = statusFilter === '' || status === statusFilter;

        row.style.display = searchMatch && roleMatch && statusMatch ? '' : 'none';
    });
}

function resetFilters() {
    document.getElementById('search').value = '';
    document.getElementById('role_id').value = '';
    document.getElementById('status').value = '';
    filterTable();
}

// Add event listeners
document.addEventListener('DOMContentLoaded', function() {
    document.getElementById('search').addEventListener('keyup', debounce(filterTable, 500));
    document.getElementById('role_id').addEventListener('change', filterTable);
    document.getElementById('status').addEventListener('change', filterTable);

    // Initial filter on page load
    filterTable();
});

function debounce(func, wait) {
    let timeout;
    return function(...args) {
        clearTimeout(timeout);
        timeout = setTimeout(() => func.apply(this, args), wait);
    };
}
</script>
@endif
