    <?php

    use Illuminate\Support\Facades\Route;
    use App\Http\Controllers\AttendanceController;

    /** Route for frontend */
    Route::get('/', 'user\AuthController@index');
    Route::post('user/login', 'user\AuthController@doLogin');
    Route::get('user/home', 'user\HomeController@index');
    Route::get('user/logout', 'user\AuthController@logout');
    Route::get('user/about', 'user\HomeController@about');
    Route::get('user/guide', 'user\HomeController@guide');
    Route::get('user/concession', 'user\HomeController@concession');
    Route::post('user/concession', 'user\HomeController@store_concession');
    Route::get('user/salary', 'user\HomeController@show_salary');
    Route::get('user/history', 'user\HomeController@show_history');
    Route::get('user/attendance', 'user\HomeController@attendance');
    Route::post('/doAttendance', 'user\HomeController@do_attendance');

    /** Route for backend */
    Route::get('/admin', 'AuthController@index');
    Route::get('register', 'AuthController@register');
    Route::post('register', 'AuthController@doRegister');
    Route::post('login', 'AuthController@doLogin');
    Route::get('dashboard', 'DashboardController@index');
    Route::get('logout', 'AuthController@logout');
    Route::resource('user', 'UserController');
    Route::resource('role', 'RoleController');
    Route::resource('salary', 'SalaryController');
    Route::resource('attendance', 'AttendanceController');
    Route::resource('concession', 'ConcessionController');

    Route::post('/attendance', [AttendanceController::class, 'store'])->name('attendance.store');


    // Route untuk AJAX requests
    Route::post('attendance/check-status', [AttendanceController::class, 'checkAttendanceStatus'])->name('attendance.check-status');
    Route::get('attendance/stats', [AttendanceController::class, 'getStats'])->name('attendance.stats');

    // Route untuk user attendance page
    Route::get('user/attendance', function() {
        return view('user.attendance');
    })->name('user.attendance');