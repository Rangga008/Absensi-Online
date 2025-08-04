<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        if (!session()->has('username')) {
            return back();
        }
        $users = DB::table('users')->count();
        $concessions = DB::table('concession')->count();
        $attendances = DB::table('attendance')->count();
        $roles = DB::table('roles')->count();
        return view('admin.dashboard', compact('users', 'concessions', 'attendances', 'roles'));
    }
}
