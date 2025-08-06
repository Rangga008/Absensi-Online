<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckAdminSession
{
    public function handle(Request $request, Closure $next)
    {
        // Skip middleware for login/logout routes
        if ($request->routeIs('admin.login') || 
            $request->routeIs('admin.login.process') || 
            $request->routeIs('admin.logout') ||
            $request->is('admin/login') || 
            $request->is('admin/logout')) {
            return $next($request);
        }

        // Debug session data
        Log::debug('CheckAdminSession middleware', [
            'route' => $request->route()->getName(),
            'path' => $request->path(),
            'session_id' => $request->session()->getId(),
            'is_admin' => session('is_admin'),
            'admin_id' => session('admin_id'),
            'all_session' => session()->all()
        ]);

        // Check if admin is logged in
        if (!session('is_admin') || !session('admin_id')) {
            Log::warning('Admin session check failed - redirecting to login', [
                'path' => $request->path(),
                'is_admin' => session('is_admin'),
                'admin_id' => session('admin_id')
            ]);
            
            return redirect()->route('admin.login')->with('message', 'Please login as admin');
        }

        // Additional check: verify admin role
        if (session('role_id') != 1) {
            Log::warning('Invalid admin role', ['role_id' => session('role_id')]);
            
            $request->session()->flush();
            return redirect()->route('admin.login')->with('message', 'Invalid admin credentials');
        }

        Log::info('Admin session valid', ['admin_id' => session('admin_id')]);
        
        return $next($request);
    }
}