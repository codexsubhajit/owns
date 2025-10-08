<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BusinessController;
use App\Http\Controllers\AuthController;

// This file is for API routes. Routes here are stateless, use the 'api' middleware group, and are typically accessed via AJAX or external clients.
// Use this for RESTful endpoints, mobile apps, or third-party integrations.

// ...existing code...

Route::middleware('auth:sanctum')->post('/businesses', [BusinessController::class, 'store']);

// ...existing code...

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// ...existing code...