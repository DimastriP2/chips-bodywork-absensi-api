<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Employee;
use App\Models\Attendance;
use App\Models\OfficeLocation;

class DashboardController extends Controller
{
    public function index()
    {
        return view('admin.dashboard', [
            'totalEmployees' => Employee::count(),
            'todayAttendances' => Attendance::whereDate('date', today())->count(),
            'officeCount' => OfficeLocation::count(),
        ]);
    }
}