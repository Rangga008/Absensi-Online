@extends('layouts.admin')

@section('title', 'Import Users')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800">
            <i class="fas fa-file-import mr-2"></i>Import Users
        </h1>
        <div>
            <a href="{{ route('admin.users.index') }}" class="btn btn-secondary">
                <i class="fas fa-arrow-left mr-2"></i>Back to Users
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-md-8">
            <div class="card shadow mb-4">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="m-0 font-weight-bold">
                        <i class="fas fa-upload mr-2"></i>Import User Data
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

                    <form action="{{ route('admin.users.import') }}" method="POST" enctype="multipart/form-data">
                        @csrf

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

                        <div class="form-group">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="generate_password" name="generate_password" value="1" checked>
                                <label class="custom-control-label" for="generate_password">
                                    <strong>Auto-generate passwords for users</strong>
                                </label>
                                <small class="form-text text-muted d-block">
                                    If checked, random passwords will be generated for users. If unchecked, passwords must be provided in the file.
                                </small>
                            </div>
                        </div>

                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-lg">
                                <i class="fas fa-upload mr-2"></i>Import Users
                            </button>
                            <a href="{{ route('admin.users.import.template') }}" class="btn btn-outline-secondary ml-2">
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
                                    <td>name</td>
                                    <td>String (3-255 chars)</td>
                                    <td><span class="badge badge-success">Yes</span></td>
                                </tr>
                                <tr>
                                    <td>email</td>
                                    <td>Valid email address</td>
                                    <td><span class="badge badge-success">Yes</span></td>
                                </tr>
                                <tr>
                                    <td>phone</td>
                                    <td>String (10-15 chars)</td>
                                    <td><span class="badge badge-success">Yes</span></td>
                                </tr>
                                <tr>
                                    <td>address</td>
                                    <td>String (max 500 chars)</td>
                                    <td><span class="badge badge-success">Yes</span></td>
                                </tr>
                                <tr>
                                    <td>role_id</td>
                                    <td>Integer (existing role ID)</td>
                                    <td><span class="badge badge-secondary">No</span></td>
                                </tr>
                                <tr>
                                    <td>password</td>
                                    <td>String (min 6 chars)</td>
                                    <td><span class="badge badge-secondary">No</span></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold text-primary mt-4">Available Roles:</h6>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead>
                                <tr>
                                    <th>Role ID</th>
                                    <th>Role Name</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($roles as $role)
                                    <tr>
                                        <td class="text-center">{{ $role->id }}</td>
                                        <td>{{ $role->role_name }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <h6 class="font-weight-bold text-primary mt-4">Validation Rules:</h6>
                    <ul class="small pl-3">
                        <li>Email addresses must be unique in the system</li>
                        <li>If role_id is not provided, default role (ID: 2) will be assigned</li>
                        <li>If password is not provided and auto-generate is disabled, import will fail</li>
                        <li>All required fields must be filled</li>
                    </ul>

                    <div class="alert alert-warning mt-3">
                        <i class="fas fa-exclamation-triangle mr-2"></i>
                        <strong>Note:</strong> Duplicate emails will be skipped. Make sure your data is clean before importing.
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
</script>
@endsection
