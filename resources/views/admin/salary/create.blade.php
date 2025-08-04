@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
<a href="{{ url('salary') }}" class="btn btn-primary"><i class="fas fa-arrow-left"></i> Back</a>
<h1 class="h3 my-4 text-gray-800">Add user salary</h1>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        <form action="{{ url('salary') }}" method="POST">
            @csrf
            <div class="form-group row">
                <div class="col-md-6 mt-2">
                    <label for="">For user</label>
                    <select name="user_id" id="" class="form-control">
                        @foreach($users as $user)
                        <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6 mt-2">
                    <label for="">Month</label>
                    <select name="month" class="form-control">
                        <option value="January">January</option>
                        <option value="February">February</option>
                        <option value="March">March</option>
                        <option value="April">April</option>
                        <option value="may">may</option>
                        <option value="June">June</option>
                        <option value="July">July</option>
                        <option value="August">August</option>
                        <option value="September">September</option>
                        <option value="October">October</option>
                        <option value="November">November</option>
                        <option value="December">December</option>
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
                        <input type="text" name="amount" class="form-control">
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