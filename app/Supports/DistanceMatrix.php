<?php

namespace App\Supports;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DistanceMatrix
{
    public static function cacheKeyBuilder(string $origin, string $destination)
    {
        $key = 'distance.invalid';

        $org = explode(',', $origin);
        $dist = explode(',', $destination);
        if (count($org) == 2 && count($dist)) {
            $keys = [ self::to2Digit($org[0]), self::to2Digit($org[1]), self::to2Digit($dist[0]), self::to2Digit($dist[1]) ];
            return sprintf('distance.%s', md5(implode(',', $keys)));
        }

        return $key;
    }

    public static function to2Digit($n)
    {
        try {
            return number_format($n, 2);
        } catch (\Exception $e) {
            // ignore
        }
        return $n;
    }

    public static function calculateDistance(string $origin, string $destination)
    {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->get('https://maps.googleapis.com/maps/api/distancematrix/json?destinations='.$destination.'&origins='.$origin.'&units=metric&key=AIzaSyAo47e4Aymv12UNMv8uRfgmzjGx75J1GVs');
        $response = json_decode($response->body());

        $distance = 0;
        if (count($response->rows)
            && count($response->rows[0]->elements)
            && isset($response->rows[0]->elements[0]->distance)) {
            $distance = $response->rows[0]->elements[0]->distance->text;
            $distance = str_replace('km', '', $distance);
            $distance = str_replace(',', '', $distance);
            $distance = (float) $distance;
        } else {
            Log::info('distancezero', ['dest' => $destination, 'origin' => $origin]);
        }

        return $distance;
    }
}
