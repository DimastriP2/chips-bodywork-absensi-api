<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\AttendanceApiController;

// Login
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {

    // Absensi
    Route::post('/checkin', [AttendanceApiController::class, 'checkIn']);
    Route::post('/checkout', [AttendanceApiController::class, 'checkOut']);

    // Riwayat
    Route::get('/history', [AttendanceApiController::class, 'history']);

    // Profil
    Route::get('/profile', [AttendanceApiController::class, 'profile']);

    // Ganti Password
    Route::post('/change-password', [AuthController::class, 'changePassword']);

});