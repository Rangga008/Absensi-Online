@extends('layouts.admin')

@section('content')
<!-- Page Heading -->
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.users.index') }}" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
    <h1 class="h3 mb-0 text-gray-800">User Details</h1>
    <div>
        <button class="btn btn-warning" data-toggle="modal" data-target="#resetPasswordModal">
            <i class="fas fa-key"></i> Reset Password
        </button>
    </div>
</div>

<!-- User Details Card -->
<div class="card shadow mb-4">
    <div class="card-body">
        <div class="row">
            <div class="col-md-6">
                <h5 class="mb-3">Basic Information</h5>
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Name</th>
                        <td>{{ $user->name }}</td>
                    </tr>
                    <tr>
                        <th>Email</th>
                        <td>{{ $user->email }}</td>
                    </tr>
                    <tr>
                        <th>Phone</th>
                        <td>{{ $user->phone }}</td>
                    </tr>
                    <tr>
                        <th>Role</th>
                        <td>{{ $user->role->role_name }}</td>
                    </tr>
                </table>
            </div>
            
            <div class="col-md-6">
                <h5 class="mb-3">Additional Information</h5>
                <table class="table table-bordered">
                    <tr>
                        <th width="30%">Address</th>
                        <td>{{ $user->address }}</td>
                    </tr>
                    <tr>
                        <th>Created At</th>
                        <td>{{ $user->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Last Updated</th>
                        <td>{{ $user->updated_at->format('d M Y H:i') }}</td>
                    </tr>
                    <tr>
                        <th>Password</th>
                        <td>
                            <span class="text-muted">••••••••</span>
                            <small class="text-info ml-2">(Hidden for security)</small>
                        </td>
                    </tr>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Reset Password Modal -->
<div class="modal fade" id="resetPasswordModal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reset Password</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="{{ route('admin.users.reset-password', $user->id) }}" method="POST">
    @csrf
    <div class="modal-body">
        <p>Are you sure you want to reset password for <strong>{{ $user->name }}</strong>?</p>
        
        <div class="form-group">
            <label for="new_password">New Password</label>
            <input type="password" class="form-control" id="new_password" name="new_password" required>
        </div>
        
        <div class="form-group">
            <label for="new_password_confirmation">Confirm New Password</label>
            <input type="password" class="form-control" id="new_password_confirmation" name="new_password_confirmation" required>
        </div>
    </div>
    <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancel</button>
        <button type="submit" class="btn btn-warning">Reset Password</button>
    </div>
</form>
        </div>
    </div>
</div>

<!-- New Password Modal (shown only after reset) -->
@if(session('new_password'))
<div class="modal fade show" id="newPasswordModal" tabindex="-1" role="dialog" style="display: block; padding-right: 17px;">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">Password Reset Successful</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" onclick="closeModal()">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p>The password has been reset successfully for <strong>{{ $user->name }}</strong>.</p>
                <div class="alert alert-info">
                    <h5 class="alert-heading">New Password</h5>
                    <div class="d-flex justify-content-between align-items-center">
                        <code id="newPassword">{{ session('new_password') }}</code>
                        <button class="btn btn-sm btn-outline-secondary" onclick="copyPassword()">
                            <i class="fas fa-copy"></i> Copy
                        </button>
                    </div>
                    <p class="mt-2 mb-0"><small class="text-danger">Please save this password securely as it won't be shown again.</small></p>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success" data-dismiss="modal" onclick="closeModal()">Close</button>
            </div>
        </div>
    </div>
</div>
<div class="modal-backdrop fade show"></div>

<script>
    function closeModal() {
        document.getElementById('newPasswordModal').style.display = 'none';
        document.querySelector('.modal-backdrop').style.display = 'none';
    }
    
    function copyPassword() {
        const password = document.getElementById('newPassword').textContent;
        navigator.clipboard.writeText(password).then(() => {
            const copyBtn = document.querySelector('#newPasswordModal button');
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied!';
            setTimeout(() => {
                copyBtn.innerHTML = '<i class="fas fa-copy"></i> Copy';
            }, 2000);
        });
    }
</script>
@endif
@endsection