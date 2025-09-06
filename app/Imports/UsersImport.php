<?php

namespace App\Imports;

use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UsersImport implements ToCollection, WithHeadingRow, WithValidation
{
    private $importedCount = 0;
    private $skippedCount = 0;
    private $errorCount = 0;
    private $errors = [];
    private $generatePassword;

    public function __construct($generatePassword = true)
    {
        $this->generatePassword = $generatePassword;
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

                // Validate required fields
                if (empty($rowArray['name']) || empty($rowArray['email']) || empty($rowArray['phone']) || empty($rowArray['address'])) {
                    $this->errorCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": Missing required fields (name, email, phone, address)";
                    continue;
                }

                // Check if email already exists
                if (User::where('email', $rowArray['email'])->exists()) {
                    $this->errorCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": Email {$rowArray['email']} already exists";
                    continue;
                }

                // Validate role_id if provided
                $roleId = $rowArray['role_id'] ?? null;
                if ($roleId && !Role::find($roleId)) {
                    $this->errorCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": Role ID {$roleId} not found";
                    continue;
                }

                // If no role_id provided, use default role (assuming role 2 is user)
                if (!$roleId) {
                    $roleId = 2; // Default to user role
                }

                // Generate password if not provided
                $password = $rowArray['password'] ?? null;
                if (!$password && $this->generatePassword) {
                    $password = Str::random(8);
                } elseif (!$password) {
                    $this->errorCount++;
                    $this->errors[] = "Row " . ($index + 2) . ": Password is required";
                    continue;
                }

                // Create user
                User::create([
                    'name' => $rowArray['name'],
                    'email' => $rowArray['email'],
                    'phone' => $rowArray['phone'],
                    'address' => $rowArray['address'],
                    'role_id' => $roleId,
                    'password' => Hash::make($password),
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
            'name' => 'required|string|min:3|max:255',
            'email' => 'required|email|unique:users,email',
            'phone' => 'required|string|min:10|max:15',
            'address' => 'required|string|max:500',
            'role_id' => 'nullable|exists:roles,id',
            'password' => 'nullable|string|min:6'
        ];
    }

    public function customValidationMessages()
    {
        return [
            'name.required' => 'Name is required',
            'name.min' => 'Name must be at least 3 characters',
            'email.required' => 'Email is required',
            'email.email' => 'Email must be a valid email address',
            'email.unique' => 'Email already exists',
            'phone.required' => 'Phone is required',
            'phone.min' => 'Phone must be at least 10 characters',
            'phone.max' => 'Phone must not exceed 15 characters',
            'address.required' => 'Address is required',
            'role_id.exists' => 'Role ID does not exist',
            'password.min' => 'Password must be at least 6 characters'
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
