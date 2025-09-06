<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\AttendancesImport;

class AttendanceImportController extends Controller
{
    public function showImportForm()
    {
        $roles = Role::whereNull('deleted_at')->get();
        return view('admin.attendance.import', compact('roles'));
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
            'role_id' => 'nullable|exists:roles,id',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'update_existing' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $roleId = $request->input('role_id');
            $startDate = $request->input('start_date');
            $endDate = $request->input('end_date');
            $updateExisting = $request->boolean('update_existing');

            $import = new AttendancesImport($roleId, $startDate, $endDate, $updateExisting);
            Excel::import($import, $file);

            $importedCount = $import->getImportedCount();
            $skippedCount = $import->getSkippedCount();
            $errorCount = $import->getErrorCount();
            $errors = $import->getErrors();

            $message = "Import completed: {$importedCount} records imported, {$skippedCount} skipped, {$errorCount} errors.";

            if ($errorCount > 0) {
                return redirect()->back()
                    ->with('warning', $message)
                    ->with('import_errors', $errors);
            }

            return redirect()->route('admin.attendances.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('Attendance import error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="attendance_import_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() {
            $handle = fopen('php://output', 'w');
            
            // Add CSV headers
            fputcsv($handle, [
                'user_id',
                'present_date',
                'present_time',
                'description',
                'latitude',
                'longitude'
            ]);

            // Add example data
            fputcsv($handle, [
                '1',
                '2024-01-15',
                '08:00',
                'Hadir',
                '-6.906000',
                '107.623400'
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}