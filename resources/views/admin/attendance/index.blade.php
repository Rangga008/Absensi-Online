@extends('layouts.admin')

@section('content')

<!-- Page Heading -->
@if(session('role_id') == 1)
<h1 class="h3 mb-2 text-gray-800">Management attendance</h1>
<p class="mb-4">Disini fitur untuk menyunting dan menghapus data absen pengguna.</p>
@else 
<h1 class="h3 mb-2 text-gray-800">List Attendance</h1>
@endif

<!-- DataTales Example -->
<div class="card shadow mb-4">
    <div class="card-body">
        @if(session()->has('message'))
        <div class="alert alert-success">
            {!! session('message') !!}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        
        @if(session()->has('success'))
        <div class="alert alert-success">
            {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        
        @if(session()->has('error'))
        <div class="alert alert-danger">
            {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
        @endif
        
        @if(session('role_id') == 1)
        <div class="text-right">
            <a href="{{ route('admin.attendances.create') }}" class="btn btn-success m-2">
                <i class="fas fa-plus mr-1"></i> New attendance
            </a>
        </div>
        @endif
        
        <div class="table-responsive">
            <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Name</th>
                        <th>Present at</th>
                        <th>Description</th>
                        @if(session('role_id') !== 3)
                        <th>Action</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attendance->user->name ?? 'N/A' }}</td>
                        <td>{{ date('D, d F Y H:i', strtotime($attendance->present_at)) }}</td>
                        <td>
                            <span class="badge 
                                @if($attendance->description == 'Hadir') badge-success
                                @elseif($attendance->description == 'Terlambat') badge-warning
                                @elseif($attendance->description == 'Sakit') badge-info
                                @elseif($attendance->description == 'Izin') badge-secondary
                                @elseif($attendance->description == 'Dinas Luar') badge-primary
                                @elseif($attendance->description == 'WFH') badge-dark
                                @else badge-light
                                @endif">
                                {{ $attendance->description }}  
                            </span>
                        </td>
                        @if(session('role_id') !== 3)
                        <td>
                            <a href="{{ route('admin.attendances.show', $attendance->id) }}" 
                               class="btn btn-info btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            @if(session('role_id') == 1)
                            <a href="{{ route('admin.attendances.edit', $attendance->id) }}" 
                               class="btn btn-primary btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.attendances.destroy', $attendance->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" 
                                        onclick="return confirm('Apakah anda yakin akan dihapus?')" 
                                        class="btn btn-danger btn-sm" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                            @endif
                        </td>
                        @endif
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ session('role_id') !== 3 ? '5' : '4' }}" class="text-center">
                            <em>No attendance records found</em>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $attendances->links() }}
            </div>
        </div>
    </div>
</div>

@endsection