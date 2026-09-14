<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new \RuntimeException('DemoSeeder is available only in local/testing environments.');
        }

        $credentials = DB::transaction(function () {
            $credentials = [];
            OfficeLocation::firstOrCreate(['id' => 1], [
                'office_name' => 'Kantor Demo Chips Bodywork',
                'latitude' => -6.2,
                'longitude' => 106.816666,
                'radius' => 100,
            ]);

            foreach (range(0, 3) as $number) {
                $email = $number === 0 ? 'admin@example.test' : 'staff'.$number.'@example.test';
                $user = User::where('email', $email)->first();
                if (!$user) {
                    $password = Str::random(20);
                    $user = User::create([
                        'name' => $number === 0 ? 'Admin Demo' : 'Karyawan Demo '.$number,
                        'email' => $email,
                        'password' => Hash::make($password),
                        'role' => $number === 0 ? 'admin' : 'employee',
                    ]);
                    $credentials[] = [$email, $password];
                }

                if ($number === 0) {
                    continue;
                }

                // Do not attach demo records to a pre-existing account with another role.
                if ($user->role !== 'employee') {
                    continue;
                }
                Employee::firstOrCreate(['user_id' => $user->id], [
                    'name' => $user->name,
                    'employee_number' => 'DEMO-00'.$number,
                    'position' => ['Teknisi', 'Administrasi', 'Customer Service'][$number - 1],
                ]);

                foreach (range(1, 6) as $daysAgo) {
                    if (($daysAgo + $number) % 4 === 0) {
                        continue;
                    }
                    $date = CarbonImmutable::today()->subDays($daysAgo);
                    Attendance::firstOrCreate([
                        'user_id' => $user->id, 'date' => $date->toDateString(),
                    ], [
                        'check_in_time' => '08:00:00',
                        'check_out_time' => $daysAgo === 1 && $number === 2 ? null : '17:00:00',
                        'check_in_latitude' => -6.2,
                        'check_in_longitude' => 106.816666,
                        'distance' => 0,
                        'status' => 'hadir',
                    ]);
                }
            }

            return $credentials;
        });

        if ($this->command && $credentials !== []) {
            $this->command->warn('Akun demo lokal. Simpan password ini; menjalankan ulang seeder tidak menggantinya.');
            $this->command->table(['Email', 'Password'], $credentials);
        }
    }
}
