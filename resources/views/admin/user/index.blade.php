@extends('layouts.admin')

@section('title', 'User Management')

@section('content')
<!-- Page Heading -->
<h1 class="h3 mb-2 text-gray-800">User Management</h1>

<div class="card shadow mb-4">
    <div class="card-body">
        @include('admin.user.partials.alerts')

        <div class="d-flex justify-content-between mb-3">
            <div></div> <!-- Empty div to maintain flex spacing -->

            <div>
                <a href="{{ route('admin.users.import.form') }}" class="btn btn-primary mr-2">
                    <i class="fas fa-file-import mr-1"></i> Import Users
                </a>
                <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                    <i class="fas fa-plus mr-1"></i> Add User
                </a>
            </div>
        </div>
        
        <div id="usersTableContainer">
            @include('admin.user.partials.table', ['users' => $users])
        </div>

        <div id="paginationContainer" class="d-flex justify-content-center mt-3">
            {{ $users->appends(request()->query())->links() }}
        </div>
</div>
@endsection

@section('scripts')
<!-- Search functionality removed - using global search from admin layout -->
@endsection
