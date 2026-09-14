<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Models\User;

test('employee accounts cannot access admin dashboard or change the office', function () {
    $this->actingAs(User::factory()->create(['role' => 'employee']))
        ->get('/admin/dashboard')->assertForbidden();
    $this->post('/admin/office', [])->assertForbidden();
});

test('dashboard supports an empty database without dividing by zero', function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']))
        ->get('/admin/dashboard')->assertOk()
        ->assertViewHas('attendanceRate', 0)
        ->assertViewHas('pendingEmployees', 0)
        ->assertSee('Belum ada check in hari ini.');
});

test('dashboard metrics use employee accounts and real attendance records', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    foreach ([1, 2, 3] as $number) {
        $staff = User::factory()->create(['role' => 'employee']);
        Employee::create([
            'user_id' => $staff->id, 'name' => $staff->name, 'employee_number' => 'TEST-'.$number,
        ]);
        if ($number < 3) {
            Attendance::create([
                'user_id' => $staff->id, 'date' => today()->toDateString(),
                'check_in_time' => '08:00:00',
                'check_out_time' => $number === 1 ? '17:00:00' : null,
            ]);
        }
    }
    Attendance::create([
        'user_id' => $admin->id, 'date' => today()->toDateString(), 'check_in_time' => '08:00:00',
    ]);
    $this->actingAs($admin)->get('/admin/dashboard')->assertOk()
        ->assertViewHas('totalEmployees', 3)
        ->assertViewHas('todayAttendances', 2)
        ->assertViewHas('completedAttendances', 1)
        ->assertViewHas('pendingEmployees', 1)
        ->assertViewHas('onsiteEmployees', 1)
        ->assertViewHas('trend', fn ($trend) => $trend->count() === 7);
});

test('office settings validate ranges and retain existing values on failure', function () {
    $office = OfficeLocation::create([
        'office_name' => 'Original', 'latitude' => -6.2, 'longitude' => 106.8, 'radius' => 100,
    ]);
    $this->actingAs(User::factory()->create(['role' => 'admin']));
    $this->from('/admin/office')->post('/admin/office', [
        'office_name' => 'Invalid', 'latitude' => 91, 'longitude' => -181, 'radius' => 0,
    ])->assertSessionHasErrors(['latitude', 'longitude', 'radius']);
    expect($office->fresh()->office_name)->toBe('Original');
    $this->post('/admin/office', [
        'office_name' => 'Updated', 'latitude' => -6.21, 'longitude' => 106.81, 'radius' => 200,
    ])->assertSessionHasNoErrors()->assertRedirect();
    expect($office->fresh()->office_name)->toBe('Updated');
    $this->assertDatabaseCount('office_locations', 1);
});
