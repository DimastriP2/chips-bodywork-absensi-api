<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ProfileController;

use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\EmployeeController;
use App\Http\Controllers\Admin\AttendanceController;
use App\Http\Controllers\Admin\OfficeLocationController;

/*
|--------------------------------------------------------------------------
| Landing Page
|--------------------------------------------------------------------------
*/

Route::get('/', function () {
    return redirect()->route('login');
});

/*
|--------------------------------------------------------------------------
| Dashboard Laravel
|--------------------------------------------------------------------------
*/

Route::get('/dashboard', function () {
    return redirect()->route('admin.dashboard');
})->middleware(['auth'])->name('dashboard');

/*
|--------------------------------------------------------------------------
| Profile
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {

    Route::get('/profile', [ProfileController::class, 'edit'])
        ->name('profile.edit');

    Route::patch('/profile', [ProfileController::class, 'update'])
        ->name('profile.update');

    Route::delete('/profile', [ProfileController::class, 'destroy'])
        ->name('profile.destroy');
});

/*
|--------------------------------------------------------------------------
| ADMIN AREA
|--------------------------------------------------------------------------
*/

Route::middleware(['auth', 'admin'])
    ->prefix('admin')
    ->group(function () {

        /*
        |-------------------------------------------------------
        | Dashboard
        |-------------------------------------------------------
        */

        Route::get('/dashboard', [DashboardController::class, 'index'])
            ->name('admin.dashboard');

        /*
        |-------------------------------------------------------
        | Data Karyawan
        |-------------------------------------------------------
        */

        Route::resource('employees', EmployeeController::class);

        /*
        |-------------------------------------------------------
        | Rekap Absensi
        |-------------------------------------------------------
        */

        Route::get('attendances', [AttendanceController::class, 'index'])
            ->name('attendances.index');

        /*
        |-------------------------------------------------------
        | Export CSV
        |-------------------------------------------------------
        */

        Route::get('attendance/export/csv', [AttendanceController::class, 'exportCsv'])
            ->name('attendance.export.csv');

        /*
        |-------------------------------------------------------
        | Export PDF
        |-------------------------------------------------------
        */

        Route::get('attendance/export/pdf', [AttendanceController::class, 'exportPdf'])
            ->name('attendance.export.pdf');

        /*
        |-------------------------------------------------------
        | Lokasi Kantor
        |-------------------------------------------------------
        */

        Route::get('office', [OfficeLocationController::class, 'index'])
            ->name('office.index');

        Route::post('office', [OfficeLocationController::class, 'store'])
            ->name('office.store');
    });

require __DIR__.'/auth.php';