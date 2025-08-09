@extends('layouts.admin')

@section('styles')
<style>
.filter-section {
    width: 200px;
}
#roleFilter {
    cursor: pointer;
}
</style>
@endsection

@section('content')
<h1 class="h3 mb-2 text-gray-800">Users Attendance</h1>

<div class="card shadow mb-4">
    <div class="card-header py-3 d-flex justify-content-between align-items-center">
        <h6 class="m-0 font-weight-bold text-primary">Attendance Summary</h6>
        <div class="filter-section">
            <select id="roleFilter" class="form-control form-control-sm">
            <option value="all">All Roles</option>
            @foreach($roles as $role)
                <option value="{{ $role->id }}" {{ request('role_id') == $role->id ? 'selected' : '' }}>
                    {{ $role->role_name }} <!-- Perhatikan ini menggunakan role_name bukan name -->
                </option>
            @endforeach
            </select>
        </div>
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered" id="attendanceTable">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Total Attendance</th>
                        <th>Last Attendance</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @include('admin.attendance.partials.user_rows')
                </tbody>
            </table>
            <div id="pagination-links">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
$(document).ready(function() {
    // Filter realtime berdasarkan role
    $('#roleFilter').change(function() {
    const roleId = $(this).val();
    const url = new URL(window.location.href);
    
    if (roleId === 'all') {
        url.searchParams.delete('role_id');
    } else {
        url.searchParams.set('role_id', roleId);
    }
    
    window.location.href = url.toString();
    });

    // Auto refresh setiap 30 detik
    setInterval(function() {
        const roleId = $('#roleFilter').val();
        
        $.ajax({
            url: "{{ route('admin.attendances.index') }}",
            data: { role_id: roleId },
            success: function(data) {
                $('#attendanceTable tbody').html($(data).find('#attendanceTable tbody').html());
            }
        });
    }, 30000);
});
</script>
@endsection