@extends('layouts.admin')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <a href="{{ route('admin.attendances.index') }}" class="btn btn-primary">
        <i class="fas fa-arrow-left"></i> Back to Users
    </a>
    <h1 class="h3 text-gray-800">Attendance for {{ $user->name }}</h1>
    <a href="{{ route('admin.attendances.create') }}?user_id={{ $user->id }}" 
       class="btn btn-success">
        <i class="fas fa-plus"></i> Add Attendance
    </a>
</div>

<div class="card shadow mb-4">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-bordered">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Date & Time</th>
                        <th>Status</th>
                        <th>Location</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($attendances as $attendance)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $attendance->present_at->format('d M Y H:i') }}</td>
                        <td>
                            <span class="badge badge-{{ $attendance->status_badge }}">
                                {{ $attendance->description }}
                            </span>
                        </td>
                        <td>
                            @if($attendance->latitude)
                            <a href="#" data-toggle="modal" data-target="#mapModal{{ $attendance->id }}">
                                View Location
                            </a>
                            @else
                            N/A
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('admin.attendances.show', $attendance->id) }}" 
                               class="btn btn-info btn-sm">
                                <i class="fas fa-eye"></i>
                            </a>
                            <a href="{{ route('admin.attendances.edit', $attendance->id) }}" 
                               class="btn btn-primary btn-sm">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form action="{{ route('admin.attendances.destroy', $attendance->id) }}" 
                                  method="POST" class="d-inline">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm" 
                                        onclick="return confirm('Delete this attendance?')">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            {{ $attendances->links() }}
        </div>
    </div>
</div>

<!-- Modal for Map -->
@foreach($attendances as $attendance)
@if($attendance->latitude)
<div class="modal fade" id="mapModal{{ $attendance->id }}" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Location on {{ $attendance->present_at->format('d M Y') }}</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div id="map{{ $attendance->id }}" style="height: 400px;"></div>
            </div>
        </div>
    </div>
</div>
@endif
@endforeach

@endsection

@section('scripts')
@foreach($attendances as $attendance)
@if($attendance->latitude)
<script>
$(document).ready(function() {
    $('#mapModal{{ $attendance->id }}').on('shown.bs.modal', function () {
        var map = L.map('map{{ $attendance->id }}').setView([{{ $attendance->latitude }}, {{ $attendance->longitude }}], 15);
        
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png').addTo(map);
        
        L.marker([{{ $attendance->latitude }}, {{ $attendance->longitude }}])
            .addTo(map)
            .bindPopup('Attendance Location');
    });
});
</script>
@endif
@endforeach
@endsection