<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\OfficeLocation;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Support\Facades\DB;

class AttendanceService
{
    public function __construct(private Geofence $geofence) {}

    public function record(User $user, array $coordinates, bool $checkingOut): Attendance
    {
        return DB::transaction(function () use ($user, $coordinates, $checkingOut) {
            // A user row exists even before the first attendance. Locking only a
            // missing attendance row would not serialize two first check-ins.
            User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $now = now();
            $attendance = Attendance::query()
                ->where('user_id', $user->id)
                ->where('date', $now->toDateString())
                ->lockForUpdate()
                ->first();

            if (!$checkingOut && $attendance) {
                $this->reject('Anda sudah melakukan check in hari ini', 409, [
                    'attendance' => $attendance,
                ]);
            }

            if ($checkingOut && (!$attendance || !$attendance->check_in_time)) {
                $this->reject('Anda belum melakukan check in hari ini', 404);
            }

            if ($checkingOut && $attendance->check_out_time !== null) {
                $this->reject('Anda sudah melakukan absen pulang hari ini', 409);
            }

            $office = OfficeLocation::query()->orderBy('id')->first();
            if (!$office) {
                $this->reject('Lokasi kantor belum diatur', 404);
            }

            if (!is_numeric($office->latitude) || !is_numeric($office->longitude)
                || abs((float) $office->latitude) > 90
                || abs((float) $office->longitude) > 180
                || (int) $office->radius < 1) {
                $this->reject('Konfigurasi lokasi kantor tidak valid. Hubungi admin.', 503);
            }

            $distance = $this->geofence->distanceInMeters(
                (float) $office->latitude,
                (float) $office->longitude,
                (float) $coordinates['latitude'],
                (float) $coordinates['longitude'],
            );

            // Compare the actual distance; rounding is for display/storage only.
            if ($distance > (int) $office->radius) {
                $this->reject('Anda berada di luar radius kantor', 403, [
                    'distance' => round($distance),
                ]);
            }

            if ($checkingOut) {
                if ($now->format('H:i:s') < $attendance->check_in_time) {
                    $this->reject('Waktu pulang tidak boleh mendahului waktu masuk.', 409);
                }

                $attendance->update([
                    'check_out_time' => $now->format('H:i:s'),
                    'check_out_latitude' => $coordinates['latitude'],
                    'check_out_longitude' => $coordinates['longitude'],
                ]);

                return $attendance;
            }

            return Attendance::create([
                'user_id' => $user->id,
                'date' => $now->toDateString(),
                'check_in_time' => $now->format('H:i:s'),
                'check_in_latitude' => $coordinates['latitude'],
                'check_in_longitude' => $coordinates['longitude'],
                'distance' => round($distance),
                'status' => 'hadir',
            ]);
        }, 3);
    }

    private function reject(string $message, int $status, array $extra = []): never
    {
        throw new HttpResponseException(response()->json(['message' => $message] + $extra, $status));
    }
}
