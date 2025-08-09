@foreach($users as $user)
<tr>
    <td>{{ $loop->iteration }}</td>
    <td>{{ $user->name }}</td>
    <td>{{ $user->attendances_count }}</td>
    <td>
        @if($user->last_attendance)
            {{ $user->last_attendance->present_at->format('d M Y H:i') }}
            <span class="badge badge-{{ $user->last_attendance->status_badge }}">
                {{ $user->last_attendance->description }}
            </span>
        @else
            No attendance
        @endif
    </td>
    <td>
        <a href="{{ route('admin.users.attendances', $user->id) }}" 
           class="btn btn-primary btn-sm">
            View Attendances
        </a>
    </td>
</tr>
@endforeach