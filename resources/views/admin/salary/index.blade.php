@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
<h1 class="h3 mb-2 text-gray-800">Management salary</h1>
<p class="mb-4">Disini fitur untuk menambahkan, menyunting, dan menghapus data gaji pengguna.</p>

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        @if(session()->has('message'))
        <div class="alert alert-success">
            {{ session('message') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        <div class="text-right">
            <a href="{{ url('salary/create') }}" class="btn btn-success m-2"><i class="fas fa-plus mr-1"></i> Add user salary</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Month</th>
                        <th>Year</th>
                        <th>Amount</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($salaries as $salary)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $salary->user->name }}</td>
                        <td>{{ $salary->month }}</td>
                        <td>{{ $salary->year }}</td>
                        <td>Rp {{ number_format($salary->amount) }}</td>
                        <td>
                            <a href="{{ url('salary/' . $salary->id . '/edit') }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ url('salary/' . $salary->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Apakah anda yakin akan dihapus?')" class="bg-danger text-white" style="border: 0px"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection