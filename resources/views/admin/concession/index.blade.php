@extends('layouts.admin')

@section('title', 'Concession Management')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-medical-alt mr-2"></i>
            @if(session('role_id') == 3)
                My Concessions
            @else
                Concession Management
            @endif
        </h1>
        
        @if(session('role_id') !== 3)
        <a href="{{ route('admin.concessions.create') }}" class="btn btn-success">
            <i class="fas fa-plus mr-1"></i> Add Concession
        </a>
        @endif
    </div>

    <div class="card shadow">
        <div class="card-header bg-primary text-white py-3">
            <h6 class="m-0 font-weight-bold">
                <i class="fas fa-list mr-2"></i>
                Concession List
            </h6>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show">
                    <i class="fas fa-check-circle mr-2"></i>
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show">
                    <i class="fas fa-exclamation-circle mr-2"></i>
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <!-- Filter Section -->
            @if(session('role_id') !== 3)
            <div class="row mb-4">
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="status_filter"><strong>Filter by Status:</strong></label>
                        <select class="form-control" id="status_filter" onchange="filterTable()">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label for="user_filter"><strong>Filter by Employee:</strong></label>
                        <select class="form-control" id="user_filter" onchange="filterTable()">
                            <option value="">All Employees</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}">{{ $user->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="search"><strong>Search:</strong></label>
                        <div class="input-group">
                            <input type="text" class="form-control" id="search" placeholder="Search by reason or employee..." onkeyup="filterTable()">
                            <div class="input-group-append">
                                <span class="input-group-text">
                                    <i class="fas fa-search"></i>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="concessionsTable">
                    <thead class="bg-light">
                        <tr>
                            <th width="50">No</th>
                            <th>Employee</th>
                            <th>Reason</th>
                            <th>Start Date</th>
                            <th>End Date</th>
                            <th>Duration</th>
                            <th>Status</th>
                            <th>Submitted At</th>
                            @if(session('role_id') !== 3)
                            <th width="120">Actions</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($concessions as $concession)
                        <tr>
                            <td class="text-center">{{ $loop->iteration }}</td>
                            <td>
                                <div class="d-flex align-items-center">
                                    @if($concession->user->profile_photo_path)
                                        <img src="{{ asset('storage/' . $concession->user->profile_photo_path) }}" 
                                             class="rounded-circle mr-2" 
                                             width="35" 
                                             height="35" 
                                             alt="{{ $concession->user->name }}">
                                    @else
                                        <div class="avatar bg-primary text-white rounded-circle mr-2 d-flex align-items-center justify-content-center" 
                                             style="width: 35px; height: 35px;">
                                            {{ substr($concession->user->name, 0, 1) }}
                                        </div>
                                    @endif
                                    <div>
                                        <strong>{{ $concession->user->name }}</strong>
                                        <br>
                                        <small class="text-muted">{{ $concession->user->email }}</small>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge badge-info badge-pill py-1 px-2 mb-1">
                                    {{ ucfirst($concession->reason) }}
                                </span>
                                @if($concession->description)
                                    <br>
                                    <small class="text-muted">{{ Str::limit($concession->description, 50) }}</small>
                                @endif
                            </td>
                            <td>
                                @if($concession->start_date)
                                    <span class="text-primary">
                                        {{ $concession->start_date->locale('id')->isoFormat('D MMM YYYY') }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($concession->end_date)
                                    <span class="text-primary">
                                        {{ $concession->end_date->locale('id')->isoFormat('D MMM YYYY') }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                @if($concession->start_date && $concession->end_date)
                                    @php
                                        $duration = $concession->start_date->diffInDays($concession->end_date) + 1;
                                    @endphp
                                    <span class="badge badge-secondary">
                                        {{ $duration }} day{{ $duration > 1 ? 's' : '' }}
                                    </span>
                                @else
                                    <span class="text-muted">N/A</span>
                                @endif
                            </td>
                            <td>
                                <span class="badge badge-pill py-2 px-3 badge-{{ 
                                    $concession->status == 'approved' ? 'success' : 
                                    ($concession->status == 'rejected' ? 'danger' : 'warning') 
                                }}">
                                    <i class="fas {{ 
                                        $concession->status == 'approved' ? 'fa-check' : 
                                        ($concession->status == 'rejected' ? 'fa-times' : 'fa-clock') 
                                    }} mr-1"></i>
                                    {{ ucfirst($concession->status) }}
                                </span>
                            </td>
                            <td>
                                <small class="text-muted">
                                    {{ $concession->created_at->locale('id')->isoFormat('D MMM YYYY HH:mm') }}
                                </small>
                            </td>
                            @if(session('role_id') !== 3)
                            <td>
                                <div class="btn-group btn-group-sm" role="group">
                                    <a href="{{ route('admin.concessions.show', $concession->id) }}" 
                                       class="btn btn-info" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>
                                    <a href="{{ route('admin.concessions.edit', $concession->id) }}" 
                                       class="btn btn-primary" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form action="{{ route('admin.concessions.destroy', $concession->id) }}" 
                                          method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-danger" 
                                                title="Delete"
                                                onclick="return confirm('Are you sure you want to delete this concession? This action cannot be undone.')">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                            @endif
                        </tr>
                        @empty
                        <tr>
                            <td colspan="{{ session('role_id') !== 3 ? 9 : 8 }}" class="text-center py-4">
                                <div class="text-muted">
                                    <i class="fas fa-file-medical-alt fa-3x mb-3"></i>
                                    <h5>No concessions found</h5>
                                    <p>No concession requests have been submitted yet.</p>
                                    @if(session('role_id') !== 3)
                                    <a href="{{ route('admin.concession.create') }}" class="btn btn-primary mt-2">
                                        <i class="fas fa-plus mr-1"></i> Create First Concession
                                    </a>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            @if($concessions->hasPages())
            <div class="d-flex justify-content-between align-items-center mt-4">
                <div class="text-muted">
                    Showing {{ $concessions->firstItem() }} to {{ $concessions->lastItem() }} of {{ $concessions->total() }} entries
                </div>
                <nav>
                    {{ $concessions->links() }}
                </nav>
            </div>
            @endif
        </div>
    </div>
</div>

@if(session('role_id') !== 3)
<script>
function filterTable() {
    const statusFilter = document.getElementById('status_filter').value.toLowerCase();
    const userFilter = document.getElementById('user_filter').value;
    const searchFilter = document.getElementById('search').value.toLowerCase();
    
    const rows = document.querySelectorAll('#concessionsTable tbody tr');
    
    rows.forEach(row => {
        const status = row.cells[6].textContent.toLowerCase();
        const employeeId = row.cells[1].querySelector('strong').textContent.toLowerCase();
        const reason = row.cells[2].textContent.toLowerCase();
        const employeeEmail = row.cells[1].querySelector('small').textContent.toLowerCase();
        
        const statusMatch = statusFilter === '' || status.includes(statusFilter);
        const userMatch = userFilter === '' || row.getAttribute('data-user-id') === userFilter;
        const searchMatch = searchFilter === '' || 
                           reason.includes(searchFilter) || 
                           employeeId.includes(searchFilter) ||
                           employeeEmail.includes(searchFilter);
        
        row.style.display = statusMatch && userMatch && searchMatch ? '' : 'none';
    });
}

// Add user ID to each row for filtering
document.addEventListener('DOMContentLoaded', function() {
    const rows = document.querySelectorAll('#concessionsTable tbody tr');
    @foreach($concessions as $concession)
        rows[{{ $loop->index }}].setAttribute('data-user-id', '{{ $concession->user_id }}');
    @endforeach
});
</script>
@endif

<style>
.avatar {
    font-weight: bold;
    font-size: 14px;
}
.badge {
    font-size: 0.85em;
}
.table th {
    border-top: none;
    font-weight: 600;
    color: #495057;
}
.card-header {
    border-bottom: 1px solid #e3e6f0;
}
.btn-group .btn {
    border-radius: 4px;
    margin: 0 2px;
}
</style>
@endsection