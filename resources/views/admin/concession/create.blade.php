@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <a href="{{ route('admin.concessions.index') }}" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back
    </a>
    <h1 class="h3 mb-0 text-gray-800">Add New Concession</h1>
</div>

<div class="card shadow">
    <div class="card-body">
        <form action="{{ route('admin.concessions.store') }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="user_id">Employee</label>
                        <select name="user_id" id="user_id" class="form-control" required>
                            <option value="">Select Employee</option>
                            @foreach($users as $user)
                                <option value="{{ $user->id }}" {{ old('user_id') == $user->id ? 'selected' : '' }}>
                                    {{ $user->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="reason">Reason</label>
                        <select name="reason" id="reason" class="form-control" required>
                            <option value="">Select Reason</option>
                            <option value="sakit" {{ old('reason') == 'sakit' ? 'selected' : '' }}>Sakit</option>
                            <option value="izin" {{ old('reason') == 'izin' ? 'selected' : '' }}>Izin</option>
                            <option value="cuti" {{ old('reason') == 'cuti' ? 'selected' : '' }}>Cuti</option>
                        </select>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="start_date">Start Date</label>
                        <input type="date" name="start_date" id="start_date" 
                               class="form-control" value="{{ old('start_date') }}" required>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="end_date">End Date</label>
                        <input type="date" name="end_date" id="end_date" 
                               class="form-control" value="{{ old('end_date') }}" required>
                    </div>
                </div>
                
                <div class="col-md-12">
                    <div class="form-group">
                        <label for="description">Description</label>
                        <textarea name="description" id="description" 
                                  class="form-control" rows="4" required>{{ old('description') }}</textarea>
                    </div>
                </div>
                
                <div class="col-md-12 mt-3">
                    <button type="submit" class="btn btn-success">
                        <i class="fas fa-save"></i> Submit
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection