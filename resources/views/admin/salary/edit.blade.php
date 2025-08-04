@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
<a href="{{ url('salary') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>

<h1 class="h3 m-4 text-gray-800">Edit user salary</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url('salary') }}/{{ $salary->id }}" method="POST">
            @method('PUT')
            @csrf
            <div class="form-group row">
                <div class="col-md-6 mt-2">
                    <label for="">For user</label>
                    <select name="user_id" id="" class="form-control">
                        @foreach($users as $user)
                        <option value="{{ $user->id }}" {{ $user->id == $salary->user_id ? 'selected' : '' }}>{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Month</label>
                    <select name="month" class="form-control">
                        <option value="January" {{ $salary->month == 'January' ? 'selected' : '' }}>January</option>
                        <option value="February" {{ $salary->month == 'February' ? 'selected' : '' }}>February</option>
                        <option value="March" {{ $salary->month == 'March' ? 'selected' : '' }}>March</option>
                        <option value="April" {{ $salary->month == 'April' ? 'selected' : '' }}>April</option>
                        <option value="may" {{ $salary->month == 'May' ? 'selected' : '' }}>may</option>
                        <option value="June" {{ $salary->month == 'June' ? 'selected' : '' }}>June</option>
                        <option value="July" {{ $salary->month == 'July' ? 'selected' : '' }}>July</option>
                        <option value="August" {{ $salary->month == 'August' ? 'selected' : '' }}>August</option>
                        <option value="September" {{ $salary->month == 'September' ? 'selected' : '' }}>September</option>
                        <option value="October" {{ $salary->month == 'October' ? 'selected' : '' }}>October</option>
                        <option value="November" {{ $salary->month == 'November' ? 'selected' : '' }}>November</option>
                        <option value="December" {{ $salary->month == 'December' ? 'selected' : '' }}>December</option>
                    </select>
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Year</label>
                    <select class="form-control" name="year">
                        @for($year = (int)date('Y'); 1900 <= $year; $year--) <option value="<?= $year; ?>"><?= $year; ?></option>
                            @endfor
                    </select>
                </div>
                <div class="col-md-6 mt-2">
                    <label for="inlineFormInputGroupUsername">Amount</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <div class="input-group-text">Rp</div>
                        </div>
                        <input type="text" name="amount" class="form-control" value="{{ $salary->amount }}">
                    </div>
                    @error('amount')
                    <small class="text-danger">{{ $message }}</small>
                    @enderror
                </div>

                <div class="col-md-6 mt-3">
                    <button type="submit" class="btn btn-success">Submit</button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection