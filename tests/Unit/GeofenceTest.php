<?php

use App\Services\Geofence;

test('identical coordinates have zero distance', function () {
    expect((new Geofence)->distanceInMeters(-6.2, 106.8, -6.2, 106.8))->toBe(0.0);
});

test('one degree at the equator is about 111195 meters', function () {
    $distance = (new Geofence)->distanceInMeters(0, 0, 0, 1);
    expect(abs($distance - 111194.9266))->toBeLessThan(0.01);
});

test('antipodal coordinates remain finite', function () {
    $distance = (new Geofence)->distanceInMeters(-6.2, 106.8, 6.2, -73.2);
    expect(is_finite($distance))->toBeTrue();
    expect(abs($distance - M_PI * 6371000))->toBeLessThan(1.0);
});
