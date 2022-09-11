<?php

namespace App\Supports;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DistanceMatrix
{
    public const TEN_MINUTES = 10 * 60;

    public static function cacheKeyBuilder(string $origin, string $destination)
    {
        $key = 'distance.invalid';

        $org = explode(',', $origin);
        $dist = explode(',', $destination);
        if (count($org) == 2 && count($dist)) {
            $keys = [self::toXDigit($org[0], 3), self::toXDigit($org[1], 3), self::toXDigit($dist[0], 3), self::toXDigit($dist[1], 3)];
            return sprintf('distance.%s', md5(implode(',', $keys)));
        }

        return $key;
    }

    public static function toXDigit($n, $precision = 2, $ts = ',')
    {
        try {
            $n = number_format($n, $precision, '.', $ts);
        } catch (\Exception $e) {
            // ignore
            return $n;
        }
        return $n;
    }

    public static function calculateDistance(string $origin, string $destination)
    {
        $k = DistanceMatrix::cacheKeyBuilder($origin, $destination);

        if ($k == 'distance.invalid') return 0;
        if (Cache::has($k)) return Cache::get($k);

        $distance = DistanceMatrix::callDistanceMatrix($origin, $destination);
        Cache::put($k, $distance, DistanceMatrix::TEN_MINUTES);

        return $distance;
    }


    protected static function callDistanceMatrix(string $origin, string $destination)
    {
        $headers = [
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ];
        $url = sprintf('https://maps.googleapis.com/maps/api/distancematrix/json?destinations=%s&origins=%s&key=%s&units=metric', $destination, $origin, config('services.maps'));

        $response = Http::withHeaders($headers)->get($url);

        $result = json_decode($response->body());

        if ($response->status() != 200) {
            Log::info('distance400', ['dest' => $destination, 'origin' => $origin, 'body' => $response->body(), 'status' => $response->status(), 'ok' => $response->ok()]);
        }

        $distance = 0;
        if (count($result->rows)
            && count($result->rows[0]->elements)
            && isset($result->rows[0]->elements[0]->distance)) {
            $distance = $result->rows[0]->elements[0]->distance->value;
            $distance = $distance / 1000;
        } else {
            Log::info('distancezero', ['dest' => $destination, 'origin' => $origin, 'response' => json_decode($response->body(), true)]);
        }

        return $distance;
    }
}
