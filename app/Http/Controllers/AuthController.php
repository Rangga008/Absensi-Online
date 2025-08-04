<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function index()
    {
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
            'password' => password_hash($request->password, PASSWORD_DEFAULT),
            'address' => $request->address,
            'role_id' => 1,
        ]);

        return redirect('/admin')->with('success', 'New user has been created!');
    }

    public function doLogin(Request $request)
    {
        $request->validate([
            'email' => 'required',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->get()->first();

        if (isset($user)) {
            if (password_verify($request->password, $user->password)) {
                $request->session()->put('username', $user->name);
                $request->session()->put('role_id', $user->role_id);
                return redirect('dashboard')->with('message', 'Welcome <strong>' . $user->name . '</strong>');
            } else {
                return redirect('/admin')->with('message', 'Wrong password!');
            }
        } else {
            return redirect('/admin')->with('message', 'Email not found!');
        }
    }

    public function logout()
    {
        session()->forget('username');
        session()->forget('role_id');
        return redirect('/admin')->with('success', 'You have been logout!');
    }
}
