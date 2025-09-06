@extends('layouts.admin')

@section('content')
<div class="container">
    <h4 class="mb-4">Export Attendance</h4>

    <form id="exportForm" method="POST" action="{{ route('attendance.export') }}">
        @csrf
        <div class="form-group">
            <label for="export_type">Export Type</label>
            <select id="export_type" name="export_type" class="form-control" required>
                <option value="">-- Select Export Type --</option>
                <option value="by_date">By Specific Date</option>
                <option value="by_user">By User</option>
                <option value="by_role">By Role</option>
                <option value="by_date_range">By Date Range</option>
            </select>
        </div>

        <div class="form-group mt-3">
            <label for="format">Export Format</label>
            <select id="format" name="format" class="form-control" required>
                <option value="excel">Excel</option>
                <option value="pdf">PDF</option>
            </select>
        </div>

        {{-- Hidden inputs for export parameters --}}
        <input type="hidden" name="specific_date" id="hidden_specific_date">
        <input type="hidden" name="user_id" id="hidden_user_id">
        <input type="hidden" name="role_id" id="hidden_role_id">
        <input type="hidden" name="start_date" id="hidden_start_date">
        <input type="hidden" name="end_date" id="hidden_end_date">

        <button type="submit" class="btn btn-primary mt-4">Export</button>
    </form>
</div>

{{-- Modal untuk masing-masing opsi --}}
@include('admin.attendance.partials.modal-by-date')
@include('admin.attendance.partials.modal-by-user')
@include('admin.attendance.partials.modal-by-role')
@include('admin.attendance.partials.modal-by-date-range')

@endsection

@push('scripts')
<script>
    document.getElementById('export_type').addEventListener('change', function (e) {
        e.preventDefault();
        switch(this.value) {
            case 'by_date':
                $('#modalByDate').modal('show');
                break;
            case 'by_user':
                $('#modalByUser').modal('show');
                break;
            case 'by_role':
                $('#modalByRole').modal('show');
                break;
            case 'by_date_range':
                $('#modalByDateRange').modal('show');
                break;
        }
    });

    // Handle save button clicks and transfer values to hidden inputs
    document.querySelectorAll('.modal .btn-save').forEach(btn => {
        btn.addEventListener('click', function() {
            let modal = this.closest('.modal');

            // Transfer values based on modal type
            if (modal.id === 'modalByDate') {
                const specificDate = document.getElementById('specific_date').value;
                document.getElementById('hidden_specific_date').value = specificDate;
            } else if (modal.id === 'modalByUser') {
                const userId = document.getElementById('user_id').value;
                document.getElementById('hidden_user_id').value = userId;
            } else if (modal.id === 'modalByRole') {
                const roleId = document.getElementById('role_id').value;
                document.getElementById('hidden_role_id').value = roleId;
            } else if (modal.id === 'modalByDateRange') {
                const startDate = document.getElementById('start_date').value;
                const endDate = document.getElementById('end_date').value;
                document.getElementById('hidden_start_date').value = startDate;
                document.getElementById('hidden_end_date').value = endDate;
            }

            $(modal).modal('hide');
        });
    });

    // User search functionality
    document.getElementById('user_search').addEventListener('input', function() {
        const searchTerm = this.value.toLowerCase();
        const selectElement = document.getElementById('user_id');
        const options = selectElement.options;

        for (let i = 0; i < options.length; i++) {
            const option = options[i];
            const text = option.text.toLowerCase();
            if (text.includes(searchTerm)) {
                option.style.display = '';
            } else {
                option.style.display = 'none';
            }
        }
    });

    // Clear search when modal is closed
    $('#modalByUser').on('hidden.bs.modal', function() {
        document.getElementById('user_search').value = '';
        const selectElement = document.getElementById('user_id');
        const options = selectElement.options;
        for (let i = 0; i < options.length; i++) {
            options[i].style.display = '';
        }
    });
</script>
@endpush
