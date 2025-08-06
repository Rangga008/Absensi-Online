<?php

use Illuminate\Support\Facades\Route;


use App\Http\Controllers\user\AuthController as UserAuthController;
use App\Http\Controllers\user\HomeController;
use App\Http\Controllers\AuthController as AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\user\AttendanceController as UserAttendanceController;


/** Route untuk halaman welcome/landing page */
Route::get('/', function () {
    return view('welcome');
});

/** Authentication Routes (Non-prefixed) */
// Main login form (for users)
Route::get('/login', [UserAuthController::class, 'index'])->name('login');
Route::post('/login', [UserAuthController::class, 'doLogin'])->name('login.process');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('user.logout');

/** Route untuk frontend user */
Route::prefix('user')->middleware(['check.user.session'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('user.home');
    Route::get('/about', [HomeController::class, 'about'])->name('user.about');
    Route::get('/guide', [HomeController::class, 'guide'])->name('user.guide');
    Route::match(['get', 'post'], '/concession', [HomeController::class, 'concession'])->name('user.concession');
    Route::post('/store-concession', [HomeController::class, 'store_concession'])->name('user.store_concession');
    Route::get('/salary', [HomeController::class, 'show_salary'])->name('user.salary');
    Route::get('/history', [HomeController::class, 'show_history'])->name('user.history');
    Route::get('/attendance', [HomeController::class, 'attendance'])->name('user.attendance');
    Route::post('/do-attendance', [HomeController::class, 'do_attendance'])->name('user.do_attendance');
    Route::post('/logout', [UserAuthController::class, 'logout'])->name('user.logout');
    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('user.attendance');
    Route::post('/do-attendance', [UserAttendanceController::class, 'store'])->name('user.do_attendance');
    
});

/** Route untuk backend admin - SIMPLIFIED FOR DEBUGGING */
Route::prefix('admin')->group(function () {
    // Public admin routes (no middleware)
    Route::get('/login', [AdminAuthController::class, 'index'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'doLogin'])->name('admin.login.process');
    Route::get('/register', [AdminAuthController::class, 'register'])->name('admin.register');
    Route::post('/register', [AdminAuthController::class, 'doRegister'])->name('admin.register.process');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    
    // TEMPORARY: Remove middleware for debugging
    // ADD BACK AFTER FIXING THE ISSUE
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    
    // Protected admin routes - TEMPORARY WITHOUT MIDDLEWARE FOR DEBUGGING
    // ADD MIDDLEWARE BACK AFTER TESTING
        
    Route::resource('users', UserController::class)->names([
        'index' => 'admin.users.index',
        'create' => 'admin.users.create',
        'store' => 'admin.users.store',
        'show' => 'admin.users.show',
        'edit' => 'admin.users.edit',
        'update' => 'admin.users.update',
        'destroy' => 'admin.users.destroy'
    ]);
    
    Route::resource('roles', RoleController::class)->names([
        'index' => 'admin.roles.index',
        'create' => 'admin.roles.create',
        'store' => 'admin.roles.store',
        'show' => 'admin.roles.show',
        'edit' => 'admin.roles.edit',
        'update' => 'admin.roles.update',
        'destroy' => 'admin.roles.destroy'
    ]);
    
    Route::resource('salaries', SalaryController::class)->names([
        'index' => 'admin.salaries.index',
        'create' => 'admin.salaries.create',
        'store' => 'admin.salaries.store',
        'show' => 'admin.salaries.show',
        'edit' => 'admin.salaries.edit',
        'update' => 'admin.salaries.update',
        'destroy' => 'admin.salaries.destroy'
    ]);
    
    Route::resource('attendances', AttendanceController::class)->names([
        'index' => 'admin.attendances.index',
        'create' => 'admin.attendances.create',
        'store' => 'admin.attendances.store',
        'show' => 'admin.attendances.show',
        'edit' => 'admin.attendances.edit',
        'update' => 'admin.attendances.update',
        'destroy' => 'admin.attendances.destroy'
    ]);
    
    Route::resource('concessions', ConcessionController::class)->names([
        'index' => 'admin.concessions.index',
        'create' => 'admin.concessions.create',
        'store' => 'admin.concessions.store',
        'show' => 'admin.concessions.show',
        'edit' => 'admin.concessions.edit',
        'update' => 'admin.concessions.update',
        'destroy' => 'admin.concessions.destroy'
    ]);
});

// Debug route untuk testing session
Route::get('/debug-session', function () {
    return [
        'session_id' => session()->getId(),
        'all_session' => session()->all(),
        'is_admin' => session('is_admin'),
        'admin_id' => session('admin_id'),
    ];
});

/** Route untuk AJAX requests */
Route::prefix('api')->group(function () {
    Route::post('/attendance/check-status', [UserAttendanceController::class, 'checkAttendanceStatus'])
        ->name('attendance.check-status');
    Route::get('/attendance/stats', [UserAttendanceController::class, 'getStats'])
        ->name('attendance.stats');
    Route::post('/attendance', [UserAttendanceController::class, 'store'])
        ->name('attendance.store');
});