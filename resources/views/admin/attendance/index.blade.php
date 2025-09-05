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

#attendanceTable th.sortable:hover {
    background-color: #e9ecef;
    cursor: pointer;
}

#attendanceTable th.sortable::after {
    content: '⇅';
    margin-left: 5px;
    font-size: 0.8em;
    color: #6c757d;
}

#attendanceTable th.sorted-asc::after {
    content: '↑';
    color: #4e73df;
}

#attendanceTable th.sorted-desc::after {
    content: '↓';
    color: #4e73df;
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
    <a href="{{ route('admin.attendances.create') }}" class="btn btn-primary">
        <i class="fas fa-plus mr-2"></i>Add Attendance
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Attendance Summary</h6>
    </div>
    
    <div class="card-body">
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
                        <th class="sortable {{ request('sort') == 'name' ? 'sorted-' . request('direction', 'asc') : '' }}" 
                            data-sort="name">Name</th>
                        <th class="sortable {{ request('sort') == 'role' ? 'sorted-' . request('direction', 'asc') : '' }}" 
                            data-sort="role">Role</th>
                        <th>Total Attendance</th>
                        <th class="sortable {{ request('sort') == 'last_attendance' ? 'sorted-' . request('direction', 'asc') : '' }}" 
                            data-sort="last_attendance">Last Attendance</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $index => $user)
                        <tr>
                            <td>{{ $users->firstItem() + $index }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    
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
                        {{ $users->appends(request()->query())->links() }}
                    </div>
                </div>
            </div>
        @endif
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Handle role filter change
    $('#roleFilter').change(function() {
        updateFilters();
    });
    
    // Handle status filter change
    $('#statusFilter').change(function() {
        updateFilters();
    });
    
    // Handle sorting
    $('.sortable').click(function() {
        const sortField = $(this).data('sort');
        const currentSort = '{{ request('sort') }}';
        const currentDirection = '{{ request('direction', 'asc') }}';
        
        let newDirection = 'asc';
        if (currentSort === sortField) {
            newDirection = currentDirection === 'asc' ? 'desc' : 'asc';
        }
        
        updateFilters({
            sort: sortField,
            direction: newDirection
        });
    });
    
    function updateFilters(additionalParams = {}) {
        const params = {
            role_id: $('#roleFilter').val(),
            status: $('#statusFilter').val(),
            ...additionalParams
        };
        
        // Remove empty params
        Object.keys(params).forEach(key => {
            if (params[key] === 'all' || params[key] === '') {
                delete params[key];
            }
        });
        
        // Convert to URL
        const queryString = $.param(params);
        window.location.href = "{{ route('admin.attendances.index') }}" + (queryString ? '?' + queryString : '');
    }
    
    // Auto-refresh every 30 seconds
    let refreshInterval = setInterval(refreshData, 30000);
    
    function refreshData() {
        $.ajax({
            url: window.location.href,
            data: { ajax: 1 },
            success: function(data) {
                const $newData = $(data);
                $('#attendanceTable tbody').html($newData.find('#attendanceTable tbody').html());
                $('.pagination').html($newData.find('.pagination').html());
            }
        });
    }
    
    // Pause auto-refresh when tab is inactive
    $(window).focus(function() {
        if (!refreshInterval) {
            refreshInterval = setInterval(refreshData, 30000);
        }
    }).blur(function() {
        clearInterval(refreshInterval);
        refreshInterval = null;
    });
});
</script>
@endsection