<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\user\AuthController as UserAuthController;
use App\Http\Controllers\user\HomeController;
use App\Http\Controllers\AuthController as AdminAuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\user\AttendanceController as UserAttendanceController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SalaryController;
use App\Http\Controllers\AttendanceController;
use App\Http\Controllers\AttendanceImportController;

use App\Http\Controllers\ConcessionController;
use App\Http\Controllers\SettingController;

/** Route untuk halaman welcome/landing page */
Route::get('/', function () {
    return view('welcome');
});

/** Authentication Routes (Non-prefixed) */
Route::get('/login', [UserAuthController::class, 'index'])->name('login');
Route::post('/login', [UserAuthController::class, 'doLogin'])->name('login.process');
Route::post('/logout', [UserAuthController::class, 'logout'])->name('user.logout');

/** Route untuk frontend user */
Route::prefix('user')->middleware(['check.user.session'])->group(function () {
    Route::get('/home', [HomeController::class, 'index'])->name('user.home');
    Route::get('/about', [HomeController::class, 'about'])->name('user.about');
    Route::get('/guide', [HomeController::class, 'guide'])->name('user.guide');
    Route::get('/salary', [HomeController::class, 'show_salary'])->name('user.salary');
    Route::get('/history', [HomeController::class, 'show_history'])->name('user.history');
    Route::get('/attendance', [UserAttendanceController::class, 'index'])->name('user.attendance');
    Route::post('/do-attendance', [UserAttendanceController::class, 'store'])->name('user.do_attendance');
    Route::get('/concession', [ConcessionController::class, 'createForUser'])->name('user.concession.create');
    Route::post('/concession', [ConcessionController::class, 'storeForUser'])->name('user.concession.store');
    Route::get('/concession/history', [ConcessionController::class, 'userHistory'])->name('user.concession.history');
    
});

/** Route untuk backend admin */
Route::prefix('admin')->middleware(['check.admin.session'])->group(function () {
    // Authentication
    Route::get('/login', [AdminAuthController::class, 'index'])->name('admin.login');
    Route::post('/login', [AdminAuthController::class, 'doLogin'])->name('admin.login.process');
    Route::get('/register', [AdminAuthController::class, 'register'])->name('admin.register');
    Route::post('/register', [AdminAuthController::class, 'doRegister'])->name('admin.register.process');
    Route::post('/logout', [AdminAuthController::class, 'logout'])->name('admin.logout');
    Route::patch('users/{user}/restore', [UserController::class, 'restore'])->name('admin.users.restore');
    Route::delete('users/{user}/force-delete', [UserController::class, 'forceDelete'])->name('admin.users.force-delete');
    Route::patch('users/restore-all', [UserController::class, 'restoreAll'])->name('admin.users.restore-all');
    
    
    // Dashboard
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('admin.dashboard');
    Route::get('/settings', [SettingController::class, 'index'])->name('admin.settings.index');
    Route::post('/update', [SettingController::class, 'update'])->name('admin.settings.update');

    // Password Reset
    Route::get('users/{user}/reset-password', [UserController::class, 'showResetPasswordForm'])
        ->name('admin.users.reset-password.form');
    Route::post('users/{user}/reset-password', [UserController::class, 'processResetPassword'])
        ->name('admin.users.reset-password');
        Route::get('concessions/create', [ConcessionController::class, 'create'])
         ->name('admin.concessions.create');
        
    // Resources
    // Add these import routes BEFORE the resource route
    Route::get('users/import', [UserController::class, 'showImportForm'])
        ->name('admin.users.import.form');

    Route::post('users/import', [UserController::class, 'import'])
        ->name('admin.users.import');

    Route::get('users/import/template', [UserController::class, 'downloadTemplate'])
        ->name('admin.users.import.template');

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

    // Attendance Routes
    // Add these missing import routes BEFORE the resource route
    Route::get('attendances/import', [AttendanceImportController::class, 'showImportForm'])
        ->name('admin.attendances.import.form');

    Route::post('attendances/import', [AttendanceImportController::class, 'import'])
        ->name('admin.attendances.import');

    Route::get('attendances/import/template', [AttendanceImportController::class, 'downloadTemplate'])
        ->name('admin.attendances.import.template');

    // Export routes - MUST be before resource route to avoid conflict
    Route::get('attendances/export', [AttendanceController::class, 'showExportForm'])
        ->name('admin.attendances.export.form');
    Route::post('attendances/export', [AttendanceController::class, 'processExport'])
        ->name('admin.attendances.export.process');

    Route::resource('attendances', AttendanceController::class)->names([
        'index' => 'admin.attendances.index',
        'create' => 'admin.attendances.create',
        'store' => 'admin.attendances.store',
        'show' => 'admin.attendances.show',
        'edit' => 'admin.attendances.edit',
        'update' => 'admin.attendances.update',
        'destroy' => 'admin.attendances.destroy'
    ]);

    // User-specific attendances
    Route::get('users/{user}/attendances', [AttendanceController::class, 'userAttendances'])
        ->name('admin.users.attendances');

    // User attendance export routes
    Route::get('attendances/user/{user}/export-pdf', [AttendanceController::class, 'exportUserPdf'])
        ->name('admin.attendances.exportUserPdf');
    Route::get('attendances/user/{user}/export-excel', [AttendanceController::class, 'exportUserExcel'])
        ->name('admin.attendances.exportUserExcel');

    // Concessions
     Route::resource('concessions', ConcessionController::class)->names([
        'index' => 'admin.concessions.index',
        'create' => 'admin.concessions.create',
        'store' => 'admin.concessions.store',
        'show' => 'admin.concessions.show',
        'edit' => 'admin.concessions.edit',
        'update' => 'admin.concessions.update',
        'destroy' => 'admin.concessions.destroy'
    ]);

    // Tambahkan routes untuk approve/reject
    Route::post('concessions/{id}/approve', [ConcessionController::class, 'approve'])
         ->name('admin.concessions.approve');
    Route::post('concessions/{id}/reject', [ConcessionController::class, 'reject'])
         ->name('admin.concessions.reject');
});
/** Route untuk AJAX requests */
Route::prefix('api')->group(function () {

    Route::get('/attendance/stats', [UserAttendanceController::class, 'getStats'])
        ->name('attendance.stats');
    Route::post('/attendance', [UserAttendanceController::class, 'store'])
        ->name('attendance.store');
});

// Export form and process routes
Route::prefix('admin')->middleware(['check.admin.session'])->group(function () {
    Route::get('attendances/export', [AttendanceController::class, 'showExportForm'])
        ->name('admin.attendances.export.form');
    Route::post('attendances/export', [AttendanceController::class, 'processExport'])
        ->name('admin.attendances.export.process');
    Route::get('/attendance/export', [AttendanceController::class, 'export'])
    ->name('attendance.export');
    Route::post('/attendance/export', [AttendanceController::class, 'export'])
    ->name('attendance.export');

});

// Route untuk mendapatkan pengaturan absensi
Route::get('/api/attendance-settings', function() {
    return response()->json([
        'office_lat' => Setting::getValue('office_lat', -6.906000000000),
        'office_lng' => Setting::getValue('office_lng', 107.623400000000),
        'max_distance' => Setting::getValue('max_distance', 50000),
    ]);
});

// Debug route
Route::get('/debug-session', function () {
    return [
        'session_id' => session()->getId(),
        'all_session' => session()->all(),
        'is_admin' => session('is_admin'),
        'admin_id' => session('admin_id'),
    ];
});

Route::post('/attendance/check-status', [AttendanceController::class, 'checkAttendanceStatus'])
    ->name('attendance.check-status');