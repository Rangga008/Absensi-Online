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
        
        <div class="text-right mb-3">
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add user
            </a>
        </div>
        
        <div class="table-responsive">
            <table class="table table-bordered" id="usersTable" width="100%" cellspacing="0">
                <thead class="bg-light">
                    <tr>
                        <th class="sortable" data-column="name">Name 
                            <i class="fas fa-sort float-right"></i>
                        </th>
                        <th class="sortable" data-column="email">Email
                            <i class="fas fa-sort float-right"></i>
                        </th>
                        <th class="sortable" data-column="phone">Phone
                            <i class="fas fa-sort float-right"></i>
                        </th>
                        <th>Address</th>
                        <th class="sortable" data-column="role_name">Position
                            <i class="fas fa-sort float-right"></i>
                        </th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody id="usersTableBody">
                    @foreach($users as $user)
                    <tr data-id="{{ $user->id }}">
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->address }}</td>
                        <td>{{ $user->role->role_name }}</td>
                        <td>
                            <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm" title="Edit">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" onclick="return confirm('Apakah anda yakin akan dihapus?')" 
                                        class="btn btn-danger btn-sm" title="Delete">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            <div class="d-flex justify-content-center" id="paginationLinks">
                {{ $users->links() }}
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Variabel untuk state sorting
    let currentSort = {
        column: 'name',
        direction: 'asc'
    };
    
    // Fungsi untuk melakukan sorting
    async function sortUsers(column) {
        // Toggle direction jika kolom sama
        if (currentSort.column === column) {
            currentSort.direction = currentSort.direction === 'asc' ? 'desc' : 'asc';
        } else {
            currentSort.column = column;
            currentSort.direction = 'asc';
        }
        
        try {
            // Tampilkan loading indicator
            const tableBody = document.getElementById('usersTableBody');
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center">Loading...</td></tr>';
            
            // Kirim request ke server
            const response = await fetch(`{{ route("admin.users.index") }}?sort=${currentSort.column}&direction=${currentSort.direction}`);
            const data = await response.json();
            
            // Update tabel dengan data baru
            renderUsers(data.users);
            updateSortIcons();
        } catch (error) {
            console.error('Error:', error);
            tableBody.innerHTML = '<tr><td colspan="6" class="text-center text-danger">Error loading data</td></tr>';
        }
    }
    
    // Fungsi untuk render users ke tabel
    function renderUsers(users) {
        const tableBody = document.getElementById('usersTableBody');
        tableBody.innerHTML = '';
        
        users.forEach(user => {
            const row = document.createElement('tr');
            row.setAttribute('data-id', user.id);
            
            row.innerHTML = `
                <td>${user.name}</td>
                <td>${user.email}</td>
                <td>${user.phone}</td>
                <td>${user.address}</td>
                <td>${user.role.role_name}</td>
                <td>
                    <a href="/admin/users/${user.id}" class="btn btn-info btn-sm" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="/admin/users/${user.id}/edit" class="btn btn-primary btn-sm" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="/admin/users/${user.id}" method="POST" class="d-inline">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="_method" value="DELETE">
                        <button type="submit" onclick="return confirm('Apakah anda yakin akan dihapus?')" 
                                class="btn btn-danger btn-sm" title="Delete">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </td>
            `;
            
            tableBody.appendChild(row);
        });
    }
    
    // Fungsi untuk update icon sort
    function updateSortIcons() {
        document.querySelectorAll('.sortable i').forEach(icon => {
            icon.className = 'fas fa-sort float-right';
        });
        
        const currentIcon = document.querySelector(`th[data-column="${currentSort.column}"] i`);
        if (currentIcon) {
            currentIcon.className = currentSort.direction === 'asc' 
                ? 'fas fa-sort-up float-right' 
                : 'fas fa-sort-down float-right';
        }
    }
    
    // Tambahkan event listener untuk header kolom
    document.querySelectorAll('.sortable').forEach(header => {
        header.addEventListener('click', () => {
            const column = header.getAttribute('data-column');
            sortUsers(column);
        });
    });
});
</script>
@endsection