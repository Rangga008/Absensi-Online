@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
<a href="{{ url('role') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
<h1 class="h3 m-3 text-gray-800">Edit role</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url('role') }}/{{ $role->id }}" method="POST">
            @method('PUT')
            @csrf
            <div class="form-group row justify-content-center">
                <div class="col-md-5 text-center">
                    <label for="">Name</label>
                    <input type="text" placeholder="New role .." value="{{ $role->role_name }}" class="form-control" name="role_name" value="{{ old('role_name') }}">
                    @error('role_name')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
                <div class="col-md-12 text-center">
                    <button type="submit" class="btn btn-success mt-2">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection