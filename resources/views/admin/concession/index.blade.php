@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h1 class="h3 mb-0 text-gray-800">
        @if(session('role_id') == 3)
            My Concessions
        @else
            Concession Management
        @endif
    </h1>
    
    @if(session('role_id') !== 3)
    <a href="{{ route('admin.concessions.create') }}" class="btn btn-success">
        <i class="fas fa-plus"></i> Add Concession
    </a>
    @endif
</div>

<div class="card shadow">
    <div class="card-body">
        @if(session('success'))
            <div class="alert alert-success">
                {{ session('success') }}
            </div>
        @endif

        <div class="table-responsive">
            <table class="table table-bordered">
                <thead class="bg-light">
                    <tr>
                        <th>No</th>
                        <th>Employee</th>
                        <th>Reason</th>
                        <th>Start Date</th>
                        <th>End Date</th>
                        <th>Status</th>
                        @if(session('role_id') !== 3)
                        <th>Actions</th>
                        @endif
                    </tr>
                </thead>
                <tbody>
                    @foreach($concessions as $concession)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $concession->user->name }}</td>
                        <td>{{ ucfirst($concession->reason) }}</td>
                        <td>{{ $concession->start_date ? $concession->start_date->format('d M Y') : 'N/A' }}</td>
                        <td>{{ $concession->end_date ? $concession->end_date->format('d M Y') : 'N/A' }}</td>
                        <td>
                            <span class="badge badge-{{ 
                                $concession->status == 'approved' ? 'success' : 
                                ($concession->status == 'rejected' ? 'danger' : 'warning') 
                            }}">
                                {{ ucfirst($concession->status) }}
                            </span>
                        </td>
                        @if(session('role_id') !== 3)
                        <td>
                            <a href="{{ route('admin.concessions.edit', $concession->id) }}" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.concessions.destroy', $concession->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Are you sure?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                        @endif
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center">
                {{ $concessions->links() }}
            </div>
        </div>
    </div>
</div>
@endsection