<?php

use App\Http\Controllers\Api\AttendanceApiController;
use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login'])->middleware('throttle:mobile-login');

Route::middleware(['auth:sanctum', 'throttle:mobile-api'])->group(function () {
    Route::post('/checkin', [AttendanceApiController::class, 'checkIn']);
    Route::post('/checkout', [AttendanceApiController::class, 'checkOut']);
    Route::get('/history', [AttendanceApiController::class, 'history']);
    Route::get('/profile', [AttendanceApiController::class, 'profile']);
    Route::post('/change-password', [AuthController::class, 'changePassword'])
        ->middleware('throttle:5,1');
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/attendance/today', [AttendanceApiController::class, 'today']);
    Route::get('/attendance/summary', [AttendanceApiController::class, 'summary']);
    Route::get('/office', [AttendanceApiController::class, 'office']);
});
