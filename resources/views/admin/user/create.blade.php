@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
<a href="{{ url('user') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
<h1 class="h3 my-4 text-gray-800">Add users</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url('user') }}" method="POST">
            @csrf
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="">Name</label>
                    <input type="text" class="form-control" name="name" value="{{ old('name') }}">
                    @error('name')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="">Phone</label>
                    <input type="text" class="form-control" name="phone" value="{{ old('phone') }}">
                    @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Email</label>
                    <input type="text" class="form-control" name="email" value="{{ old('email') }}">
                    @error('email')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Password</label>
                    <input type="text" class="form-control" name="password">
                    @error('password')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Role</label>
                    <select name="role_id" class="form-control">
                        <option value="1">Super admin</option>
                        <option value="2">Staff HRD</option>
                        <option value="3">Pimpinan</option>
                        <option value="4">Karyawan</option>
                    </select>
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Address</label>
                    <textarea name="address" class="form-control">{{ old('address') }}</textarea>
                    @error('address')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection