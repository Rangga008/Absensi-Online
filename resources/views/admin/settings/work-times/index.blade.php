@extends('layouts.admin')

@section('title', 'Work Times Management')

@section('content')
<div class="container-fluid">
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-between align-items-center">
                        <h4 class="card-title mb-0">Work Times Management</h4>
                        <a href="{{ route('admin.settings.work-times.create') }}" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Add New Work Time
                        </a>
                    </div>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            {{ session('success') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    @if(session('error'))
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            {{ session('error') }}
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    @endif

                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="table-dark">
                                <tr>
                                    <th>Name</th>
                                    <th>Start Time</th>
                                    <th>End Time</th>
                                    <th>Late Threshold</th>
                                    <th>Users Assigned</th>
                                    <th>Status</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($workTimes as $workTime)
                                    <tr>
                                        <td>
                                            <strong>{{ $workTime->name }}</strong>
                                            @if($workTime->description)
                                                <br><small class="text-muted">{{ $workTime->description }}</small>
                                            @endif
                                        </td>
                                        <td>{{ $workTime->formatted_start_time }}</td>
                                        <td>{{ $workTime->formatted_end_time }}</td>
                                        <td>{{ $workTime->formatted_late_threshold }}</td>
                                        <td>
                                            <span class="badge bg-info">{{ $workTime->users()->count() }} users</span>
                                        </td>
                                        <td>
                                            <span class="badge {{ $workTime->is_active ? 'bg-success' : 'bg-danger' }}">
                                                {{ $workTime->is_active ? 'Active' : 'Inactive' }}
                                            </span>
                                        </td>
                                        <td>
                                            <div class="btn-group" role="group">
                                                <a href="{{ route('admin.settings.work-times.show', $workTime->id) }}"
                                                   class="btn btn-sm btn-info" title="View">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="{{ route('admin.settings.work-times.edit', $workTime->id) }}"
                                                   class="btn btn-sm btn-warning" title="Edit">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                                <button type="button" class="btn btn-sm btn-{{ $workTime->is_active ? 'secondary' : 'success' }}"
                                                        onclick="toggleStatus({{ $workTime->id }})" title="Toggle Status">
                                                    <i class="fas fa-toggle-{{ $workTime->is_active ? 'off' : 'on' }}"></i>
                                                </button>
                                            </div>
                                            @if($workTime->users()->count() == 0)
                                                <button type="button" class="btn btn-sm btn-danger ml-1"
                                                        onclick="deleteWorkTime({{ $workTime->id }})" title="Delete">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @else
                                                <button type="button" class="btn btn-sm btn-danger ml-1" title="Cannot Delete - Has Users" disabled>
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="7" class="text-center py-4">
                                            <div class="text-muted">
                                                <i class="fas fa-clock fa-3x mb-3"></i>
                                                <p>No work times found.</p>
                                                <a href="{{ route('admin.settings.work-times.create') }}" class="btn btn-primary">
                                                    Create your first work time
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function toggleStatus(workTimeId) {
    if (confirm('Are you sure you want to toggle the status of this work time?')) {
        fetch(`/admin/settings/work-times/${workTimeId}/toggle-status`, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'Content-Type': 'application/json',
            },
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('An error occurred while updating the status');
        });
    }
}

function deleteWorkTime(workTimeId) {
    if (confirm('Are you sure you want to delete this work time?')) {
        // Create a form and submit it
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/settings/work-times/${workTimeId}`;

        // Add CSRF token
        const csrfToken = document.createElement('input');
        csrfToken.type = 'hidden';
        csrfToken.name = '_token';
        csrfToken.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
        form.appendChild(csrfToken);

        // Add method DELETE
        const methodInput = document.createElement('input');
        methodInput.type = 'hidden';
        methodInput.name = '_method';
        methodInput.value = 'DELETE';
        form.appendChild(methodInput);

        // Append form to body and submit
        document.body.appendChild(form);
        form.submit();
    }
}
</script>
@endsection
