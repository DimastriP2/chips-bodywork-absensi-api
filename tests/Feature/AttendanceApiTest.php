<?php

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OfficeLocation;
use App\Models\User;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    $this->travelTo(CarbonImmutable::parse('2026-09-14 08:00:00', 'Asia/Jakarta'));
    $this->staff = User::factory()->create(['role' => 'employee']);
    Employee::create([
        'user_id' => $this->staff->id,
        'name' => $this->staff->name,
        'employee_number' => 'CB-001',
    ]);
    $this->office = OfficeLocation::create([
        'office_name' => 'Demo office',
        'latitude' => -6.2,
        'longitude' => 106.816666,
        'radius' => 100,
    ]);
    $this->coordinates = ['latitude' => -6.2, 'longitude' => 106.816666];
});

afterEach(function () {
    $this->travelBack();
});

test('attendance endpoints require authentication even without an Accept header', function () {
    $this->get('/api/attendance/today')->assertUnauthorized()
        ->assertHeader('Content-Type', 'application/json');
});

test('only provisioned employees can record attendance', function () {
    Sanctum::actingAs(User::factory()->create(['role' => 'employee']));
    $this->postJson('/api/checkin', $this->coordinates)->assertForbidden();

    Sanctum::actingAs(User::factory()->create(['role' => 'admin']));
    $this->postJson('/api/checkin', $this->coordinates)->assertForbidden();
    $this->assertDatabaseCount('attendances', 0);
});

test('check in uses server time and authenticated identity', function () {
    Sanctum::actingAs($this->staff);
    $other = User::factory()->create();

    $this->postJson('/api/checkin', $this->coordinates + [
        'user_id' => $other->id, 'date' => '2000-01-01', 'check_in_time' => '01:00:00',
    ])->assertCreated()->assertJsonPath('attendance.user_id', $this->staff->id)
        ->assertJsonPath('attendance.date', '2026-09-14')
        ->assertJsonPath('attendance.check_in_time', '08:00:00')
        ->assertJsonPath('attendance.status', 'hadir');

    $this->getJson('/api/attendance/today')->assertOk()->assertJsonPath('state', 'checked_in');
});

test('invalid coordinates never reach distance calculation', function ($coordinates) {
    Sanctum::actingAs($this->staff);
    $this->postJson('/api/checkin', $coordinates)->assertUnprocessable();
    $this->assertDatabaseCount('attendances', 0);
})->with([
    'missing' => [[]],
    'text' => [['latitude' => 'invalid', 'longitude' => 106.8]],
    'latitude range' => [['latitude' => 91, 'longitude' => 106.8]],
    'longitude range' => [['latitude' => -6.2, 'longitude' => -181]],
    'array' => [['latitude' => [], 'longitude' => 106.8]],
]);

test('missing or invalid office configuration prevents attendance', function () {
    Sanctum::actingAs($this->staff);
    $this->office->update(['radius' => -1]);
    $this->postJson('/api/checkin', $this->coordinates)->assertStatus(503);
    $this->office->delete();
    $this->postJson('/api/checkin', $this->coordinates)->assertNotFound();
    $this->assertDatabaseCount('attendances', 0);
});

test('outside radius check in and check out do not write records', function () {
    Sanctum::actingAs($this->staff);
    $outside = ['latitude' => -6.3, 'longitude' => 106.8];
    $this->postJson('/api/checkin', $outside)->assertForbidden();
    $this->assertDatabaseCount('attendances', 0);
    $this->postJson('/api/checkin', $this->coordinates)->assertCreated();
    $this->postJson('/api/checkout', $outside)->assertForbidden();
    expect(Attendance::first()->check_out_time)->toBeNull();
});

test('repeated check in and check out preserve the original times', function () {
    Sanctum::actingAs($this->staff);
    $this->postJson('/api/checkin', $this->coordinates)->assertCreated();
    $this->travelTo(CarbonImmutable::parse('2026-09-14 09:00:00', 'Asia/Jakarta'));
    $this->postJson('/api/checkin', $this->coordinates)->assertConflict();
    $this->travelTo(CarbonImmutable::parse('2026-09-14 17:00:00', 'Asia/Jakarta'));
    $this->postJson('/api/checkout', $this->coordinates)->assertOk();
    $this->travelTo(CarbonImmutable::parse('2026-09-14 18:00:00', 'Asia/Jakarta'));
    $this->postJson('/api/checkout', $this->coordinates)->assertConflict();
    $this->assertDatabaseCount('attendances', 1);
    $this->assertDatabaseHas('attendances', [
        'check_in_time' => '08:00:00', 'check_out_time' => '17:00:00',
    ]);
    $this->getJson('/api/attendance/today')->assertJsonPath('state', 'checked_out');
});

test('check out requires today check in and rejects a backwards server clock', function () {
    Sanctum::actingAs($this->staff);
    $this->postJson('/api/checkout', $this->coordinates)->assertNotFound();
    $this->postJson('/api/checkin', $this->coordinates)->assertCreated();
    $this->travelTo(CarbonImmutable::parse('2026-09-14 07:59:00', 'Asia/Jakarta'));
    $this->postJson('/api/checkout', $this->coordinates)->assertConflict();
});

test('day boundary follows Jakarta even when the reference instant is UTC', function () {
    $this->travelTo(CarbonImmutable::parse('2026-09-14 17:01:00', 'UTC'));
    Sanctum::actingAs($this->staff);
    $this->postJson('/api/checkin', $this->coordinates)->assertCreated()
        ->assertJsonPath('attendance.date', '2026-09-15')
        ->assertJsonPath('attendance.check_in_time', '00:01:00');
});

test('database uniqueness blocks writes that bypass the service', function () {
    Attendance::create(['user_id' => $this->staff->id, 'date' => '2026-09-14']);
    $this->expectException(QueryException::class);
    Attendance::create(['user_id' => $this->staff->id, 'date' => '2026-09-14']);
});

test('history and summary are scoped to the current user and month', function () {
    Attendance::create([
        'user_id' => $this->staff->id, 'date' => '2026-09-13',
        'check_in_time' => '08:00:00', 'check_out_time' => '17:00:00',
    ]);
    Attendance::create([
        'user_id' => $this->staff->id, 'date' => '2026-09-14', 'check_in_time' => '08:00:00',
    ]);
    Attendance::create([
        'user_id' => $this->staff->id, 'date' => '2026-08-31', 'check_in_time' => '08:00:00',
    ]);
    Attendance::create([
        'user_id' => User::factory()->create()->id, 'date' => '2026-09-14',
        'check_in_time' => '08:00:00', 'check_out_time' => '17:00:00',
    ]);

    Sanctum::actingAs($this->staff);
    $this->getJson('/api/history')->assertOk()->assertJsonCount(3, 'attendances');
    $this->getJson('/api/history?month=2026-09&page=1&per_page=1')
        ->assertOk()->assertJsonCount(1, 'attendances')
        ->assertJsonPath('meta.total', 2)->assertJsonPath('meta.last_page', 2)
        ->assertJsonPath('attendances.0.date', '2026-09-14');
    $this->getJson('/api/attendance/summary?month=2026-09')->assertOk()
        ->assertJsonPath('summary.present_days', 2)
        ->assertJsonPath('summary.completed_days', 1)
        ->assertJsonPath('summary.incomplete_days', 1)
        ->assertJsonPath('summary.recorded_minutes', 540);
    $this->getJson('/api/history?per_page=101')->assertUnprocessable();
    $this->getJson('/api/attendance/summary?month=2026-13')->assertUnprocessable();
});

test('empty state and office coordinates are available to mobile clients', function () {
    Sanctum::actingAs($this->staff);
    $this->getJson('/api/attendance/today')->assertOk()
        ->assertJsonPath('state', 'not_checked_in')->assertJsonPath('attendance', null);
    $this->getJson('/api/attendance/summary')->assertJsonPath('summary.present_days', 0);
    $this->getJson('/api/office')->assertOk()
        ->assertJsonPath('office.radius', 100)->assertJsonPath('office.latitude', -6.2);
});
