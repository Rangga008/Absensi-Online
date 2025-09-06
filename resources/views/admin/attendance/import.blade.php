@extends('layouts.admin')

@section('title', 'Import Attendance')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-import mr-2"></i>Import Attendance
        </h1>
        <div>
            <a href="{{ route('admin.attendances.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Attendance
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-upload mr-2"></i>Import Attendance Data
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

                    @if(session('warning'))
                        <div class="alert alert-warning alert-dismissible fade show">
                            <i class="fas fa-exclamation-triangle mr-2"></i>
                            {{ session('warning') }}
                            <button type="button" class="close" data-dismiss="alert">
                                <span>&times;</span>
                            </button>
                        </div>
                    @endif

                    @if($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0">
                                @foreach($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form action="{{ route('admin.attendances.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="file"><strong>File Upload *</strong></label>
                                    <div class="custom-file">
                                        <input type="file" class="custom-file-input" id="file" name="file" required accept=".csv,.xlsx,.xls">
                                        <label class="custom-file-label" for="file">Choose file...</label>
                                    </div>
                                    <small class="form-text text-muted">
                                        Supported formats: CSV, XLSX, XLS (Max: 2MB)
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="role_id"><strong>Filter by Role</strong></label>
                                    <select class="form-control" id="role_id" name="role_id">
                                        <option value="">All Roles</option>
                                        @foreach($roles as $role)
                                            <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                {{ $role->role_name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    <small class="form-text text-muted">
                                        Only import attendance for specific role
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="start_date"><strong>Start Date</strong></label>
                                    <input type="date" class="form-control" id="start_date" name="start_date" value="{{ old('start_date') }}">
                                    <small class="form-text text-muted">
                                        Only import attendance from this date
                                    </small>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="form-group">
                                    <label for="end_date"><strong>End Date</strong></label>
                                    <input type="date" class="form-control" id="end_date" name="end_date" value="{{ old('end_date') }}">
                                    <small class="form-text text-muted">
                                        Only import attendance until this date
                                    </small>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="update_existing" name="update_existing" value="1" {{ old('update_existing') ? 'checked' : '' }}>
                                <label class="custom-control-label" for="update_existing">
                                    <strong>Update existing records</strong>
                                </label>
                                <small class="form-text text-muted d-block">
                                    If checked, existing attendance records for the same user and date will be updated. 
                                    If unchecked, duplicate records will be skipped.
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload mr-2"></i>Import Attendance
                            </button>
                            <a href="{{ route('admin.attendances.import.template') }}" class="btn btn-outline-secondary ml-2">
                                <i class="fas fa-download mr-2"></i>Download Template
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if(session('import_errors'))
                <div class="card shadow mb-4">
                    <div class="card-header bg-warning text-dark py-3">
                        <h6 class="m-0 font-weight-bold">
                            <i class="fas fa-exclamation-triangle mr-2"></i>Import Errors
                        </h6>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered table-sm">
                                <thead>
                                    <tr>
                                        <th width="80">Row</th>
                                        <th>Error Message</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach(session('import_errors') as $error)
                                        <tr>
                                            <td class="text-center">
                                                <span class="badge badge-danger">{{ explode(':', $error)[0] }}</span>
                                            </td>
                                            <td>{{ substr(strstr($error, ':'), 2) }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif
        </div>

        <div class="col-md-4">
            <div class="card shadow">
                <div class="card-header bg-info text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-info-circle mr-2"></i>Import Instructions
                    </h6>
                </div>
                <div class="card-body">
                    <h6 class="font-weight-bold text-primary">File Format Requirements:</h6>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-check-circle text-success mr-2"></i>CSV, XLSX, or XLS format</li>
                        <li><i class="fas fa-check-circle text-success mr-2"></i>Maximum file size: 2MB</li>
                        <li><i class="fas fa-check-circle text-success mr-2"></i>First row must contain headers</li>
                    </ul>

                    <h6 class="font-weight-bold text-primary mt-4">Required Columns:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Column</th>
                                    <th>Format</th>
                                    <th>Required</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>user_id</td>
                                    <td>Integer</td>
                                    <td><span class="badge badge-success">Yes</span></td>
                                </tr>
                                <tr>
                                    <td>present_date</td>
                                    <td>YYYY-MM-DD</td>
                                    <td><span class="badge badge-success">Yes</span></td>
                                </tr>
                                <tr>
                                    <td>present_time</td>
                                    <td>HH:MM</td>
                                    <td><span class="badge badge-success">Yes</span></td>
                                </tr>
                                <tr>
                                    <td>description</td>
                                    <td>Hadir/Terlambat/Sakit/Izin/Dinas Luar/WFH</td>
                                    <td><span class="badge badge-success">Yes</span></td>
                                </tr>
                                <tr>
                                    <td>latitude</td>
                                    <td>Decimal</td>
                                    <td><span class="badge badge-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td>longitude</td>
                                    <td>Decimal</td>
                                    <td><span class="badge badge-secondary">No</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold text-primary mt-4">Validation Rules:</h6>
                    <ul class="small pl-3">
                        <li>Each user can only have one attendance record per day</li>
                        <li>User ID must exist in the system</li>
                        <li>Dates must be in valid format</li>
                        <li>Description must be one of the allowed values</li>
                    </ul>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Note:</strong> Duplicate records (same user and date) will be skipped unless "Update existing records" is checked.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
// Show file name in custom file input
document.querySelector('.custom-file-input').addEventListener('change', function(e) {
    var fileName = document.getElementById("file").files[0].name;
    var nextSibling = e.target.nextElementSibling;
    nextSibling.innerText = fileName;
});

// Date validation
document.addEventListener('DOMContentLoaded', function() {
    const startDate = document.getElementById('start_date');
    const endDate = document.getElementById('end_date');

    if (startDate && endDate) {
        startDate.addEventListener('change', function() {
            if (this.value && endDate.value && this.value > endDate.value) {
                endDate.value = this.value;
            }
        });

        endDate.addEventListener('change', function() {
            if (this.value && startDate.value && this.value < startDate.value) {
                startDate.value = this.value;
            }
        });
    }
});
</script>
@endsection