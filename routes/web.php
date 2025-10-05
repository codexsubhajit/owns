<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

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
