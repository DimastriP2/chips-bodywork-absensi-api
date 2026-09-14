<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\CarbonImmutable;

class DashboardController extends Controller
{
    public function index()
    {
        $today = CarbonImmutable::today();
        $employees = User::where('role', 'employee')->whereHas('employee');
        $totalEmployees = (clone $employees)->count();
        $attendance = Attendance::whereIn('user_id', (clone $employees)->select('id'))
            ->whereNotNull('check_in_time');
        $daily = (clone $attendance)->where('date', $today->toDateString());
        $present = (clone $daily)->count();
        $completed = (clone $daily)->whereNotNull('check_out_time')->count();

        $counts = (clone $attendance)
            ->whereBetween('date', [$today->subDays(6)->toDateString(), $today->toDateString()])
            ->selectRaw('date, COUNT(*) as total')->groupBy('date')->pluck('total', 'date');

        $trend = collect(range(6, 0))->map(function ($daysAgo) use ($today, $counts) {
            $date = $today->subDays($daysAgo);

            return [
                'date' => $date->toDateString(),
                'label' => $date->locale('id')->translatedFormat('D, d M'),
                'total' => (int) ($counts[$date->toDateString()] ?? 0),
            ];
        });

        return view('admin.dashboard', [
            'totalEmployees' => $totalEmployees,
            'todayAttendances' => $present,
            'completedAttendances' => $completed,
            'pendingEmployees' => max(0, $totalEmployees - $present),
            'onsiteEmployees' => max(0, $present - $completed),
            'attendanceRate' => $totalEmployees ? (int) round($present / $totalEmployees * 100) : 0,
            'todayLabel' => $today->locale('id')->translatedFormat('l, d F Y'),
            'trend' => $trend,
            'recentAttendances' => (clone $daily)->with('user')
                ->orderByDesc('check_in_time')->limit(8)->get(),
            'office' => OfficeLocation::query()->orderBy('id')->first(),
        ]);
    }
}
