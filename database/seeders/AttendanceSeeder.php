<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Attendance;
use App\Models\User;

class AttendanceSeeder extends Seeder
{
    public function run(): void
    {
        $user = User::where('role', 'employee')->first();

        if (!$user) {
            return;
        }

        Attendance::firstOrCreate([
            'user_id' => $user->id,
            'date' => now()->toDateString(),
        ], [
            'user_id' => $user->id,
            'date' => now()->toDateString(),
            'check_in_time' => '08:05:00',
            'check_out_time' => '17:10:00',
            'check_in_latitude' => '-6.2000000',
            'check_in_longitude' => '106.8166660',
            'check_out_latitude' => '-6.2000000',
            'check_out_longitude' => '106.8166660',
            'distance' => 35,
            'status' => 'hadir',
        ]);
    }
}