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
        $url = sprintf('https://maps.googleapis.com/maps/api/distancematrix/json?destinations=%s&origins=%s&key=%s&units=metric', $destination, $origin, config('services.maps'));
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
    ])->get($url);

	try {
	Log::info('distancebody', ['b' => $response->status(), 'r' => $response]);
            Log::info('distance400', ['dest' => $destination, 'origin' => $origin, 'body' => $response->body(), 'status' => $response->status(), 'ok' => $response->ok()]);
            return 0;

        $response = json_decode($response->body());
	} catch (\Exception $e) {
		report($e);
		dd($response);
	}

        $distance = 0;
        if (count($response->rows)
            && count($response->rows[0]->elements)
            && isset($response->rows[0]->elements[0]->distance)) {
            $distance = $response->rows[0]->elements[0]->distance->value;
            $distance = $distance / 1000;
        } else {
            Log::info('distancezero', ['dest' => $destination, 'origin' => $origin, 'response' => json_decode($response->body(), true)]);
        }

        return $distance;
    }
}
