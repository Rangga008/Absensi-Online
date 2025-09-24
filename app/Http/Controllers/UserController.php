<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use App\Models\WorkTime;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Validator;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\UsersImport;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $search = $request->get('search');
        $sort = $request->get('sort', 'name');
        $direction = $request->get('direction', 'asc');
        $show_deleted = $request->get('show_deleted', false);

        $usersQuery = User::with('role');
        
        // Show deleted users if requested
        if ($show_deleted) {
            $usersQuery = $usersQuery->onlyTrashed();
        }
        
        $users = $usersQuery->when($search, function($query) use ($search) {
                $query->where(function($q) use ($search) {
                    $q->where('name', 'like', "%$search%")
                      ->orWhere('email', 'like', "%$search%")
                      ->orWhere('phone', 'like', "%$search%")
                      ->orWhere('address', 'like', "%$search%")
                      ->orWhereHas('role', function($q) use ($search) {
                          $q->where('role_name', 'like', "%$search%");
                      });
                });
            })
            ->orderBy($sort, $direction)
            ->paginate(10);

        if ($request->ajax()) {
            return response()->json([
                'html' => view('admin.user.partials.table', compact('users', 'show_deleted'))->render(),
                'pagination' => (string) $users->appends(request()->query())->links()
            ]);
        }

        return view('admin.user.index', compact('users', 'sort', 'direction', 'search', 'show_deleted'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }
        
        try {
            $roles = Role::all();
            return view('admin.user.create', compact('roles'));
        } catch (\Exception $e) {
            Log::error('Error loading create form', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|min:10|max:15',
            'password' => 'required|string|min:6',
            'email' => 'required|email|unique:users,email',
            'address' => 'required|string|max:500',
            'role_id' => 'required|exists:roles,id'
        ]);

        try {
            User::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'password' => Hash::make($request->password),
                'email' => $request->email,
                'role_id' => $request->role_id,
                'address' => $request->address,
            ]);

            return redirect()->route('admin.users.index')->with('success', 'User has been created successfully!');
        } catch (\Exception $e) {
            Log::error('Error creating user', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error creating user: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $user = User::with('role')->findOrFail($id);
            
            if (request()->ajax()) {
                return view('admin.user.modal', compact('user'));
            }
            
            return view('admin.user.show', compact('user'));
        } catch (\Exception $e) {
            Log::error('Error showing user', ['error' => $e->getMessage(), 'id' => $id]);
            
            if (request()->ajax()) {
                return response()->json(['error' => 'User not found'], 404);
            }
            
            return back()->with('error', 'User not found');
        }
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        try {
            $user = User::findOrFail($id);
            $roles = Role::all();
            return view('admin.user.edit', compact('user', 'roles'));
        } catch (\Exception $e) {
            Log::error('Error loading edit form', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'User not found');
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|min:3|max:255',
            'phone' => 'required|string|min:10|max:15',
            'email' => 'required|email|unique:users,email,' . $id,
            'address' => 'required|string|max:500',
            'role_id' => 'required|exists:roles,id',
            'password' => 'nullable|string|min:6'
        ]);

        try {
            $user = User::findOrFail($id);
            
            $updateData = [
                'name' => $request->name,
                'phone' => $request->phone,
                'email' => $request->email,
                'address' => $request->address,
                'role_id' => $request->role_id,
            ];

            // Only update password if provided
            if ($request->filled('password')) {
                $updateData['password'] = Hash::make($request->password);
            }

            $user->update($updateData);

            return redirect()->route('admin.users.index')->with('success', 'User has been updated successfully!');
        } catch (\Exception $e) {
            Log::error('Error updating user', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error updating user: ' . $e->getMessage())->withInput();
        }
    }

    public function resetPassword(Request $request, User $user)
    {
        // Check admin session
        if (!session('is_admin') || !session('admin_id')) {
            return redirect()->route('admin.login')->with('message', 'Please login as admin');
        }

        // Validate the new password
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        try {
            // Update user's password
            $user->update([
                'password' => Hash::make($request->new_password)
            ]);
            
            // Log the password reset
            Log::info('Manual password reset for user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'admin_id' => session('admin_id')
            ]);
            
            return back()->with([
                'success' => 'Password for ' . $user->name . ' has been updated successfully!',
            ]);
            
        } catch (\Exception $e) {
            Log::error('Manual password reset failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'admin_id' => session('admin_id')
            ]);
            
            return back()->with('error', 'Password reset failed: ' . $e->getMessage());
        }
    }

    public function showResetPasswordForm(User $user)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }
        
        return view('admin.user.reset-password', compact('user'));
    }

    public function processResetPassword(Request $request, User $user)
    {
        // Check admin session
        if (!session('is_admin') || !session('admin_id')) {
            return redirect()->route('admin.login')->with('message', 'Please login as admin');
        }

        // Validate the new password
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed'
        ]);

        try {
            $generateRandom = $request->has('generate_random');
            
            if ($generateRandom) {
                $newPassword = Str::random(12);
            } else {
                $request->validate([
                    'new_password' => 'required|string|min:6|confirmed'
                ]);
                $newPassword = $request->new_password;
            }
            
            // Update user's password
            $user->update([
                'password' => Hash::make($newPassword)
            ]);
            
            // Log the password reset
            Log::info('Manual password reset for user', [
                'user_id' => $user->id,
                'user_email' => $user->email,
                'admin_id' => session('admin_id')
            ]);
            
            return redirect()->route('admin.users.show', $user->id)
                ->with('success', 'Password has been updated successfully!')
                ->with('new_password', $newPassword);
            
        } catch (\Exception $e) {
            Log::error('Manual password reset failed', [
                'error' => $e->getMessage(),
                'user_id' => $user->id,
                'admin_id' => session('admin_id')
            ]);
            
            return back()->with('error', 'Password reset failed: ' . $e->getMessage());
        }
    }

    /**
     * Soft delete the specified resource (mark as deleted).
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting admin user
            if ($user->role_id == 1 && User::where('role_id', 1)->count() <= 1) {
                return back()->with('error', 'Cannot delete the last admin user');
            }
            
            // Soft delete the user
            $user->delete();
            
            Log::info('User soft deleted', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'admin_id' => session('admin_id')
            ]);
            
            return redirect()->route('admin.users.index')->with('success', 'User has been moved to trash successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting user', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Restore soft deleted user.
     */
    public function restore($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            $user->restore();
            
            Log::info('User restored', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'admin_id' => session('admin_id')
            ]);
            
            return redirect()->route('admin.users.index')->with('success', 'User has been restored successfully!');
        } catch (\Exception $e) {
            Log::error('Error restoring user', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error restoring user: ' . $e->getMessage());
        }
    }

    /**
     * Permanently delete user from database.
     */
    public function forceDelete($id)
    {
        try {
            $user = User::onlyTrashed()->findOrFail($id);
            $userName = $user->name;
            
            // Prevent deleting admin user
            if ($user->role_id == 1) {
                return back()->with('error', 'Cannot permanently delete admin user');
            }
            
            $user->forceDelete();
            
            Log::warning('User permanently deleted', [
                'user_id' => $id,
                'user_name' => $userName,
                'admin_id' => session('admin_id')
            ]);
            
            return redirect()->route('admin.users.index', ['show_deleted' => 1])
                ->with('success', 'User has been permanently deleted!');
        } catch (\Exception $e) {
            Log::error('Error permanently deleting user', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error permanently deleting user: ' . $e->getMessage());
        }
    }

    /**
     * Restore all soft deleted users.
     */
    public function restoreAll()
    {
        try {
            $count = User::onlyTrashed()->count();
            User::onlyTrashed()->restore();
            
            Log::info('All users restored', [
                'count' => $count,
                'admin_id' => session('admin_id')
            ]);
            
            return redirect()->route('admin.users.index')
                ->with('success', "{$count} users have been restored successfully!");
        } catch (\Exception $e) {
            Log::error('Error restoring all users', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error restoring users: ' . $e->getMessage());
        }
    }

    public function showImportForm()
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        try {
            $roles = Role::all();
            return view('admin.user.import', compact('roles'));
        } catch (\Exception $e) {
            Log::error('Error loading import form', ['error' => $e->getMessage()]);
            return back()->with('error', 'Error loading form: ' . $e->getMessage());
        }
    }

    public function import(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'file' => 'required|file|mimes:csv,xlsx,xls|max:2048',
            'generate_password' => 'boolean'
        ]);

        if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
        }

        try {
            $file = $request->file('file');
            $generatePassword = $request->boolean('generate_password', true);

            $import = new UsersImport($generatePassword);
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

            return redirect()->route('admin.users.index')
                ->with('success', $message);

        } catch (\Exception $e) {
            Log::error('User import error: ' . $e->getMessage());
            return redirect()->back()
                ->with('error', 'Import failed: ' . $e->getMessage())
                ->withInput();
        }
    }

    public function downloadTemplate()
    {
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="user_import_template.csv"',
            'Pragma' => 'no-cache',
            'Cache-Control' => 'must-revalidate, post-check=0, pre-check=0',
            'Expires' => '0'
        ];

        $callback = function() {
            $handle = fopen('php://output', 'w');

            // Add CSV headers
            fputcsv($handle, [
                'name',
                'email',
                'phone',
                'address',
                'role_id',
                'password'
            ]);

            // Add example data
            fputcsv($handle, [
                'John Doe',
                'john@example.com',
                '081234567890',
                'Jl. Example No. 123',
                '2',
                'password123'
            ]);

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Assign shift to user
     */
    public function assignShift(Request $request, $userId)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'shift_id' => 'nullable|exists:work_times,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid shift selected'
            ], 400);
        }

        try {
            $user = User::findOrFail($userId);
            $shiftId = $request->shift_id;

            // If shift_id is null, remove shift assignment
            if ($shiftId === null) {
                $user->update(['shift_id' => null]);

                Log::info('User shift removed', [
                    'user_id' => $user->id,
                    'user_name' => $user->name,
                    'admin_id' => session('admin_id')
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'Shift removed successfully',
                    'shift_name' => 'No Shift'
                ]);
            }

            // Assign new shift
            $shift = WorkTime::findOrFail($shiftId);
            $user->update(['shift_id' => $shiftId]);

            Log::info('User shift assigned', [
                'user_id' => $user->id,
                'user_name' => $user->name,
                'shift_id' => $shift->id,
                'shift_name' => $shift->name,
                'admin_id' => session('admin_id')
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Shift assigned successfully',
                'shift_name' => $shift->name,
                'shift_time' => $shift->formatted_start_time . ' - ' . $shift->formatted_end_time
            ]);

        } catch (\Exception $e) {
            Log::error('Error assigning shift to user', [
                'error' => $e->getMessage(),
                'user_id' => $userId,
                'admin_id' => session('admin_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error assigning shift: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get available shifts for assignment
     */
    public function getAvailableShifts()
    {
        try {
            $shifts = WorkTime::where('is_active', true)
                ->orderBy('name')
                ->get(['id', 'name', 'start_time', 'end_time', 'late_threshold']);

            return response()->json([
                'success' => true,
                'shifts' => $shifts
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching available shifts', ['error' => $e->getMessage()]);

            return response()->json([
                'success' => false,
                'message' => 'Error fetching shifts'
            ], 500);
        }
    }

    /**
     * Bulk assign shifts to multiple users
     */
    public function bulkAssignShift(Request $request)
    {
        if (!session('is_admin')) {
            return redirect()->route('admin.login');
        }

        $validator = Validator::make($request->all(), [
            'user_ids' => 'required|array|min:1',
            'user_ids.*' => 'exists:users,id',
            'shift_id' => 'nullable|exists:work_times,id'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Invalid data provided'
            ], 400);
        }

        try {
            $userIds = $request->user_ids;
            $shiftId = $request->shift_id;
            $shiftName = $shiftId ? WorkTime::find($shiftId)->name : 'No Shift';

            $updatedCount = 0;

            foreach ($userIds as $userId) {
                $user = User::find($userId);
                if ($user) {
                    $user->update(['shift_id' => $shiftId]);
                    $updatedCount++;
                }
            }

            Log::info('Bulk shift assignment', [
                'user_count' => $updatedCount,
                'shift_id' => $shiftId,
                'shift_name' => $shiftName,
                'admin_id' => session('admin_id')
            ]);

            return response()->json([
                'success' => true,
                'message' => "Successfully assigned {$shiftName} to {$updatedCount} users"
            ]);

        } catch (\Exception $e) {
            Log::error('Error in bulk shift assignment', [
                'error' => $e->getMessage(),
                'admin_id' => session('admin_id')
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error in bulk assignment: ' . $e->getMessage()
            ], 500);
        }
    }
}
