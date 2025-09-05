<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class CheckUserSession
{
    public function handle(Request $request, Closure $next)
    {
        // Skip middleware untuk login routes
        if ($request->routeIs('login') || 
            $request->routeIs('login.process') || 
            $request->is('login')) {
            return $next($request);
        }

        // Check jika user sudah login dan bukan admin
        if (!session('user_id') || session('is_admin')) {
            Log::warning('User session check failed - redirecting to login', [
                'path' => $request->path(),
                'user_id' => session('user_id'),
                'is_admin' => session('is_admin')
            ]);
            
            // Clear session dan redirect ke login
            $request->session()->flush();
            return redirect()->route('login')
                ->with('message', 'Sesi telah berakhir, silakan login kembali');
        }

        return $next($request);
    }
}