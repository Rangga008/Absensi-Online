@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
<a href="{{ url('concession') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
<h1 class="h3 my-4 text-gray-800">Add concession</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url('concession') }}" method="POST">
            @csrf
            <div class="form-group row">
                <div class="col-md-6 mt-2">
                    <label for="">Name</label>
                    <select name="user_id" id="" class="form-control">
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label for="">Reason</label>
                    <select name="reason" class="form-control">
                        <option value="sakit">Sakit</option>
                        <option value="izin">Izin</option>
                        <option value="cuti">Cuti</option>
                    </select>
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Description</label>
                    <textarea name="description" class="form-control">{{ old('description') }}</textarea>
                    @error('description')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>
            </div>
            <div class="row">
                <div class="col-md-6">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection