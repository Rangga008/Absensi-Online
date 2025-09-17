<?php
// Add these routes to your routes/web.php file

use App\Http\Controllers\LogoController;

// Logo routes (add inside admin middleware group)
Route::post('/logo/upload', [LogoController::class, 'upload'])->name('admin.logo.upload');
Route::get('/logo', [LogoController::class, 'getLogo'])->name('logo.serve');

// Or add outside admin middleware if you want public access to logo
// Route::get('/logo', [LogoController::class, 'getLogo'])->name('logo.serve');
