<table class="table table-bordered" width="100%" cellspacing="0">
    <thead>
        <tr>
            <th>Name</th>
            <th>Email</th>
            <th>Phone</th>
            <th>Position</th>
            @if(isset($show_deleted) && $show_deleted)
                <th>Deleted At</th>
            @endif
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
        @forelse($users as $user)
        <tr class="{{ isset($show_deleted) && $show_deleted ? 'table-warning' : '' }}">
            <td>
                {{ $user->name }}
                @if(isset($show_deleted) && $show_deleted)
                    <span class="badge badge-warning ml-2">Deleted</span>
                @endif
            </td>
            <td>{{ $user->email }}</td>
            <td>{{ $user->phone }}</td>
            <td>{{ $user->role->role_name }}</td>
            
            @if(isset($show_deleted) && $show_deleted)
                <td>{{ $user->deleted_at->format('M d, Y H:i') }}</td>
            @endif
            
            <td>
                @if(isset($show_deleted) && $show_deleted)
                    <!-- Actions for deleted users -->
                    <form action="{{ route('admin.users.restore', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PATCH')
                        <button type="submit" onclick="return confirm('Are you sure you want to restore this user?')" 
                                class="btn btn-success btn-sm" title="Restore">
                            <i class="fas fa-undo"></i> Restore
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.users.force-delete', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" 
                                onclick="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone!')" 
                                class="btn btn-danger btn-sm" title="Permanently Delete">
                            <i class="fas fa-trash-alt"></i> Delete Permanently
                        </button>
                    </form>
                @else
                    <!-- Actions for active users -->
                    <a href="{{ route('admin.users.show', $user->id) }}" class="btn btn-info btn-sm" title="View">
                        <i class="fas fa-eye"></i>
                    </a>
                    <a href="{{ route('admin.users.edit', $user->id) }}" class="btn btn-primary btn-sm" title="Edit">
                        <i class="fas fa-edit"></i>
                    </a>
                    <form action="{{ route('admin.users.destroy', $user->id) }}" method="POST" class="d-inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" onclick="return confirm('Are you sure? This will move the user to trash.')" 
                                class="btn btn-warning btn-sm" title="Move to Trash">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                @endif
            </td>
        </tr>
        @empty
        <tr>
            <td colspan="{{ isset($show_deleted) && $show_deleted ? '6' : '5' }}" class="text-center">
                @if(request('search'))
                <div class="py-4">
                    <i class="fas fa-search fa-3x text-muted mb-3"></i>
                    <h5>No users found matching "{{ request('search') }}"</h5>
                    <p class="text-muted">Try adjusting your search terms</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">Clear Search</a>
                </div>
                @elseif(isset($show_deleted) && $show_deleted)
                <div class="py-4">
                    <i class="fas fa-trash fa-3x text-muted mb-3"></i>
                    <h5>No deleted users found</h5>
                    <p class="text-muted">All users are currently active</p>
                    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-primary">View Active Users</a>
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

<!-- Filter buttons -->
<div class="d-flex justify-content-between align-items-center mt-3">
    <div class="btn-group" role="group" aria-label="User filters">
        <a href="{{ route('admin.users.index') }}" 
           class="btn btn-outline-primary {{ !request('show_deleted') ? 'active' : '' }}">
            <i class="fas fa-users"></i> Active Users
        </a>
        <a href="{{ route('admin.users.index', ['show_deleted' => 1]) }}" 
           class="btn btn-outline-warning {{ request('show_deleted') ? 'active' : '' }}">
            <i class="fas fa-trash"></i> Deleted Users
        </a>
    </div>
    
    @if(isset($show_deleted) && $show_deleted && $users->total() > 0)
    <div>
        <form action="{{ route('admin.users.restore-all') }}" method="POST" class="d-inline">
            @csrf
            @method('PATCH')
            <button type="submit" 
                    onclick="return confirm('Are you sure you want to restore all deleted users?')" 
                    class="btn btn-success">
                <i class="fas fa-undo"></i> Restore All
            </button>
        </form>
    </div>
    @endif
</div>