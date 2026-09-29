<?php

namespace App\Services;

class GeoLocationService
{
    /**
     * Hitung jarak antara dua koordinat GPS menggunakan formula Haversine (dalam satuan meter).
     */
    public function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): float
    {
        $earthRadius = 6371000; // Radius rata-rata bumi dalam meter

        $latFrom = deg2rad($lat1);
        $lonFrom = deg2rad($lon1);
        $latTo = deg2rad($lat2);
        $lonTo = deg2rad($lon2);

        $latDelta = $latTo - $latFrom;
        $lonDelta = $lonTo - $lonFrom;

        $angle = 2 * asin(sqrt(
            pow(sin($latDelta / 2), 2) +
            cos($latFrom) * cos($latTo) * pow(sin($lonDelta / 2), 2)
        ));

        return round($angle * $earthRadius, 2);
    }

    /**
     * Periksa apakah posisi pengguna berada di dalam radius yang diizinkan.
     */
    public function isWithinRadius(float $userLat, float $userLon, float $branchLat, float $branchLon, float $radiusMeters): bool
    {
        return $this->calculateDistance($userLat, $userLon, $branchLat, $branchLon) <= $radiusMeters;
    }
}
