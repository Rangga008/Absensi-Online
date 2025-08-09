@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.roles.index') }}" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to Roles
    </a>
    <h1 class="h3 mb-0 text-gray-800">Add New Role</h1>
</div>

<div class="card shadow">
    <div class="card-body">
        @if ($errors->any())
            <div class="alert alert-danger">
                <ul>
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('admin.roles.store') }}" method="POST">
            @csrf
            <div class="form-group">
                <label for="role_name">Role Name</label>
                <input type="text" class="form-control @error('role_name') is-invalid @enderror" 
                       id="role_name" name="role_name" 
                       placeholder="Enter role name" value="{{ old('role_name') }}" required>
                @error('role_name')
                    <div class="invalid-feedback">{{ $message }}</div>
                @enderror
            </div>
            
            <div class="form-group text-right">
                <button type="submit" class="btn btn-success">
                    <i class="fas fa-save"></i> Save Role
                </button>
            </div>
        </form>
    </div>
</div>
@endsection