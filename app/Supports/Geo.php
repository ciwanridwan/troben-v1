<?php

namespace App\Supports;

use App\Models\MapMapping;
use App\Models\MapMappingPending;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Geo
{
    public const TEN_MINUTES = 10 * 60;

    public static function cacheKeyBuilder(string $place, $parent = 'georeverse')
    {
        $key = $parent.'.invalid';

        $plc = explode(',', $place);
        if (count($plc) == 2) {
            $keys = [
                self::toXDigit($plc[0], 3),
                self::toXDigit($plc[1], 3)
            ];
            return sprintf($parent.'.%s', md5(implode(',', $keys)));
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

    public static function getReverse(string $coord) {
        $k = self::cacheKeyBuilder($coord);
        if (Cache::has($k)) {
            if ($k == 'georeverse.invalid') {
                $result = '';
            } else {
                $result = Cache::get($k);
            }
        } else {
            $result = self::callReverseService($coord);
            Cache::put($k, $result, self::TEN_MINUTES);
        }

        return $result;
    }

    public static function getReverseMeta(string $coord) {
        $k = self::cacheKeyBuilder($coord, 'geometa');
        if (Cache::has($k)) {
            if ($k == 'geometa.invalid') {
                $result = null;
            } else {
                $result = Cache::get($k);
            }
        } else {
            $result = self::callReverseService($coord, true);
            if ($result == null) return null;

            Cache::put($k, $result, self::TEN_MINUTES);
        }

        return $result;
    }

    public static function getRegional(string $coord, bool $get_subdistrict = false)
    {
        $coordExp = explode(',', $coord);
        if (count($coordExp) != 2) return null;

        $result = self::getReverseMeta($coord);
        if (is_null($result)) return null;

        $comps = collect($result);

        $province_check = $comps->filter(function($r) { return in_array('administrative_area_level_1', $r->types); })->first();
        $province = null;
        if (! is_null($province_check)) {
            $province = [
                'k' => $province_check->place_id,
                'v' => collect($province_check->address_components)->filter(function($r) { return in_array('administrative_area_level_1', $r->types); })->first()->long_name,
            ];
        }

        $regency_check = $comps->filter(function($r) { return in_array('administrative_area_level_2', $r->types); })->first();
        $regency = null;
        if (! is_null($regency_check)) {
            $regency = [
                'k' => $regency_check->place_id,
                'v' => collect($regency_check->address_components)->filter(function($r) { return in_array('administrative_area_level_2', $r->types); })->first()->long_name,
            ];
        }

        $district_check = $comps->filter(function($r) { return in_array('administrative_area_level_3', $r->types); })->first();
        $district = null;
        if (! is_null($district_check)) {
            $district = [
                'k' => $district_check->place_id,
                'v' => collect($district_check->address_components)->filter(function($r) { return in_array('administrative_area_level_3', $r->types); })->first()->long_name,
            ];
        }

        $subdistrict_check = $comps->filter(function($r) { return in_array('administrative_area_level_4', $r->types); })->first();
        $subdistrict = null;
        if (! is_null($subdistrict_check)) {
            $subdistrict = [
                'k' => $subdistrict_check->place_id,
                'v' => collect($subdistrict_check->address_components)->filter(function($r) { return in_array('administrative_area_level_4', $r->types); })->first()->long_name,
            ];
        }

        if ($province == null && $regency == null && $district == null) return null;

        $key = 'province';
        $provinceRegional = null;
        if ($province != null) {
            $provinceRegional = self::findInDB($key, $province, $coord);
        }
        // no province match, terminate func
        if ($provinceRegional == null) return null;

        $provinceId = $provinceRegional->regional_id;
        $key = 'regency';
        $regencyRegional = null;
        if ($regency != null) {
            $regencyRegional = self::findInDB($key, $regency, $coord, $provinceId);
        }
        // no regency match, terminate func
        if ($regencyRegional == null) return null;

        $regencyId = $regencyRegional->regional_id;
        $key = 'district';
        $districtRegional = null;
        if ($district != null) {
            $districtRegional = self::findInDB($key, $district, $coord, $provinceId, $regencyId);
        }
        if ($districtRegional == null) { // district still null, fallback to province level
            $districtRegional = self::findInDB($key, $district, $coord, $provinceId);
        }

        // no regency match, terminate func
        if ($districtRegional == null) return null;
        $districtId = $districtRegional->regional_id;

        $result = [
            'province' => $provinceId,
            'regency' => $regencyId,
            'district' => $districtId,
        ];

        if ($get_subdistrict) {
            // fetch subdistrict too
            $key = 'subdistrict';
            $subdistrictRegional = null;
            if ($subdistrict != null) {
                $subdistrictRegional = self::findInDB($key, $district, $coord, $provinceId, $regencyId, $districtId);
            }
            if ($subdistrictRegional == null) { // subdistrict still null, fallback to regency level
                $subdistrictRegional = self::findInDB($key, $district, $coord, $provinceId, $regencyId);
            }
            if ($subdistrictRegional == null) { // subdistrict still null, fallback to province level
                $subdistrictRegional = self::findInDB($key, $district, $coord, $provinceId);
            }
            if ($subdistrictRegional == null) return null;
            $subdistrictId = $subdistrictRegional->regional_id;

            $result['subdistrict'] = $subdistrictId;
        }

        return $result;
    }

    public static function callReverseService(string $coord, bool $isRaw = false)
    {
        $url = sprintf('https://maps.googleapis.com/maps/api/geocode/json?latlng=%s&key=%s&language=id', $coord, config('services.maps'));
        $request = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->get($url);
        $response = json_decode($request->body());
        
        if ($isRaw) {
            // err checker on raw
            if ($response === null && json_last_error() !== JSON_ERROR_NONE) {
                Log::error('reversegeozero', ['coord' => $coord, 'response' => $request->body()]);
                return null;
            }

            if (isset($response->error_message)) {
                Log::error('reversegeozero', ['coord' => $coord, 'response' => $response]);
                return null;
            }

            if (isset($response->results) && count($response->results)) {
                return $response->results;
            }

            return null;
        }

        // err checker
        if ($response === null && json_last_error() !== JSON_ERROR_NONE) {
            Log::error('reversegeozero', ['coord' => $coord, 'response' => $request->body()]);
            return [
                'address' => $coord,
                'placeid' => '',
            ];
        }

        if (isset($response->results)
            && count($response->results)
            && isset($response->results[0]->formatted_address)
            && isset($response->results[0]->place_id)) {
            return [
                'address' => $response->results[0]->formatted_address,
                'placeid' => $response->results[0]->place_id,
            ];
        } else {
            Log::error('reversegeozero', ['coord' => $coord, 'response' => $response]);
            return [
                'address' => $coord,
                'placeid' => '',
            ];
        }
    }

    private static function regionCleaner(string $str)
    {
        $blacklist = [
            'Kota',
            'Kecamatan',
            'Kabupaten'
        ];

        foreach($blacklist as $b) {
            $str = str_replace($b, '', $str);
        }

        $str = preg_replace("/[^A-Za-z0-9 ]/", '', $str);

        $str = trim($str);

        return $str;
    }

    private static function findInDB($type, $place, $coord, $provinceId = null, $regencyId = null, $districtId = null)
    {
        // find in mapping first
        $result = MapMapping::where('google_placeid', $place['k'])->first();
        if (! is_null($result)) return $result;

        switch ($type) {
            case 'province': $t = 'geo_provinces'; break;
            case 'regency': $t = 'geo_regencies'; break;
            case 'district': $t = 'geo_districts'; break;
            case 'subdistrict': $t = 'geo_sub_districts'; break;
            default: throw new \Exception('Invalid type'); break;
        }

        // first check in local db
        $result = DB::table($t)->where('name', 'ilike', '%'.self::regionCleaner($place['v']).'%');

        if ($provinceId != null) $result = $result->where('province_id', $provinceId);
        if ($regencyId != null) $result = $result->where('regency_id', $regencyId);
        if ($districtId != null) $result = $result->where('district_id', $districtId);

        $regionLocal = $result->first();
        if ($regionLocal != null) {
            // if exist, find it and create to table mapping
            $result = MapMapping::firstOrCreate([
                'level' => $type,
                'regional_id' => $regionLocal->id,
            ], [
                'google_placeid' => $place['k'],
                'google_name' => $place['v'],
                'name' => $regionLocal->name,
            ]);

            return $result;
        }

        // not found in local, fallback save to pending
        list($lat, $lon) = explode(',', $coord);
        MapMappingPending::create([
            'level' => $type,
            'google_name' => $place['v'],
            'google_placeid' => $place['k'],
            'lat' => $lat,
            'lon' => $lon,
        ]);

        return null;
    }
}
