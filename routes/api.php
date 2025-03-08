<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\API\Auth\AuthController;

// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');

// Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
//     return $request->user();
// });

// Route::get('/home', [HomeController::class, 'index'])->name('home');

Route::post('/login', [AuthController::class, 'login'])->name('login');
Route::post('/register', [AuthController::class, 'register'])->name('register');

// Admin
Route::prefix('admin')->middleware(['auth:sanctum', 'user-access:admin'])->group(function () {

    // Logout route
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});

// User
Route::middleware(['auth:sanctum', 'user-access:customer'])->group(function () {
    
    // Logout route
    Route::get('/logout', [AuthController::class, 'logout'])->name('logout');
});