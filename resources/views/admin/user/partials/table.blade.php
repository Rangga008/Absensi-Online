<table class="table table-bordered" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Position</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
        <tr>
            <td>{{ $user->name }}</td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone }}</td>
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
                    <button type="submit" onclick="return confirm('Are you sure?')" 
                            class="btn btn-danger btn-sm" title="Delete">
                        <i class="fas fa-trash"></i>
                    </button>
                </form>
            </td>
        </tr>
        @empty
<tr>
    <td colspan="5" class="text-center">
        @if(request('search'))
        <div class="py-4">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h5>No users found matching "{{ request('search') }}"</h5>
            <p class="text-muted">Try adjusting your search terms</p>
            <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">Clear Search</a>
        </div>
        @else
        <div class="py-4">
            <i class="fas fa-users fa-3x text-muted mb-3"></i>
            <h5>No users found</h5>
            <p class="text-muted">Get started by adding a new user</p>
            <a href="{{ route('admin.users.create') }}" class="btn btn-success">
                <i class="fas fa-plus mr-1"></i> Add User
            </a>
        </div>
        @endif
    </td>
</tr>
@endforelse
    </tbody>
</table>