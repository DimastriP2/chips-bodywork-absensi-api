<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AttendanceLocationRequest;
use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Services\AttendanceService;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;

class AttendanceApiController extends Controller
{
    public function checkIn(AttendanceLocationRequest $request, AttendanceService $service)
    {
        return response()->json([
            'message' => 'Check in berhasil',
            'attendance' => $service->record($request->user(), $request->validated(), false),
        ], 201);
    }

    public function checkOut(AttendanceLocationRequest $request, AttendanceService $service)
    {
        return response()->json([
            'message' => 'Absen pulang berhasil',
            'attendance' => $service->record($request->user(), $request->validated(), true),
        ]);
    }

    public function profile(Request $request)
    {
        $user = $request->user()->load('employee');

        return response()->json([
            'message' => 'Profil berhasil diambil',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
            'employee' => $user->employee,
        ]);
    }

    public function history(Request $request)
    {
        $data = $request->validate([
            'month' => ['sometimes', 'date_format:Y-m'],
            'page' => ['sometimes', 'integer', 'min:1'],
            'per_page' => ['sometimes', 'integer', 'between:1,100'],
        ]);

        $query = Attendance::where('user_id', $request->user()->id)
            ->orderByDesc('date')->orderByDesc('id');

        if (isset($data['month'])) {
            $month = CarbonImmutable::createFromFormat('!Y-m', $data['month']);
            $query->whereBetween('date', [$month->toDateString(), $month->endOfMonth()->toDateString()]);
        }

        // Keep the original list response for existing mobile clients.
        if (!$request->hasAny(['page', 'per_page'])) {
            return response()->json([
                'message' => 'Riwayat absensi berhasil diambil',
                'attendances' => $query->get(),
            ]);
        }

        $page = $query->paginate($data['per_page'] ?? 20);

        return response()->json([
            'message' => 'Riwayat absensi berhasil diambil',
            'attendances' => $page->items(),
            'meta' => [
                'current_page' => $page->currentPage(),
                'last_page' => $page->lastPage(),
                'per_page' => $page->perPage(),
                'total' => $page->total(),
            ],
        ]);
    }

    public function today(Request $request)
    {
        $now = now();
        $attendance = Attendance::where('user_id', $request->user()->id)
            ->where('date', $now->toDateString())->first();

        return response()->json([
            'message' => 'Status absensi hari ini berhasil diambil',
            'date' => $now->toDateString(),
            'timezone' => config('app.timezone'),
            'server_time' => $now->toIso8601String(),
            'state' => !$attendance ? 'not_checked_in'
                : ($attendance->check_out_time ? 'checked_out' : 'checked_in'),
            'attendance' => $attendance,
        ]);
    }

    public function summary(Request $request)
    {
        $data = $request->validate(['month' => ['sometimes', 'date_format:Y-m']]);
        $month = CarbonImmutable::createFromFormat('!Y-m', $data['month'] ?? now()->format('Y-m'));
        $records = Attendance::where('user_id', $request->user()->id)
            ->whereBetween('date', [$month->toDateString(), $month->endOfMonth()->toDateString()])
            ->whereNotNull('check_in_time')->get();
        $complete = $records->filter(fn ($row) => $row->check_out_time !== null);
        $minutes = $complete->sum(function ($row) {
            $start = CarbonImmutable::parse($row->date.' '.$row->check_in_time);
            $end = CarbonImmutable::parse($row->date.' '.$row->check_out_time);

            return max(0, (int) $start->diffInMinutes($end, false));
        });

        return response()->json([
            'message' => 'Ringkasan absensi berhasil diambil',
            'month' => $month->format('Y-m'),
            'timezone' => config('app.timezone'),
            'summary' => [
                'present_days' => $records->count(),
                'completed_days' => $complete->count(),
                'incomplete_days' => $records->count() - $complete->count(),
                'recorded_minutes' => $minutes,
            ],
        ]);
    }

    public function office()
    {
        $office = OfficeLocation::query()->orderBy('id')->firstOrFail();

        return response()->json([
            'message' => 'Lokasi kantor berhasil diambil',
            'office' => [
                'office_name' => $office->office_name,
                'latitude' => (float) $office->latitude,
                'longitude' => (float) $office->longitude,
                'radius' => (int) $office->radius,
            ],
        ]);
    }
}
