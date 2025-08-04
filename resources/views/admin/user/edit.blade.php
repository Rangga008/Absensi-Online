@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
<a href="{{ url('user') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>

<h1 class="h3 m-4 text-gray-800">Edit users</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url('user') }}/{{ $user->id }}" method="POST">
            @method('PUT')
            @csrf
            <div class="form-group row">
                <div class="col-md-6">
                    <label for="">Name</label>
                    <input type="text" name="name" class="form-control" value="{{ $user->name }}">
                    @error('name')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6">
                    <label for="">Phone</label>
                    <input type="text" name="phone" class="form-control" value="{{ $user->phone }}">
                    @error('phone')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Email</label>
                    <input type="text" name="email" class="form-control" value="{{ $user->email }}">
                    @error('email')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Password</label>
                    <input type="password" class="form-control" value="asdasdasdas" readonly>
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Role</label>
                    <select name="role_id" id="" class="form-control">
                        @foreach($roles as $role)
                        <option value="{{ $role->id }}" {{ $user->role_id == $role->id ? 'selected' : '' }}>{{ $role->role_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Address</label>
                    <textarea name="address" class="form-control">{{ $user->address }}</textarea>
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