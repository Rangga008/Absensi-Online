<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;

class AuthController extends Controller
{
    public function index()
    {
        // Redirect if already logged in as admin
        if (session('is_admin') && session('admin_id')) {
            return redirect()->route('admin.dashboard');
        }
        
        return view('auth.login');
    }

    public function register(Request $request)
    {
        return view('auth.register');
    }

    public function doRegister(Request $request)
    {
        $request->validate([
            'name' => 'required',
            'phone' => 'required',
            'email' => 'required|unique:users',
            'password' => 'required|min:6',
            'repeat_password' => 'required_with:password|same:password',
            'address' => 'required'
        ]);

        User::create([
            'name' => $request->name,
            'phone' => $request->phone,
            'email' => $request->email,
            'password' => Hash::make($request->password), // Use Hash::make instead
            'address' => $request->address,
            'role_id' => 1,
        ]);

        return redirect()->route('admin.login')->with('success', 'New user has been created!');
    }

    public function doLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->with('message', 'Email not found');
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->with('message', 'Incorrect password');
        }

        if ($user->role_id != 1) {
            return back()->with('message', 'Not authorized as admin');
        }

        // Clear any existing session data
        $request->session()->flush();
        
        // Regenerate session ID for security
        $request->session()->regenerate();
        
        // Set admin session data
        $request->session()->put([
            'admin_id' => $user->id,
            'admin_name' => $user->name,
            'admin_email' => $user->email,
            'role_id' => $user->role_id,
            'is_admin' => true
        ]);

        // Force session save
        $request->session()->save();

        Log::info('Admin login successful', [
            'user_id' => $user->id,
            'email' => $user->email,
            'session_data' => $request->session()->all()
        ]);

        // Use different redirect method
        return redirect()->intended(route('admin.dashboard'));
    }

    public function logout(Request $request)
    {
        Log::info('Admin logout', ['admin_id' => session('admin_id')]);
        
        // Clear session
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('admin.login')->with('success', 'Successfully logged out');
    }
}