<?php

namespace App\Http\Controllers\user;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class AuthController extends Controller
{
    public function index()
    {
        return view('user.auth');
    }

    public function doLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required'
        ]);

        $user = User::where('email', $request->email)->first();

        if (isset($user)) {
            if (password_verify($request->password, $user->password)) {
                $request->session()->put('user_id', $user->id);
                $request->session()->put('username', $user->name);
                return redirect('user/home');
            } else {
                return redirect('/')->with('message', 'Wrong password!');
            }
        } else {
            return redirect('/')->with('message', 'Email not found!');
        }
    }

    public function logout()
    {
        session()->forget('username');
        return redirect('/')->with('success', 'You have been logout!');
    }
}
