<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function index()
    {
        // Cek jika user sudah login, redirect ke home
        if (session()->has('user_id')) {
            return redirect()->route('user.home');
        }
        
        return view('user.auth');
    }

    public function doLogin(Request $request)
{
    $request->validate([
        'email' => 'required|email',
        'password' => 'required'
    ]);

    $user = User::where('email', $request->email)->first();

    if ($user && password_verify($request->password, $user->password)) {
        // Clear any existing admin session
        $request->session()->forget(['admin_id', 'is_admin']);
        
        // Set user session
        $request->session()->regenerate();
        session([
            'user_id' => $user->id,
            'username' => $user->name,
            'user_email' => $user->email,
            'role_id' => $user->role_id
        ]);
        
        return redirect()->route('user.home');
    }

    return back()->withErrors(['email' => 'Invalid credentials']);
}

    public function logout(Request $request)
{
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/login');
}
}