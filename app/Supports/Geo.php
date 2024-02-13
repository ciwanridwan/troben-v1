<?php

namespace App\Supports;

use App\Models\Geo\SubDistrict;
use App\Models\MapMapping;
use App\Models\MapMappingPending;
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

    public static function getReverse(string $coord)
    {
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

    public static function getReverseMeta(string $coord)
    {
        $k = self::cacheKeyBuilder($coord, 'geometa');
        if (Cache::has($k)) {
            if ($k == 'geometa.invalid') {
                $result = null;
            } else {
                $result = Cache::get($k);
            }
        } else {
            $result = self::callReverseService($coord, true);
            if ($result == null) {
                return null;
            }

            Cache::put($k, $result, self::TEN_MINUTES);
        }

        return $result;
    }

    public static function getRegional(string $coord, bool $get_subdistrict = false)
    {
        $coordExp = explode(',', $coord);
        if (count($coordExp) != 2) {
            Log::info('failed-geo-fase-1', [$coord]);
            return null;
        }

        $result = self::getReverseMeta($coord);
        if (is_null($result)) {
            Log::info('failed-geo-fase-2', [$coord]);
            return null;
        }

        $comps = collect($result);

        $province_check = $comps->filter(function ($r) {
            return in_array('administrative_area_level_1', $r->types);
        })->first();
        $province = null;
        if (! is_null($province_check)) {
            $province = [
                'k' => $province_check->place_id,
                'v' => collect($province_check->address_components)->filter(function ($r) {
                    return in_array('administrative_area_level_1', $r->types);
                })->first()->long_name,
            ];
        }

        $regency_check = $comps->filter(function ($r) {
            return in_array('administrative_area_level_2', $r->types);
        })->first();
        $regency = null;
        if (! is_null($regency_check)) {
            $regency = [
                'k' => $regency_check->place_id,
                'v' => collect($regency_check->address_components)->filter(function ($r) {
                    return in_array('administrative_area_level_2', $r->types);
                })->first()->long_name,
            ];
        }

        $district_check = $comps->filter(function ($r) {
            return in_array('administrative_area_level_3', $r->types);
        })->first();
        $district = null;
        if (! is_null($district_check)) {
            $district = [
                'k' => $district_check->place_id,
                'v' => collect($district_check->address_components)->filter(function ($r) {
                    return in_array('administrative_area_level_3', $r->types);
                })->first()->long_name,
            ];
        }

        $subdistrict_check = $comps->filter(function ($r) {
            return in_array('administrative_area_level_4', $r->types);
        })->first();
        $subdistrict = null;
        if (! is_null($subdistrict_check)) {
            $subdistrict = [
                'k' => $subdistrict_check->place_id,
                'v' => collect($subdistrict_check->address_components)->filter(function ($r) {
                    return in_array('administrative_area_level_4', $r->types);
                })->first()->long_name,
            ];
        }

        // cannot find, try plus code method
        if ($province == null && $regency == null && $district == null) {
            if (is_null($subdistrict_check)) {
                $plus_code_check = $comps->filter(function($r) {
                    return in_array('plus_code', $r->types);
                })->first();
                if (is_null($plus_code_check)) {
                    Log::info('failed-geo-fase-3', [$coord]);
                    return null;
                }

                $plus_code_name = collect($plus_code_check->address_components)->filter(function($r) {
                    return in_array('administrative_area_level_4', $r->types);
                })->first()->long_name ?? '';

                MapMappingPending::create([
                    'level' => 'place',
                    'google_name' => $plus_code_check->place_id,
                    'google_placeid' => '[ '. $plus_code_name .' ]'.$plus_code_check->formatted_address,
                    'lat' => $coordExp[0],
                    'lon' => $coordExp[1],
                ]);

                $plus_code_find = MapMapping::query()
                    ->where('level', 'subdistrict')
                    ->where(function($q) use ($plus_code_name, $plus_code_check) {
                        $q->where('google_placeid', $plus_code_check->place_id)
                        ->orWhere('google_name', $plus_code_name);
                    })
                    ->first();
                if (is_null($plus_code_find)) {
                    Log::info('failed-geo-fase-4', [$coord]);
                    return null;
                }

                $geo_find = SubDistrict::find($plus_code_find->regional_id);
                if (is_null($geo_find)) {
                    Log::info('failed-geo-fase-5', [$coord]);
                    return null;
                }

                // plus code found
                $result = [
                    'province' => $geo_find->province_id,
                    'regency' => $geo_find->regency_id,
                    'district' => $geo_find->district_id,
                    'subdistrict' => $geo_find->id,
                ];

                return $result;
            }
            
        } 

        $key = 'province';
        $provinceRegional = null;
        if ($province != null) {
            $provinceRegional = self::findInDB($key, $province, $coord);
        }
        // no province match, terminate func
        if ($provinceRegional == null) {
            Log::info('failed-geo-fase-6', [$coord]);
            return null;
        }

        $provinceId = $provinceRegional->regional_id;
        $key = 'regency';
        $regencyRegional = null;
        if ($regency != null) {
            $regencyRegional = self::findInDB($key, $regency, $coord, $provinceId);
        }
        // no regency match, terminate func
        if ($regencyRegional == null) {
            Log::info('failed-geo-fase-7', [$coord]);
            return null;
        }

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
        if ($districtRegional == null) {
            Log::info('failed-geo-fase-8', [$coord]);
            return null;
        }
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
                $subdistrictRegional = self::findInDB($key, $subdistrict, $coord, $provinceId, $regencyId, $districtId);
            }
            if ($subdistrictRegional == null) { // subdistrict still null, fallback to regency level
                $subdistrictRegional = self::findInDB($key, $subdistrict, $coord, $provinceId, $regencyId);
            }
            if ($subdistrictRegional == null) { // subdistrict still null, fallback to province level
                $subdistrictRegional = self::findInDB($key, $subdistrict, $coord, $provinceId);
            }
            if ($subdistrictRegional == null) {
                Log::info('failed-geo-fase-9', [$coord]);
                return null;
            }
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

    public static function callGeocodingService(string $address)
    {
        $url = sprintf('https://maps.googleapis.com/maps/api/geocode/json?address=%s&key=%s&language=id', $address, config('services.maps'));
        $request = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json'
        ])->get($url);
        $response = json_decode($request->body());

        // err checker
        if ($response === null && json_last_error() !== JSON_ERROR_NONE) {
            Log::error('geocodezero', ['address' => $address, 'response' => $request->body()]);
            return [
                'lat' => 0,
                'lon' => 0,
            ];
        }

        if (isset($response->results)
            && count($response->results)
            && isset($response->results[0]->geometry)) {
            return [
                'lat' => $response->results[0]->geometry->location->lat,
                'lon' => $response->results[0]->geometry->location->lng,
            ];
        } else {
            Log::error('geocodezero', ['address' => $address, 'response' => $response]);
            return [
                'lat' => 0,
                'lon' => 0,
            ];
        }
    }

    public static function findInDB($type, $place, $coord, $provinceId = null, $regencyId = null, $districtId = null)
    {
        // find in mapping first
        $result = MapMapping::where('google_placeid', $place['k'])->first();
        if (! is_null($result)) {
            return $result;
        }

        switch ($type) {
            case 'province': $t = 'geo_provinces';
                break;
            case 'regency': $t = 'geo_regencies';
                break;
            case 'district': $t = 'geo_districts';
                break;
            case 'subdistrict': $t = 'geo_sub_districts';
                break;
            default: throw new \Exception('Invalid type');
                break;
        }

        // first check in local db
        $placeName = self::regionCleaner($place['v']);
        $result = DB::table($t)->whereRaw("REPLACE(name, ' ', '') ilike '%".$placeName."%'");

        if ($provinceId != null) {
            $result = $result->where('province_id', $provinceId);
        }
        if ($regencyId != null) {
            $result = $result->where('regency_id', $regencyId);
        }
        if ($districtId != null) {
            $result = $result->where('district_id', $districtId);
        }

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

    private static function regionCleaner(string $str)
    {
        $blacklist = [
            'Kota',
            'Kecamatan',
            'Kabupaten',
            'Distrik',
        ];

        foreach ($blacklist as $b) {
            $str = str_replace($b, '', $str);
        }

        $str = preg_replace('/[^A-Za-z0-9 ]/', '', $str);

        $str = str_replace(' ', '', $str);
        $str = trim($str);

        return $str;
    }
}
