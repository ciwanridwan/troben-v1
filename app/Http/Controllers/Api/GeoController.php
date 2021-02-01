<?php

namespace App\Http\Controllers\Api;

use App\Models\Geo\Country;
use App\Models\Geo\District;
use App\Models\Geo\Province;
use App\Http\Controllers\Controller;
use App\Models\Geo\Regency;
use App\Models\Geo\SubDistrict;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class GeoController extends Controller
{
    const TYPE_COUNTRY = Country::class;
    const TYPE_PROVINCE = Province::class;
    const TYPE_REGENCY = Regency::class;
    const TYPE_DISTRICT = District::class;
    const TYPE_SUB_DISTRICT = SubDistrict::class;

    /**
     * Filtered attributes.
     *
     * @var array
     */
    protected array $attributes  = [];

    /**
     * Get list of geographical information
     * Route Path       : {API_DOMAIN}/geo
     * Route Method     : GET
     * Route Name       : api.geo
     *
     * @param \Illuminate\Http\Request $request
     *
     * @return \Illuminate\Http\JsonResponse
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request): JsonResponse
    {
        $this->attributes = Validator::make($request->all(), [
            'type' => [ 'required', Rule::in([
                'country', 'province', 'regency', 'district', 'sub_district'
            ])],
            'q' => 'string|nullable',
            'country_id' => 'nullable',
            'province_id'=> 'nullable',
            'regency_id' => 'nullable',
            'district_id' => 'nullable',
            'id' => 'nullable'
        ])->validate();

        switch($this->attributes['type'])
        {
            case 'country':
                return $this->getCountries();
            case 'province':
                return $this->getProvinces();
            case 'regency':
                return $this->getRegencies();
            case 'district':
                return $this->getDistricts();
            case 'sub_district':
                return $this->getSubDistricts();
        }
    }

    /**
     * Get list countries.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getCountries(): JsonResponse
    {
        $query = $this->getBasicBuilder(with(self::TYPE_COUNTRY)->query());

        return response()->json($query->paginate(request('per_page', 15)));
    }

    /**
     * Get list of provinces.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getProvinces(): JsonResponse
    {
        $query = $this->getBasicBuilder(with(self::TYPE_PROVINCE)->query());
        $query->when(request()->has('country_id'), fn($q) => $q->where('country_id', $this->attributes['country_id']));

        return response()->json($query->paginate(request('per_page', 15)));
    }

    /**
     * Get Regencies.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getRegencies(): JsonResponse
    {
        $query = $this->getBasicBuilder(with(self::TYPE_REGENCY)->query());
        $query->when(request()->has('country_id'), fn($q) => $q->where('country_id', $this->attributes['country_id']));
        $query->when(request()->has('province_id'), fn($q) => $q->where('province_id', $this->attributes['province_id']));

        return response()->json($query->paginate(request('per_page', 15)));
    }

    /**
     * Get list of districts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getDistricts(): JsonResponse
    {
        $query = $this->getBasicBuilder(with(self::TYPE_DISTRICT)->query());

        $query->when(request()->has('country_id'), fn($q) => $q->where('country_id', $this->attributes['country_id']));
        $query->when(request()->has('province_id'), fn($q) => $q->where('province_id', $this->attributes['province_id']));
        $query->when(request()->has('regency_id'), fn($q) => $q->where('regency_id', $this->attributes['regency_id']));

        return response()->json($query->paginate(request('per_page', 15)));
    }

    /**
     * Get list of sub districts.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function getSubDistricts(): JsonResponse
    {
        $query = $this->getBasicBuilder(with(self::TYPE_SUB_DISTRICT)->query());

        $query->when(request()->has('country_id'), fn($q) => $q->where('country_id', $this->attributes['country_id']));
        $query->when(request()->has('province_id'), fn($q) => $q->where('province_id', $this->attributes['province_id']));
        $query->when(request()->has('regency_id'), fn($q) => $q->where('regency_id', $this->attributes['regency_id']));
        $query->when(request()->has('district_id'), fn($q) => $q->where('district_id', $this->attributes['district_id']));

        return response()->json($query->paginate(request('per_page', 15)));
    }

    /**
     * Get Basic Builder.
     *
     * @param \Illuminate\Database\Eloquent\Builder $builder
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    private function getBasicBuilder(Builder $builder): Builder
    {
        $builder->when(request()->has('id'), fn ($q) => $q->where('id', $this->attributes['id']));
        $builder->when(request()->has('q') and request()->has('id') === false,
            fn ($q) => $q->where('name', 'like', '%'.$this->attributes['q'].'%')
        );

        return $builder;
    }
}
