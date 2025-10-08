<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BusinessController;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
|
| This file is for web routes. Routes here use the 'web' middleware group,
| support sessions, CSRF protection, and are intended for browser-based
| interactions. Use this for pages, forms, and views rendered for users
| in the browser.
|
*/

Route::get('/', function () {
    return view('welcome');
});

Route::get('/register', function () {
    return view('register');
});

Route::get('/login', function () {
    return view('login');
});

// Add POST routes for register and login (web forms)
Route::post('/register', [AuthController::class, 'register'])->name('web.register');
Route::post('/login', [AuthController::class, 'login'])->name('web.login');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [BusinessController::class, 'dashboard'])->name('dashboard');
    Route::post('/business', [BusinessController::class, 'store'])->name('business.store');
    Route::post('/business/{business}/generate-qr', [BusinessController::class, 'generateQr'])->name('business.generateQr');
    Route::post('/logout', function (\Illuminate\Http\Request $request) {
        auth()->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect('/login');
    })->name('logout');
});
