<?php

namespace App\Services;

class Geofence
{
    public function distanceInMeters(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $dLat = deg2rad($lat2 - $lat1);
        $dLon = deg2rad($lon2 - $lon1);
        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLon / 2) ** 2;

        // Floating point rounding can otherwise produce sqrt(negative) at antipodes.
        $a = max(0.0, min(1.0, $a));

        return 6371000 * 2 * atan2(sqrt($a), sqrt(1 - $a));
    }
}
