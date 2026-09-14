<?php

use App\Models\Attendance;
use App\Models\User;
use Database\Seeders\DemoSeeder;

test('demo seeding is repeatable without changing passwords or duplicating attendance', function () {
    $this->seed(DemoSeeder::class);
    $count = Attendance::count();
    $password = User::where('email', 'admin@example.test')->firstOrFail()->password;
    $this->seed(DemoSeeder::class);
    $this->assertDatabaseCount('users', 4);
    $this->assertDatabaseCount('employees', 3);
    expect(Attendance::count())->toBe($count);
    expect(User::where('email', 'admin@example.test')->firstOrFail()->password)->toBe($password);
});

test('demo seeding is disabled in production', function () {
    $this->app->instance('env', 'production');
    $this->expectException(RuntimeException::class);
    $this->seed(DemoSeeder::class);
});
