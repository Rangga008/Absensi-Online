@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
<h1 class="h3 mb-2 text-gray-800">Management users</h1>
<p class="mb-4">Disini fitur untuk menambahkan, menyunting, dan menghapus data pengguna.</p>

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
            <a href="{{ url('user/create') }}" class="btn btn-success m-2"><i class="fas fa-plus mr-1"></i> Add user</a>
        </div>
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>address</th>
                        <th>Position</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                    <tr>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->address }}</td>
                        <td>{{ $user->role->role_name }}</td>
                        <td>
                            <a href="{{ url('user/' . $user->id . '/edit') }}" class="btn btn-primary btn-sm"><i class="fas fa-edit"></i></a>
                            <form action="{{ url('user/' . $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Apakah anda yakin akan dihapus?')" class="bg-danger text-white" style="border: 0px"><i class="fas fa-trash"></i></button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>

@endsection