<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // Debug untuk melihat apa yang terjadi
        Log::info('Dashboard accessed', [
            'session_id' => $request->session()->getId(),
            'session_data' => session()->all(),
            'is_admin' => session('is_admin'),
            'admin_id' => session('admin_id'),
            'role_id' => session('role_id'),
            'url' => $request->fullUrl(),
            'method' => $request->method(),
            'headers' => $request->headers->all()
        ]);

        // Temporary: Bypass session check untuk debugging
        // HAPUS SETELAH MASALAH TERATASI
        if (!session('is_admin')) {
            Log::warning('No admin session found in dashboard');
            // Sementara tampilkan data debug
            dd([
                'message' => 'No admin session',
                'session' => session()->all(),
                'request_path' => $request->path(),
                'route_name' => $request->route() ? $request->route()->getName() : 'no route'
            ]);
        }

        // Jika session ada, siapkan data untuk view
        try {
            // Siapkan semua data yang mungkin dibutuhkan view
            $data = [
                'admin_name' => session('admin_name'),
                'admin_email' => session('admin_email'),
                'users' => 0,
                'attendances' => 0,
                'roles' => 0,
                'concessions' => 0,
                'salaries' => 0,
                'total_users' => 0,
                'total_attendances' => 0,
                'total_roles' => 0,
                'total_concessions' => 0,
                'total_salaries' => 0
            ];

            // Coba ambil data dari database
            try {
                if (class_exists('\App\Models\User')) {
                    $data['users'] = \App\Models\User::count();
                    $data['total_users'] = $data['users'];
                }
            } catch (\Exception $e) {
                Log::warning('Could not get users count', ['error' => $e->getMessage()]);
            }

            try {
                if (class_exists('\App\Models\Attendance')) {
                    $data['attendances'] = \App\Models\Attendance::whereDate('created_at', today())->count();
                    $data['total_attendances'] = \App\Models\Attendance::count();
                }
            } catch (\Exception $e) {
                Log::warning('Could not get attendances count', ['error' => $e->getMessage()]);
            }

            try {
                if (class_exists('\App\Models\Role')) {
                    $data['roles'] = \App\Models\Role::count();
                    $data['total_roles'] = $data['roles'];
                }
            } catch (\Exception $e) {
                Log::warning('Could not get roles count', ['error' => $e->getMessage()]);
            }

            try {
                if (class_exists('\App\Models\Concession')) {
                    $data['concessions'] = \App\Models\Concession::count();
                    $data['total_concessions'] = $data['concessions'];
                }
            } catch (\Exception $e) {
                Log::warning('Could not get concessions count', ['error' => $e->getMessage()]);
            }

            try {
                if (class_exists('\App\Models\Salary')) {
                    $data['salaries'] = \App\Models\Salary::count();
                    $data['total_salaries'] = $data['salaries'];
                }
            } catch (\Exception $e) {
                Log::warning('Could not get salaries count', ['error' => $e->getMessage()]);
            }

            Log::info('Dashboard data prepared', $data);
            
            return view('admin.dashboard', $data);
            
        } catch (\Exception $e) {
            Log::error('Dashboard view error', ['error' => $e->getMessage()]);
            
            // Fallback: Return dengan data minimal
            return view('admin.dashboard', [
                'admin_name' => session('admin_name', 'Admin'),
                'admin_email' => session('admin_email', 'admin@example.com'),
                'users' => 0,
                'attendances' => 0,
                'roles' => 0,
                'concessions' => 0,
                'salaries' => 0,
                'total_users' => 0,
                'total_attendances' => 0,
                'total_roles' => 0,
                'total_concessions' => 0,
                'total_salaries' => 0,
                'error' => 'Some data could not be loaded: ' . $e->getMessage()
            ]);
        }
    }
}