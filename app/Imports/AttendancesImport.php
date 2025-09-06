<?php

namespace App\Imports;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;

class AttendancesImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $importedCount = 0;
    private $skippedCount = 0;
    private $errorCount = 0;
    private $errors = [];
    private $roleId;
    private $startDate;
    private $endDate;
    private $updateExisting;

    public function __construct($roleId = null, $startDate = null, $endDate = null, $updateExisting = false)
    {
        $this->roleId = $roleId;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->updateExisting = $updateExisting;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $rowArray = $row->toArray();
                
                // Skip empty rows
                if (empty(array_filter($rowArray))) {
                    $this->skippedCount++;
                    continue;
                }

                // Validate user exists
                $user = User::find($rowArray['user_id']);
                if (!$user) {
                    $this->errorCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": User ID {$rowArray['user_id']} not found";
                    continue;
                }

                // Filter by role if specified
                if ($this->roleId && $user->role_id != $this->roleId) {
                    $this->skippedCount++;
                    continue;
                }

                // Parse date and time
                $presentDate = Carbon::parse($rowArray['present_date']);
                $presentTime = Carbon::parse($rowArray['present_time']);
                $presentAt = $presentDate->copy()->setTime(
                    $presentTime->hour,
                    $presentTime->minute,
                    $presentTime->second
                );

                // Filter by date range if specified
                if ($this->startDate && $presentDate->lt(Carbon::parse($this->startDate))) {
                    $this->skippedCount++;
                    continue;
                }

                if ($this->endDate && $presentDate->gt(Carbon::parse($this->endDate))) {
                    $this->skippedCount++;
                    continue;
                }

                // Check for existing attendance
                $existingAttendance = Attendance::where('user_id', $user->id)
                    ->whereDate('present_date', $presentDate->format('Y-m-d'))
                    ->first();

                if ($existingAttendance) {
                    if ($this->updateExisting) {
                        // Update existing record
                        $existingAttendance->update([
                            'present_at' => $presentAt,
                            'description' => $rowArray['description'],
                            'latitude' => $rowArray['latitude'] ?? null,
                            'longitude' => $rowArray['longitude'] ?? null,
                        ]);
                        $this->importedCount++;
                    } else {
                        $this->skippedCount++;
                        $this->errors[] = "Row " . ($index + 2) . ": Duplicate attendance for user {$user->name} on {$presentDate->format('Y-m-d')}";
                    }
                    continue;
                }

                // Create new attendance
                Attendance::create([
                    'user_id' => $user->id,
                    'present_at' => $presentAt,
                    'present_date' => $presentDate->format('Y-m-d'),
                    'description' => $rowArray['description'],
                    'latitude' => $rowArray['latitude'] ?? null,
                    'longitude' => $rowArray['longitude'] ?? null,
                    'ip_address' => request()->ip(),
                    'user_agent' => 'Imported from CSV/Excel'
                ]);

                $this->importedCount++;

            } catch (\Exception $e) {
                $this->errorCount++;
                $this->errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
                continue;
            }
        }
    }

    public function rules(): array
    {
        return [
            'user_id' => 'required|integer',
            'present_date' => 'required|date',
            'present_time' => 'required|date_format:H:i',
            'description' => 'required|in:Hadir,Terlambat,Sakit,Izin,Dinas Luar,WFH',
            'latitude' => 'nullable|numeric',
            'longitude' => 'nullable|numeric'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'user_id.required' => 'User ID is required',
            'user_id.integer' => 'User ID must be an integer',
            'present_date.required' => 'Present date is required',
            'present_date.date' => 'Present date must be a valid date',
            'present_time.required' => 'Present time is required',
            'present_time.date_format' => 'Present time must be in HH:MM format',
            'description.required' => 'Description is required',
            'description.in' => 'Description must be one of: Hadir, Terlambat, Sakit, Izin, Dinas Luar, WFH'
        ];
    }

    public function getImportedCount()
    {
        return $this->importedCount;
    }

    public function getSkippedCount()
    {
        return $this->skippedCount;
    }

    public function getErrorCount()
    {
        return $this->errorCount;
    }

    public function getErrors()
    {
        return $this->errors;
    }
}