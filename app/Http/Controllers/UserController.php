<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
{
    // Check admin session
    if (!session('is_admin') || !session('admin_id')) {
        return redirect()->route('admin.login')->with('message', 'Please login as admin');
    }
    
    try {
        $sortColumn = $request->get('sort', 'name');
        $sortDirection = $request->get('direction', 'asc');
        
        $users = User::with('role')
            ->orderBy($sortColumn, $sortDirection)
            ->paginate(10);
            
        if ($request->ajax()) {
        return response()->json([
            'users' => $users->items(),
            'pagination' => $users->links()->toHtml()
        ]);
        }
        
        return view('admin.user.index', compact('users'));
    } catch (\Exception $e) {
        Log::error('Error loading users', ['error' => $e->getMessage()]);
        
        if ($request->ajax()) {
            return response()->json(['error' => 'Error loading data'], 500);
        }
        
        return back()->with('error', 'Error loading users: ' . $e->getMessage());
    }
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

    // Ganti method resetPassword di UserController dengan ini:

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

// Atau buat method terpisah untuk reset password manual
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
     * Remove the specified resource from storage.
     */
    public function destroy($id)
    {
        try {
            $user = User::findOrFail($id);
            
            // Prevent deleting admin user
            if ($user->role_id == 1 && User::where('role_id', 1)->count() <= 1) {
                return back()->with('error', 'Cannot delete the last admin user');
            }
            
            $user->delete();
            
            return redirect()->route('admin.users.index')->with('success', 'User has been deleted successfully!');
        } catch (\Exception $e) {
            Log::error('Error deleting user', ['error' => $e->getMessage(), 'id' => $id]);
            return back()->with('error', 'Error deleting user: ' . $e->getMessage());
        }
    }
}