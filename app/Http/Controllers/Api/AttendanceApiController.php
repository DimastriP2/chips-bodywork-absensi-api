<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Attendance;
use App\Models\OfficeLocation;

class AttendanceApiController extends Controller
{
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = auth()->user();

        $alreadyCheckIn = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if ($alreadyCheckIn) {
            return response()->json([
                'message' => 'Anda sudah melakukan check in hari ini',
                'attendance' => $alreadyCheckIn,
            ], 409);
        }

        $office = OfficeLocation::first();

        if (!$office) {
            return response()->json([
                'message' => 'Lokasi kantor belum diatur',
            ], 404);
        }

        $distance = $this->calculateDistance(
            $office->latitude,
            $office->longitude,
            $request->latitude,
            $request->longitude
        );

        if ($distance > $office->radius) {
            return response()->json([
                'message' => 'Anda berada di luar radius kantor',
                'distance' => round($distance),
            ], 403);
        }

        $attendance = Attendance::create([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in_time' => now()->format('H:i:s'),
            'check_in_latitude' => $request->latitude,
            'check_in_longitude' => $request->longitude,
            'distance' => round($distance),
            'status' => 'hadir',
        ]);

        return response()->json([
            'message' => 'Check in berhasil',
            'attendance' => $attendance,
        ], 201);
    }

    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required',
            'longitude' => 'required',
        ]);

        $user = auth()->user();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', now()->toDateString())
            ->first();

        if (!$attendance) {
            return response()->json([
                'message' => 'Anda belum melakukan check in hari ini',
            ], 404);
        }

        if ($attendance->check_out_time !== null) {
            return response()->json([
                'message' => 'Anda sudah melakukan absen pulang hari ini',
            ], 409);
        }

        $office = OfficeLocation::first();

        if (!$office) {
            return response()->json([
                'message' => 'Lokasi kantor belum diatur',
            ], 404);
        }

        $distance = $this->calculateDistance(
            $office->latitude,
            $office->longitude,
            $request->latitude,
            $request->longitude
        );

        if ($distance > $office->radius) {
            return response()->json([
                'message' => 'Anda berada di luar radius kantor',
                'distance' => round($distance),
            ], 403);
        }

        $attendance->update([
            'check_out_time' => now()->format('H:i:s'),
            'check_out_latitude' => $request->latitude,
            'check_out_longitude' => $request->longitude,
        ]);

        return response()->json([
            'message' => 'Absen pulang berhasil',
            'attendance' => $attendance,
        ], 200);
    }

    public function profile()
    {
        $user = auth()->user()->load('employee');

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

    public function history()
    {
        $attendances = Attendance::where('user_id', auth()->id())
            ->orderBy('date', 'desc')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Riwayat absensi berhasil diambil',
            'attendances' => $attendances,
        ]);
    }

    private function calculateDistance($lat1, $lon1, $lat2, $lon2)
    {
        $earthRadius = 6371000;

        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);

        $a =
            sin($dLat / 2) * sin($dLat / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($dLon / 2) * sin($dLon / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return $earthRadius * $c;
    }
}