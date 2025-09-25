@extends('layouts.admin')

@section('title', 'Edit Work Time')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title mb-0">Edit Work Time</h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('admin.settings.work-times.update', $workTime->id) }}">
                        @csrf
                        @method('PUT')

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Work Time Name <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control @error('name') is-invalid @enderror"
                                           id="name" name="name" value="{{ old('name', $workTime->name) }}" required>
                                    @error('name')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">e.g., "Morning Shift", "Afternoon Shift", "Night Shift"</div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="late_threshold" class="form-label">Late Threshold <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('late_threshold') is-invalid @enderror"
                                           id="late_threshold" name="late_threshold"
                                           value="{{ old('late_threshold', $workTime->late_threshold) }}" required>
                                    @error('late_threshold')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                    <div class="form-text">Time after which attendance is marked as late</div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="start_time" class="form-label">Start Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('start_time') is-invalid @enderror"
                                           id="start_time" name="start_time"
                                           value="{{ old('start_time', $workTime->start_time) }}" required>
                                    @error('start_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="mb-3">
                                    <label for="end_time" class="form-label">End Time <span class="text-danger">*</span></label>
                                    <input type="time" class="form-control @error('end_time') is-invalid @enderror"
                                           id="end_time" name="end_time"
                                           value="{{ old('end_time', $workTime->end_time) }}" required>
                                    @error('end_time')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="description" class="form-label">Description</label>
                            <textarea class="form-control @error('description') is-invalid @enderror"
                                      id="description" name="description" rows="3">{{ old('description', $workTime->description) }}</textarea>
                            @error('description')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                            <div class="form-text">Optional description for this work time</div>
                        </div>

                        <div class="mb-3">
                            <div class="form-check">
                                <input type="checkbox" class="form-check-input" id="is_active" name="is_active"
                                       value="1" {{ old('is_active', $workTime->is_active) ? 'checked' : '' }}>
                                <label class="form-check-label" for="is_active">
                                    Active
                                </label>
                            </div>
                            <div class="form-text">Check this box to make this work time available for assignment</div>
                        </div>

                        @if($workTime->users()->count() > 0)
                            <div class="alert alert-warning">
                                <i class="fas fa-exclamation-triangle"></i>
                                <strong>Warning:</strong> This work time has {{ $workTime->users()->count() }} users assigned to it.
                                Changes to the schedule may affect their attendance calculations.
                            </div>
                        @endif

                        <div class="d-flex justify-content-between">
                            <a href="{{ route('admin.settings.work-times.index') }}" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Back to List
                            </a>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Update Work Time
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Auto-adjust end time when start time changes
document.getElementById('start_time').addEventListener('change', function() {
    const startTime = this.value;
    if (startTime) {
        // Set end time to 8 hours after start time
        const [hours, minutes] = startTime.split(':');
        const endHours = (parseInt(hours) + 8) % 24;
        const endTime = `${endHours.toString().padStart(2, '0')}:${minutes}`;
        document.getElementById('end_time').value = endTime;
    }
});

// Validate that end time is after start time
document.getElementById('end_time').addEventListener('change', function() {
    const startTime = document.getElementById('start_time').value;
    const endTime = this.value;

    if (startTime && endTime && endTime <= startTime) {
        alert('End time must be after start time');
        this.value = '';
    }
});
</script>
@endsection
